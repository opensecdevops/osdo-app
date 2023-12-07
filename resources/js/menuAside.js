import {
  mdiHomeOutline,
  mdiCompost,
  mdiPackageVariantClosed,
  mdiArchiveSearchOutline,
  mdiCog
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
    label: 'Packages',
    icon: mdiPackageVariantClosed,
    menu: [
      {
        icon: mdiCog,
        label: 'Management',
        route: 'packages.index',
      },
      {
        icon: mdiArchiveSearchOutline,
        label: 'Validate',
        route: 'packages.test',
      }
    ]
  },
]
