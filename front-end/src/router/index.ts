import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '@/views/HomePage.vue'
import PriceHistory from '@/views/PriceHistory.vue'
const routes = [
  {
  path: '/',
  name: 'Home',
  component: HomePage,
},
{
  path: '/price-history',
  name: 'Price History',
  component: PriceHistory
},

]
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
