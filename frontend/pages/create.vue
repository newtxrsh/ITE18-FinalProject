<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh]" style="background: #ffffff;">
    <div class="text-center">
      <div class="w-20 h-20 mx-auto mb-6 rounded-2xl flex items-center justify-center" style="background: rgb(15, 24, 38);">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#8db3ff" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold mb-3" style="color: #000000;">Create Task</h1>
      <p class="mb-6" style="color: rgba(0, 0, 0, 0.5);">
        Use the <strong>Create</strong> button in the sidebar to create a new task from anywhere.
      </p>
      <NuxtLink 
        to="/tasks" 
        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-semibold transition-all"
        style="background: rgb(15, 24, 38); color: #8db3ff;"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 11l3 3L22 4"/>
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        </svg>
        <span>Go to Tasks</span>
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
// SEO Meta
useHead({
  title: 'Create Task',
  meta: [
    { name: 'description', content: 'Create a new task with title, description, due date, subtasks, and collaborators.' },
    { property: 'og:title', content: 'Create Task | Task Manager' },
    { property: 'og:description', content: 'Create and organize new tasks with subtasks and collaborators.' },
  ],
})
</script>

<style scoped>
/* No additional styles needed */
</style>
