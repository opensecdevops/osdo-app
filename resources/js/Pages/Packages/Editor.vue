<script setup>
import { Head } from '@inertiajs/vue3'
import { mdiClose, mdiTestTube, mdiCodeBraces, mdiSourceBranch } from '@mdi/js'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import TreeComponent from '@/Components/TreeList/TreeComponent.vue'
import { ref, shallowRef, computed, reactive, onMounted } from 'vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Generator from '@/Pages/Generator/Generator.vue'
import Ajv from "ajv";
import schema from "./schema.json";
import betterAjvErrors from 'better-ajv-errors';

const props = defineProps({
    package: Object,
    structure: Object
});

let original = {};

onMounted(() => {
    original = JSON.parse(JSON.stringify(props.structure));
    console.log(original);
});

const testView = ref(false);
const form = ref();
const templates = ref();
const openFiles = reactive({});
const selectedFile = ref();
const editorRef = shallowRef();
const errors = ref([]);

const MONACO_EDITOR_OPTIONS = {
    automaticLayout: true,
    formatOnType: true,
    formatOnPaste: true,
};

const MONACO_EDITOR_OPTIONS_DIFF = {
    automaticLayout: true,
    formatOnType: true,
    formatOnPaste: true,
    readOnly: true,
}

const getFullPath = (file) => file.path ? `${file.path}/${file.name}` : file.name;

const getFile = (file) => {
    const fullPath = getFullPath(file);
    if (!openFiles[fullPath]) {
        openFiles[fullPath] = { ...file, show: true };
    }
    openFiles[fullPath].show = true;
    selectedFile.value = openFiles[fullPath];
};

const closeFile = (file) => {
    const fullPath = getFullPath(file);
    if (openFiles[fullPath]) {
        openFiles[fullPath].show = false;
        if (selectedFile.value === openFiles[fullPath]) {
            const nextFile = Object.values(openFiles).find(f => f.show);
            selectedFile.value = nextFile || null;
        }
    }
};

const selectedFileCode = computed({
    get: () => selectedFile.value?.content || '',
    set: (value) => {
        if (selectedFile.value) {
            selectedFile.value.content = value;
        }
    }
});

const extensionLanguage = [
    { extension: "json", language: "json" },
    { extension: "md", language: "markdown" },
    { extension: "hbs", language: "handlebars" }
];

const computedTypeFile = computed(() => {
    const extension = selectedFile.value?.extension;
    return extensionLanguage.find(item => item.extension === extension)?.language || '';
});

const isValid = ref(true);


const extractDependencies = (objeto) => {
    const uniqueDependencies = new Set();

    const loopObject = (obj) => {
        Object.keys(obj).forEach(key => {
            if (key === 'dependencies' && Array.isArray(obj[key])) {
                obj[key].forEach(dep => uniqueDependencies.add(dep));
            } else if (obj[key] && typeof obj[key] === 'object') {
                loopObject(obj[key]);
            }
        });
    };

    loopObject(objeto);

    return [...uniqueDependencies];
};


