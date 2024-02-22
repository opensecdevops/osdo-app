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
                    <List :files="item.elements" @open="handleOpen" @contextMenu="showContextMenu"/>
                </ul>
            </template>
            <template v-else>
                <div v-on:dblclick="$emit('open', item)" @contextmenu.prevent="showContextMenu($event, item)"
                    class="cursor-pointer flex flex-1 items-center overflow-hidden"
                    :class="{ 'bg-gray-500': targetRow == item }">
                    <BaseIcon :path="getIconPath(item.extension)" class="mr-1" />
                    <span class="truncate"> {{ item.name }} </span>


                </div>
            </template>
        </li>
    </ul>
</template>

<script setup>

import { ref } from 'vue';
import { mdiCodeJson, mdiFolderOpenOutline, mdiFolderOutline, mdiTextBox, mdiCodeArray, mdiChevronDown, mdiChevronRight, mdiTrashCan, mdiPencil } from '@mdi/js'
import BaseIcon from '@/Components/BaseIcon.vue';


const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
});

//Create array of refs to store the floating elements

const emit = defineEmits(['open', 'contextMenu']);

function handleOpen(file) {
    emit('open', file);
}

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

const showMenu = ref(false);
const targetRow = ref({});

const showContextMenu = (event, file) => {
    if (file.extension === 'hbs') {
        event.preventDefault();
        targetRow.value = file;
        emit('contextMenu',  event, file);
    }
};

const showDelete = ref(false);
const modalConfirmDelete = ref(false)

function handleActionClick(action) {
    if (action === 'delete') {

    }
    console.log(action);
    console.log(targetRow.value);
}


const closeContextMenu = () => {
    showMenu.value = false;
    targetRow.value = null;
};
</script>