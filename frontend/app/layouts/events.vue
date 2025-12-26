<template>
  <div class="events-layout">
    <slot />
  </div>
</template>

<script setup lang="ts">
// Events layout - full page with infinite scroll support
</script>

<style scoped>
.events-layout {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
}
</style>
