<template>
  <div class="form-container">
    <h3>Domain Breakdown</h3>
    <p class="form-description">Break down the system into logical domains with clear objectives.</p>

    <div class="domains-list">
      <div v-for="(domain, index) in localContent.domains" :key="index" class="domain-card">
        <div class="card-header">
          <h4>Domain {{ index + 1 }}</h4>
          <button type="button" class="btn-remove" @click="removeDomain(index)">✕</button>
        </div>

        <div class="form-group">
          <label>Domain Name *</label>
          <input 
            v-model="domain.name" 
            placeholder="e.g., Authentication, Catalog, Orders"
            required
          />
        </div>

        <div class="form-group">
          <label>Objective *</label>
          <textarea 
            v-model="domain.objective" 
            placeholder="What is the main objective of this domain?"
            rows="3"
            required
          />
        </div>

        <div class="form-group">
          <label>Owner (Optional)</label>
          <select v-model="domain.owner_user_id">
            <option :value="null">No owner assigned</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.role }})
            </option>
          </select>
        </div>
      </div>

      <button type="button" class="btn btn-secondary" @click="addDomain">
        + Add Domain
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted, ref } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:modelValue'])

const users = ref([])

const localContent = reactive({
  domains: props.modelValue.domains || []
})

async function loadUsers() {
  try {
    // Users endpoint not implemented yet - would need backend support
    users.value = []
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

function addDomain() {
  localContent.domains.push({
    name: '',
    objective: '',
    owner_user_id: null
  })
  emitUpdate()
}

function removeDomain(index) {
  localContent.domains.splice(index, 1)
  emitUpdate()
}

function emitUpdate() {
  emit('update:modelValue', { ...localContent })
}

watch(() => localContent.domains, () => {
  emitUpdate()
}, { deep: true })

onMounted(() => {
  if (localContent.domains.length === 0) {
    addDomain()
  }
  loadUsers()
})
</script>

<style scoped>
.form-container { padding: 1rem; }
.form-description { color: #718096; margin-bottom: 1.5rem; font-size: .9rem; }

.domains-list { display: flex; flex-direction: column; gap: 1rem; }

.domain-card {
  background: #f8f9fa; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1.25rem;
}

.card-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
}

.card-header h4 { margin: 0; font-size: 1rem; font-weight: 600; color: #2d3748; }

.btn-remove {
  background: none; border: none; color: #e53e3e; cursor: pointer; font-size: 1.2rem;
  padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
  border-radius: 4px; transition: background .2s;
}
.btn-remove:hover { background: #fff5f5; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.form-group input,
.form-group textarea,
.form-group select {
  width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .9rem; box-sizing: border-box;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { outline: none; border-color: #7367f0; }

.btn { padding: .7rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; }
.btn-secondary { background: #f7f7f7; color: #4a5568; width: 100%; }
.btn-secondary:hover { background: #e2e8f0; }
</style>
