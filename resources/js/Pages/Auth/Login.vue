<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import { mdiAccount, mdiAsterisk, mdiHomeOutline, mdiAccountPlus, mdiLockQuestion } from '@mdi/js'
import LayoutGuest from '@/Layouts/LayoutGuest.vue'
import SectionFullScreen from '@/Components/SectionFullScreen.vue'
import CardBox from '@/Components/CardBox.vue'
import FormCheckRadioGroup from '@/Components/FormCheckRadioGroup.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseDivider from '@/Components/BaseDivider.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import FormValidationErrors from '@/Components/FormValidationErrors.vue'
import NotificationBarInCard from '@/Components/NotificationBarInCard.vue'
import BaseLevel from '@/Components/BaseLevel.vue'

const props = defineProps({
  canResetPassword: Boolean,
  status: {
    type: String,
    default: null
  }
})

const form = useForm({
  email: '',
  password: '',
  remember: []
})

const submit = () => {
  form
    .transform(data => ({
      ...data,
      remember: form.remember && form.remember.length ? 'on' : ''
    }))
    .post(route('login'), {
      onFinish: () => form.reset('password'),
    })
}
</script>

<template>
  <LayoutGuest>

    <Head title="Login" />

    <SectionFullScreen v-slot="{ cardClass }" bg="blue">
      <CardBox :class="cardClass" is-form @submit.prevent="submit">
        <FormValidationErrors />

        <NotificationBarInCard v-if="status" color="info">
          {{ status }}
        </NotificationBarInCard>

        <FormField label="Email" label-for="email" help="Please enter your email">
          <FormControl v-model="form.email" :icon="mdiAccount" id="email" autocomplete="email" type="email" required />
        </FormField>

        <FormField label="Password" label-for="password" help="Please enter your password">
          <FormControl v-model="form.password" :icon="mdiAsterisk" type="password" id="password"
            autocomplete="current-password" required />
        </FormField>

        <FormCheckRadioGroup v-model="form.remember" name="remember" :options="{ remember: 'Remember' }" />

        <BaseDivider />

        <BaseLevel>
          <BaseButtons>
            <BaseButton type="submit" color="info" label="Login" :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing" />
          </BaseButtons>
          <BaseButtons>
            <BaseButton route-name="register" color="info" outline :icon="mdiAccountPlus" />
            <BaseButton v-if="canResetPassword" route-name="password.request" color="info" outline
              :icon="mdiLockQuestion" />
            <BaseButton route-name="dashboard" icon-size="" color="info" outline :icon="mdiHomeOutline" />
          </BaseButtons>

        </BaseLevel>
      </CardBox>
    </SectionFullScreen>
  </LayoutGuest>
</template>
