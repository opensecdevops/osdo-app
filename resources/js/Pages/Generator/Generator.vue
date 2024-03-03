<script setup>
import { reactive, watch, ref } from 'vue';
import { useFloating, autoUpdate } from '@floating-ui/vue';
import hljs from 'highlight.js/lib/common';
import hljsVuePlugin from "@highlightjs/vue-plugin";
import 'highlight.js/styles/atom-one-dark.css';
import Handlebars from "handlebars";
import { validate, setLocales } from "robust-validator";
import en from "robust-validator/dist/i18n/en.json";

import FormCheckRadio from '@/Components/FormCheckRadio.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import CardBox from '@/Components/CardBox.vue';
import {
    mdiCompost,
    mdiFileCodeOutline,
    mdiPlus,
    mdiDeleteForeverOutline,
    mdiChevronDown,
    mdiChevronUp,
    mdiContentCopy
} from '@mdi/js'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Tabs from '@/Components/Tabs/Tabs.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import NotificationBarInCard from "@/Components/NotificationBarInCard.vue";


setLocales(en);

const props = defineProps({
    form: Object,
    templates: Array,
})

const generate = ref(null);
const errors = ref({});
const code = ref(null);

const getErrors = (block, field) => {
    if (errors.value[block] && errors.value[block]['fields'][field] === false) {
        return !errors.value[block]['fields'][field];
    }
    return false;
}

const getErrorMessage = (block, field) => {
    if (errors.value[block] && errors.value[block]['errors'][field]) {
        return errors.value[block]['errors'][field][0]['message'];
    }
    return "";
}


Handlebars.registerHelper("xif", function (expression, options) {
    return Handlebars.helpers["x"].apply(this, [expression, options]) ? options.fn(this) : options.inverse(this);
});


Handlebars.registerHelper("x", function (expression, options) {
    let result;
    let contextVars = {};
    let context = this;
    for (let key in context) {
        if (context.hasOwnProperty(key) && !(key in options)) { // Avoid overriding options
            contextVars[key] = context[key];
        }
    }
    let contextValues = Object.values(contextVars);
    let func = new Function(...Object.keys(contextVars), 'return ' + expression);
    try {
        result = func(...contextValues);
    } catch (e) {
        console.warn('•Expression: {{x \'' + expression + '\'}}\n•JS-Error: ', e, '\n•Context: ', context);
    }
    return result;
});



const submit = async () => {
    errors.value = {};
    let datas;

    for (const [key, value] of Object.entries(allBlocks)) {
        if (value.view) {
            datas = {
                ...datas,
                [key]: formData[key]
            }
        }
    }

    let rules = {};

    for (const [key, value] of Object.entries(datas)) {
        const block = props.form.blocks.find(b => b.template === key);
        block.fields.forEach(field => {
            if (field && field.rules) {
                if (!rules.hasOwnProperty(key)) {
                    rules[key] = {};
                }
                if (field.name) {
                    rules[key][field.name] = field.rules;
                }
            }
        });
    }

    for (const [key, value] of Object.entries(datas)) {
        if (rules.hasOwnProperty(key)) {
            const result = await validate(value, rules[key]);
            errors.value = {
                ...errors.value,
                [key]: result
            }
        }
    }

    for (const error in errors.value) {
        if (errors.value[error].isValid === false) {
            return;
        }
    }

    errors.value = {};

    for (const [key, value] of Object.entries(datas)) {
        const block = props.form.blocks.find(b => b.template === key);
        block.fields.forEach(field => {
            if (field.type === 'select') {
                datas[key] = {
                    ...datas[key],
                    [`${field.name}_value`]: field.options.find(option => option.id === value[field.name]).value
                }
            }
        });
    }

    let loadTempaltes = [];

    for (const [key, value] of Object.entries(datas)) {
        let template = props.templates.find(template => template.file === key);
        if (loadTempaltes.includes(template.file)) {
            continue;
        }
        Handlebars.registerPartial(template.file, template.content);
        loadTempaltes.push(template.file);
        const loadConfig = props.form.blocks.find(block => block.template === key);
        if (loadConfig.dependencies) {
            loadConfig.dependencies.forEach(dependency => {
                let template = props.templates.find(template => template.file === dependency);
                if (loadTempaltes.includes(template.file)) {
                    return;
                }
                Handlebars.registerPartial(template.file, template.content);
                loadTempaltes.push(template.file);
            });
        }
    }

    const mainTemplate = props.templates.find(template => template.file === props.form.template);
    const renders = [];
    let renderedHtml;
    try {
        const compiledTemplate = Handlebars.compile(mainTemplate.content);
        renderedHtml = compiledTemplate(datas);

    } catch (error) {
        console.error(error);
        errors.value = {
            ...errors.value,
            Handlebars: {
                errors: {
                    main: [{
                        message: error.message
                    }]
                }
            }
        }
        return;
    }


    renders.push({
        file: props.form.file,
        view: renderedHtml,
        language: props.form.language
    });
    console.log(datas);

    for (const [key, value] of Object.entries(datas)) {
        let block = props.form.blocks.find(b => b.template === key);
        if (block.extra) {
            for (const [keyextra, extra] of Object.entries(block.extra)) {
                let generateExtra = true;
                console.log(extra);
                if (extra.dependencies) {
                    console.log(extra.dependencies.some(dep => dep in datas));
                    if (!extra.dependencies.some(dep => dep in datas)) {
                        generateExtra = false;
                    }
                }

                if (generateExtra) {
                    const extraTemplate = props.templates.find(template => template.file === extra.template);
                    if (loadTempaltes.includes(extraTemplate.file)) {
                        continue;
                    }
                    const compiledExtraTemplate = Handlebars.compile(extraTemplate.content);
                    const renderedExtraHtml = compiledExtraTemplate(datas);
                    renders.push({
                        file: extra.route ? extra.route + extra.file : extra.file,
                        view: renderedExtraHtml,
                        language: extra.language
                    });
                }
            }
        }
    }

    generate.value = renders;
}


