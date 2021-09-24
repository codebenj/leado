<template>
  <Section className="lead-history" :pageTitle="pageTitle">
    <template v-slot:content>
      <!-- overview cards -->
      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
          <el-card class="box-card m-card-1" v-if="lead">
            <el-tag type="primary" class="dark-blue-gray">Enquirer</el-tag>
            <el-tag type="primary" :class="histories[0].color">{{
              histories[0].escalation_level
            }}</el-tag>
            <p class="mb0">{{ `${lead.customer.first_name} ${lead.customer.last_name}` }}</p>
            <p class="detail">{{ `${lead.customer.contact_number}` }}</p>
            <p class="detail mb">{{ `${lead.customer.address.full_address}` }}</p>
            <p class="detail">&nbsp;</p>
            <p>
              <small>
                <el-tag type="info">{{
                  histories[0].created_at | moment("k:mm DD/MM/YYYY")
                }}</el-tag>
              </small>
            </p>
          </el-card>
        </el-col>
        <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
          <el-card class="box-card m-card-2" v-if="lead">
            <el-tag type="primary" class="dark-blue-gray">Organisation</el-tag>
            <el-tag type="primary" :class="histories[0].color">{{
              histories[0].escalation_status
            }}</el-tag>
            <p class="mb0">
                {{ histories[0].organisation ? histories[0].organisation.name : 'No Organisation Assigned' }}

                <ManualIcon :org="activeEscalation && activeEscalation.organisation" />
            </p>
            <p class="detail">{{ activeEscalation.organisation ? activeEscalation.organisation.contact_number : '' }}</p>
            <p class="detail">{{ activeEscalation.organisation ? activeEscalation.organisation.organisation_users[0].user.email : '' }}</p>
            <p class="detail mb">{{ activeEscalation.organisation ? activeEscalation.organisation.address.state : '' }}</p>
            <p>
              <small>
                <el-tag type="info">{{
                  histories[0].created_at | moment("k:mm DD/MM/YYYY")
                }}</el-tag>
              </small>
            </p>
          </el-card>
        </el-col>
        <el-col v-if="lead && lead.staff_comments" :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
          <el-card class="box-card m-card-3" v-if="lead">
            <el-tag type="primary" class="dark-blue-gray">Staff Comment</el-tag>
            <p class="detail">{{ lead.staff_comments }}</p>
          </el-card>
        </el-col>
      </el-row>

      <!-- ASSIGNED USERS -->
      <el-row v-if="assignedUsers.length" :gutter="24">
        <el-col :xs="24" :sm="24" :md="12" :lg="8" :xl="8">
          <el-card class="box-card m-card-1" v-if="lead">
            <el-tag type="primary" class="dark-blue-gray">Assigned Users</el-tag>
            <p class="detail" v-for="AU in assignedUsers" :key="AU.id">{{ AU.first_name }} {{ AU.last_name }}</p>
          </el-card>
        </el-col>
      </el-row>

      <!-- actual meters -->
      <MetersForm v-if="metersForm.id"/>

      <!-- lead history -->
      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-none" v-if="histories" shadow="never">
            <div slot="header" class="clearfix">
              <h4>
                Lead History
                <el-button-group class="fl-right r-btn-reset">
                   <el-button
                    type="primary"
                    @click="
                      $router.push({
                        name: 'admin.leads.update',
                        params: { id: histories[0].id },
                      })
                    "
                    >Edit Lead</el-button
                  >
                  <el-button
                    type="primary"
                    @click.native="showEscalation()"
                    v-if="isManualUpdateEnabled(activeEscalation && activeEscalation.organisation)"
                    >Update Escalation</el-button
                  >
                  <el-button class="fl-right" :disabled="lead.lead_escalations[0].escalation_level == 'Unassigned'" type="primary" @click="reassignLead" v-if="lead && lead.customer_type == 'Supply & Install'"
                    >Reassign Lead</el-button>

                  <el-button class="fl-right" type="primary" @click="openAssignUserToLeadModal">Assign User</el-button>
                </el-button-group>

                <!-- ASSIGN USER TO LEAD -->
                <AssignUsersToLead />
              </h4>
            </div>

            <data-tables
              :data="histories"
              :total="histories.length"
              :table-props="tableProps"
              :pagination-props="{ pageSizes: [10, 15, 20] }"
            >

              <el-table-column label="Lead ID" width="120" prop="lead_id">
                <template slot-scope="{ row }">
                  {{ (row.lead.lead_id) ? row.lead.lead_id : row.lead_id }}
                </template>
              </el-table-column>

              <el-table-column label="Organisation" prop="organisation.name" />

              <el-table-column
                label="Escalation Level"
                prop="escalation_level"
                width="160px"
              >
                <template slot-scope="{ row }">
                  <el-tag
                    size="medium"
                    type="primary"
                    :class="row.color"
                    effect="dark"
                    disable-transitions
                    >{{ row.escalation_level }}</el-tag
                  >
                </template>
              </el-table-column>

              <el-table-column
                label="Escalation Status"
                prop="escalation_status"
                width="160px"
              >
                <template slot-scope="{ row }">
                  <el-tag
                    size="medium"
                    type="primary"
                    :class="row.color"
                    effect="dark"
                    disable-transitions
                    >{{ row.escalation_status }}</el-tag
                  >
                </template>
              </el-table-column>

              <el-table-column label="Reason" prop="reason">
                <template slot-scope="{ row }">
                  <label v-if="row.escalation_status == 'Won'">{{ row.metadata.response }}</label>
                  <label v-else>{{ row.reason }}</label>
                  <br />
                  <label v-if="row.escalation_status == 'Won'">
                    <b v-if="row.gutter_edge_meters">Gutter Edge: {{ row.gutter_edge_meters }}m</b>
                    <b v-if="row.valley_meters">Valley: {{ row.valley_meters }}m</b>
                    <b v-if="row.valley_meters">Installed Date: {{ row.installed_date }}</b>
                  </label>
                  <el-tooltip
                    v-if="row.comments"
                    :content="row.comments"
                    placement="top"
                  >
                    <i class="el-icon-s-comment"></i>
                  </el-tooltip>
                </template>
              </el-table-column>

              <el-table-column label="Date" prop="created_at">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                </template>
              </el-table-column>
            </data-tables>
          </el-card>
        </el-col>
      </el-row>

      <!-- lead comments -->
      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <LeadComments />
        </el-col>
      </el-row>

      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-none" v-if="notifications" shadow="never">
            <div slot="header" class="clearfix">
              <h4>
                Enquirer Notifications

                <el-button
                  class="fl-right r-btn-reset"
                  type="primary"
                  @click="sendNotification"
                  >Send Enquirer Notification</el-button
                >
              </h4>
            </div>

            <data-tables
              :data="notifications"
              :total="notifications.length"
              :table-props="tableProps"
              :pagination-props="{ pageSizes: [10, 15, 20] }"
            >
              <el-table-column label="Message" prop="description" />

              <el-table-column label="Date Sent" prop="created_at" width="300">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                </template>
              </el-table-column>
            </data-tables>
          </el-card>
        </el-col>
      </el-row>

      <el-dialog
        title="Reassign Lead"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible="reassignDialogVisible"
        :show-close="false"
        append-to-body
        width="30%"
      >
        <ReassignForm />
      </el-dialog>

      <el-dialog
        title="Send Enquirer a Message"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible="sendNotificationDialogVisible"
        :show-close="false"
        append-to-body
        width="30%"
      >
        <SendNotificationForm />
      </el-dialog>

      <EscalationModal />
    </template>
  </Section>
