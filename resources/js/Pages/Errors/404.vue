<script setup>
import { Head } from '@inertiajs/vue3'

import LayoutGuest from '@/Layouts/LayoutGuest.vue'
import SectionFullScreen from '@/Components/SectionFullScreen.vue'

defineProps({
    message: {
        type: String,
        default: null
    }
})


</script>



<!-- resources/js/Pages/Errors/MyCustom404.vue -->
<template>
    <LayoutGuest>

        <Head title="Error 404" />

        <SectionFullScreen v-slot="{ cardClass }" bg="blue">
            <div>
                <h1 class="text-5xl">404 Not Found</h1>
           
                <p>{{ message }}</p>
            </div>
        </SectionFullScreen>
    </LayoutGuest>
</template>