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
                    <TreeComponent :files="item.elements" @open="handleOpen" />
                </ul>
            </template>
            <template v-else>
                <span v-on:dblclick="$emit('open', item)" v-on:click.right="showContextMenu"
                    class="cursor-pointer flex flex-1 items-center overflow-hidden">
                    <BaseIcon :path="getIconPath(item.extension)" class="mr-1" />
                    <span class="truncate"> {{ item.name }} </span>
                    <div ref="floating" :style="floatingStyles" v-if="itemsPoper && item.extension === 'hbs'"
                        class="text-sm p-2 text-left text-slate-500 border-b border-gray-100 lg:border lg:bg-white lg:absolute lg:top-full lg:left-0 lg:min-w-full lg:z-20 lg:rounded-lg lg:shadow-lg lg:dark:bg-slate-800 dark:border-slate-700">

                        <!-- Opciones del menú -->
                        <BaseButton :icon="mdiTrashCan">Eliminar</BaseButton>
                        <BaseButton :icon="mdiPencil">Renombrar</BaseButton>
                        <!-- Más botones para otras acciones -->


                    </div>

                </span>
            </template>
        </li>
    </ul>
</template>

<script setup>

import { ref } from 'vue';
import { mdiCodeJson, mdiFolderOpenOutline, mdiFolderOutline, mdiTextBox, mdiCodeArray, mdiChevronDown, mdiChevronRight, mdiTrashCan, mdiPencil } from '@mdi/js'
import BaseIcon from './BaseIcon.vue';
import { useFloating, autoUpdate } from '@floating-ui/vue';
import BaseButton from './BaseButton.vue';
import { computed } from 'vue';


const reference = ref(null);
const floating = ref(null);

const { floatingStyles } = useFloating(reference, floating, {
    whileElementsMounted: autoUpdate,
});

const props = defineProps({
    files: {
        type: Object,
        required: true,
    },
});

const itemsPoper = ref(false);

const emit = defineEmits(['open']);

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

const showContextMenu = (event, file) => {
    event.preventDefault();
    file.showDropdown = true;
};

</script>