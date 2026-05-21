<script setup>
import { ref } from 'vue'
import { mdiConnection } from '@mdi/js'
import { Head, useForm, router  } from '@inertiajs/vue3'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import Table from '@/Components/Table/Table.vue'
import { computed } from 'vue'



const props = defineProps({ services: Array, servicesUser: Object })

const columns = [
  {
    title: 'Service',
    key: 'service',
    align: 'left'
  },
]

const actions = {
  add: { 'active': true },
  delete: { 'active': true },
}

const serviceSelect = computed(() => {
  return props.services.map((service) => {
    return {
      id: service.id,
      label: service.service
    }
  })
})


const showAdd = ref(false)

const changeAdd = (() => showAdd.value = true)

const form =  useForm({
  token: null,
  service: null,
})

const submit = () => {
  form.post(route('service.store'), {
    onFinish: () => {
      form.token = null
      form.service = null

      showAdd.value = false
     }
  })
}

const deleteItem = (item) => {
  router.delete(route('service.destroy', item.id))
}
  

</script>

<template>
  <Head title="Servicios" />

  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiConnection" title="Servicios" main>

      </SectionTitleLineWithButton>

      <CardBox class="pb-10 relative" has-table>

        <Table :items="servicesUser" :columns="columns" :actions="actions" @add="changeAdd" @delete="deleteItem"></Table>

        <div v-if="showAdd">
          <CardBox isForm @submit.prevent="submit">
            <FormField label="Token" help="Required. Token for login">
              <FormControl name="token" required v-model="form.token" :errors="!form.errors.token" />
            </FormField>


            <FormField label="Service" help="Required. Service to connect">
              <FormControl name="service" required :options="serviceSelect" v-model="form.service" />
            </FormField>

            <template #footer>
              <BaseButtons>
                <BaseButton label="Save" color="info" type="submit"/>
                <BaseButton label="Cancel" color="danger" outline @click="!!showAdd" />
              </BaseButtons>
            </template>
          </CardBox>
        </div>
      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>

</template>