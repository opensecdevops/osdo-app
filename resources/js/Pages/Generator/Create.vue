<script setup>
import { Head } from '@inertiajs/vue3'

import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import {
    mdiCompost,
} from '@mdi/js'
import Generator from './Generator.vue'


const props = defineProps({
    package: Object,
    form: Object,
    templates: Array,
})



</script>

<template>
    <Head :title="`Package: ${package.name}`" />
    <LayoutAuthenticated>
        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiCompost" title="Generator" main>

            </SectionTitleLineWithButton>
            
            <Generator :form="form" :templates="templates" />


        </SectionMain>
    </LayoutAuthenticated>
</template>

<style scoped>
pre code.hljs {
    border-bottom-right-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}
</style>