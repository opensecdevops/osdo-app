import '../css/main.css'

import { createPinia } from 'pinia'
import { useDarkModeStore } from '@/Stores/darkMode.js'
import { darkModeKey, styleKey } from '@/config.js'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m'
import { install as VueMonacoEditorPlugin } from '@guolao/vue-monaco-editor'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'

const pinia = createPinia()

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia)
      .use(ZiggyVue, Ziggy)
      .use(VueMonacoEditorPlugin, {
        paths: {
          vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs'
        },
      })
      .mount(el)
  },
  progress: {
    color: '#4B5563'
  }
})

const darkModeStore = useDarkModeStore(pinia)

