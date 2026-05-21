<script setup>
import { createPopper } from '@popperjs/core'
import { ref, onBeforeUnmount } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'

const props = defineProps({
    label: {
        type: [String, Number],
        default: null
    },
    icon: {
        type: String,
        default: null
    },
    iconSize: {
        type: [String, Number],
        default: null
    },
    color: {
        type: String,
        default: 'white'
    },
    small: Boolean,
    outline: Boolean,
    active: Boolean,
    disabled: Boolean,
    roundedFull: Boolean
})

const popoverShow = ref(false)
const btnRef = ref(null)
const popoverRef = ref(null)
let popperInstance = null

const togglePopover = () => {
    popoverShow.value = !popoverShow.value
    if (popoverShow.value && btnRef.value && popoverRef.value) {
        // Creamos una instancia de Popper con nuestros elementos de referencia
        popperInstance = createPopper(btnRef.value, popoverRef.value, {
            placement: 'bottom'
        })
    } else {
        // Asegurarse de que Popper se destruya correctamente
        if (popperInstance) {
            popperInstance.destroy()
            popperInstance = null
        }
    }
}

// Opcional: Limpieza si el componente se desmonta mientras el popover está visible
onBeforeUnmount(() => {
    if (popperInstance) {
        popperInstance.destroy()
    }
})
</script>


<template>
    <BaseButton :color="color" rounded-full :icon="icon" :small="small" :disabled="disabled" :rounded-full="roundedFull"
        :outline="outline" :label="label" v-on:click="togglePopover()">

    </BaseButton>
    <div ref="popoverRef" v-bind:class="{ 'hidden': !popoverShow, 'block': popoverShow }"
            class="bg-pink-600 border-0 mb-3 block z-50 font-normal leading-normal text-sm max-w-xs text-left no-underline break-words rounded-lg">
            <div>
                <div
                    class="bg-pink-600 text-white opacity-75 font-semibold p-3 mb-0 border-b border-solid border-blueGray-100 uppercase rounded-t-lg">
                    pink popover title
                </div>
                <div class="text-white p-3">
                    And here's some amazing content. It's very engaging. Right?
                </div>
            </div>
        </div>
</template>
  