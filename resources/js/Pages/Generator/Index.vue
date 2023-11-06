<script setup>
import { mdiCompost } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import Table from '@/Components/Table/Table.vue' 
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  packages: Object
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
  <Head title="Packages" />

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiCompost" title="Generator" main>

      </SectionTitleLineWithButton>

        <CardBox class="pb-10 relative" has-table>
          <Table :items="packages" :columns="columns" :actions="actions"> </Table>
        </CardBox>
        
    </SectionMain>
  </LayoutAuthenticated>
</template>