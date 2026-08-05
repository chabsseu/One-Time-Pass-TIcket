<script setup lang="ts">
import NavBar from '@/components/NavBar.vue'
import { reactive, ref } from 'vue'
import api from "@/services/api";
import { onMounted } from 'vue';
import Swal from 'sweetalert2';

interface TicketForm {
  date: string
  plateNumber: string
  driver: string
  company: string
  amount: number
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

const form = reactive<TicketForm>({
  date: '',
  plateNumber: '',
  driver: '',
  company: '',
  amount: 30, // default, but editable
})

const controlNo = ref()
const loading = ref(false)
const error = ref('')
const tickets = ref<Ticket[]>([]);

async function loadControlNumber() {
    const response = await api.get("/tickets/next-control-number");
    controlNo.value = response.data.control_no;
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
            amount: form.amount
        })

        await loadControlNumber();

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
        error.value="Unable to save ticket."

    }finally{

        loading.value=false

    }
    
}
function resetForm() {
        form.date = "";
        form.plateNumber = "";
        form.driver = "";
        form.company = "";
        form.amount = 30;
}

async function loadTickets() {
    const response = await api.get("/tickets");
    tickets.value = response.data;
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
});
</script>

<template>
    <NavBar />
  <form
    @submit.prevent="handleSubmit"
    class="mx-auto flex max-w-md flex-col gap-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm mt-3"
  >
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
      <label for="amount" class="text-sm font-medium text-gray-700">Amount</label>
      <div class="flex items-center gap-2">
        <span class="text-lg font-bold text-gray-900">₱</span>
        <input
          id="amount"
          v-model.number="form.amount"
          type="number"
          min="0"
          step="0.01"
          required
          class="w-full rounded-md border border-gray-300 px-3 py-2 text-lg font-bold focus:border-blue-500 focus:outline-none"
        />
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
          placeholder="e.g. NAV 0571"
          class="rounded-md border border-gray-300 px-3 py-2 text-sm uppercase focus:border-blue-500 focus:outline-none"
        />
      </div>

      <div class="flex flex-col gap-1">
        <label for="driver" class="text-sm font-medium text-gray-700">Driver</label>
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
      class="mt-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
    >
      {{ loading ? 'Saving...' : 'Submit' }}
    </button>
  </form>


  <div class="p-6">
    <table class="min-w-full border border-gray-300 ">

        <thead class="bg-blue-500">
            <tr>
                <th class="border border-black p-2 text-white">Control No.</th>
                <th class="border border-black p-2 text-white">Date</th>
                <th class="border border-black p-2 text-white">Plate Number</th>
                <th class="border border-black p-2 text-white">Driver</th>
                <th class="border border-black p-2 text-white">Company</th>
                <th class="border border-black p-2 text-white">Amount</th>
            </tr>
        </thead>

        <tbody>

            <tr
                v-for="ticket in tickets"
                :key="ticket.id"
            >
                <td class="border p-2">{{ ticket.control_no }}</td>
                <td class="border p-2">{{ formatDate(ticket.date) }}</td>
                <td class="border p-2">{{ ticket.plate_number }}</td>
                <td class="border p-2">{{ ticket.driver }}</td>
                <td class="border p-2">{{ ticket.company }}</td>
                <td class="border p-2">₱{{ ticket.amount }}</td>
            </tr>

        </tbody>

    </table>
  </div>
</template>

