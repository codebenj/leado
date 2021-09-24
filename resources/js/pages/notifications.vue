<template>
  <Section className="notifications" :pageTitle="pageTitle">

    <template
      v-slot:button
    >
      <el-button
        dusk="btn-create-lead-page"
        class="fl-right r-btn-reset"
        type="primary"
        @click="sendCustomNotification()"
        >Custom Notification</el-button
      >
    </template>

    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg" :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-input
              v-model="filters[0].value"
              placeholder="Search and hit enter..."
              clearable
              @change="search"
              :disabled="loading"
            />
          </el-col>
        </el-row>
        <el-timeline
          class="timeline"
          v-infinite-scroll="load"
          ref="timeline"
          v-loading="loading"
        >
          <el-timeline-item
            :hide-timestamp="true"
            :key="i"
            :timestamp="`LEAD # ${leadNotification.id} ${customerName(leadNotification)}`"
            placement="top"
            v-for="(leadNotification, i) in notifications"
          >
            <el-collapse class="custom-collapse" v-model="activeAccordion" accordion>
              <el-collapse-item :title="`LEAD # ${showLeadId(leadNotification)} ${customerName(leadNotification)}`" :name="i">
                <el-timeline>
                  <NotificationItem
                    v-for="(notification, n) in leadNotification.notifications"
                    :key="n"
                    :notification="notification"
                  />
                </el-timeline>
              </el-collapse-item>
            </el-collapse>
          </el-timeline-item>
        </el-timeline>

      </el-card>

      <el-dialog
        :before-close="handleCustomNotificationModalClose"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible.sync="showCustomeNotification"
        width="40%"
        :show-close="true"
        append-to-body
        custom-class="organisation-status"
        :destroy-on-close="true"
      >
        <CustomNotificationModal v-on:handleCustomNotificationModalClose="handleCustomNotificationModalClose" />
      </el-dialog>

    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import CustomNotificationModal from "~/components/notifications/CustomNotificationModal";
import NotificationItem from "~/partials/NotificationItem";
import { mapGetters } from "vuex";

export default {
  name: "Notifications",
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
    NotificationItem,
    CustomNotificationModal
  },

  computed: mapGetters({
    notifications: "notifications/notifications",
    loading: "notifications/loading",
  }),

  data: () => ({
    showCustomeNotification: false,
    pageTitle: "Notifications",
    filters: [
      {
        props: "search",
        value: "",
      },
    ],
    payload: {},
    activeAccordion: "",
  }),

  methods: {
    handleCustomNotificationModalClose(){
      this.showCustomeNotification = false
    },

    sendCustomNotification(){
      this.showCustomeNotification = true
    },

    showLeadId(leadNotification){
      return (leadNotification.lead_id) ? leadNotification.lead_id : leadNotification.id
    },

    load() {
      // dispatch fetch next page
      this.$store.dispatch("notifications/fetchMoreNotifications", this.payload);
    },

    search(value) {
      if (!this.awaitingSearch) {
        setTimeout(() => {
          this.payload.key = value;
          this.$store.dispatch("notifications/fetchNotifications", this.payload);
          this.awaitingSearch = false;
        }, 800);
      }

      this.awaitingSearch = true;
    },

    customerName({ customer }) {
      return customer ? `${customer.first_name} ${customer.last_name}` : ''
    },
  },

  beforeMount() {
    this.$store.dispatch("notifications/fetchNotifications", this.payload);
  },

  mounted() {
    var channel = this.$echo.channel("traleado-global");

    channel.listen(".notification.created", (data) => {
      this.$store.dispatch("notifications/fetchNotifications", this.payload);
    });
  },
};
</script>

<style scoped>
  .timeline{
    padding-left: 0px;
  }
</style>
