<template>
  <div>
    <p class="hint">Edit the JSON content of this artifact:</p>
    <textarea :value="jsonValue" @input="handleInput" :disabled="disabled" rows="15" class="json-editor" />
    <p v-if="jsonError" class="json-error">{{ jsonError }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
const props = defineProps({ modelValue: Object, disabled: Boolean })
const emit  = defineEmits(['update:modelValue'])
const jsonError = ref(null)

const jsonValue = computed(() => JSON.stringify(props.modelValue, null, 2))

function handleInput(e) {
  try {
    const parsed = JSON.parse(e.target.value)
    jsonError.value = null
    emit('update:modelValue', parsed)
  } catch {
    jsonError.value = 'Invalid JSON'
  }
}
</script>

<style scoped>
.hint { color: #a0aec0; font-size: .875rem; margin-bottom: .75rem; }
.json-editor { width: 100%; padding: .875rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-family: 'Courier New', monospace; font-size: .875rem; box-sizing: border-box; resize: vertical; }
.json-editor:focus { outline: none; border-color: #7367f0; }
.json-error { color: #e53e3e; font-size: .8rem; margin-top: .25rem; }
</style>
