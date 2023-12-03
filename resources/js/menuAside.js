import {
  mdiHomeOutline,
  mdiCompost,
  mdiPackageVariantClosed,
} from '@mdi/js'

export default [
  {
    route: 'dashboard',
    icon: mdiHomeOutline,
    label: 'Dashboard'
  },
  {
    route: 'generator.index',
    label: 'Generator',
    icon: mdiCompost
  },
  {
    route: 'packages.index',
    label: 'Packages',
    icon: mdiPackageVariantClosed
  }
]
