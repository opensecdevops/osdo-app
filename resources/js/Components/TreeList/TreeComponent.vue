<template>
    <List :files="props.files" @open="handleOpen" @contextMenu="showContextMenu" />
    <div class="fixed top-[0] left-[0] w-full h-full bg-transparent before:content-[''] before:absolute before:w-full before:h-full hover:cursor-pointer"
        @click="closeContextMenu" v-if="showMenu" />


    <ContextMenu v-if="showMenu" :actions="contextMenuActions" @action-clicked="handleActionClick" :x="menuX" :y="menuY" />

    <CardBoxModal v-model="modalConfirmDelete" title="Please confirm deleting the item" button-label="Confirm"
        button="danger" has-cancel @confirm="$emit('delete', currentItem)">
        <p>are you sure, want delete the item? </p>
        <p>Not is possible revert!</p>
    </CardBoxModal>
</template>

<script setup>

import { ref } from 'vue';
import { mdiCodeJson, mdiTextBox, mdiCodeArray,} from '@mdi/js'
import ContextMenu from '@/Components/ContextMenu.vue';
import CardBoxModal from '@/Components/CardBoxModal.vue'
import List from './List.vue'


const reference = ref(null);
const floating = ref(null);

const stylesFloating = ref(null);
const contextMenuActions = ref([
    { label: 'Edit', action: 'edit' },
    { label: 'Delete', action: 'delete' },
]);
const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
});


const emit = defineEmits(['open']);

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

    }
    console.log(action);
    console.log(targetRow.value);
}


const closeContextMenu = () => {
    showMenu.value = false;
    targetRow.value = null;
};
</script>