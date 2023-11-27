<script setup>
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { Head, router } from '@inertiajs/vue3'
import FormCheckRadio from '@/Components/FormCheckRadio.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import CardBox from '@/Components/CardBox.vue';
import { reactive, watch, ref } from 'vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiCompost, mdiFileCodeOutline, mdiPlus, mdiDeleteForeverOutline, mdiChevronDown, mdiChevronUp, mdiContentCopy  } from '@mdi/js'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'
import Tabs from '@/Components/Tabs/Tabs.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { useFloating, autoUpdate } from '@floating-ui/vue';
import hljs from 'highlight.js/lib/common';
import hljsVuePlugin from "@highlightjs/vue-plugin";
import 'highlight.js/styles/atom-one-dark.css';

const props = defineProps({
    package: Object,
    version: Object,
    form: Object,
    generate: Object,
    errors: {
        type: Object,
        default: () => ({})
    },
})

const code = ref(null);

watch(() => props.generate, (value) => {
    if (value) {
        console.log(value[0].view)
        tabs[1].disabled = false;
        code.value = value[0].view;
    }
},
)

const highlightjs = hljsVuePlugin.component;


const extractAllBlock = () => {
    const blocks = {};
    props.form.blocks.forEach(block => {
        blocks[block.template] = {
            view: block.enabled ? block.enabled : false,
            collapse: block.collapse ? block.collapse : false,
            label: block.name,
            template: block.template,
        }
    })
    return blocks;
}
const allBlocks = reactive(extractAllBlock());

const formData = reactive(
    props.form.blocks.reduce((acc, block) => {
        acc[block.template] = block.fields.reduce((fAcc, field) => {
            fAcc[field.name] = 'default' in field ? field.default : null;
            return fAcc;
        }, {});
        return acc;
    }, {})
);

function toggleBlock(blockTemplate, isActive) {
    if (isActive) {
        activateBlock(blockTemplate);
        const dependencies = getDependencies(blockTemplate);
        dependencies.forEach(dependency => {
            if (isDependencyNeededByOtherBlocks(dependency)) {
                activateBlock(dependency);
            }
        });
    } else {
        const canDeactivate = canDeactivateBlock(blockTemplate);
        if (canDeactivate) {
            const dependencies = getDependencies(blockTemplate);
            console.log(dependencies)
            deactivateBlock(blockTemplate);
            resetBlock(blockTemplate);
            dependencies.forEach(dependency => {
                if (!isDependencyNeededByOtherBlocks(dependency)) {
                    deactivateBlock(dependency);
                }
            });
        } else {
            console.warn('Cannot deactivate block, it is a dependency for another active block.');
        }
    }
}

function updateField(blockTemplate, fieldName, value, type) {
    if (type != 'switch' && type != 'select') {
        return;
    }
    const field = getFieldByName(blockTemplate, fieldName);
    if (field.type == 'switch' && typeof field.dependencies != 'undefined') {
        if (value == true) {
            field.dependencies.forEach(dependence => {
                activateBlock(dependence);
            });
        } else {
            field.dependencies.forEach(dependence => {
                if (!isDependencyNeededByOtherBlocks(dependence)) {
                    deactivateBlock(dependence);
                }
            });
        }
    } else if (field.type == 'select') {
        field.options.forEach(option => {
            if (option.id == value) {
                if (typeof option.dependencies != 'undefined') {
                    option.dependencies.forEach(dependence => {
                        activateBlock(dependence);
                    });
                }
            } else {
                if (typeof option.dependencies != 'undefined') {
                    option.dependencies.forEach(dependence => {
                        if (!isDependencyNeededByOtherBlocks(dependence)) {
                            deactivateBlock(dependence);
                        }
                    });
                }
            }
        });
    }
}

function activateBlock(blockTemplate) {
    if (allBlocks[blockTemplate] && !allBlocks[blockTemplate].view) {
        allBlocks[blockTemplate].view = true;
        const dependencies = getDependencies(blockTemplate);
        dependencies.forEach(dependency => {
            activateBlock(dependency); // Recursividad aquí
        });
    }
}

