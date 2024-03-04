<template>
    <div ref="editorContainer" class="editor-container"></div>
  </template>
  
  <script setup>
  import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
  import * as monaco from 'monaco-editor';
  import { useProps } from '@vueuse/core'; // Asegúrate de instalar @vueuse/core si aún no lo has hecho
  
  const { value, language } = useProps(['value', 'language']);
  const editorContainer = ref(null);
  const editor = ref(null);
  
  // Observa los cambios en 'value' para actualizar el contenido del editor
  watch(value, (newValue) => {
    if (editor.value && newValue !== editor.value.getValue()) {
      editor.value.setValue(newValue);
    }
  });
  
  // Inicializa Monaco Editor al montar el componente
  onMounted(() => {
    editor.value = monaco.editor.create(editorContainer.value, {
      value: value.value,
      language: language.value,
    });
  
    editor.value.onDidChangeModelContent(() => {
      emit('input', editor.value.getValue());
    });
  });
  
  // Limpia el editor antes de desmontar el componente
  onBeforeUnmount(() => {
    if (editor.value) {
      editor.value.dispose();
    }
  });
  </script>
  
  <style>
  .editor-container {
    height: 100%; /* or set a specific height */
    width: 100%;
  }
  </style>
  