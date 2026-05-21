<script setup>
import { computed, ref } from 'vue'
import { mdiTrashCan, mdiPlus, mdiPencil, mdiLink } from '@mdi/js'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import FormControl from '@/Components/FormControl.vue'
import defaultActions from './defaultActions'

String.prototype.interpolate = function (params) {
    const names = Object.keys(params);
    const vals = Object.values(params);
    return new Function(...names, `return \`${this}\`;`)(...vals);
};

const props = defineProps({
  columns: Array,
  items: Object,
  perPage: {
    type: Number,
    default: 5
  },
  actions: {
    type: Object,
    default() {
      return defaultActions
    }
  }
})

defineEmits(['add', 'edit', 'delete']);

const mergedActions = computed(() => Object.assign({}, defaultActions, props.actions))

const generateURL = (url, id) => {
  if (typeof id === 'undefined') {
    return '#';
  }
  let obj = props.items.data.find((o) => o.id === id);
  if (obj) {
    return url.interpolate(obj);
  }
  return '#';
}

const modalConfirmDelete = ref(false)

const currentItem = ref(null)
</script>

<template>
  <FormControl v-if="mergedActions.search.active" placeholder="Search" transparent borderless />


  <div class="mb-3">
    <template v-if="items.total == 0">

      <NotificationBar color="danger" :dismissable="false">
        <b>Empty table.</b> When there's nothing to show
      </NotificationBar>

    </template>
    <template v-else>
      <table>
        <thead>
          <tr>
            <th v-for="(column, keyCol) in columns" :key="`header-${keyCol}`">
              {{ column.title }}
            </th>
            <th id="column-action" v-if="mergedActions.delete.edit || mergedActions.edit.active">
              Actions
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, keyItem) in items.data" :key="`tr-${keyItem}`">
            <td v-for="(column, keyCol) in columns" :data-label="item[column.key]" :key="`data-${keyCol}`">
              {{ item[column.key] }}
            </td>
            <td class="before:hidden lg:w-1 whitespace-nowrap">
              <BaseButtons type="justify-start lg:justify-end" no-wrap>
                <slot name="action-edit" v-bind:item="item">
                  <BaseButton v-if="mergedActions.edit.active" color="info" :icon="mdiPencil" small
                  @click="$emit('edit', item)"  />
                </slot>
                <slot name="action-link" v-bind:item="item">
                  <BaseButton :href="generateURL(mergedActions.link.link, item.id)" v-if="mergedActions.link.active" color="contrast"
                    :icon="mdiLink" small />
                </slot>
                <slot name="action-delete" v-bind:item="item">
                  <BaseButton v-if="mergedActions.delete.active" color="danger" outline :icon="mdiTrashCan" small
                    @click="currentItem = item, modalConfirmDelete = true" />
                </slot>
              </BaseButtons>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="mergedActions.pagination.active" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
        <BaseLevel>
          <BaseButtons>
            <BaseButton v-for="page in pagesList" :key="page" :active="page === currentPage" :label="page + 1"
              :color="page === currentPage ? 'lightDark' : 'whiteDark'" small @click="currentPage = page" />
          </BaseButtons>
          <small>Page {{ items.current_page }} of {{ items.last_page }}</small>
        </BaseLevel>
      </div>
    </template>

    <slot name="action-add">

      <div class="relative" v-if="mergedActions.add.active">
        <div class="absolute top-1 right-6">
          <BaseButton @click="$emit('add')" type="submit" color="info" :icon="mdiPlus" :small="true"
            :roundedFull="true" />
        </div>
      </div>
    </slot>
  </div>

  <CardBoxModal v-model="modalConfirmDelete" title="Please confirm deleting the item" button-label="Confirm"
    button="danger" has-cancel @confirm="$emit('delete', currentItem)">
    <p>are you sure, want delete the item? </p>
    <p>Not is possible revert!</p>
  </CardBoxModal>
</template>
