<template>
  <AppLayout>
    <div class="page" v-if="project">
      <!-- Page header -->
      <div class="page-header">
        <div class="breadcrumb">
          <router-link to="/projects">Projects</router-link>
          <span>›</span>
          <span>{{ project.name }}</span>
        </div>
        <div class="header-actions">
          <StatusBadge :status="project.status" type="project" />
          <div v-if="can.manageProjects" class="status-transition">
            <select :value="project.status" @change="handleStatusChange">
              <option value="draft">Draft</option>
              <option value="discovery">Discovery</option>
              <option value="execution">Execution</option>
              <option value="delivered">Delivered</option>
            </select>
          </div>
        </div>
      </div>

      <div class="project-title-row">
        <h2>{{ project.name }}</h2>
        <span class="client-label">{{ project.client_name }}</span>
      </div>

      <!-- Gate error alert -->
      <div v-if="transitionError" class="alert alert-warning">
        <strong>⚠ Transition blocked:</strong> {{ transitionError }}
      </div>

      <!-- General error alert -->
      <div v-if="errorMessage" class="alert alert-error">
        <strong>❌ Error:</strong> {{ errorMessage }}
        <button class="alert-close" @click="errorMessage = null">✕</button>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]"
          @click="activeTab = tab.id"
        >{{ tab.label }}</button>
      </div>

      <!-- Tab: Artifacts -->
      <div v-if="activeTab === 'artifacts'" class="tab-content">
        <div class="section-header">
          <h3>Framework Artifacts</h3>
          <button v-if="can.manageProjects" class="btn btn-sm btn-primary" @click="showArtifactModal = true">+ Add</button>
        </div>

        <div class="artifacts-list">
          <div
            v-for="artifact in artifacts"
            :key="artifact.id"
            class="artifact-row"
            :class="{ 'is-blocked': artifact.blocked_reason }"
          >
            <div class="artifact-info">
              <router-link :to="`/projects/${project.id}/artifacts/${artifact.id}`" class="artifact-name">
                {{ artifact.type_label }}
              </router-link>
              <span v-if="artifact.blocked_reason" class="blocked-badge">
                🔒 {{ artifact.blocked_reason }}
              </span>
            </div>
            <div class="artifact-meta">
              <span class="owner-name" v-if="artifact.owner">{{ artifact.owner.name }}</span>
              <StatusBadge :status="artifact.status" type="artifact" />
            </div>
          </div>
        </div>
      </div>

      <!-- Tab: Modules -->
      <div v-if="activeTab === 'modules'" class="tab-content">
        <div class="section-header">
          <h3>Modules</h3>
          <button v-if="can.editModules" class="btn btn-sm btn-primary" @click="showModuleModal = true">+ New Module</button>
        </div>

        <div v-if="!modules.length" class="empty-small">No modules yet.</div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Domain</th>
              <th>Status</th>
              <th>Validation</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="mod in modules" :key="mod.id">
              <td>
                <router-link :to="`/projects/${project.id}/modules/${mod.id}`">{{ mod.name }}</router-link>
              </td>
              <td>{{ mod.domain }}</td>
              <td><StatusBadge :status="mod.status" type="module" /></td>
              <td>
                <span v-if="mod.validation_check?.valid" class="check-ok">✓ Ready</span>
                <span v-else class="check-fail">✗ Incomplete</span>
              </td>
              <td>
                <button
                  v-if="can.editModules && mod.status === 'draft' && mod.validation_check?.valid"
                  class="btn btn-xs btn-success"
                  @click="validateModule(mod)"
                >Validate</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tab: Audit Timeline -->
      <div v-if="activeTab === 'audit'" class="tab-content">
        <h3>Activity Timeline</h3>
        <div v-if="!auditEvents.length" class="empty-small">No events yet.</div>
        <div class="timeline">
          <div v-for="event in auditEvents" :key="event.id" class="timeline-item">
            <div class="timeline-dot" :class="`dot-${event.action}`"></div>
            <div class="timeline-content">
              <div class="timeline-header">
                <strong>{{ event.actor?.name }}</strong>
                <span class="action-badge">{{ actionLabel(event.action) }}</span>
                <span class="entity-label">{{ event.entity_type }} #{{ event.entity_id }}</span>
              </div>
              <div class="timeline-diff" v-if="event.before_json || event.after_json">
                <span v-if="event.before_json?.status" class="diff-before">{{ event.before_json.status }}</span>
                <span v-if="event.before_json?.status"> → </span>
                <span v-if="event.after_json?.status" class="diff-after">{{ event.after_json.status }}</span>
              </div>
              <div class="timeline-time">{{ formatDate(event.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-else class="loading-full">
      <div class="spinner-lg" />
    </div>

    <!-- Quick Module Create Modal -->
    <Teleport to="body">
      <div v-if="showModuleModal" class="modal-overlay" @click.self="showModuleModal = false">
        <div class="modal">
          <div class="modal-header">
            <h3>New Module</h3>
            <button @click="showModuleModal = false">✕</button>
          </div>
          <form @submit.prevent="createModule">
            <div class="form-group">
              <label>Name</label>
              <input v-model="moduleForm.name" required />
            </div>
            <div class="form-group">
              <label>Domain</label>
              <input v-model="moduleForm.domain" required placeholder="E.g: Auth, Catalog, Orders" />
            </div>
            <div class="modal-actions">
              <button type="button" class="btn btn-secondary" @click="showModuleModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Artifact Create Modal -->
    <Teleport to="body">
      <div v-if="showArtifactModal" class="modal-overlay" @click.self="showArtifactModal = false">
        <div class="modal">
          <div class="modal-header">
            <h3>New Artifact</h3>
            <button @click="showArtifactModal = false">✕</button>
          </div>
          <form @submit.prevent="createArtifact">
            <div class="form-group">
              <label>Artifact Type</label>
              <select v-model="artifactForm.type" required>
                <option value="">Select...</option>
                <option value="strategic_alignment">Strategic Alignment</option>
                <option value="big_picture">Big Picture</option>
                <option value="domain_breakdown">Domain Breakdown</option>
                <option value="module_matrix">Module Matrix</option>
                <option value="module_engineering">Module Engineering</option>
                <option value="system_architecture">System Architecture</option>
                <option value="phase_scope">Phase Scope</option>
              </select>
            </div>
            <div class="modal-actions">
              <button type="button" class="btn btn-secondary" @click="showArtifactModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary">Create</button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { projectsApi, artifactsApi, modulesApi, auditApi } from '@/api'
import AppLayout from '@/components/common/AppLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'

const route   = useRoute()
const router  = useRouter()
const auth    = useAuthStore()
const { can } = auth

const project         = ref(null)
const artifacts       = ref([])
const modules         = ref([])
const auditEvents     = ref([])
const activeTab       = ref('artifacts')
const transitionError = ref(null)
const showModuleModal  = ref(false)
const showArtifactModal = ref(false)
const errorMessage    = ref(null)

const moduleForm = reactive({ name: '', domain: '' })
const artifactForm = reactive({ type: '' })

const tabs = [
  { id: 'artifacts', label: '📄 Artifacts' },
  { id: 'modules',   label: '🔧 Modules' },
  { id: 'audit',     label: '📋 Audit' },
]

async function load() {
  const id = route.params.id
  try {
    // Load project first
    const projRes = await projectsApi.get(id)
    project.value = projRes.data.data
    
    // Then load other data
    try {
      const artRes = await artifactsApi.list(id)
      artifacts.value = artRes.data.data || []
    } catch (err) {
      artifacts.value = []
    }
    
    try {
      const modRes = await modulesApi.list(id)
      modules.value = modRes.data.data || []
    } catch (err) {
      modules.value = []
    }
    
    try {
      const auditRes = await auditApi.timeline(id)
      auditEvents.value = auditRes.data.data || []
    } catch (err) {
      auditEvents.value = []
    }
  } catch (err) {
    errorMessage.value = 'Failed to load project'
    setTimeout(() => router.push('/projects'), 2000)
  }
}

async function handleStatusChange(e) {
  transitionError.value = null
  try {
    const { data } = await projectsApi.transition(project.value.id, e.target.value)
    project.value = data.data
  } catch (err) {
    transitionError.value = err.response?.data?.message || 'Error changing status'
    e.target.value = project.value.status // revert select
  }
}

async function validateModule(mod) {
  errorMessage.value = null
  try {
    await modulesApi.validate(project.value.id, mod.id)
    const idx = modules.value.findIndex(m => m.id === mod.id)
    if (idx >= 0) modules.value[idx].status = 'validated'
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error validating module'
  }
}

async function createModule() {
  errorMessage.value = null
  try {
    const { data } = await modulesApi.create(route.params.id, moduleForm)
    showModuleModal.value = false
    // Redirect to the newly created module
    router.push(`/projects/${route.params.id}/modules/${data.data.id}`)
  } catch (err) {
    showModuleModal.value = false
    errorMessage.value = err.response?.data?.message || 'Error creating module'
  }
}

async function createArtifact() {
  errorMessage.value = null
  try {
    const payload = {
      type: artifactForm.type,
      content_json: {}
    }
    const { data } = await artifactsApi.create(route.params.id, payload)
    showArtifactModal.value = false
    // Reset form
    artifactForm.type = ''
    // Redirect to the newly created artifact
    router.push(`/projects/${route.params.id}/artifacts/${data.data.id}`)
  } catch (err) {
    showArtifactModal.value = false
    // Check if it's a duplicate artifact error
    if (err.response?.status === 500 && err.response?.data?.message?.includes('UNIQUE constraint')) {
      errorMessage.value = 'An artifact of this type already exists in this project. Only one of each type is allowed per project.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Error creating artifact'
    }
  }
}

function actionLabel(action) {
  const map = { created: 'Created', updated: 'Updated', status_changed: 'Changed status', validated: 'Validated', completed: 'Completed' }
  return map[action] || action
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleString('es', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(load)
</script>

<style scoped>
.page { padding: 2rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.breadcrumb { display: flex; gap: .5rem; align-items: center; color: #718096; font-size: .875rem; }
.breadcrumb a { color: #7367f0; text-decoration: none; }
.header-actions { display: flex; gap: 1rem; align-items: center; }
.status-transition select { padding: .5rem .875rem; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: .875rem; }

.project-title-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.project-title-row h2 { font-size: 1.75rem; font-weight: 700; color: #2d3748; margin: 0; }
.client-label { color: #718096; font-size: 1rem; }

.alert { padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: .9rem; position: relative; }
.alert-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
.alert-close { position: absolute; top: .75rem; right: .75rem; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: inherit; opacity: .6; }
.alert-close:hover { opacity: 1; }

.tabs { display: flex; gap: .5rem; border-bottom: 2px solid #e2e8f0; margin-bottom: 1.5rem; }
.tab-btn { padding: .75rem 1.25rem; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-size: .9rem; font-weight: 500; color: #718096; margin-bottom: -2px; transition: all .2s; }
.tab-btn.active { color: #7367f0; border-bottom-color: #7367f0; }

.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.section-header h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }

.artifacts-list { display: flex; flex-direction: column; gap: .75rem; }
.artifact-row {
  display: flex; align-items: center; justify-content: space-between;
  background: #fff; border-radius: 10px; padding: 1rem 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1.5px solid transparent;
}
.artifact-row.is-blocked { border-color: #fcd34d; background: #fffbeb; }
.artifact-info { display: flex; flex-direction: column; gap: .375rem; }
.artifact-name { font-weight: 600; color: #2d3748; text-decoration: none; font-size: .95rem; }
.artifact-name:hover { color: #7367f0; }
.blocked-badge { color: #92400e; font-size: .8rem; }
.artifact-meta { display: flex; gap: .75rem; align-items: center; }
.owner-name { color: #718096; font-size: .875rem; }

.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.data-table th { background: #f8f7fa; padding: .875rem 1rem; text-align: left; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: #718096; }
.data-table td { padding: .875rem 1rem; border-top: 1px solid #f0f0f0; font-size: .9rem; }
.data-table td a { color: #7367f0; text-decoration: none; font-weight: 500; }
.check-ok   { color: #276749; font-size: .875rem; font-weight: 500; }
.check-fail { color: #c53030; font-size: .875rem; }

.timeline { display: flex; flex-direction: column; gap: 0; }
.timeline-item { display: flex; gap: 1rem; padding: 1rem 0; border-left: 2px solid #e2e8f0; padding-left: 1.5rem; position: relative; }
.timeline-dot {
  position: absolute; left: -7px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff;
}
.dot-created { background: #48bb78; }
.dot-updated { background: #4299e1; }
.dot-status_changed { background: #ed8936; }
.dot-validated { background: #7367f0; }
.dot-completed { background: #48bb78; }
.timeline-header { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.action-badge { background: #edf2f7; color: #4a5568; font-size: .75rem; padding: .2rem .6rem; border-radius: 50px; }
.entity-label { color: #a0aec0; font-size: .8rem; }
.diff-before { background: #fff5f5; color: #c53030; padding: .125rem .5rem; border-radius: 4px; font-size: .8rem; }
.diff-after  { background: #f0fff4; color: #276749; padding: .125rem .5rem; border-radius: 4px; font-size: .8rem; }
.timeline-time { color: #a0aec0; font-size: .8rem; margin-top: .25rem; }

.empty-small { color: #a0aec0; padding: 2rem; text-align: center; background: #fff; border-radius: 10px; }

.btn { padding: .6rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: .875rem; cursor: pointer; border: none; }
.btn-sm { padding: .45rem 1rem; font-size: .8rem; }
.btn-xs { padding: .3rem .75rem; font-size: .75rem; }
.btn-primary { background: #7367f0; color: #fff; }
.btn-success { background: #48bb78; color: #fff; }
.btn-secondary { background: #f7f7f7; color: #4a5568; }
.loading-full { display: flex; justify-content: center; padding: 5rem; }
.spinner-lg { width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #7367f0; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: #fff; border-radius: 16px; padding: 2rem; width: 100%; max-width: 480px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.modal-header h3 { font-size: 1.2rem; font-weight: 700; margin: 0; }
.modal-header button { background: none; border: none; font-size: 1.2rem; cursor: pointer; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.form-group input,
.form-group select { width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .9rem; box-sizing: border-box; }
.modal-actions { display: flex; gap: .75rem; justify-content: flex-end; margin-top: 1.5rem; }
</style>
