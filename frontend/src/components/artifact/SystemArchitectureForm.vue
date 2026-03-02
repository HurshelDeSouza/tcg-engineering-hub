<template>
  <div class="form-container">
    <h3>System Architecture</h3>
    <p class="form-description">Define the overall system architecture and technical decisions.</p>

    <div class="form-group">
      <label>Authentication Model *</label>
      <input 
        v-model="localContent.auth_model" 
        placeholder="e.g., JWT with refresh tokens, OAuth 2.0, Session-based"
        required
      />
      <span class="hint">Minimum 5 characters</span>
    </div>

    <div class="form-group">
      <label>API Style *</label>
      <input 
        v-model="localContent.api_style" 
        placeholder="e.g., REST, GraphQL, gRPC"
        required
      />
      <span class="hint">Minimum 3 characters</span>
    </div>

    <div class="form-group">
      <label>Data Model Notes *</label>
      <textarea 
        v-model="localContent.data_model_notes" 
        placeholder="Describe the data model approach, database choice, normalization strategy, etc."
        rows="4"
        required
      />
      <span class="hint">Minimum 10 characters</span>
    </div>

    <div class="form-group">
      <label>Scalability Notes (Optional)</label>
      <textarea 
        v-model="localContent.scalability_notes" 
        placeholder="How will the system scale? Load balancing, caching, horizontal/vertical scaling, etc."
        rows="4"
      />
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:modelValue'])

const localContent = reactive({
  auth_model: props.modelValue.auth_model || '',
  api_style: props.modelValue.api_style || '',
  data_model_notes: props.modelValue.data_model_notes || '',
  scalability_notes: props.modelValue.scalability_notes || ''
})

function emitUpdate() {
  emit('update:modelValue', { ...localContent })
}

watch(localContent, () => {
  emitUpdate()
}, { deep: true })
</script>

<style scoped>
.form-container { padding: 1rem; }
.form-description { color: #718096; margin-bottom: 1.5rem; font-size: .9rem; }

.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-size: .875rem; font-weight: 500; color: #4a5568; margin-bottom: .5rem; }
.form-group input,
.form-group textarea {
  width: 100%; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .9rem; box-sizing: border-box; font-family: inherit;
}
.form-group input:focus,
.form-group textarea:focus { outline: none; border-color: #7367f0; }

.hint { display: block; font-size: .75rem; color: #a0aec0; margin-top: .25rem; }
</style>
