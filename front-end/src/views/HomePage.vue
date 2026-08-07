<script setup lang="ts">
import NavBar from '@/components/NavBar.vue'
import { reactive, ref, onMounted } from 'vue'
import api from "@/services/api";
import Swal from 'sweetalert2';

interface TicketForm {
  date: string
  plateNumber: string
  driver: string
  company: string
}

interface Ticket {
    id: number;
    control_no: string;
    date: string;
    plate_number: string;
    driver: string;
    company: string;
    amount: number;
}

interface PriceForm {
    amount: number | null;
    effective_date: string;
}

interface PriceHistoryEntry {
    id: number;
    amount: number | string;
    effective_date: string;
}

const form = reactive<TicketForm>({
  date: '',
  plateNumber: '',
  driver: '',
  company: '',
})

const controlNo = ref('')
const loading = ref(false)
const error = ref('')
const tickets = ref<Ticket[]>([]);
const currentPrice = ref(0);
const showModal = ref(false);
const showModalHistory = ref(false);
const priceHistory = ref<PriceHistoryEntry[]>([]);
const selectedPriceId = ref<number | null>(null);
const isEditing = ref(false);
const ticketToPrint = ref<Ticket | null>(null);
const search = ref("")

const priceForm = reactive<PriceForm>({
    amount: null,
    effective_date: ""
});



// --- Add-price modal ---
function openModal(){
    isEditing.value = false;
    selectedPriceId.value = null;
    priceForm.amount = null;
    priceForm.effective_date = "";
    showModal.value = true;
    loadPriceHistory();
}

function closeModal(){
    showModal.value = false;
    isEditing.value = false;
    selectedPriceId.value = null;
    priceForm.amount = null;
    priceForm.effective_date = "";
}

// --- History modal ---
function openModalHistory(){
    showModalHistory.value = true;
    loadPriceHistory();
}

function closeModalHistory(){
    showModalHistory.value = false;
}

// --- Edit flow ---
const editPrice = async (id: number) => {
    selectedPriceId.value = id;
    isEditing.value = true;
    await fetchPrice(id);
    showModal.value = true;
    closeModalHistory();
}

const fetchPrice = async (id: number) => {
    try {
        const response = await api.get(`/price-history/${id}`);
        priceForm.amount = Number(response.data.amount);
        priceForm.effective_date = response.data.effective_date.split('T')[0]; // ← trim to YYYY-MM-DD
    } catch (err) {
        await Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Unable to load price."
        });
    }
};

const updatePrice = async (id: number) => {
    try {
        await api.put(`/price-history/${id}`, {
            amount: priceForm.amount,
            effective_date: priceForm.effective_date
        });

        await Swal.fire({
            icon: "success",
            title: "Success!",
            text: "Price updated successfully."
        });

        await loadPriceHistory();
        await loadCurrentPrice();
        openModalHistory();
        closeModal();

    } catch (err) {
        await Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Update failed."
        });
    }
};

// print function
function printTicket(ticket: Ticket){
    ticketToPrint.value = ticket;

    setTimeout(() =>{
        window.print();
    }, 100);
}

// --- Add flow ---
async function savePrice() {
    try {
        await api.post("/price-history", {
            amount: priceForm.amount,
            effective_date: priceForm.effective_date
        });

        await Swal.fire({
            icon: "success",
            title: "Success!",
            text: "New price has been added."
        });

        await loadCurrentPrice();
        await loadPriceHistory();
        closeModal();

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Unable to save new price."
        });
    }
}

// --- Shared save handler for the modal button ---
async function handleSavePrice() {
    if (isEditing.value && selectedPriceId.value) {
        await updatePrice(selectedPriceId.value);
    } else {
        await savePrice();
    }
}

async function loadPriceHistory(){
    try{
        const response = await api.get('/price-history');
        priceHistory.value = response.data;
    } catch (err){
        await Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Unable to load price history."
        });
    }
}

async function loadCurrentPrice(){
    try {
        const response = await api.get('/current-price');
        currentPrice.value = Number(response.data.amount) || 0;
    } catch (err) {
        console.error('Failed to load current price:', err);
        currentPrice.value = 0;
    }
}

async function loadControlNumber() {
    try {
        const response = await api.get("/tickets/next-control-number");
        controlNo.value = response.data.control_no;
    } catch (err) {
        console.error('Failed to load control number:', err);
    }
}

async function handleSubmit(){
    loading.value = true
    error.value = ""

    try{
        const response = await api.post("/tickets", {
            date: form.date,
            plate_number: form.plateNumber,
            driver: form.driver,
            company: form.company,
        })

        await loadControlNumber();
        await loadTickets();

        console.log(response.data)

        await Swal.fire({
            icon: "success",
            title: "Success!",
            text: "Ticket created successfully.",
            confirmButtonText: "OK",
        });

        resetForm();

    }catch(err){
        console.error(err)
        error.value = "Unable to save ticket."
    }finally{
        loading.value = false
    }
}

