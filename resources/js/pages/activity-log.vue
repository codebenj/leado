<template>
  <Section className="activity-log" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg" :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-input
              v-model="filters[0].value"
              placeholder="Search and hit enter..."
              clearable
              @change="search"
              :disabled="awaitingSearch"
            />
          </el-col>
        </el-row>

        <el-timeline
          class="timeline"
          v-infinite-scroll="load"
          v-loading="awaitingSearch"
        >
          <el-timeline-item
            :hide-timestamp="true"
            :key="i"
            :timestamp="`LEAD # ${activitylog.id} ${customerName(activitylog)}`"
            placement="top"
            v-for="(activitylog, i) in activitylogs"
          >
            <el-collapse
              class="custom-collapse"
              v-model="activeAccordion"
              accordion
            >
              <el-collapse-item
                :title="`LEAD # ${showLeadId(activitylog)} ${customerName(activitylog)}`"
                :name="i"
              >
                <el-timeline>
                  <el-card
                    v-for="(activity, index) in activitylog.activities"
                    :key="index"
                  >
                    <el-tag
                      size="medium"
                      type="primary"
                      class="activity-log-status"
                      :class="
                        activity.properties.metadata.lead_escalation_color
                      "
                      effect="dark"
                      disable-transitions
                      >{{
                        splitEscalation(
                          activity.properties.metadata.lead_escalation_status,
                          "level"
                        )
                      }}</el-tag
                    >
                    <el-tag
                      size="medium"
                      type="primary"
                      class="activity-log-status"
                      :class="
                        activity.properties.metadata.lead_escalation_color
                      "
                      effect="dark"
                      disable-transitions
                      >{{
                        splitEscalation(
                          activity.properties.metadata.lead_escalation_status,
                          "status"
                        )
                      }}</el-tag
                    >

                    <p class="text-uppercase m-none">
                      <strong>{{ activity.properties.title }}</strong>
                    </p>
                    <p>
                      <small>{{ activity.properties.description }}</small>
                    </p>
                    <p>
                      <small>{{
                        activity.created_at | moment("k:mm DD/MM/YYYY")
                      }}</small>
                    </p>

                    <el-tag
                      v-if="activity.org_name"
                      size="medium"
                      type="info"
                      effect="dark"
                      class="text-uppercase notification-type"
                    >
                      {{ activity.org_name }}
                    </el-tag>

                    <el-tag
                      v-if="activity.properties.metadata.notification_type"
                      size="medium"
                      type="info"
                      effect="light"
                      class="text-uppercase notification-type"
                    >
                      {{ activity.properties.metadata.to.replaceAll('organization', 'organisation') }} {{ activity.properties.metadata.notification_type == "activity_log" ? "email" : activity.properties.metadata.notification_type }}
                    </el-tag>
                    <!-- <el-link href="/admin/activity-log/view/email/1" target="_blank"><i class="el-icon-view el-icon--right"></i> </el-link> -->
                    <el-link v-if="activity.properties.metadata.notification_type == 'activity_log'" @click.native="showEmail(activity.id)"
                      ><i class="el-icon-view el-icon--right"></i>
                    </el-link>
                  </el-card>
                </el-timeline>
              </el-collapse-item>
            </el-collapse>
          </el-timeline-item>
        </el-timeline>
      </el-card>
    </template>
  </Section>
</template>
<script>
import { mapGetters } from "vuex";
import Section from "~/components/Section";

export default {
  name: "ActivityLog",
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
  },
  computed: mapGetters({
    activitylogs: "activitylogs/activitylogs",
    loading: "activitylogs/loading",
  }),

  data: () => ({
    count: 0,
    pageTitle: "Activity Log",
    filters: [
      {
        props: "search",
        value: "",
      },
    ],
    awaitingSearch: false,
    payload: {},
    activeAccordion: "",
  }),

  methods: {
    showLeadId(leadNotification){
      return (leadNotification.lead_id) ? leadNotification.lead_id : leadNotification.id
    },

    showEmail(activity_id) {
      const route = this.$router.resolve({
        path: `/admin/activity-log/view/email/${activity_id}`,
      });
      window.open(route.href, "_blank");
    },

    badgeColor(color) {
      return color == "gray" ? "purple" : color;
    },

    load() {
      // dispatch fetch next page
      this.$store.dispatch("activitylogs/fetchMoreActivityLogs", this.payload);
    },

    splitEscalation(escalation, type) {
      const splitStatus = escalation.split("-");

      if(splitStatus.length == 2){
        return (type == "status" ? splitStatus[1] : splitStatus[0]).trim();
      }else if(splitStatus.length == 3){
        return (type == "status" ? splitStatus[1]+' - '+splitStatus[2] : splitStatus[0]).trim();
      }
    },

    search(value) {
      if (!this.awaitingSearch) {
        setTimeout(() => {
          this.payload.key = value;
          this.$store.dispatch("activitylogs/fetchActivityLogs", this.payload);
          this.awaitingSearch = false;
        }, 800);
      }

      this.awaitingSearch = true;
    },

    customerName( customer ) {
      return customer ? `${customer.first_name} ${customer.last_name}` : "";
    },
  },

  beforeMount() {
    this.$store.dispatch("activitylogs/fetchActivityLogs", []);
  },

  mounted() {
    var channel = this.$echo.channel("traleado-global");

    channel.listen(".activity.created", (data) => {
      this.$store.dispatch("activitylogs/fetchActivityLogs", this.payload);
    });
  },
};
</script>

<style scoped>
  .timeline{
    padding-left: 0px;
  }
</style>
