import {
  mdiMonitor,
  mdiCompost,
  mdiPackageVariantClosed,
  mdiServerNetwork 
} from '@mdi/js'

export default [
  {
    route: 'dashboard',
    icon: mdiMonitor,
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
