<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
});
</script>

<template>
  <button
    :type="type"
    class="inline-flex items-center rounded-full border border-input bg-background px-6 py-2.5 text-sm font-semibold text-foreground shadow-sm transition duration-150 ease-in-out hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background disabled:opacity-25"
  >
    <slot />
  </button>
</template>
