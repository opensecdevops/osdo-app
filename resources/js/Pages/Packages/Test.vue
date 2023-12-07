<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiPackageVariantClosedPlus, mdiFolderZipOutline } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
 import FormFilePicker from '@/Components/FormFilePicker.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'


const form = useForm({
  package: null,
})

function submitPackage() {
  form.post('/packages/test')
}

</script>

<template>
  <Head title="Test Package" />
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiPackageVariantClosedPlus" title="Test Package" main>

      </SectionTitleLineWithButton>


      <CardBox is-form @submit.prevent="submitPackage">
        <FormValidationErrors />


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <FormField label="Upload Zip Package" help="Required. Zip package">
              <FormFilePicker :icon="mdiFolderZipOutline" name="name" required v-model="form.package" accept=".zip"/>
            </FormField>
           
          </div>
          <div>
            You can upload your package before publishing to verify that the data structure is correct and that it works correctly with the OSDO builder.</div>
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