</template>

<script>
import { DataTables } from "vue-data-tables";
import { mapGetters } from "vuex";
import Section from "~/components/Section";
import ReassignForm from "./reassign";
import SendNotificationForm from "./sendnotification";
import LeadComments from "~/components/LeadComments.vue";
import MetersForm from "./meters.vue";
import AssignUsersToLead from "~/components/AssignUsersToLead.vue";
import EscalationModal from "~/components/escalations/main";
import { isManualUpdateEnabled } from "~/helpers";
import ManualIcon from '~/components/ManualIcon'

export default {
  name: "LeadHistory",
  layout: "master",
  middleware: ["auth"],

  components: {
    Section,
    DataTables,
    SendNotificationForm,
    ReassignForm,
    LeadComments,
    MetersForm,
    // ASSIGN USER TO LEAD
    AssignUsersToLead,
    EscalationModal,
    ManualIcon
  },

  data() {
    return {
      pageTitle: "Lead Overview",
      tableProps: {
        rowClassName: function (row, index) {
          const { color } = row.row;

          return color;
        },
      },
    };
  },

  computed: mapGetters({
    lead: "leadhistory/lead",
    histories: "leadhistory/histories",
    metersForm: "leadhistory/metersForm",
    notifications: "leadhistory/notifications",
    reassignDialogVisible: "leadhistory/reassignDialogVisible",
    sendNotificationDialogVisible: "leadhistory/sendNotificationDialogVisible",
    activeEscalation: "leadhistory/active_escalation",
    escalationModals: "leadescalation/escalationModals",
    assignedUsers: "leadhistory/assignedUsers",
  }),

  methods: {
    // ASSIGN USER TO LEAD
    openAssignUserToLeadModal(){
			this.fetchUsers();
			this.$store.dispatch("leadhistory/setDialog", { close: true, form: "assign_user_to_lead" });
		},

    fetchUsers(){
			this.$store.dispatch("leadhistory/fetchUsers");
		},

    fetchAssignedUsers(){
			this.$store.dispatch("leadhistory/fetchAssignedUsers", this.$route.params.id);
		},

    reassignLead() {
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "reassign",
      });
    },

    sendNotification() {
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "send_notification",
      });
    },

    getLeadHistory() {
      const leadId = this.$route.params.id ? this.$route.params.id : "";

      if (leadId) {
        this.$store.dispatch("leadhistory/getLeadHistory", leadId);
      }
    },

    isManualUpdateEnabled: isManualUpdateEnabled,
     modal(escalationLevel, escalationStatus) {
      switch (escalationLevel) {
        case "Accept Or Decline":
          if (
            escalationStatus == "Declined-Lapsed" ||
            escalationStatus == "Declined"
          ) {
            return "declined";
          }

          return "aod";
        case "Confirm Enquirer Contacted":
          if (
            escalationStatus == "Declined" ||
            escalationStatus == "Discontinued"
          ) {
            return "declined";
          }

          return "cec";
        case "In Progress":
          return "inprogress";
        case "Won":
          return "won";
        case "Lost":
          return "lost";
        default:
          return "finished";
      }
    },

    showEscalation() {
      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: this.activeEscalation,
        showEscalation: true,
        modal: this.modal(this.activeEscalation.escalation_level, this.activeEscalation.escalation_status),
      });
    },
  },

  beforeMount() {
    // this.getLeadHistory();
    // this.$store.dispatch("leads/fetchOrgAll");
  },

  mounted(){
    this.fetchAssignedUsers();

    this.getLeadHistory();
    this.$store.dispatch("leads/fetchOrgAll");
  }
};
</script>

<style scoped>
.detail{
  margin-bottom: 0;
  color: gray;
  font-size: 14px;
}
.mb{
  margin-bottom: 1rem;
}
.mb0{
  margin-bottom: 0;
}

  .m-card-1{
    margin-left: 20px;
  }

  @media all and (max-width: 768px){
    .m-card-1, .m-card-2, .m-card-3{
      margin: 10px 20px;
    }
  }

  @media all and (max-width: 426px){
    .r-btn-reset{
      float: none;
    }
  }
</style>
