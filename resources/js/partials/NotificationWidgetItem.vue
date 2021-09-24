<template>
  <router-link
    class="dropdown-item noti-container notify-item py-2"
    :class="{ 'active': notification.is_read == '0' }"
    :to="{ name: 'notifications' }"
  >
    <div class="noti-wrapper">
      <span
        class="mb-2 badge"
        :class=" `badge-${badgeColor}` "
      >{{ notification.status }}</span>
      <div class="notify-item__text">
        <h6 class="text-capitalize ml-1 mb-2">
          <small>
            <strong>{{ notification.title.toLowerCase() }}</strong>
          </small>
        </h6>
        <p class="noti-text text-break ml-1 mb-2">{{ notification.message.toLowerCase() }}</p>
      </div>
      <div>
        <span class="noti-tex ml-1 mb-0">
          <small>Date: {{ notification.created_at | moment("k:mm DD/MM/YYYY") }} {{ notification.created_at }}</small>
        </span>
      </div>
    </div>
  </router-link>
</template>
<script>
export default {
  name: "NotificationWidgetItem",
  props: [
      "notification"
    ],
  data: () => ({
  }),
  computed: {
      badgeColor() {
          return this.notification.color == 'gray' ? 'purple' : this.notification.color
      }
  }
};
</script>