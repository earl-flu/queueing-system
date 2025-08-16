<template>
  <div class="col-12 col-xl-6">
    <input
      type="radio"
      class="btn-check"
      name="theme-options"
      :id="theme.value"
      @click="$emit('change-theme', theme.value)"
      :checked="currentTheme === theme.value"
    />
    <label
      class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4"
      :for="theme.value"
    >
      <span class="material-icons-outlined">{{ theme.icon }}</span>
      <span>{{ theme.label }}</span>
    </label>
  </div>
</template>

<script setup>
const props = defineProps({
  theme: Object,
  currentTheme: String,
});

</script>
