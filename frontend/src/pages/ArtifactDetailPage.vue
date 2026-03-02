<template>
  <AppLayout>
    <div class="page" v-if="artifact">
      <div class="page-header">
        <router-link :to="`/projects/${projectId}`" class="back-link">← Back to Project</router-link>
        <div class="header-right">
          <StatusBadge :status="artifact.status" type="artifact" />
          <div v-if="can.editArtifacts" class="status-select">
            <select :value="artifact.status" @change="handleStatusChange">
              <option value="not_started">Not Started</option>
              <option value="in_progress">In Progress</option>
              <option value="blocked">Blocked</option>
              <option value="done">Done</option>
            </select>
          </div>
        </div>
      </div>

      <h2>{{ artifact.type_label }}</h2>

      <!-- Gate error -->
      <div v-if="statusError" class="alert alert-warning">
        <strong>⚠ Blocked by Gate:</strong> {{ statusError }}
      </div>

      <!-- Blocked reason (current) -->
      <div v-if="artifact.blocked_reason" class="alert alert-warning">
        <strong>🔒 Condition not met:</strong> {{ artifact.blocked_reason }}
      </div>

      <!-- Save alerts -->
      <div v-if="saveSuccess" class="alert-success">✓ Changes saved</div>
      <div v-if="saveError"   class="alert-danger">{{ saveError }}</div>

      <!-- Dynamic content form based on artifact type -->
      <div class="form-card">
        <component
          :is="formComponent"
          v-model="form"
          :disabled="!can.editArtifacts"
        />
      </div>

      <div class="save-bar" v-if="can.editArtifacts">
        <button class="btn btn-primary" @click="save" :disabled="saving">
          {{ saving ? 'Saving...' : '💾 Save' }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { artifactsApi } from '@/api'
import AppLayout from '@/components/common/AppLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import StrategicAlignmentForm from '@/components/artifact/StrategicAlignmentForm.vue'
import BigPictureForm from '@/components/artifact/BigPictureForm.vue'
import GenericArtifactForm from '@/components/artifact/GenericArtifactForm.vue'

const route     = useRoute()
const { can }   = useAuthStore()
const projectId = route.params.projectId
const artifactId = route.params.id

const artifact    = ref(null)
const saving      = ref(false)
const saveSuccess = ref(false)
const saveError   = ref(null)
const statusError = ref(null)
const form = ref({})

const formComponent = computed(() => {
  const map = {
    strategic_alignment: StrategicAlignmentForm,
    big_picture:         BigPictureForm,
  }
  return map[artifact.value?.type] || GenericArtifactForm
})

async function load() {
  const { data } = await artifactsApi.get(projectId, artifactId)
  artifact.value = data.data
  form.value = { ...(data.data.content_json || {}) }
}

async function save() {
  saving.value = true
  saveError.value = null
  saveSuccess.value = false
  try {
    const { data } = await artifactsApi.update(projectId, artifactId, { content_json: form.value })
    artifact.value = data.data
    saveSuccess.value = true
    setTimeout(() => (saveSuccess.value = false), 3000)
  } catch (err) {
    saveError.value = err.response?.data?.message || 'Error saving'
  } finally {
    saving.value = false
  }
}

async function handleStatusChange(e) {
  statusError.value = null
  try {
    const { data } = await artifactsApi.updateStatus(projectId, artifactId, e.target.value)
    artifact.value = data.data
  } catch (err) {
    statusError.value = err.response?.data?.message || 'Error changing status'
    e.target.value = artifact.value.status
  }
}

onMounted(load)
</script>

<style scoped>
.page { padding: 2rem; max-width: 900px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.back-link { color: #7367f0; text-decoration: none; font-size: .875rem; }
.header-right { display: flex; gap: .75rem; align-items: center; }
.status-select select { padding: .5rem .875rem; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: .875rem; }
h2 { font-size: 1.5rem; font-weight: 700; margin: 0 0 1.5rem; color: #2d3748; }
.alert { padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem; }
.alert-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.alert-success { background: #f0fff4; border: 1px solid #9ae6b4; color: #276749; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }
.alert-danger  { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }
.form-card { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 1.5rem; }
.save-bar { display: flex; justify-content: flex-end; }
.btn { padding: .7rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; }
.btn-primary { background: #7367f0; color: #fff; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }
</style>
