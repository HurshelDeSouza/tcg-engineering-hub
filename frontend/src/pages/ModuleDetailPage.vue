<template>
  <AppLayout>
    <div class="page" v-if="module">
      <div class="page-header">
        <div class="breadcrumb">
          <router-link :to="`/projects/${projectId}`">← Back to Project</router-link>
        </div>
        <div class="header-right">
          <StatusBadge :status="module.status" type="module" />
          <button
            v-if="can.editModules && module.status === 'draft'"
            class="btn btn-success"
            :disabled="!module.validation_check?.valid || validating"
            @click="handleValidate"
            :title="!module.validation_check?.valid ? 'Complete required fields to validate' : ''"
          >
            {{ validating ? 'Validating...' : '✓ Validate Module' }}
          </button>
        </div>
      </div>

      <h2>{{ module.name }}</h2>
      <p class="domain-label">Domain: <strong>{{ module.domain }}</strong> · version: {{ module.version_note || '—' }}</p>

      <!-- Validation errors -->
      <div v-if="module.validation_check && !module.validation_check.valid" class="validation-warning">
        <strong>⚠ To validate this module you need:</strong>
        <ul>
          <li v-for="e in module.validation_check.errors" :key="e">{{ e }}</li>
        </ul>
      </div>

      <!-- Save alert -->
      <div v-if="saveSuccess" class="alert-success">✓ Changes saved</div>
      <div v-if="saveError"   class="alert-danger">{{ saveError }}</div>

      <!-- Form: 10 TCG module fields -->
      <div class="form-grid">

        <!-- Objective -->
        <div class="field-card full-width">
          <label class="field-label required">Objective</label>
          <p class="field-hint">What does this module do? What is its purpose within the system?</p>
          <textarea v-model="form.objective" :disabled="!can.editModules" rows="3" />
        </div>

        <!-- Inputs -->
        <div class="field-card">
          <label class="field-label required">Inputs <span class="tag">array</span></label>
          <p class="field-hint">What data or events does this module receive?</p>
          <ArrayInput v-model="form.inputs" :disabled="!can.editModules" placeholder="Add input..." />
        </div>

        <!-- Outputs -->
        <div class="field-card">
          <label class="field-label required">Outputs <span class="tag">array</span></label>
          <p class="field-hint">What data or events does this module produce?</p>
          <ArrayInput v-model="form.outputs" :disabled="!can.editModules" placeholder="Add output..." />
        </div>

        <!-- Responsibility -->
        <div class="field-card full-width">
          <label class="field-label required">Responsibility</label>
          <p class="field-hint">Who or what system is operationally responsible for this module?</p>
          <input v-model="form.responsibility" :disabled="!can.editModules" />
        </div>

        <!-- Data Structure -->
        <div class="field-card">
          <label class="field-label">Data Structure</label>
          <p class="field-hint">Tables, schemas, relevant models.</p>
          <textarea v-model="form.data_structure" :disabled="!can.editModules" rows="4" />
        </div>

        <!-- Logic Rules -->
        <div class="field-card">
          <label class="field-label">Business Rules</label>
          <p class="field-hint">Validations, conditions, decision flow.</p>
          <textarea v-model="form.logic_rules" :disabled="!can.editModules" rows="4" />
        </div>

        <!-- Failure Scenarios -->
        <div class="field-card">
          <label class="field-label">Failure Scenarios</label>
          <p class="field-hint">What can go wrong? How is it handled?</p>
          <textarea v-model="form.failure_scenarios" :disabled="!can.editModules" rows="4" />
        </div>

        <!-- Audit Trail Requirements -->
        <div class="field-card">
          <label class="field-label">Audit Trail Requirements</label>
          <p class="field-hint">What events should be logged?</p>
          <textarea v-model="form.audit_trail_requirements" :disabled="!can.editModules" rows="4" />
        </div>

        <!-- Dependencies -->
        <div class="field-card">
          <label class="field-label">Dependencies <span class="tag">modules</span></label>
          <p class="field-hint">IDs of modules this depends on.</p>
          <ArrayInput v-model="form.dependencies" :disabled="!can.editModules" type="number" placeholder="Module ID..." />
        </div>

        <!-- Version Note -->
        <div class="field-card">
          <label class="field-label">Version Note</label>
          <p class="field-hint">E.g: v1.0 - Initial design</p>
          <input v-model="form.version_note" :disabled="!can.editModules" />
        </div>

      </div>

      <!-- Save button -->
      <div class="save-bar" v-if="can.editModules">
        <button class="btn btn-primary" @click="saveModule" :disabled="saving">
          {{ saving ? 'Saving...' : '💾 Save Changes' }}
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { modulesApi } from '@/api'
import AppLayout from '@/components/common/AppLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ArrayInput from '@/components/common/ArrayInput.vue'