// Función recursiva para desactivar un bloque y, si es posible, sus dependencias.
function deactivateBlock(blockTemplate) {
    // Verificar si el bloque actual puede desactivarse.
    if (allBlocks[blockTemplate] && allBlocks[blockTemplate].view) {
        if (canDeactivateBlock(blockTemplate)) {
            allBlocks[blockTemplate].view = false;
            resetBlock(blockTemplate); // Resetear los valores del bloque a los valores por defecto.

            // Obtener las dependencias del bloque actual.
            const dependencies = getDependencies(blockTemplate);
            dependencies.forEach(dependency => {
                // Intentar desactivar cada dependencia si ya no es necesaria.
                if (!isDependencyNeededByOtherBlocks(dependency)) {
                    deactivateBlock(dependency); // Recursividad aquí.
                }
            });
        } else {
            console.warn(`Cannot deactivate ${blockTemplate}, it is a dependency for another active block.`);
        }
    }
}


// Función para resetear un bloque a su estado por defecto.
function resetBlock(blockTemplate) {
    if (formData[blockTemplate]) {
        const defaultData = props.form.blocks.find(block => block.template === blockTemplate);
        defaultData.fields.forEach(field => {
            formData[blockTemplate][field.name] = 'default' in field ? field.default : null;
        });
    }
}

// Función para obtener las dependencias de un bloque.
function getDependencies(blockTemplate) {
    const block = props.form.blocks.find(b => b.template === blockTemplate);

    let dependencies = block && block.dependencies ? [...block.dependencies] : [];

    if (block && block.fields) {
        block.fields.forEach(field => {
            if (field.type === "select" && formData[blockTemplate] && formData[blockTemplate][field.name]) {
                const selectedOption = field.options.find(
                    option => option.id === formData[blockTemplate][field.name]
                );
                if (selectedOption && selectedOption.dependencies) {
                    dependencies = [...dependencies, ...selectedOption.dependencies];
                }
            }
            if (field.type === "switch" && formData[blockTemplate] && formData[blockTemplate][field.name] === true) {
                // Asumiendo que un 'switch' activado podría tener dependencias.
                // Esto depende de la estructura de tus datos; ajusta según sea necesario.
                if (field.dependencies) {
                    dependencies = [...dependencies, ...field.dependencies];
                }
            }
            // Agregar aquí más lógica si hay más tipos de campos con dependencias.
        });
    }

    return dependencies;
}

function isDependencyNeededByOtherBlocks(dependencyTemplate) {

    //Recuperamos todos los bloques activos menos el que llega en dependencyTemplate
    const activeBlocks = Object.keys(allBlocks).filter(block => {
        return block !== dependencyTemplate && allBlocks[block].view;
    });

    // Si no hay bloques activos, no necesitamos seguir buscando
    if (activeBlocks.length === 0) return false;

    // Revisar a nivel de bloque si existe la dependencia
    const isNeededByBlock = activeBlocks.some(block => {
        return props.form.blocks.some(b => {
            return b.template === block && b.dependencies && b.dependencies.includes(dependencyTemplate);
        });
    });
    // Si encontramos la dependencia a nivel de bloque, no necesitamos seguir buscando
    if (isNeededByBlock) return true;

    // Revisar a nivel de campo en cada bloque
    let isNeededByField = props.form.blocks.some(block => {
        return block.fields.some(field => {
            // Revisar opciones en campos de tipo 'select'
            if (field.type === 'select' && field.options) {
                return field.options.some(option => {
                    // Revisar si alguna opción que ha sido seleccionada tiene la dependencia
                    const isOptionSelected = formData[block.template] && formData[block.template][field.name] === option.id;
                    return isOptionSelected && option.dependencies && option.dependencies.includes(dependencyTemplate);
                });
            }
            // Revisar valor en campos de tipo 'switch'
            else if (field.type === 'switch') {
                const switchValue = formData[block.template] && formData[block.template][field.name];
                // Si el interruptor está activo y tiene dependencias, verificar si la dependencia necesaria está incluida
                return switchValue && block.dependencies && block.dependencies.includes(dependencyTemplate);
            }
            return false;
        });
    });

    return isNeededByField;
}



// Función para comprobar si se puede desactivar un bloque.
function canDeactivateBlock(blockTemplate) {
    return !isDependencyNeededByOtherBlocks(blockTemplate);
}

// Función para obtener un campo por su nombre dentro de un bloque.
function getFieldByName(blockTemplate, fieldName) {
    const block = props.form.blocks.find(block => block.template === blockTemplate);
    return block.fields.find(field => field.name === fieldName);
}


const showBlock = (block) => {
    for (const [key, value] of Object.entries(allBlocks)) {
        //console.log(key, value);
        if (key == block.template && value.view == true) {
            return true;
        }
    }
    return false;
}


const submit = () => {
    const data = {
        status: allBlocks,
        data: formData,
    }
    router.post(`/generator/${props.package.name}/generate/${route().params.id}`, data, {
        preserveState: true,
        only: ['generate']
    })
}

