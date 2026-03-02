<template>
  <!-- Strategic Alignment Form -->
  <div class="artifact-form">
    <div class="form-group">
      <label class="field-label">Transformation</label>
      <p class="hint">What strategic transformation drives this project?</p>
      <textarea :value="modelValue.transformation" @input="update('transformation', $event.target.value)" :disabled="disabled" rows="3" />
    </div>
    <div class="form-group">
      <label class="field-label">Supported Decisions</label>
      <ArrayInput :model-value="modelValue.supported_decisions || []" @update:model-value="update('supported_decisions', $event)" :disabled="disabled" placeholder="Add decision..." />
    </div>
    <div class="form-group">
      <label class="field-label">Measurable Success</label>
      <div v-for="(m, i) in (modelValue.measurable_success || [])" :key="i" class="metric-row">
        <input :value="m.metric" @input="updateMetric(i, 'metric', $event.target.value)" placeholder="Metric" :disabled="disabled" />
        <input :value="m.target" @input="updateMetric(i, 'target', $event.target.value)" placeholder="Target" :disabled="disabled" />
        <button v-if="!disabled" type="button" @click="removeMetric(i)" class="remove-btn">✕</button>
      </div>
      <button v-if="!disabled" type="button" class="add-btn" @click="addMetric">+ Add metric</button>
    </div>
    <div class="form-group">
      <label class="field-label">Out of Scope</label>
      <ArrayInput :model-value="modelValue.out_of_scope || []" @update:model-value="update('out_of_scope', $event)" :disabled="disabled" placeholder="Add item..." />
    </div>
  </div>
</template>

<script setup>
import ArrayInput from '@/components/common/ArrayInput.vue'

const props = defineProps({ modelValue: Object, disabled: Boolean })
const emit  = defineEmits(['update:modelValue'])

function update(key, val) { emit('update:modelValue', { ...props.modelValue, [key]: val }) }
function updateMetric(i, field, val) {
  const arr = [...(props.modelValue.measurable_success || [])]
  arr[i] = { ...arr[i], [field]: val }
  update('measurable_success', arr)
}
function addMetric() { update('measurable_success', [...(props.modelValue.measurable_success || []), { metric: '', target: '' }]) }
function removeMetric(i) {
  const arr = [...(props.modelValue.measurable_success || [])]
  arr.splice(i, 1)
  update('measurable_success', arr)
}
</script>

<style scoped>
.artifact-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-group label.field-label { font-weight: 600; font-size: .875rem; color: #2d3748; display: block; margin-bottom: .25rem; }
.hint { color: #a0aec0; font-size: .8rem; margin: 0 0 .75rem; }
textarea, input[type=text], input:not([type]) {
  width: 100%; padding: .7rem .875rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .875rem; box-sizing: border-box; font-family: inherit; resize: vertical;
}
textarea:focus, input:focus { outline: none; border-color: #7367f0; }
textarea:disabled, input:disabled { background: #f8f7fa; color: #a0aec0; }
.metric-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
.metric-row input { flex: 1; }
.remove-btn { background: none; border: none; color: #e53e3e; cursor: pointer; }
.add-btn { background: none; border: 1.5px dashed #cbd5e0; color: #718096; border-radius: 8px; padding: .5rem 1rem; font-size: .8rem; cursor: pointer; width: 100%; text-align: left; transition: all .2s; }
.add-btn:hover { border-color: #7367f0; color: #7367f0; }
</style>
