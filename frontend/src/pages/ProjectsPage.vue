<template>
  <AppLayout>
    <div class="page">
      <!-- Header -->
      <div class="page-header">
        <div>
          <h2>Projects</h2>
          <p>Manage all TCG Framework projects</p>
        </div>
        <button v-if="can.manageProjects" class="btn btn-primary" @click="showCreateModal = true">
          + New Project
        </button>
      </div>

      <!-- Filters -->
      <div class="card filters-card">
        <input v-model="filters.search" placeholder="Search by name..." class="search-input" @input="fetchProjects" />
        <select v-model="filters.status" @change="fetchProjects" class="select">
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="discovery">Discovery</option>
          <option value="execution">Execution</option>
          <option value="delivered">Delivered</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner-lg" />
        <p>Loading projects...</p>
      </div>

      <!-- Empty -->
      <div v-else-if="!projects.length" class="empty-state">
        <div class="empty-icon">📂</div>
        <h3>No projects</h3>
        <p>Create your first project to get started.</p>
      </div>

      <!-- Projects grid -->
      <div v-else class="projects-grid">
        <div
          v-for="project in projects"
          :key="project.id"
          class="project-card"
          @click="goToProject(project.id)"
        >
          <div class="project-card-header">
            <div class="project-name">{{ project.name }}</div>
            <StatusBadge :status="project.status" type="project" />
          </div>
          <div class="project-client">
            <span class="label">Client:</span> {{ project.client_name }}
          </div>
          <div class="project-meta">
            <span>By {{ project.created_by?.name }}</span>
            <span>{{ formatDate(project.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          :class="{ active: page === pagination.current_page }"
          @click="fetchProjects(page)"
        >{{ page }}</button>
      </div>
    </div>

    <!-- Create Modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal">
          <div class="modal-header">
            <h3>New Project</h3>
            <button @click="showCreateModal = false">✕</button>
          </div>
          <form @submit.prevent="createProject">
            <div class="form-group">
              <label>Project Name</label>
              <input v-model="createForm.name" required placeholder="E.g: E-Commerce Platform" />
            </div>
            <div class="form-group">
              <label>Client</label>
              <input v-model="createForm.client_name" required placeholder="Client name" />
            </div>
            <div v-if="createError" class="alert-danger">{{ createError }}</div>
            <div class="modal-actions">
              <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="creating">
                {{ creating ? 'Creating...' : 'Create Project' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { projectsApi } from '@/api'
import AppLayout from '@/components/common/AppLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const router  = useRouter()
const { can } = useAuthStore()

const loading  = ref(false)
const projects = ref([])
const pagination = ref({})
const filters  = reactive({ search: '', status: '' })

const showCreateModal = ref(false)
const creating        = ref(false)
const createError     = ref(null)
const createForm      = reactive({ name: '', client_name: '' })

async function fetchProjects(page = 1) {
  loading.value = true
  try {
    const { data } = await projectsApi.list({ ...filters, page })
    projects.value  = data.data
    pagination.value = data.meta
  } finally {
    loading.value = false
  }
}

async function createProject() {
  creating.value   = true
  createError.value = null
  try {
    const { data } = await projectsApi.create(createForm)
    showCreateModal.value = false
    router.push(`/projects/${data.data.id}`)
  } catch (err) {
    createError.value = err.response?.data?.message || 'Error creating project'
  } finally {
    creating.value = false
  }
}

function goToProject(id) { router.push(`/projects/${id}`) }

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('es', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(fetchProjects)
</script>

<style scoped>
.page { padding: 2rem; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h2 { font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0; }
.page-header p  { color: #718096; margin: .25rem 0 0; font-size: .875rem; }

.card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 1.5rem; }
.filters-card { display: flex; gap: 1rem; align-items: center; }
.search-input, .select {
  padding: .6rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .9rem; flex: 1;
}
.search-input:focus, .select:focus { outline: none; border-color: #7367f0; }

.loading-state, .empty-state { text-align: center; padding: 4rem 2rem; color: #718096; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.spinner-lg {
  width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #7367f0;
  border-radius: 50%; animation: spin .7s linear infinite; margin: 0 auto 1rem;
}
@keyframes spin { to { transform: rotate(360deg); } }

.projects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.25rem; }
.project-card {
  background: #fff; border-radius: 12px; padding: 1.5rem; cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,.06); transition: all .2s; border: 1.5px solid transparent;
}
.project-card:hover { border-color: #7367f0; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(115,103,240,.15); }
.project-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
.project-name { font-weight: 600; font-size: 1rem; color: #2d3748; }
.project-client { color: #4a5568; font-size: .875rem; margin-bottom: .75rem; }
.project-client .label { color: #718096; }
.project-meta { display: flex; justify-content: space-between; color: #a0aec0; font-size: .8rem; border-top: 1px solid #f7fafc; padding-top: .75rem; }

.pagination { display: flex; gap: .5rem; justify-content: center; margin-top: 2rem; }
.pagination button { padding: .5rem .875rem; border-radius: 6px; border: 1.5px solid #e2e8f0; background: #fff; cursor: pointer; font-size: .875rem; }
.pagination button.active { background: #7367f0; color: #fff; border-color: #7367f0; }

.btn { padding: .7rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #7367f0; color: #fff; }
.btn-primary:hover:not(:disabled) { background: #5e50ee; }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-secondary { background: #f7f7f7; color: #4a5568; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #fff; border-radius: 16px; padding: 2rem; width: 100%; max-width: 480px; box-shadow: 0 25px 50px rgba(0,0,0,.2); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.modal-header h3 { font-size: 1.2rem; font-weight: 700; margin: 0; }
.modal-header button { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #718096; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.form-group input, .form-group select { width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .9rem; box-sizing: border-box; }
.modal-actions { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }
.alert-danger { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; border-radius: 8px; padding: .75rem 1rem; font-size: .875rem; margin-bottom: 1rem; }
</style>
