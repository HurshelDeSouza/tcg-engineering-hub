<template>
  <div class="form-container">
    <h3>Module Matrix</h3>
    <p class="form-description">Overview of all modules with priorities and phases.</p>

    <div class="modules-list">
      <div v-for="(module, index) in localContent.modules_overview" :key="index" class="module-card">
        <div class="card-header">
          <h4>Module {{ index + 1 }}</h4>
          <button type="button" class="btn-remove" @click="removeModule(index)">✕</button>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Module Name *</label>
            <input 
              v-model="module.name" 
              placeholder="e.g., Authentication Module"
              required
            />
          </div>

          <div class="form-group">
            <label>Domain *</label>
            <input 
              v-model="module.domain" 
              placeholder="e.g., Auth, Catalog"
              required
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Priority</label>
            <select v-model="module.priority">
              <option value="">Not set</option>
              <option value="high">High</option>
              <option value="medium">Medium</option>
              <option value="low">Low</option>
            </select>
          </div>

          <div class="form-group">
            <label>Phase</label>
            <input 
              v-model="module.phase" 
              placeholder="e.g., Phase 1, MVP"
            />
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-secondary" @click="addModule">
        + Add Module
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:modelValue'])

const localContent = reactive({
  modules_overview: props.modelValue.modules_overview || []
})

function addModule() {
  localContent.modules_overview.push({
    name: '',
    domain: '',
    priority: '',
    phase: ''
  })
  emitUpdate()
}

function removeModule(index) {
  localContent.modules_overview.splice(index, 1)
  emitUpdate()
}

function emitUpdate() {
  emit('update:modelValue', { ...localContent })
}

watch(() => localContent.modules_overview, () => {
  emitUpdate()
}, { deep: true })

onMounted(() => {
  if (localContent.modules_overview.length === 0) {
    addModule()
  }
})
</script>

<style scoped>
.form-container { padding: 1rem; }
.form-description { color: #718096; margin-bottom: 1.5rem; font-size: .9rem; }

.modules-list { display: flex; flex-direction: column; gap: 1rem; }

.module-card {
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

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.form-group input,
.form-group select {
  width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .9rem; box-sizing: border-box;
}
.form-group input:focus,
.form-group select:focus { outline: none; border-color: #7367f0; }

.btn { padding: .7rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; border: none; }
.btn-secondary { background: #f7f7f7; color: #4a5568; width: 100%; }
.btn-secondary:hover { background: #e2e8f0; }
</style>
