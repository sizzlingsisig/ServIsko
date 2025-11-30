import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore.js'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Home page (landing + dashboard - accessible with or without auth)
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/HomeView.vue'),
      meta: { layout: 'DefaultLayout', requiresAuth: false },
    },
    // Auth routes
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { layout: 'SideBarLayout', requiresAuth: false },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { layout: 'SideBarLayout', requiresAuth: false },
    },
    {
      path: '/forgotpassword',
      name: 'forgotpassword',
      component: () => import('@/views/ForgotPasswordView.vue'),
      meta: { layout: 'SideBarLayout', requiresAuth: false },
    },
    // {
    //   path: '/dashboard/about',
    //   name: 'about',
    //   component: () => import('@/views/AboutView.vue'),
    //   meta: { layout: 'DefaultLayout', requiresAuth: true },
    // },
    // Dashboard listings route (optional, can be removed if not needed)
    // {
    //   path: '/dashboard/listings',
    //   name: 'dashboard-listings',
    //   component: () => import('@/views/ListingsView.vue'),
    //   meta: { layout: 'DefaultLayout', requiresAuth: true },
    // },

    // Public listings route (for /listings)
    {
      path: '/listings',
      name: 'listings',
      component: () => import('@/views/ListingsView.vue'),
      meta: { layout: 'DefaultLayout', requiresAuth: false },
    },
    {
      path: '/listings/:id',
      name: 'listing-details',
      component: () => import('@/views/ListingDetailsView.vue'),
      meta: { layout: 'DefaultLayout', requiresAuth: false },
      props: true,
    },

    // {
    //   path: '/providers',
    //   name: 'providers',
    //   component: () => import('@/views/ProvidersView.vue'),
    //   meta: { layout: 'DefaultLayout', requiresAuth: true },
    // },
    // {
    //   path: '/dashboard/messages',
    //   name: 'messages',
    //   component: () => import('@/views/MessagesView.vue'),
    //   meta: { layout: 'DefaultLayout', requiresAuth: true },
    // },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/Profile/ProfileView.vue'),
      meta: { layout: 'ProfileLayout', requiresAuth: true },
    },
    // {
    //   path: '/dashboard/settings',
    //   name: 'settings',
    //   component: () => import('@/views/SettingsView.vue'),
    //   meta: { layout: 'DefaultLayout', requiresAuth: true },
    // },
    // 404 catch-all route
    {
      path: '/:catchAll(.*)',
      name: 'NotFound',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { layout: 'BlankLayout' },
    },
  ],
})

// Global navigation guard
router.beforeEach((to, from) => {
  const authStore = useAuthStore()

  // Redirect authenticated users away from auth pages
  if (
    (to.name === 'login' || to.name === 'register' || to.name === 'forgotpassword') &&
    authStore.isAuthenticated
  ) {
    return { name: 'home' }
  }
})

export default router
