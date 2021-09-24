<template>
  <div class="notification-dropdown dropdown-menu dropdown-menu-right">
    <div class="arrow_box_right">
      <div class="notif-wrapper noti-list">
        <div v-if="notifications.length">
          <NotificationWidgetItem v-for="notification in notifications" :notification="notification" :key="notification.id" />
        </div>

        <div class="d-flex mt-4 mb-4 jc-center ai-center no-notif" v-else>
            <h6><strong>No Notification</strong></h6>
        </div>
      </div>
      <div v-if="this.$store.state.auth.user['role_id']==1 ||this.$store.state.auth.user['role_id']==2">
      <router-link :to="{ name: 'admin.notifications' }"
        class="noti-footer primary text-center d-block border-top border-top-blue-grey border-top-lighten-4 text-bold-400 py-1">
        <small>Read All Notifications</small>
      </router-link>
      </div>
      <div v-if="this.$store.state.auth.user['role_id']==3 ||this.$store.state.auth.user['role_id']==4">
      <router-link :to="{ name: 'organisation.notifications' }"
        class="noti-footer primary text-center d-block border-top border-top-blue-grey border-top-lighten-4 text-bold-400 py-1">
        <small>Read All Notifications</small>
      </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import Card from '~/components/Card'
import NotificationWidgetItem from '~/partials/NotificationWidgetItem'
import { mapGetters } from 'vuex'

export default {
  name: 'NotificationWidget',
  components: {
		NotificationWidgetItem,
	},

  data: () => ({
		notifications: [
      {
        id: '1',
        color: 'red',
        status: 'Declined-Lapsed',
        title: '',
        message: '“confirm contacted” status getting no response from installer',
        created_at: '07/08/2020',
        is_read: '0',
      }
    ]
	}),
}
</script>