const toggleColapse = (block) => {
    allBlocks[block.template].collapse = !allBlocks[block.template].collapse;
}

const tabs = reactive([
    { name: 'tab1', title: 'Generator', icon: mdiCompost, disabled: false },
    { name: 'tab2', title: 'Result', icon: mdiFileCodeOutline, disabled: true },
]);

const visiblePoper = ref(false);
const changeVisiblePoper = () => {
    visiblePoper.value = !visiblePoper.value;
}
const reference = ref(null);
const floating = ref(null);
const { floatingStyles } = useFloating(reference, floating, {
    whileElementsMounted: autoUpdate,
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(function () {
        console.log('Async: Copying to clipboard was successful!');
    }, function (err) {
        console.error('Async: Could not copy text: ', err);
    });
}

</script>

<template>
    <Head :title="`Package: ${package.name}`" />
    <LayoutAuthenticated>
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiCompost" title="Generator" main>

            </SectionTitleLineWithButton>

            <Tabs :tabs="tabs">
                <template #tab1>
                    <BaseButton color="info" rounded-full :icon="mdiPlus" ref="reference" class="relative float-right ml-2"
                        @click="changeVisiblePoper()">
                        <div ref="floating" :style="floatingStyles" v-if="visiblePoper"
                            class="text-sm p-2 text-left text-slate-500 border-b border-gray-100 lg:border lg:bg-white lg:absolute lg:top-full lg:left-0 lg:min-w-full lg:z-20 lg:rounded-lg lg:shadow-lg lg:dark:bg-slate-800 dark:border-slate-700">

                            <div v-for="block in allBlocks" :key="block">
                                <div v-if="block.view == false" class="flex">
                                    <BaseIcon :path="mdiPlus" class="text-blue-500 mr-2" size="24"
                                        @click="toggleBlock(block.template, true)" />
                                    <span class="text-base">{{ block.label }}</span>
                                </div>
                            </div>


                        </div>
                    </BaseButton>

                    <CardBox is-form @submit.prevent="submit">

                        <FormValidationErrors />

                        <div v-for="block in form.blocks" :key="block">
                            <CardBox v-if="showBlock(block)" class="bg-slate-300/30 mb-2">
                                <section class="py-1 lg:px-0 lg:mx-auto">
                                    <div class="relative float-right">
                                        <BaseIcon :path="mdiDeleteForeverOutline" class="text-red-500 mr-2" size="25"
                                            @click="toggleBlock(block.template, false)" />
                                        <BaseIcon :path="allBlocks[block.template].collapse ? mdiChevronDown : mdiChevronUp"
                                            size="25" @click="toggleColapse(block)" />
                                    </div>
                                    <h2 class="text-2xl text-gray-500 dark:text-slate-400">
                                        {{ block.name }}
                                    </h2>
                                </section>
                                <section v-show="!allBlocks[block.template].collapse">
                                    <FormField v-for="element in block.fields" :key="element" :label="element.label">
                                        <template v-if="element.type == 'switch'">
                                            <FormCheckRadio :type="element.type"
                                                v-model="formData[block.template][element.name]" :name="element.name"
                                                :inputValue="true"
                                                @change="updateField(block.template, element.name, formData[block.template][element.name], element.type)">
                                            </FormCheckRadio>
                                        </template>
                                        <template v-else>
                                            <FormControl :type="element.type" :options="element.options"
                                                v-model="formData[block.template][element.name]" :name="element.name"
                                                @change="updateField(block.template, element.name, formData[block.template][element.name], element.type)">
                                            </FormControl>
                                        </template>
                                    </FormField>
                                </section>
                            </CardBox>
                        </div>
                        <BaseButtons class="mt-3 relative float-right">
                            <BaseButton color="info" :icon="mdiCompost" small label="Generate" type="submit">
                            </BaseButton>
                        </BaseButtons>
                    </CardBox>
                </template>
                <template #tab2>
                    <div v-for="segment in generate" :key="segment" class="pb-2">
                        
                        <div class="bg-gray-800 text-white border-b-2 p-2 rounded-t-lg">
                            {{ segment.file }}
                            <BaseIcon :path="mdiContentCopy " size="20" class="float-right cursor-pointer" @click="copyToClipboard(segment.view)" />
                        </div>
                    <highlightjs :language="segment.language" :code="segment.view"/>
                    </div>
                </template>

            </Tabs>
        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
pre code.hljs {
    border-bottom-right-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}
</style>