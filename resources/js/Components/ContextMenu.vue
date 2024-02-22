<template>
    <div
        class="fixed z-50 absolute text-sm border-b border-gray-100 border bg-white left-0 rounded-lg shadow-lg dark:bg-slate-800 dark:border-slate-700"
        :style="{ top: y + 'px', left: x + 'px' }"
    >
      <div class="block lg:flex items-center relative cursor-pointer navbar-item-label text-black dark:text-white hover:text-slate-400 dark:hover:text-slate-400 py-2 px-3"
          v-for="action in actions"
          :key="action.action"
          @click="emitAction(action.action)"
      >
        {{ action.label }}
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const { actions, x, y } = defineProps(['actions', 'x', 'y']);
  const emit = defineEmits(['action-clicked']);
  
  const emitAction = (action) => {
    emit('action-clicked', action);
  };
  </script>
