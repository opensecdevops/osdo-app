<script setup>
import { Head, router } from '@inertiajs/vue3'
import { mdiClose } from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import TreeComponent from '@/Components/TreeComponent.vue'
import { ref, shallowRef, computed, reactive } from 'vue'
import axios from 'axios'

const MONACO_EDITOR_OPTIONS = {
    automaticLayout: true,
    formatOnType: true,
    formatOnPaste: true,
}

const porps = defineProps({
    package: Object,
    files: Object
});

const openFiles = ref([]);

const codes = reactive({});

const getFile = (file) => {
    const index = openFiles.value.indexOf(file);
    if (index === -1) {
        console.log(route().params.packageName);
        const filePah = file.path ? file.path + '/' + file.name : file.name;
        console.log(filePah);

        axios.get(route('packages.file', { packageName: route().params.packageName, file: filePah })).then(response => {
            const fullPath = getFullPath(file);
            let code;
            if (file.extension === 'json' && typeof response.data === 'object') {
                code = JSON.stringify(response.data, null, 2);
            } else {
                code = response.data;
            }
            codes[fullPath] = {
                originalCode: code,
                current: code,
                extension: file.extension
            }

        });

        openFiles.value.push(file);
        selectedFile.value = file;
    }
};

const closeFile = (file) => {
    const index = openFiles.value.indexOf(file);
    if (index !== -1) {
        openFiles.value.splice(index, 1);
        if (selectedFile.value === file) {
            selectedFile.value = openFiles.value[openFiles.value.length - 1];
        }
    }
};

const code = ref();

const selectedFile = ref();

const editorRef = shallowRef()
const handleMount = editor => (editorRef.value = editor)

const selectedFileCode = computed({
    get() {
        const fullPath = getFullPath(selectedFile.value);
        if (selectedFile.value) {
            if (codes[fullPath]) {
                return codes[fullPath].current;
            }
        }
        return '';
    },
    set(value) {
        const fullPath = getFullPath(selectedFile.value);
        if (codes[fullPath]) {
            codes[fullPath].current = value;
        }
    }
});

const getFullPath = (file) => {
    return file.path ? file.path + '/' + file.name : file.name;
};

const renderEditor = computed(() => {
    return selectedFile.value !== undefined;
});

</script>

<template>
    <Head title="Editor Package" />
    <LayoutAuthenticated>
        <section class="p-6">
            <div class="flex h-screen ">
                <!-- Barra lateral de archivos -->
                <div class="w-1/6 bg-gray-800 text-white rounded-l-lg overflow-auto">
                    <TreeComponent :files="files" @open="getFile" />
                </div>

                <!-- Área principal -->
                <div class="flex flex-col w-4/6 ">
                    <!-- Área de pestañas -->
                    <div class="bg-gray-200 flex rounded-tr-lg overflow-auto min-h-12">
                        <div v-for="file in openFiles" :key="file" @click="selectedFile = file"
                            :class="{ 'text-sky-500': selectedFile === file }"
                            class="flex items-center justify-between bg-white px-4 py-2 mt-1 ml-1 rounded-t-lg shadow ">
                            <span>{{ file.name }}</span>
                            <BaseIcon class="text-gray-800" @click.stop="closeFile(file)" :path="mdiClose" />
                        </div>

                    </div>

                    <!-- Área de edición de código -->
                    <div class="flex-grow bg-white rounded-br-lg">
                        <vue-monaco-editor v-if="renderEditor" v-model:value="selectedFileCode" theme="vs-dark"
                            language="markdown" :options="MONACO_EDITOR_OPTIONS" @mount="handleMount" />
                    </div>
                </div>
            </div>

        </section>


    </LayoutAuthenticated>
</template>