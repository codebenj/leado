function page (path) {
  return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
  { path: '/', name: 'welcome', component: page('welcome.vue') },

  { path: '/dashboard', name: 'dashboard', meta: { requiresAuth: true, allowedRoles: ['administrator', 'organisation', 'user'] }, component: page('dashboard/index.vue') },

  // Admin //

  // { path: '/admin/dashboard', name: 'admin.dashboard', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('dashboard.vue') },

  // Admin - Leads
  { path: '/admin/leads/create', name: 'admin.leads.create', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/leads/form.vue') },
  { path: '/admin/leads/update/:id', name: 'admin.leads.update', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/leads/form.vue') },
  { path: '/admin/leads/history/:id', name: 'admin.leads.history', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/leads/history.vue') },

  // Admin - Organisation
  { path: '/admin/organisations/:id?', name: 'admin.organisations', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/organisations/index.vue') },
  { path: '/admin/organisations/create', name: 'admin.organisations.create', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/organisations/form.vue') },
  { path: '/admin/organisations/show/:id', name: 'admin.organisations.show', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/organisations/show.vue') },
  { path: '/admin/organisations/update/:id', name: 'admin.organisations.update', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/organisations/form.vue') },
  { path: '/admin/organisations/import', name: 'admin.organisations.import', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/organisations/import.vue') },

  { path: '/admin/reports', name: 'admin.reports', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('reports.vue') },
  { path: '/admin/postcodes', name: 'admin.postcodes', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('postcodes.vue') },
  { path: '/admin/notifications', name: 'admin.notifications', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('notifications.vue') },
  { path: '/admin/activity-log', name: 'activitylog', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('activity-log.vue') },
  { path: '/admin/activity-log/view/email/:activity_id', name: 'activitylog.view.email', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('activity-view-email.vue') },

  // Admin - Org. Locator
  { path: '/admin/org-locator', name: 'orglocator', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('org-locator.vue') },
  { path: '/admin/org-locator/import', name: 'orglocator.import', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/orglocators/import.vue') },
  { path: '/admin/org-locator/create', name: 'orglocator.create', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/orglocators/create.vue') },
  { path: '/admin/org-locator/update/:id', name: 'orglocator.update', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/orglocators/create.vue') },

  // Admin - Users
  { path: '/admin/users', name: 'admin.users', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('users/index.vue') },
  { path: '/admin/users/create', name: 'admin.users.create', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('users/form.vue') },
  { path: '/admin/users/update/:id', name: 'admin.users.update', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('users/form.vue') },

  { path: '/admin/time-settings', name: 'admin.time.settings', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/timesettings/index.vue') },
  { path: '/admin/escalation/settings', name: 'admin.escalation.settings', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/settings/index.vue') },
  { path: '/admin/settings', name: 'admin.settings', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/settings/admin.vue') },
  { path: '/admin/roles-permissions', name: 'admin.roles.permissions', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('roles/index.vue') },
  { path: '/admin/lead/:id/stats', name: 'admin.lead.stats', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('stats.vue') },
  { path: '/admin/organization/:organization_id/stats', name: 'admin.organization.stats', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('organization-stats.vue') },

  // Admin - Customer
  { path: '/admin/customers', name: 'admin.customers', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/customers/index.vue') },

  // Admin - Scrum Board
  { path: '/admin/scrum-board', name: 'scrumboard', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('scrum-board.vue') },
  // Admin - Find Store
  { path: '/admin/stores', name: 'admin.stores', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/stores/index.vue') },
  { path: '/admin/stores/import', name: 'admin.stores.import', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('admin/stores/import.vue') },

  // End of Admin //

  // Organisation //

  // { path: '/organisation/dashboard', name: 'organisation.dashboard', meta: { requiresAuth: true, allowedRoles: ['organisation'] }, component: page('dashboard.vue') },
  { path: '/organisation/responses/:id', name: 'organisation.redirect.lead', meta: { requiresAuth: true, allowedRoles: ['organisation'] }, component: page('organisation/redirect.vue') },
  { path: '/organisation/lead/:id', name: 'organisation.lead', meta: { requiresAuth: true, allowedRoles: ['organisation'] }, component: page('organisation/history.vue') },
  { path: '/organisation/notifications', name: 'organisation.notifications', meta: { requiresAuth: true, allowedRoles: ['organisation'] }, component: page('notifications.vue') },

  // End of Organisation //

  // Profile //
  { path: '/my/profile', name: 'my.profile', meta: { requiresAuth: true, allowedRoles: ['administrator', 'user'] }, component: page('my-profile.vue') },
  { path: '/profile', name: 'profile', meta: { requiresAuth: true, allowedRoles: ['organisation', 'user'] }, component: page('my-profile.vue') },
  // End of Profile

  // User //
  // { path: '/user/dashboard', name: 'user.dashboard', meta: { requiresAuth: true, allowedRoles: ['user'] }, component: page('user/index.vue') },
  // End of User

  { path: '/login', name: 'login', component: page('auth/login.vue') },
  { path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue') },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue') },
  { path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify.vue') },
  { path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend.vue') },

  { path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: page('settings/password.vue') }
    ],
    meta: { requiresAuth: true, adminAuth: false, organisationAuth: true, userAuth: true }
  },

  { path: '*', component: page('errors/404.vue') }
]
