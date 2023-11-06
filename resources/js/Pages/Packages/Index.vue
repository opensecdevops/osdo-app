<script setup>
import { reactive, ref } from 'vue'
import { mdiPackageVariantClosed, mdiPlus } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import Table from '@/Components/Table/Table.vue' 
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  packages: Object
})

const actions = {
  add: { 'active': true },
  delete: { 'active': true },
}


const goToImport = () => {
  router.get(route('packages.import.create'))
}

</script>

<template>
  <Head title="Packages" />

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiPackageVariantClosed" title="Packages" main>

      </SectionTitleLineWithButton>


        <CardBox class="pb-10 relative" has-table>

        <Table :items="packages" @add="goToImport()" :actions="actions"></Table>
       
        </CardBox>
        
    </SectionMain>
  </LayoutAuthenticated>
</template>