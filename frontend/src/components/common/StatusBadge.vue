<template>
  <span class="badge" :class="badgeClass">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: String,
  type: { type: String, default: 'artifact' }, // 'project' | 'artifact' | 'module'
})

const config = {
  // Project statuses
  draft:        { label: 'Draft',        color: 'gray' },
  discovery:    { label: 'Discovery',    color: 'blue' },
  execution:    { label: 'Execution',    color: 'purple' },
  delivered:    { label: 'Delivered',    color: 'green' },
  // Artifact statuses
  not_started:  { label: 'Not Started',  color: 'gray' },
  in_progress:  { label: 'In Progress',  color: 'blue' },
  blocked:      { label: 'Blocked',      color: 'orange' },
  done:         { label: 'Done',         color: 'green' },
  // Module statuses
  validated:    { label: 'Validated',    color: 'green' },
  ready_for_build: { label: 'Ready',     color: 'purple' },
}

const label     = computed(() => config[props.status]?.label || props.status)
const badgeClass = computed(() => `badge-${config[props.status]?.color || 'gray'}`)
</script>

<style scoped>
.badge {
  display: inline-block; padding: .25rem .75rem; border-radius: 50px;
  font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em;
}
.badge-gray   { background: #edf2f7; color: #718096; }
.badge-blue   { background: #ebf8ff; color: #2b6cb0; }
.badge-green  { background: #f0fff4; color: #276749; }
.badge-purple { background: #faf5ff; color: #6b46c1; }
.badge-orange { background: #fffaf0; color: #c05621; }
</style>
