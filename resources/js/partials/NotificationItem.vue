<template>
  <el-card>
      <el-tag
        size="medium"
        type="primary"
        class="activity-log-status"
        :class="notification.metadata.lead_escalation_color"
        effect="dark"
        disable-transitions>{{ splitEscalation(notification.metadata.lead_escalation_status, 'level') }}
      </el-tag>

      <el-tag
        size="medium"
        type="primary"
        class="activity-log-status"
        :class="notification.metadata.lead_escalation_color"
        effect="dark"
        disable-transitions>{{ splitEscalation(notification.metadata.lead_escalation_status, 'status') }}
      </el-tag>

      <p class="text-uppercase m-none"><strong>{{ notification.title }} </strong></p>
      <p><small>{{ notification.description }}</small></p>
      <p><small>{{ notification.created_at | moment("k:mm DD/MM/YYYY") }}</small></p>

      <el-tag v-if="notification.org_name" size="medium" type="info" effect="dark" class="text-uppercase notification-type">
        {{ notification.org_name }}
      </el-tag>

      <el-tag v-if="notification.metadata.notification_type" size="medium" type="info" effect="light" class="text-uppercase notification-type">
        {{ notification.metadata.to.replaceAll('organization', 'organisation') }} {{ notification.metadata.notification_type }}
      </el-tag>
    </el-card>
</template>
<script>
export default {
  name: 'NotificationItem',
  props: ['notification'],

  data: () => ({
    iconClass: 'icon-briefcase'
  }),

  methods: {
    splitEscalation(escalation, type) {
      const splitStatus = escalation.split(' - ');

      if(splitStatus.length == 2){
        return (type == 'status' ? splitStatus[1] : splitStatus[0]).trim()
      }else if(splitStatus.length == 3){
        return (type == 'status' ? splitStatus[1]+' - '+splitStatus[2] : splitStatus[0]).trim()
      }
		}
  },

  mounted() {
  }
}
</script>
