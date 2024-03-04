<template>
    <List :class="{'hidden': testView}" :files="props.files" @open="handleOpen" @contextMenu="showContextMenu"  v-model:targetFile="targetFile" :edit="edit" />
    <div class="fixed top-[0] left-[0] w-full h-full bg-transparent before:content-[''] before:absolute before:w-full before:h-full hover:cursor-pointer"
        @click="closeContextMenu" v-if="showMenu || edit" />


    <ContextMenu v-if="showMenu && !edit" :actions="contextMenuActions" @action-clicked="handleActionClick" :x="menuX" :y="menuY" />

    <CardBoxModal v-model="modalConfirmDelete" title="Please confirm deleting the item" button-label="Confirm"
        button="danger" has-cancel @confirm="$emit('delete', targetFile), targetFile = null" @cancel="targetFile = null">
        <p>are you sure, want delete the item? </p>
        <p>Not is possible revert!</p>
    </CardBoxModal>
</template>

<script setup>

import { ref } from 'vue';
import ContextMenu from '@/Components/ContextMenu.vue';
import CardBoxModal from '@/Components/CardBoxModal.vue'
import List from './List.vue'

const targetFile = ref(null);

const contextMenuActions = ref([
    { label: 'Edit', action: 'edit' },
    { label: 'Delete', action: 'delete' },
]);
const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
    testView: {
        type: Boolean,
        required: true,
    },
});


const emit = defineEmits(['open', 'delete']);
const currentItem = ref(null);
const edit = ref(false);

function handleOpen(file) {
    emit('open', file);
}

const showMenu = ref(false);
const menuX = ref(0);
const menuY = ref(0);
const targetRow = ref({});

const showContextMenu = (event, file) => {
    if (file.extension === 'hbs') {
        event.preventDefault();
        showMenu.value = true;
        targetRow.value = file;
        menuX.value = event.clientX;
        menuY.value = event.clientY;
    }
};

const showDelete = ref(false);
const modalConfirmDelete = ref(false)

function handleActionClick(action) {
    if (action === 'delete') {
        modalConfirmDelete.value = true;
        showMenu.value = false;
    }
    if (action === 'edit') {
        edit.value = true;
        showMenu.value = false;
    }

    console.log(action);
    console.log(targetRow.value);
}


const closeContextMenu = () => {
    showMenu.value = false;
    edit.value = false;
    targetRow.value = null;
};
</script>