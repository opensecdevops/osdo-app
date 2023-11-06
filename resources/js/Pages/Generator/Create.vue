<script setup>
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { Head, router } from '@inertiajs/vue3'
import FormCheckRadio from '@/Components/FormCheckRadio.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import CardBox from '@/Components/CardBox.vue';
import { reactive, watch } from 'vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiCompost } from '@mdi/js'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue' 

const props = defineProps({
    package: Object,
    version: Object,
    form: Object,
    errors: {
        type: Object,
        default: () => ({})
    },
})

function extractDependencies(blocks) {
    const dependenciesSet = new Set();

    function findDependencies(formItems) {
        formItems.forEach(item => {
            if (item.dependencies) {
                item.dependencies.forEach(dep => dependenciesSet.add(dep));
            }
            if (item.fields) {
                findDependencies(item.fields);
            }
        });
    }

    findDependencies(blocks);

    return Array.from(dependenciesSet);
}

const dependencies = extractDependencies(props.form.blocks);
const featuresState = reactive(
    dependencies.reduce((state, dep) => {
        state[dep] = false;
        return state;
    }, {})
);


const formData = reactive(
    props.form.blocks.reduce((acc, block) => {
        acc[block.template] = block.fields.reduce((fAcc, field) => {
            fAcc[field.name] = 'default' in field ? field.default : null;
            return fAcc;
        }, {});
        return acc;
    }, {})
);


watch(formData, (newFormData) => {
    const activeDependences = [];
    for (const [blockName, fields] of Object.entries(newFormData)) {
        for (const [fieldName, fieldValue] of Object.entries(fields)) {
            const fieldConfig = findFieldConfig(blockName, fieldName);
            if (fieldConfig.type == 'switch' || fieldConfig.type == 'select') {
                if (fieldConfig.type == 'switch' && typeof fieldConfig.dependencies != 'undefined') {
                    if (newFormData[blockName][fieldConfig.name] == true) {
                        fieldConfig.dependencies.forEach(dependence => {
                            if (!activeDependences.includes(dependence)) {
                                activeDependences.push(dependence);
                            }
                        });
                    }
                }
                if (fieldConfig.type == 'select') {
                    fieldConfig.options.forEach(option => {
                        if (option.id == fieldValue) {
                            if (typeof option.dependencies != 'undefined') {
                                option.dependencies.forEach(dependence => {
                                    if (!activeDependences.includes(dependence)) {
                                        activeDependences.push(dependence);
                                    }
                                });
                            }
                        }
                    });
                }
            }
        }
    }
    if (activeDependences.length > 0) {
        dependencies.forEach(dep => {
            if (activeDependences.includes(dep)) {
                featuresState[dep] = true;
            } else {
                featuresState[dep] = false;
            }
        });
    } else {
        dependencies.forEach(dep => {
            featuresState[dep] = false;
        });
    }
}, {
    deep: true
});

const findFieldConfig = (blockName, fieldName) => {
    const blockConfig = props.form.blocks.find(block => block.template === blockName);
    if (!blockConfig) return null;
    return blockConfig.fields.find(field => field.name === fieldName);
}


const showBlock = (block) => {
    for (const [key, value] of Object.entries(featuresState)) {
        if (key == block.template) {
            return value;
        }
    }
    return true;
}

const submit = () => {
    const data = {
        status: featuresState,
        data: formData,
    }
    router.post(`/generator/${route().params.vendor}/${route().params.package}/generate/${route().params.id}`, data);
}

</script>

<template>
    <Head :title="`Package: ${package.name}`" />
    <LayoutAuthenticated>
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiCompost" title="Generator" main>

            </SectionTitleLineWithButton>

            <CardBox is-form  @submit.prevent="submit">

            <FormValidationErrors />

                <div v-for="block in form.blocks" :key="block">
                    <CardBox v-if="showBlock(block)">
                        <section class="py-6 lg:px-0 lg:mx-auto">
                            <h2 class="text-2xl text-gray-500 dark:text-slate-400">
                                {{ block.name }}
                            </h2>
                        </section>

                        <FormField v-for="element in block.fields" :key="element" :label="element.label">
                            <template v-if="element.type == 'switch'">
                                <FormCheckRadio :type="element.type" v-model="formData[block.template][element.name]"
                                    :name="element.name" :inputValue="true">
                                </FormCheckRadio>
                            </template>
                            <template v-else>
                                <FormControl :type="element.type" :options="element.options"
                                    v-model="formData[block.template][element.name]" :name="element.name"
                                    >
                                </FormControl>
                            </template>
                        </FormField>
                    </CardBox>
                </div>
                <BaseButtons class="mt-3 relative float-right">
                    <BaseButton color="info" :icon="mdiCompost" small label="Generate" type="submit">
                    </BaseButton>
                </BaseButtons>
            </CardBox>

        </SectionMain>
    </LayoutAuthenticated>
</template>