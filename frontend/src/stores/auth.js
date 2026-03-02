import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(null)
  const token = ref(localStorage.getItem('tcg_token'))

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role            = computed(() => user.value?.role)

  const can = computed(() => ({
    manageProjects: ['admin', 'pm'].includes(role.value),
    editArtifacts:  ['admin', 'pm'].includes(role.value),
    editModules:    ['admin', 'pm', 'engineer'].includes(role.value),
    isAdmin:        role.value === 'admin',
  }))

  async function login(email, password) {
    const { data } = await authApi.login({ email, password })
    token.value = data.data.token
    user.value  = data.data.user
    localStorage.setItem('tcg_token', token.value)
  }

  async function fetchMe() {
    try {
      const { data } = await authApi.me()
      user.value = data.data
    } catch {
      logout()
    }
  }

  async function logout() {
    try { await authApi.logout() } catch { /* ignore */ }
    token.value = null
    user.value  = null
    localStorage.removeItem('tcg_token')
  }

  return { user, token, isAuthenticated, role, can, login, fetchMe, logout }
})
