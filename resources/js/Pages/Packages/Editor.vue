<script setup>
import { Head } from '@inertiajs/vue3'
import { mdiClose, mdiTestTube, mdiCodeBraces, mdiSourceBranch } from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import TreeComponent from '@/Components/TreeList/TreeComponent.vue'
import { ref, shallowRef, computed, reactive } from 'vue'
import axios from 'axios'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Generator from '@/Pages/Generator/Generator.vue'

const MONACO_EDITOR_OPTIONS = {
    automaticLayout: true,
    formatOnType: true,
    formatOnPaste: true,
}

const props = defineProps({
    package: Object,
    structure: Object
});

const testView = ref(false);

const form = ref();

const templates = ref();

const openFiles = reactive({});

const selectedFile = ref();

const editorRef = shallowRef()
const handleMount = editor => (editorRef.value = editor)


const getFile = (file) => {
    const fullPath = getFullPath(file);
    console.log('getFile', fullPath, file)
    if (openFiles[fullPath]) {
        openFiles[fullPath].show = true;
        selectedFile.value = openFiles[fullPath];
        return;
    }
    openFiles[fullPath] = { ...file, show: true };
    console.log(openFiles)
    selectedFile.value = file;
};

const closeFile = (file) => {
    const fullPath = getFullPath(file);
    openFiles[fullPath].show = false;
    if (selectedFile.value === file) {
        const keys = Object.keys(openFiles);
        for (let i = 0; i < keys.length; i++) {
            if (openFiles[keys[i]].show) {
                selectedFile.value = openFiles[keys[i]];
                return;
            }
        }
        selectedFile.value = undefined;
    }
};


const selectedFileCode = computed({
    get() {
        const fullPath = getFullPath(selectedFile.value);
        if (selectedFile.value) {
            if (openFiles[fullPath]) {
                return openFiles[fullPath].content;
            }
        }
        return '';
    },
    set(value) {
        const fullPath = getFullPath(selectedFile.value);
        if (openFiles[fullPath]) {
            openFiles[fullPath].content = value;
        }
    }
});

const getFullPath = (file) => {
    return file.path ? file.path + '/' + file.name : file.name;
};

const renderEditor = computed(() => {
    return selectedFile.value !== undefined;
});

const extensionLanguage = [
    {
        "extension": "json",
        "language": "json"
    },
    {
        "extension": "md",
        "language": "markdown"
    },
    {
        "extension": "hbs",
        "language": "handlebars"
    }
]

const computedTypeFile = computed(() => {
    if (selectedFile.value) {
        const extension = selectedFile.value.extension;
        const language = extensionLanguage.find(item => item.extension === extension);
        if (language) {
            return language.language;
        }
    }
    return '';
});

const enableTest = () => {

    if (openFiles['config.json']) {
        form.value = JSON.parse(openFiles['config.json'].content);
    } else {
        for (const key in props.structure) {
            if (props.structure[key].name == 'config.json') {
                form.value = JSON.parse(props.structure[key].content);
            };
        }
    }
    const files = [];
    const regex = /([a-zA-Z0-9\s_\\.\-\(\):]+)(.md|.js|.hbs)/;
    const subst = `$1`;
    let content;
    for (const key in props.structure['templates']['elements']) {
        if(openFiles[getFullPath(props.structure['templates']['elements'][key])]){
            content = openFiles[getFullPath(props.structure['templates']['elements'][key])].content;
        } else {
            content = props.structure['templates']['elements'][key].content;
        }
        const str = props.structure['templates']['elements'][key].name

        const result = str.replace(regex, subst);

        files.push({
            file: result,
            content: content
        });
    }
    templates.value = files;
    testView.value = !testView.value;
};

</script>

<template>
    <Head title="Editor Package" />
    <LayoutAuthenticated>
        <section class="p-6">
            <div class="flex h-screen ">
                <!-- Barra lateral de archivos -->
                <div class="w-1/6 dark:bg-gray-900 dark:text-white text-black bg-slate-300 rounded-l-lg overflow-auto">
                    <BaseButtons type="justify-center">
                        <BaseButton v-if="testView" :icon="mdiCodeBraces" label="Code" color="info" @click="testView = false" />
                        <BaseButton v-else :icon="mdiTestTube" label="Test" color="info" @click="enableTest()" />
                    </BaseButtons>
                    <TreeComponent :files="structure" @open="getFile" />
                </div>

                <!-- Área principal -->
                <div class="flex flex-col w-4/6 overflow-y-auto overflow-x-hidden	 ">
                    <template v-if="testView">
                        <Generator :form="form" :templates="templates" />
                    </template>
                    <template v-else>
                        <!-- Área de pestañas -->
                        <div class="bg-gray-200 flex rounded-tr-lg overflow-auto min-h-12">
                            <div v-for="file in openFiles" :key="file" @click="selectedFile = file">
                                <div v-if="file.show" :class="{ 'text-sky-500': selectedFile === file }"
                                    class="flex items-center justify-between bg-white dark:bg-gray-800 dark:text-white px-4 py-2 mt-1 ml-1 rounded-t-lg shadow ">
                                    <span>{{ file.name }}</span>
                                    <BaseIcon class="text-gray-800 dark:text-white" @click.stop="closeFile(file)" :path="mdiClose" />
                                </div>
                            </div>

                        </div>

                        <!-- Área de edición de código -->
                        <div class="flex-grow bg-white rounded-br-lg">

                            <vue-monaco-editor v-if="renderEditor" v-model:value="selectedFileCode" theme="vs-dark"
                                :language="computedTypeFile" :options="MONACO_EDITOR_OPTIONS" @mount="handleMount" />

                        </div>
                    </template>
                </div>
            </div>

        </section>


    </LayoutAuthenticated>
</template>