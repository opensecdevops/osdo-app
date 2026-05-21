<script setup>
import SectionMain from '@/Components/SectionMain.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import { Head } from '@inertiajs/vue3'
import UserAvatar from '@/Components/UserAvatar.vue'
import { computed } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiCompost } from '@mdi/js'

const props = defineProps({
    package: Object,
    versions: Object,
    user: Object,
    service: Object
})

const lastVersion = computed(() => {
    const id = Math.max(...props.versions.map(o => o.id))
    return props.versions.find((version) => version.id == id)
})

const urlGenerator = computed(() => {
    return `/generator/${props.package.name}/generate/${lastVersion.value.id}`;
})

</script>

<template>
    <Head :title="`Package: ${package.name}`" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="grid grid-cols-1 lg:grid-cols-8 gap-3">
                <div class="col-span-6">
                    <h1 class="text-3xl font-light">{{  package.name }}</h1>
                    <hr class="h-px my-8 bg-slate-200 border-0 dark:bg-slate-900">
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-8 gap-3">
                <div class="col-span-6 text-xl italic">
                    {{ package.description }}
                </div>
                <div class="bg-slate-200 dark:bg-slate-900 col-span-2 rounded-md p-3">
                    <div class="mb-2">Manteiner</div>
                    <UserAvatar :username="user.name" api="bottts-neutral" class="w-12 h-12">
                        <slot />
                    </UserAvatar>
                    <div class="my-2">{{ user.name }}</div>
                    <div class="truncate hover:text-clip"><a :href="package.repository" target="_blank">{{
                        package.repository }}</a></div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-8 gap-3">
                <div class="col-span-6">
                    <hr class="h-px mt-8 mb-2 bg-slate-200 border-0 dark:bg-slate-900">
                    <span> {{ lastVersion.version }}</span>
                    <span class="relative float-right right-2">{{ lastVersion.created_at }}</span>
                    <hr class="h-px mt-2 mb-8 bg-slate-200 border-0 dark:bg-slate-900">
                    <article class="prose prose-slate lg:prose-sm dark:prose-invert " v-html="lastVersion.readme"></article>
                </div>
                <div class="mt-8">
                    <BaseButton :icon="mdiCompost" :href="urlGenerator" label="Generate" color="info" />

                </div>

            </div>



        </SectionMain>
    </LayoutAuthenticated>
</template>