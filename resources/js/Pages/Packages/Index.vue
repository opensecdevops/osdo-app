<script setup>
import { mdiPackageVariantClosed, mdiPlus } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import Table from '@/Components/Table/Table.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  packages: Object
})

const actions = {
  add: { 'active': true },
  delete: { 'active': true },
}


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

const goToCreate = () => {
  router.get(route('packages.create'))
}

const deletePackage = (element) => {
  router.delete(route('packages.destroy', element.id ))
}



</script>

<template>
  <Head title="Packages" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Packages" main>

      </SectionTitleLineWithButton>

      <CardBox class="pb-10 relative" has-table>

        <Table :items="packages" :columns="columns" @add="goToCreate()" :actions="actions" @delete="deletePackage"></Table>

      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>
</template>