const route     = useRoute()
const { can }   = useAuthStore()
const projectId = route.params.projectId
const moduleId  = route.params.id

const module      = ref(null)
const saving      = ref(false)
const validating  = ref(false)
const saveSuccess = ref(false)
const saveError   = ref(null)

const form = reactive({
  objective: '', inputs: [], data_structure: '', logic_rules: '',
  outputs: [], responsibility: '', failure_scenarios: '',
  audit_trail_requirements: '', dependencies: [], version_note: '',
})

async function loadModule() {
  const { data } = await modulesApi.get(projectId, moduleId)
  module.value = data.data
  Object.assign(form, {
    objective: data.data.objective || '',
    inputs:    data.data.inputs || [],
    data_structure: data.data.data_structure || '',
    logic_rules: data.data.logic_rules || '',
    outputs: data.data.outputs || [],
    responsibility: data.data.responsibility || '',
    failure_scenarios: data.data.failure_scenarios || '',
    audit_trail_requirements: data.data.audit_trail_requirements || '',
    dependencies: data.data.dependencies || [],
    version_note: data.data.version_note || '',
  })
}

async function saveModule() {
  saving.value = true
  saveError.value = null
  saveSuccess.value = false
  try {
    const { data } = await modulesApi.update(projectId, moduleId, form)
    module.value = data.data
    saveSuccess.value = true
    setTimeout(() => (saveSuccess.value = false), 3000)
  } catch (err) {
    saveError.value = err.response?.data?.message || 'Error saving'
  } finally {
    saving.value = false
  }
}

async function handleValidate() {
  validating.value = true
  try {
    const { data } = await modulesApi.validate(projectId, moduleId)
    module.value = data.data
  } catch (err) {
    saveError.value = err.response?.data?.errors?.join(', ') || err.response?.data?.message || 'Error validating'
  } finally {
    validating.value = false
  }
}

onMounted(loadModule)
</script>

<style scoped>
.page { padding: 2rem; max-width: 1200px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.breadcrumb a { color: #7367f0; text-decoration: none; font-size: .875rem; }
.header-right { display: flex; gap: .75rem; align-items: center; }
h2 { font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0 0 .25rem; }
.domain-label { color: #718096; font-size: .9rem; margin-bottom: 1.5rem; }

.validation-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
.validation-warning ul { margin: .5rem 0 0 1rem; padding: 0; font-size: .875rem; }
.alert-success { background: #f0fff4; border: 1px solid #9ae6b4; color: #276749; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }
.alert-danger  { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; border-radius: 8px; padding: .75rem 1rem; margin-bottom: 1rem; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
.field-card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
.full-width { grid-column: 1 / -1; }
.field-label { font-weight: 600; font-size: .875rem; color: #2d3748; display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
.field-label.required::after { content: '*'; color: #e53e3e; }
.field-hint { color: #a0aec0; font-size: .8rem; margin: 0 0 .75rem; }
.field-card textarea, .field-card input {
  width: 100%; padding: .7rem .875rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .875rem; box-sizing: border-box; font-family: inherit; resize: vertical;
}
.field-card textarea:focus, .field-card input:focus { outline: none; border-color: #7367f0; }
.field-card textarea:disabled, .field-card input:disabled { background: #f8f7fa; color: #a0aec0; }
.tag { background: #edf2f7; color: #718096; font-size: .7rem; padding: .1rem .5rem; border-radius: 4px; font-weight: 500; }

.save-bar { display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
.btn { padding: .7rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #7367f0; color: #fff; }
.btn-success { background: #48bb78; color: #fff; }
.btn-primary:disabled, .btn-success:disabled { opacity: .5; cursor: not-allowed; }
</style>
