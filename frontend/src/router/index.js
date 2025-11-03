import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore.js'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
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

    // File uploads page
    {
      path: '/files',
      name: 'files',
      component: () => import('@/views/FileUpload.vue'),
      meta: { layout: 'DefaultLayout', requiresAuth: true },
    },

    // Profile page
    {
      path: '/profile',
      name: 'profile',
      component: () => import('@/views/Profile/ProfileView.vue'),
      meta: { layout: 'ProfileLayout', requiresAuth: true },
    },

    // Not Found
    {
      path: '/:catchAll(.*)',
      name: 'NotFound',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { layout: 'BlankLayout' },
    },
  ],
})

// Global navigation guard
router.beforeEach((to) => {
  const authStore = useAuthStore()

  // Redirect authenticated users away from auth pages
  if (
    (to.name === 'login' || to.name === 'register' || to.name === 'forgotpassword') &&
    authStore.isAuthenticated
  ) {
    return { name: 'home' }
  }

  // Protect routes that require authentication
  if (to.meta?.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
})

export default router
