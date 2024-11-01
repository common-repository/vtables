import { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('src/domains/general/layouts/MainLayout.vue'),
    children: [
      {
        path: '',
        component: () => import('src/domains/dashboard/ui/DashboardPage.vue'),
      },
      {
        path: 'table/:id',
        component: () => import('src/domains/dashboard/ui/DashboardTable.vue'),
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('src/domains/general/ui/ErrorNotFound.vue'),
  },
];

export default routes;
