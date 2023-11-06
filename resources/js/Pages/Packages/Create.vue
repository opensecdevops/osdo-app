<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiPackageVariantClosedPlus, mdiPackageVariantClosed, mdiTextBoxOutline, mdiDiversify, mdiLicense } from '@mdi/js'
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
  name: null,
  description: null,
  type: null,
  package: null,
  version: null,
  license: null,
})

function submitPackage() {
  form.post('/packages/store')
}

</script>

<template>
  <Head title="Add Package" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiPackageVariantClosedPlus" title="Add Package" main>

      </SectionTitleLineWithButton>


      <CardBox is-form @submit.prevent="submitPackage">
        <FormValidationErrors />


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <FormField label="Name" help="Required. Package Name">
              <FormControl :icon="mdiPackageVariantClosed" name="name" required v-model="form.name"/>
            </FormField>
            <FormField label="Description" help="Required. Package Description">
              <FormControl :icon="mdiTextBoxOutline" name="description" required type="textarea" v-model="form.description" />
            </FormField>

            <FormField label="Type" help="Required. Package Type">
              <FormControl :icon="mdiTextBoxOutline" name="description" required :options="TypeOpstions" v-model="form.type" />
            </FormField>

          </div>
          <div>
            <FormField label="Package" help="Max 10Mb" :error="form.errors.package">
              <FormFilePicker label="Upload" v-model="form.package" :error="!!form.errors.package"/>
            </FormField>

            <FormField label="Version" help="Required. Package version" :error="form.errors.version">
              <FormControl :icon="mdiDiversify" name="Version" required v-model="form.version" :error="!!form.errors.version"/>
            </FormField>

            <FormField label="License" help="Required. Package License">
              <FormControl :icon="mdiLicense" name="License" required v-model="form.license"/>
            </FormField>

          </div>
        </div>
        {{ form }}

        <template #footer>
          <BaseButtons class="float-right">
            <BaseButton color="info" type="submit" label="Submit" />
          </BaseButtons>
        </template>
      </CardBox>


    </SectionMain>
  </LayoutAuthenticated>
</template>