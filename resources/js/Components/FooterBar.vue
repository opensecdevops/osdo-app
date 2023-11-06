<script setup>
import { containerMaxW } from '@/config.js'
import BaseLevel from '@/Components/BaseLevel.vue'
import JustboilLogo from '@/Components/JustboilLogo.vue'

const year = new Date().getFullYear()
</script>

<template>
  <footer class="py-2 px-6" :class="containerMaxW">
    <BaseLevel>
      <div class="text-center md:text-left">
        <b>&copy;{{ year }}, <a href="#" target="_blank">opensecdevops.com</a>.</b>
        <slot />
      </div>
      <div class="md:py-2">
        <a href="#">
        </a>
      </div>
    </BaseLevel>
  </footer>
</template>
