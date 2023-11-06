<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiPackageVariantClosedPlus, mdiPackageVariantClosed, mdiTextBoxOutline, mdiDiversify, mdiSourceBranch } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'

const TypeOpstions = [
  { id: 1, label: 'Infrastructure' },
  { id: 2, label: 'Pipeline' },
]

const form = useForm({
  repository: null,
})

function submitPackage() {
  form.post('/packages/import')
}

</script>

<template>
  <Head title="Import Package" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiPackageVariantClosedPlus" title="Import Package" main>

      </SectionTitleLineWithButton>


      <CardBox is-form @submit.prevent="submitPackage">
        <FormValidationErrors />


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <FormField label="Repository URL (Gitlab public)" help="Required. Url Repository">
              <FormControl :icon="mdiSourceBranch " name="name" required v-model="form.repository"/>
            </FormField>
           
          </div>
          <div>
            Please make sure you have read the package naming conventions before submitting your package. The authoritative name of your package will be taken from the config.json file inside the main branch of your repository, and it can not be changed after that.
          </div>
        </div>

        <template #footer>
          <BaseButtons class="float-right">
            <BaseButton color="info" type="submit" label="Submit" />
          </BaseButtons>
        </template>
      </CardBox>


    </SectionMain>
  </LayoutAuthenticated>
</template>