<template>
  <div class="artifact-form">
    <div class="form-group">
      <label class="field-label">Ecosystem Vision</label>
      <textarea :value="modelValue.ecosystem_vision" @input="update('ecosystem_vision', $event.target.value)" :disabled="disabled" rows="3" />
    </div>
    <div class="form-group">
      <label class="field-label">Impacted Domains</label>
      <ArrayInput :model-value="modelValue.impacted_domains || []" @update:model-value="update('impacted_domains', $event)" :disabled="disabled" placeholder="E.g: Auth, Catalog, Orders..." />
    </div>
    <div class="form-group">
      <label class="field-label">Success Definition</label>
      <textarea :value="modelValue.success_definition" @input="update('success_definition', $event.target.value)" :disabled="disabled" rows="3" />
    </div>
  </div>
</template>

<script setup>
import ArrayInput from '@/components/common/ArrayInput.vue'
const props = defineProps({ modelValue: Object, disabled: Boolean })
const emit  = defineEmits(['update:modelValue'])
function update(key, val) { emit('update:modelValue', { ...props.modelValue, [key]: val }) }
</script>

<style scoped>
.artifact-form { display: flex; flex-direction: column; gap: 1.5rem; }
.form-group label.field-label { font-weight: 600; font-size: .875rem; color: #2d3748; display: block; margin-bottom: .75rem; }
textarea { width: 100%; padding: .7rem .875rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .875rem; box-sizing: border-box; font-family: inherit; resize: vertical; }
textarea:focus { outline: none; border-color: #7367f0; }
textarea:disabled { background: #f8f7fa; color: #a0aec0; }
</style>
