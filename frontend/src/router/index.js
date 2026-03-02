import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/login', name: 'Login', component: () => import('@/pages/LoginPage.vue'), meta: { guest: true } },
  { path: '/', redirect: '/projects' },
  { path: '/projects', name: 'Projects', component: () => import('@/pages/ProjectsPage.vue'), meta: { auth: true } },
  { path: '/projects/:id', name: 'ProjectDetail', component: () => import('@/pages/ProjectDetailPage.vue'), meta: { auth: true } },
  { path: '/projects/:projectId/artifacts/:id', name: 'ArtifactDetail', component: () => import('@/pages/ArtifactDetailPage.vue'), meta: { auth: true } },
  { path: '/projects/:projectId/modules/:id', name: 'ModuleDetail', component: () => import('@/pages/ModuleDetailPage.vue'), meta: { auth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }

  if (to.meta.auth && !auth.isAuthenticated) return '/login'
  if (to.meta.guest && auth.isAuthenticated) return '/projects'
})

export default router
