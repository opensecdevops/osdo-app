<script setup>
import { Head, router } from '@inertiajs/vue3'
import { mdiPackageVariantClosedPlus } from '@mdi/js'
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import LayoutAuthenticated from '@/Layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'
import { ref, watch } from 'vue'
import { computed } from 'vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'

const props = defineProps({
  projects: Object,
  services: Object
})

function submitPackage() {
  console.log(filteredOptions.value);
  router.post(route('packages.store'), {  
    service: service.value, 
    type: type.value,
    id: filteredOptions.value.id,
    repository: filteredOptions.value.http_url_to_repo,
    name: filteredOptions.value.path_with_namespace,
  });
}

const service = ref(null);
const type = ref(null);

const types = [
  { id: 1, label: 'Infrastructure' },
  { id: 2, label: 'CI/CD' },
];

const searchQuery = ref('');
const filteredOptions = ref([]);

// Simular la búsqueda de repositorios (reemplazar con la lógica de la API)
const fetchRepositories = async (query) => {
  // Aquí iría el código para obtener los repositorios de GitLab utilizando su API.
  // Por ahora, esto solo simula el retraso y filtra los datos de ejemplo.
  router.get(route('packages.create'), {
    params: {
      search: query,
      service: service.value,
    },
  }, {
    preserveState: true,
    only: ['projects'],
  });
};

// Observador para buscar repositorios cuando cambie el query
watch(searchQuery, (newQuery) => {
  if (newQuery.length > 2 && selectOptionStatus.value == false) { // Puede ser ajustado a tus necesidades
    fetchRepositories(newQuery);
  }
});

// Lógica para manejar la selección de una opción
const selectOption = (option) => {
  selectOptionStatus.value = true; // Marcar la opción como seleccionada

  // Aquí puedes establecer la opción seleccionada donde sea necesario
  searchQuery.value = option.name; // Actualizar el input con el nombre del repositorio seleccionado
  filteredOptions.value = option;
};

const selectOptionStatus = ref(false);
const showSelect = computed(() => {
  if (typeof props.projects === 'undefined') {
    return false;
  }
  return props.projects.length > 0 && !selectOptionStatus.value;
});

const checkInput = (evt) => {
  if (evt.key === 'Delete' || evt.key === 'Backspace') {
    selectOptionStatus.value = false;
    filteredOptions.value = [];
  }
};

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
            <FormField label-for="type" label="Type">
              <FormControl id="type" v-model="type" :options="types" />
            </FormField>

            <FormField label-for="select-repository" label="repository">
              <FormControl id="select-repository" v-model="service" :options="services" />
            </FormField>
            <FormField label-for="repository" label="Package" v-show="service != null && type != null">
              <div class="relative" id="repository">
                <input type="text" placeholder="Search repository..." v-model="searchQuery" @keyup="checkInput"
                  class="border border-gray-300 rounded p-2 w-full" />

                <div v-show="showSelect" class="absolute border border-gray-300 bg-white w-full mt-1 rounded z-10">
                  <div v-for="option in projects" :key="option.id" @click="selectOption(option)"
                    class="p-2 hover:bg-gray-100 cursor-pointer">
                    {{ option.name }}
                  </div>
                </div>
              </div>
            </FormField>

          </div>
          <div>
            Please make sure you have read the package naming conventions before submitting your package. The
            authoritative name of your package will be taken from the config.json file inside the main branch of your
            repository, and it can not be changed after that.
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