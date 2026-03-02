import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api/v1',
  headers: { 
    'Content-Type': 'application/json', 
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: false,
})

// Attach token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('tcg_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Handle 401 globally
api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('tcg_token')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api

// Auth
export const authApi = {
  login: (data) => api.post('/login', data),
  me: () => api.get('/me'),
  logout: () => api.post('/logout'),
}

// Projects
export const projectsApi = {
  list: (params) => api.get('/projects', { params }),
  create: (data) => api.post('/projects', data),
  get: (id) => api.get(`/projects/${id}`),
  update: (id, data) => api.put(`/projects/${id}`, data),
  transition: (id, status) => api.patch(`/projects/${id}/status`, { status }),
  archive: (id) => api.delete(`/projects/${id}`),
}

// Artifacts
export const artifactsApi = {
  list: (projectId) => api.get(`/projects/${projectId}/artifacts`),
  create: (projectId, data) => api.post(`/projects/${projectId}/artifacts`, data),
  get: (projectId, id) => api.get(`/projects/${projectId}/artifacts/${id}`),
  update: (projectId, id, data) => api.put(`/projects/${projectId}/artifacts/${id}`, data),
  updateStatus: (projectId, id, status) => api.patch(`/projects/${projectId}/artifacts/${id}/status`, { status }),
}

// Modules
export const modulesApi = {
  list: (projectId, params) => api.get(`/projects/${projectId}/modules`, { params }),
  create: (projectId, data) => api.post(`/projects/${projectId}/modules`, data),
  get: (projectId, id) => api.get(`/projects/${projectId}/modules/${id}`),
  update: (projectId, id, data) => api.put(`/projects/${projectId}/modules/${id}`, data),
  validate: (projectId, id) => api.post(`/projects/${projectId}/modules/${id}/validate`),
  delete: (projectId, id) => api.delete(`/projects/${projectId}/modules/${id}`),
}

// Audit
export const auditApi = {
  timeline: (projectId, params) => api.get(`/projects/${projectId}/audit`, { params }),
}