watch(() => generate.value, (value) => {
    if (value) {
        tabs[1].disabled = false;
        code.value = value[0].view;
    }
})

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

const toggleBlock = (blockTemplate, isActive) => {
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
};

const updateField = (blockTemplate, fieldName, value, type) => {
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
};

const activateBlock = (blockTemplate) => {
    if (allBlocks[blockTemplate] && !allBlocks[blockTemplate].view) {
        allBlocks[blockTemplate].view = true;
        const dependencies = getDependencies(blockTemplate);
        dependencies.forEach(dependency => {
            activateBlock(dependency);
        });
    }
};

const deactivateBlock = (blockTemplate) => {
    if (allBlocks[blockTemplate] && allBlocks[blockTemplate].view) {
        if (canDeactivateBlock(blockTemplate)) {
            allBlocks[blockTemplate].view = false;
            resetBlock(blockTemplate);

            const dependencies = getDependencies(blockTemplate);
            dependencies.forEach(dependency => {
                if (!isDependencyNeededByOtherBlocks(dependency)) {
                    deactivateBlock(dependency);
                }
            });
        } else {
            console.warn(`Cannot deactivate ${blockTemplate}, it is a dependency for another active block.`);
        }
    }
};

const resetBlock = (blockTemplate) => {
    if (formData[blockTemplate]) {
        const defaultData = props.form.blocks.find(block => block.template === blockTemplate);
        defaultData.fields.forEach(field => {
            formData[blockTemplate][field.name] = 'default' in field ? field.default : null;
        });
    }
};

const getDependencies = (blockTemplate) => {
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
                if (field.dependencies) {
                    dependencies = [...dependencies, ...field.dependencies];
                }
            }
        });
    }

    return dependencies;
};

const isDependencyNeededByOtherBlocks = (dependencyTemplate) => {
    const activeBlocks = Object.keys(allBlocks).filter(block => {
        return block !== dependencyTemplate && allBlocks[block].view;
    });

    if (activeBlocks.length === 0) return false;

    const isNeededByBlock = activeBlocks.some(block => {
        return props.form.blocks.some(b => {
            return b.template === block && b.dependencies && b.dependencies.includes(dependencyTemplate);
        });
    });
    if (isNeededByBlock) return true;

    let isNeededByField = props.form.blocks.some(block => {
        return block.fields.some(field => {
            if (field.type === 'select' && field.options) {
                return field.options.some(option => {
                    const isOptionSelected = formData[block.template] && formData[block.template][field.name] === option.id;
                    return isOptionSelected && option.dependencies && option.dependencies.includes(dependencyTemplate);
                });
            } else if (field.type === 'switch') {
                const switchValue = formData[block.template] && formData[block.template][field.name];
                return switchValue && block.dependencies && block.dependencies.includes(dependencyTemplate);
            }
            return false;
        });
    });

    return isNeededByField;
};

const canDeactivateBlock = (blockTemplate) => {
    return !isDependencyNeededByOtherBlocks(blockTemplate);
};

const getFieldByName = (blockTemplate, fieldName) => {
    const block = props.form.blocks.find(block => block.template === blockTemplate);
    return block.fields.find(field => field.name === fieldName);
};


const showBlock = (block) => {
    for (const [key, value] of Object.entries(allBlocks)) {
        if (key == block.template && value.view == true) {
            return true;
        }
    }
    return false;
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
    placement: 'bottom-end',
    whileElementsMounted: autoUpdate,
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(function () {
    }, function (err) {
        console.error('Async: Could not copy text: ', err);
    });
}

</script>

<template>
    <NotificationBarInCard v-if="Object.keys(errors).length > 0" color="danger">
        <b>Whoops! Something went wrong.</b>
        <div v-for="(error, key) in errors" :key="error">
            <div v-if="Object.keys(error.errors).length > 0">
                {{ key }}:
                <ul class="pl-2">
                    <li v-for="(message, keymessage) in error.errors" :key="message">
                        {{ keymessage }}: {{ message[0].message }}
                    </li>
                </ul>
            </div>
        </div>

    </NotificationBarInCard>
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
                            <FormField v-for="element in block.fields" :key="element" :label="element.label"
                                :error="getErrorMessage(block.template, element.name)">
                                <template v-if="element.type == 'switch'">
                                    <FormCheckRadio :type="element.type" v-model="formData[block.template][element.name]"
                                        :name="element.name" :inputValue="true"
                                        @change="updateField(block.template, element.name, formData[block.template][element.name], element.type)">
                                    </FormCheckRadio>
                                </template>
                                <template v-else>
                                    <FormControl :type="element.type" :options="element.options"
                                        v-model="formData[block.template][element.name]" :name="element.name"
                                        :error="getErrors(block.template, element.name)"
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
                    <BaseIcon :path="mdiContentCopy" size="20" class="float-right cursor-pointer"
                        @click="copyToClipboard(segment.view)" />
                </div>
                <highlightjs :language="segment.language" :code="segment.view" />
            </div>
        </template>

    </Tabs>
</template>

<style scoped>
pre code.hljs {
    border-bottom-right-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}
</style>