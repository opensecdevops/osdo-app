<script setup>
import { mdiLogout, mdiClose, mdiLogin  } from '@mdi/js'
import { computed } from 'vue'
import AsideMenuList from '@/Components/AsideMenuList.vue'
import AsideMenuItem from '@/Components/AsideMenuItem.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { usePage } from '@inertiajs/vue3'

defineProps({
  menu: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['menu-click', 'aside-lg-close-click'])

const isLogged = usePage().props.auth.user

const logoutItem = computed(() => ({
  label: 'Logout',
  icon: mdiLogout,
  color: 'info',
  isLogout: true
}))

const LoginItem = computed(() => ({
  label: 'Sign up/Sign In',
  icon: mdiLogin,
  color: 'success',
  isLogin: true
}))

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

const asideLgCloseClick = (event) => {
  emit('aside-lg-close-click', event)
}
</script>

<template>
  <aside
    id="aside"
    class="lg:py-2 lg:pl-2 w-60 fixed flex z-40 top-0 h-screen transition-position overflow-hidden"
  >
    <div class="aside lg:rounded-2xl flex-1 flex flex-col overflow-hidden bg-slate-100 dark:bg-slate-900">
      <div class="aside-brand flex flex-row h-14 items-center justify-between bg-slate-200 dark:bg-slate-900">
        <div class="text-center flex-1 lg:text-left lg:pl-6 xl:text-center xl:pl-0">
          <b class="font-black">OSDO</b>
        </div>
        <button class="hidden lg:inline-block xl:hidden p-3" @click.prevent="asideLgCloseClick">
          <BaseIcon :path="mdiClose" />
        </button>
      </div>
      <div
        class="flex-1 overflow-y-auto overflow-x-hidden aside-scrollbars dark:aside-scrollbars-[slate]"
      >
        <AsideMenuList :menu="menu" @menu-click="menuClick" />
      </div>

      <ul>
        <template v-if="isLogged !== null">
          <AsideMenuItem :item="logoutItem"   @menu-click="menuClick" />
        </template>
        <template v-else>
          <AsideMenuItem :item="LoginItem"  @menu-click="menuClick" />
        </template>
      </ul>
      <ul>
      </ul>
    </div>
  </aside>
</template>
