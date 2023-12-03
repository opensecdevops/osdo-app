<script setup>
import { computed, ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import {
  mdiChartTimelineVariant,
  mdiPackageVariantClosed,
  mdiCompost
} from '@mdi/js'

import SectionMain from '@/Components/SectionMain.vue'
import CardBoxWidget from '@/Components/CardBoxWidget.vue'
import CardBox from '@/Components/CardBox.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import Table from '@/Components/Table/Table.vue'


const props = defineProps({
  packages: Object,
  packageGenerate: String,
})

const columns = [
  {
    title: 'Package',
    key: 'name',
  },
  {
    title: 'Description',
    key: 'description',
  },
  {
    title: 'Version',
    key: 'version',
  },
  {
    title: 'Type',
    key: 'type',
  },
]

const actions = {
  link: { 'active': true, link: '/generator/${name}' },
}

</script>

<template>
  <LayoutAuthenticated>

    <Head title="Dashboard" />
    <SectionMain>
      
      <SectionTitleLineWithButton :icon="mdiChartTimelineVariant" title="Overview" main>

      </SectionTitleLineWithButton>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <CardBoxWidget color="text-emerald-500" :icon="mdiPackageVariantClosed" :number="packages.total" label="Packages" />
        <CardBoxWidget color="text-blue-500" :icon="mdiCompost" :number="packageGenerate" label="Generated" />
      </div>

      <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Packages" main>

      </SectionTitleLineWithButton>
      <CardBox class="pb-10 relative" has-table>
        <Table :items="packages" :columns="columns" :actions="actions"> </Table>
      </CardBox>
    </SectionMain>


  </LayoutAuthenticated>
</template>
