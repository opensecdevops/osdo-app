<template>
  <ul class="p-2 text-xl">
    <li v-for="(item, index) in files" :key="index">
      <template v-if="item.type === 'folder'">
        <span @click="toggleFolder(item.name)" class="cursor-pointer flex flex-1 items-center overflow-hidden">
          <BaseIcon :path="isFolderOpen(item.name) ? mdiChevronDown : mdiChevronRight" />
          <BaseIcon :path="isFolderOpen(item.name) ? mdiFolderOpenOutline : mdiFolderOutline" class="mr-1" />
          <span class="truncate"> {{ item.name }} </span>
        </span>
        <ul v-if="isFolderOpen(item.name)" class="pl-10">
          <List :files="item.elements" @open="handleOpen" @contextMenu="showContextMenu" :targetFile="targetFile"
            @update:targetFile="update => targetFile = update" :edit="edit" />
        </ul>
      </template>
      <template v-else>
        <div @dblclick="handleOpen(item)" @contextmenu.prevent="showContextMenu($event, item)"
          class="cursor-pointer flex flex-1 items-center overflow-hidden" :class="{ 'bg-gray-500': targetFile === item }">
          <BaseIcon :path="getIconPath(item.extension)" class="mr-1" />
          <template v-if="edit && targetFile === item">
            <input v-model="item.name" class="w-full z-50" />
          </template>
          <template v-else>
            <span class="truncate"> {{ item.name }} </span>
          </template>
        </div>
      </template>
    </li>
  </ul>
</template>
  
<script setup>
import { ref } from 'vue';
import { mdiCodeJson, mdiFolderOpenOutline, mdiFolderOutline, mdiTextBox, mdiCodeArray, mdiChevronDown, mdiChevronRight, mdiPlus } from '@mdi/js';
import BaseIcon from '@/Components/BaseIcon.vue';

const props = defineProps({
  files: Object,
  targetFile: Object,
  edit: Boolean
});

const emit = defineEmits(['open', 'contextMenu', 'update:targetFile']);

const openFolders = ref([]);

const toggleFolder = (folderName) => {
  const index = openFolders.value.indexOf(folderName);
  if (index === -1) {
    openFolders.value.push(folderName);
  } else {
    openFolders.value.splice(index, 1);
  }
};

const isFolderOpen = (folderName) => openFolders.value.includes(folderName);

const getIconPath = (extension) => {
  switch (extension) {
    case 'json':
      return mdiCodeJson;
    case 'hbs':
      return mdiCodeArray;
    default:
      return mdiTextBox;
  }
};

const showContextMenu = (event, file) => {
  event.preventDefault();
  emit('update:targetFile', file);
  emit('contextMenu', event, file);
};

function handleOpen(file) {
  emit('open', file);
}

function AddFile() {
  console.log('Add File');
}
</script>
  