function resetForm() {
    form.date = "";
    form.plateNumber = "";
    form.driver = "";
    form.company = "";
}

async function loadTickets() {
    try {
        const response = await api.get("/tickets", {
            params: {
                search: search.value
            }
        });

        tickets.value = response.data;
    } catch (err) {
        console.error("Failed to load tickets:", err);
    }
}

function formatDate(date: string) {
    const d = new Date(date);
    return d.toLocaleDateString("en-US", {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
    });
}

onMounted(() => {
    loadControlNumber();
    loadTickets();
    loadCurrentPrice();
});
</script>

<template>
    <NavBar />
    <form
        @submit.prevent="handleSubmit"
        class="mx-auto flex max-w-md flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm mt-3">
        <div class="text-center">
            <h2 class="text-lg font-bold text-gray-900">QBITS RESOURCES CORPORATION</h2>
            <p class="text-sm text-gray-500">Temporary Vehicle Entry Control</p>
            <p class="text-sm font-semibold text-gray-700">ONE-TIME PASS TICKET</p>
            <p class="text-xs text-gray-400">One-time entry only. No re-entry</p>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 pt-3">
            <span class="text-sm font-medium text-gray-700">Control No.</span>
            <span class="font-mono text-lg font-bold text-red-600">
                {{ controlNo }}
            </span>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700">Amount</label>
            <div class="flex items-center justify-between rounded-md border border-gray-300 bg-gray-100 px-3 py-2">
                <span class="text-sm font-medium text-gray-600">Current Price</span>
                <span class="text-xl font-bold text-green-600">
                    ₱{{ (currentPrice ?? 0).toFixed(2) }}
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-200 pt-3">
            <div class="flex flex-col gap-1">
                <label for="date" class="text-sm font-medium text-gray-700">Date</label>
                <input
                    id="date"
                    v-model="form.date"
                    type="date"
                    required
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
            </div>

            <div class="flex flex-col gap-1">
                <label for="plateNumber" class="text-sm font-medium text-gray-700">Plate Number</label>
                <input
                    id="plateNumber"
                    v-model="form.plateNumber"
                    type="text"
                    required
                    placeholder="ABC-123"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm uppercase focus:border-blue-500 focus:outline-none"
                />
            </div>

            <div class="flex flex-col gap-1">
                <label for="driver" class="text-sm font-medium text-gray-700">Driver's name</label>
                <input
                    id="driver"
                    v-model="form.driver"
                    type="text"
                    required
                    placeholder="Driver's name"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
            </div>

            <div class="flex flex-col gap-1">
                <label for="company" class="text-sm font-medium text-gray-700">Company</label>
                <input
                    id="company"
                    v-model="form.company"
                    type="text"
                    required
                    placeholder="Company name"
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
            </div>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <button
            type="submit"
            :disabled="loading"
            class="mt-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"
        >
            {{ loading ? 'Saving...' : 'Submit' }}
        </button>
    </form>

    <!-- Add / Edit price modal -->
    <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg max-h-[90vh] overflow-y-auto">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold">
                    {{ isEditing ? 'Edit Price' : 'Add New Price' }}
                </h2>
                <button @click="closeModal" class="text-2xl">×</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Effective Date</label>
                    <input
                        v-model="priceForm.effective_date"
                        type="date"
                        class="mt-1 w-full rounded border p-2"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium">Amount</label>
                    <input
                        v-model.number="priceForm.amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 w-full rounded border p-2"
                    >
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button @click="closeModal" class="rounded bg-gray-300 px-4 py-2">
                    Cancel
                </button>
                <button
                    @click="handleSavePrice"
                    :disabled="!priceForm.amount || !priceForm.effective_date"
                    class="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                >
                    {{ isEditing ? 'Update' : 'Save' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Price history modal -->
    <div
        v-if="showModalHistory"
        @click.self="closeModalHistory"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
        <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h2 class="text-xl font-bold text-gray-800">Price History</h2>
                <button
                    @click="closeModalHistory"
                    class="text-2xl font-bold text-gray-400 hover:text-red-500"
                >
                    &times;
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto p-6">
                <div
                    v-if="priceHistory.length === 0"
                    class="rounded-lg border border-dashed border-gray-300 py-8 text-center text-gray-500"
                >
                    No price history available.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="entry in priceHistory"
                        :key="entry.id"
                        class="flex items-center justify-between rounded-lg border bg-gray-50 p-4 transition hover:bg-blue-50"
                    >
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">
                                Effective Date
                            </p>
                            <p class="font-medium text-gray-800">
                                {{ formatDate(entry.effective_date) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-gray-500">
                                Amount
                            </p>
                            <p class="text-xl font-bold text-green-600">
                                ₱{{ Number(entry.amount).toFixed(2) }}
                            </p>
                            <button
                                type="button"
                                @click="editPrice(entry.id)"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white mt-3"
                            >
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t bg-gray-50 px-6 py-4">
                <button
                    @click="closeModalHistory"
                    class="rounded-lg bg-blue-600 px-5 py-2 font-medium text-white transition hover:bg-blue-500"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <div class="p-6 overflow-hidden rounded-xl border border-gray-300 shadow mt-3">
        <button type="button" @click="openModal" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white mb-3">
            Add New Price
        </button>

        <button type="button" @click="openModalHistory" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white mb-3 ml-3">
            View Price History
        </button>
        <input v-model="search" @input="loadTickets" type="text" placeholder="Search control no., driver etc..." class="w-80 rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none ml-3"/>
        <table class="min-w-full border border-gray-300">
            <thead class="bg-blue-500">
                <tr>
                    <th class="border border-black p-2 text-white">Control No.</th>
                    <th class="border border-black p-2 text-white">Date</th>
                    <th class="border border-black p-2 text-white">Plate Number</th>
                    <th class="border border-black p-2 text-white">Driver's Name</th>
                    <th class="border border-black p-2 text-white">Company</th>
                    <th class="border border-black p-2 text-white">Amount</th>
                    <th class="border border-black p-2 text-white">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="ticket in tickets" :key="ticket.id">
                    <td class="border p-2">{{ ticket.control_no }}</td>
                    <td class="border p-2">{{ formatDate(ticket.date) }}</td>
                    <td class="border p-2">{{ ticket.plate_number }}</td>
                    <td class="border p-2">{{ ticket.driver }}</td>
                    <td class="border p-2">{{ ticket.company }}</td>
                    <td class="border p-2">₱{{ Number(ticket.amount).toFixed(2) }}</td>
                    <td class="border p-2 text-center"> 
                        <button type="button" @click="printTicket(ticket)" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white text-xl">🖨️</button>
                        <button type="button" @click="" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white text-xl ml-3">🗑️</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <!-- Printable ticket (hidden on screen, shown only when printing) -->
     <Teleport to="body">
        <div id="print-ticket" v-if="ticketToPrint">
            <div class="ticket-header">
                <h1>QBITS RESOURCES CORPORATION</h1>
                <p>Temporary Vehicle Entry Control</p>
                <p class="bold">ONE-TIME PASS TICKET</p>
                <p class="small">One-time entry only. No re-entry</p>
            </div>

            <div class="control-row">
                <span>CONTROL NO.:</span>
                <span class="control-no">{{ ticketToPrint.control_no }}</span>
            </div>

            <div class="amount">
                ₱{{ Number(ticketToPrint.amount).toFixed(2) }}
            </div>

            <div class="field-row">
                <span>DATE</span>
                <span>:</span>
                <span>{{ formatDate(ticketToPrint.date) }}</span>
            </div>

            <div class="field-row">
                <span>PLATE NUMBER</span>
                <span>:</span>
                <span>{{ ticketToPrint.plate_number }}</span>
            </div>

            <div class="field-row">
                <span>DRIVER'S NAME</span>
                <span>:</span>
                <span>{{ ticketToPrint.driver }}</span>
            </div>

            <div class="field-row">
                <span>COMPANY</span>
                <span>:</span>
                <span>{{ ticketToPrint.company }}</span>
            </div>
        </div>
    </Teleport>
</template>

<style>
#print-ticket {
    display: none;
}

@media print {
    body > *:not(#print-ticket) {
        display: none !important;
    }

    #print-ticket {
        display: block !important;
        position: static;   
        width: 4in;
        height: 4in;
        padding: 0.25in;
        font-family: Arial, sans-serif;
        color: #000;
        box-sizing: border-box;
    }

    @page {
        size: 4in 4in;
        margin: 0;
    }


    .ticket-header {
        text-align: center;
        border-bottom: 1px dashed #000;
        padding-bottom: 8px;
        margin-bottom: 8px;
    }

    .ticket-header h1 {
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 4px 0;
    }

    .ticket-header p {
        font-size: 11px;
        margin: 2px 0;
    }

    .ticket-header .bold {
        font-weight: bold;
        font-size: 13px;
    }

    .ticket-header .small {
        font-size: 9px;
        color: #444;
    }

    .control-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        font-size: 13px;
        font-weight: bold;
        margin: 8px 0;
    }

    .control-no {
        color: #c00;
        font-size: 18px;
        letter-spacing: 2px;
    }

    .amount {
        font-size: 32px;
        font-weight: bold;
        text-align: center;
        margin: 8px 0 16px 0;
    }

    .field-row {
        display: grid;
        grid-template-columns: 120px 20px 1fr;
        font-size: 12px;
        border-bottom: 1px solid #999;
        padding: 8px 0;
    }
}
</style>