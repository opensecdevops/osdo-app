<script setup>
import BaseIcon from '@/Components/BaseIcon.vue'

import { ref, computed } from 'vue';

const props = defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
});

const currentTab = ref(0);
const currentTabName = computed(() => props.tabs[currentTab.value].name);

const selectTab = (index) => {
    if (!props.tabs[index].disabled) {
        currentTab.value = index;
    }
};

const tabItemClasses = (disabled, index) => [
    'cursor-pointer py-2 px-4',
    'border-b-2',
    {
        'text-gray-300 border-transparent dark:text-gray-700': disabled,
        'text-blue-600 border-blue-600 dark:text-blue-500 dark:border-blue-500': currentTab.value === index && !disabled,
        'text-gray-500 hover:text-gray-700 hover:border-gray-300 ': currentTab.value !== index && !disabled,
    },
];
</script>

<template>
    <div>
        <!-- Tabs -->
        <ul class="flex border-b border-b-gray-300 dark:border-b-gray-700">
            <li v-for="(tab, index) in tabs" :key="tab.name" :class="tabItemClasses(tab.disabled, index)"
                @click="!tab.disabled && selectTab(index)">
                <template v-if="$slots[`title-${tab.name}`]">
                    <slot :name="'title-' + tab.name"></slot>
                </template>
                <template v-else>
                    <BaseIcon v-if="tab.icon" :path="tab.icon"></BaseIcon>
                    <span v-if="tab.icon" class="icon" :class="tab.icon"></span>
                    {{ tab.title }}
                </template>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content p-4">
            <slot :name="currentTabName"></slot>
        </div>
    </div>
</template>
  

