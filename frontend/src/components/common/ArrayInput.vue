<template>
  <div class="array-input">
    <div v-for="(item, i) in modelValue" :key="i" class="item-row">
      <input
        :value="item"
        :type="type"
        :disabled="disabled"
        @input="update(i, $event.target.value)"
        :placeholder="placeholder"
      />
      <button v-if="!disabled" type="button" class="remove-btn" @click="remove(i)">✕</button>
    </div>
    <button v-if="!disabled" type="button" class="add-btn" @click="add">+ {{ placeholder }}</button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  disabled:   { type: Boolean, default: false },
  placeholder:{ type: String, default: 'Add...' },
  type:       { type: String, default: 'text' },
})

const emit = defineEmits(['update:modelValue'])

function update(i, val) {
  const arr = [...props.modelValue]
  arr[i] = props.type === 'number' ? Number(val) : val
  emit('update:modelValue', arr)
}

function remove(i) {
  const arr = [...props.modelValue]
  arr.splice(i, 1)
  emit('update:modelValue', arr)
}

function add() {
  emit('update:modelValue', [...props.modelValue, props.type === 'number' ? null : ''])
}
</script>

<style scoped>
.array-input { display: flex; flex-direction: column; gap: .5rem; }
.item-row { display: flex; gap: .5rem; align-items: center; }
.item-row input {
  flex: 1; padding: .6rem .875rem; border: 1.5px solid #e2e8f0;
  border-radius: 8px; font-size: .875rem;
}
.item-row input:focus { outline: none; border-color: #7367f0; }
.item-row input:disabled { background: #f8f7fa; color: #a0aec0; }
.remove-btn { background: none; border: none; color: #e53e3e; cursor: pointer; font-size: .9rem; padding: 0 .25rem; }
.add-btn {
  background: none; border: 1.5px dashed #cbd5e0; color: #718096;
  border-radius: 8px; padding: .5rem 1rem; font-size: .8rem; cursor: pointer; text-align: left;
  transition: all .2s;
}
.add-btn:hover { border-color: #7367f0; color: #7367f0; }
</style>
