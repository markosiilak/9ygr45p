<template>
  <div class="default-layout">
    <slot />
  </div>
</template>

<script setup lang="ts">
// Default layout
</script>

<style scoped>
.default-layout {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
}
</style>
