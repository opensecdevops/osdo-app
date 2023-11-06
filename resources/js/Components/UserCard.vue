<script setup>
import { ref } from 'vue'
import { mdiCheckDecagram, mdiAlert } from '@mdi/js'
import BaseLevel from '@/Components/BaseLevel.vue'
import UserAvatarCurrentUser from '@/Components/UserAvatarCurrentUser.vue'
import CardBox from '@/Components/CardBox.vue'
import PillTag from '@/Components/PillTag.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'

const props = defineProps({ user: Object })

</script>

<template>
  <CardBox>
    <BaseLevel type="justify-around lg:justify-center">
      <UserAvatarCurrentUser class="lg:mx-12" />
      <div class="space-y-3 text-center md:text-left lg:mx-12">
        <div class="flex justify-center md:block">

        </div>
        <h1 class="text-2xl">
          Hola, <b>{{ user.name }}</b
          >!
        </h1>
        <p>Last login <b>12 mins ago</b> from <b>127.0.0.1</b></p>
        <div class="flex justify-center md:block">
          <BaseButtons>
            <template v-if="user.email_verified_at">
              <PillTag label="Verified" color="info" :icon="mdiCheckDecagram" />
            </template>
            <template v-else>
              <PillTag label="Not Verified" color="warning" :icon="mdiAlert" />
              <BaseButton type="submit" color="info" label="Verificar" :small="true" :roundedFull="true" />
            </template>
          </BaseButtons>
        </div>
      </div>
    </BaseLevel>
  </CardBox>
</template>
