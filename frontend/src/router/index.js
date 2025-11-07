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
      meta: { layout: 'DefaultLayout', requiresAuth: true, requiresRole: 'service-provider' },
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

  // Optional role-based protection (lightweight): if user roles are available in store
  // and route specifies a required role, block navigation when role is missing.
  if (to.meta?.requiresRole) {
    const user = authStore.user
    const roles = Array.isArray(user?.roles) ? user.roles : null
    if (roles && !roles.includes(to.meta.requiresRole)) {
      return { name: 'profile' }
    }
  }
})

export default router