const enableTest = () => {

    let formTmp = {}
    errors.value = [];
    if (openFiles['config.json']) {
        formTmp = JSON.parse(openFiles['config.json'].content);
    } else {
        for (const key in props.structure) {
            if (props.structure[key].name == 'config.json') {
                formTmp = JSON.parse(props.structure[key].content);
            };
        }
    }
    const ajv = new Ajv();
    const validate = ajv.compile(schema);
    isValid.value = validate(formTmp);
    if (!isValid.value) {
        errors.value.push(betterAjvErrors(schema, formTmp, validate.errors, { format: "js" }));
        console.log(errors.value);
    }

    //search in blocks recursive dependencies elements and create a array with names unique
    const uniqueDependencies = extractDependencies(formTmp.blocks);

    const missingDependencies = uniqueDependencies.filter(dependency =>
        !formTmp.blocks.some(block => block.template === dependency));

    if (missingDependencies.length > 0) {
        errors.value.push({ error: `Missing dependencies: ${missingDependencies.join(', ')}` });
        isValid.value = false;
    }

    const files = [];
    const regex = /([a-zA-Z0-9\s_\\.\-\(\):]+)(.md|.js|.hbs)/;
    const subst = `$1`;
    let content;
    for (const key in props.structure['templates']['elements']) {
        if (openFiles[getFullPath(props.structure['templates']['elements'][key])]) {
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

    //check if all files define in formTmp.blocks are in the files array
    for (const key in formTmp.blocks) {
        if (!files.find(file => file.file === formTmp.blocks[key].template)) {
            if (formTmp.blocks[key].template !== 'undefined') {
                errors.value.push({ error: `File ${formTmp.blocks[key].template} not found` });
                isValid.value = false;
            }
        }
    }

    console.log(errors.value);

    if (!isValid.value) {
        return;
    }

    form.value = formTmp;
    templates.value = files;
    testView.value = !testView.value;
};


const deleteFile = (file) => {
    const fullPath = getFullPath(file);
    delete openFiles[fullPath];
    const index = props.structure.templates.elements.findIndex(element => getFullPath(element) === fullPath);
    if (index !== -1) {
        props.structure.templates.elements.splice(index, 1);
    }
};

const closeError = () => {
    isValid.value = true;
    errors.value = [];
}

const enableCommit = ref(false);

const ListCommitFiles = ref([]);

const reviewCommit = () => {

    const compareAndAddToList = (original, newData) => {
        if (original.content !== newData.content) {
            ListCommitFiles.value.push({
                original: original,
                new: newData
            });
        }
    };

    const searchAndCompare = (fileKey, fileData, structure) => {
        Object.values(structure).forEach(item => {
            if (item.type === 'file' && item.name === fileKey) {
                compareAndAddToList(item, fileData);
            } else if (item.type === 'folder' && item.elements) {
                item.elements.forEach(file => {
                    if (file.name === fileKey) {
                        compareAndAddToList(file, fileData);
                    }
                });
            }
        });
    };

    for (const fileKey in openFiles) {
        searchAndCompare(fileKey, openFiles[fileKey], props.structure);
    }

    console.log(ListCommitFiles.value);
};



</script>

<template>
    <Head title="Editor Package" />
    <LayoutAuthenticated>
        <section class="p-6">
            <div class="flex">
                <div class="w-10/12 bg-red-500 text-white rounded-t-lg p-4" v-if="!isValid">
                    <BaseIcon class="float-right cursor-pointer" :path="mdiClose" @click="closeError()" />

                    <b>Whoops! Something went wrong.</b>
                    <div v-for="(error, key) in errors" :key="error">
                        {{ error.error }}
                    </div>
                </div>
            </div>

            <div class="flex h-screen">


                <!-- Barra lateral de archivos -->
                <div class="w-1/6 bg-slate-300 dark:bg-gray-900 dark:text-white text-black rounded-l-lg overflow-auto">
                    <BaseButtons type="justify-center">
                        <BaseButton v-if="testView" :icon="mdiCodeBraces" label="Code" color="info"
                            @click="testView = false" />
                        <BaseButton v-else :icon="mdiTestTube" label="Test" color="info" @click="enableTest" />
                        <BaseButton :icon="mdiSourceBranch" label="Commit" color="info" @click="reviewCommit" />

                    </BaseButtons>
                    <TreeComponent :files="structure" :testView="testView" @open="getFile" @delete="deleteFile" />
                </div>

                <!-- Área principal -->
                <div class="flex flex-col w-4/6 overflow-y-auto">
                    <template v-if="testView">
                        <Generator :form="form" :templates="templates" />
                    </template>
                    <template v-else>
                        <!-- Área de pestañas -->
                        <div class="bg-gray-200 flex rounded-tr-lg overflow-auto min-h-12">
                            <div v-for="file in Object.values(openFiles)" :key="file.name">
                                <div class="flex items-center justify-between px-4 py-2 mt-1 ml-1 bg-white dark:bg-gray-800 rounded-t-lg shadow"
                                    v-if="file.show" @click="selectedFile = file"
                                    :class="{ 'text-sky-500': selectedFile === file }">
                                    <span>{{ file.name }}</span>
                                    <BaseIcon :path="mdiClose" @click.stop="closeFile(file)" />
                                </div>
                            </div>
                        </div>

                        <!-- Área de edición de código -->
                        <div class="flex-grow bg-white rounded-br-lg">

                            <vue-monaco-diff-editor v-if="enableCommit" theme="vs-dark" original="// the original code"
                                modified="// the modified code" language="javascript" :options="MONACO_EDITOR_OPTIONS_DIFF"
                                @mount="handleMount" />

                            <vue-monaco-editor v-else v-model:value="selectedFileCode" theme="vs-dark"
                                :language="computedTypeFile" :options="MONACO_EDITOR_OPTIONS" />
                        </div>
                    </template>
                </div>
            </div>
        </section>
    </LayoutAuthenticated>
</template>
