<template>
  <component :is="dashboard" :if="dashboard" />
</template>
<script>
import Card from '~/components/Card'
import AdminDashboard from '~/components/admin/Dashboard'
import OrganisationDashboard from '~/components/organisations/Dashboard'
import UserDashboard from '~/components/user/Dashboard'
import { mapGetters } from "vuex";

export default {
  name: 'Dashboard',
  layout: 'master',
  components: {
    AdminDashboard,
    OrganisationDashboard
  },
  data: () => ({
    dashboard: ''
  }),

  computed: mapGetters({
    user: "auth/user",
  }),

  created () {
    if ( this.user ) {
      const role = this.user['role_id'] // get role id
      this.dashboard = (role == 1 || role == 2) ? AdminDashboard : OrganisationDashboard
    }
  }

}
</script>
