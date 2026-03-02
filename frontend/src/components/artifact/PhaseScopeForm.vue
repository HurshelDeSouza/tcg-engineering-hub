<template>
  <div class="form-container">
    <h3>Phase Scope</h3>
    <p class="form-description">Define what's included and excluded in this phase.</p>

    <div class="form-group">
      <label>Included Modules *</label>
      <div class="modules-selector">
        <div v-if="availableModules.length === 0" class="empty-state">
          No modules available. Create modules first in the Modules tab.
        </div>
        <div v-else class="checkbox-list">
          <label v-for="module in availableModules" :key="module.id" class="checkbox-item">
            <input 
              type="checkbox" 
              :value="module.id" 
              v-model="localContent.included_modules"
              @change="emitUpdate"
            />
            <span>{{ module.name }} ({{ module.domain }})</span>
          </label>
        </div>
      </div>
      <span class="hint">Select at least one module</span>
    </div>

    <div class="form-group">
      <label>Excluded Items (Optional)</label>
      <ArrayInput 
        v-model="localContent.excluded_items" 
        placeholder="e.g., Mobile app, Admin panel, Third-party integrations"
      />
    </div>

    <div class="form-group">
      <label>Acceptance Criteria *</label>
      <ArrayInput 
        v-model="localContent.acceptance_criteria" 
        placeholder="e.g., All tests passing, Code review completed"
      />
      <span class="hint">Add at least one acceptance criterion</span>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { modulesApi } from '@/api'
import ArrayInput from '@/components/common/ArrayInput.vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:modelValue'])
const route = useRoute()

const availableModules = ref([])

const localContent = reactive({
  included_modules: props.modelValue.included_modules || [],
  excluded_items: props.modelValue.excluded_items || [],
  acceptance_criteria: props.modelValue.acceptance_criteria || []
})

async function loadModules() {
  try {
    const { data } = await modulesApi.list(route.params.id)
    availableModules.value = data.data || []
  } catch (err) {
    console.error('Failed to load modules:', err)
  }
}

function emitUpdate() {
  emit('update:modelValue', { ...localContent })
}

watch(() => localContent.excluded_items, () => {
  emitUpdate()
}, { deep: true })

watch(() => localContent.acceptance_criteria, () => {
  emitUpdate()
}, { deep: true })

onMounted(() => {
  loadModules()
})
</script>

<style scoped>
.form-container { padding: 1rem; }
.form-description { color: #718096; margin-bottom: 1.5rem; font-size: .9rem; }

.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }

.modules-selector {
  border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 1rem; background: #f8f9fa;
}

.empty-state { color: #a0aec0; text-align: center; padding: 2rem; font-size: .9rem; }

.checkbox-list { display: flex; flex-direction: column; gap: .75rem; }

.checkbox-item {
  display: flex; align-items: center; gap: .75rem; cursor: pointer; padding: .5rem;
  border-radius: 6px; transition: background .2s;
}
.checkbox-item:hover { background: #fff; }

.checkbox-item input[type="checkbox"] {
  width: 18px; height: 18px; cursor: pointer;
}

.checkbox-item span { font-size: .9rem; color: #2d3748; }

.hint { display: block; font-size: .75rem; color: #a0aec0; margin-top: .25rem; }
</style>
