<template>
  <el-card class="box-card b-none page-header" shadow="never" id="organisation-page" v-if="organisation">

    <!-- Profile Sections -->
    <el-row :gutter="24" class="mb-5">
      <!-- Org Details -->
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <h1>Org Overview</h1>
        <h5 class="detail">
          <span class="mr-3">{{ checkData( organisation.name ) }}</span>
          <span><ManualIcon :org="organisation" /></span>
          <span v-if="!organisation.has_postcodes"><PostcodeIcon :displayOnly="true" /></span>
          <el-tag :type="colorOrganisationStatus(organisation)" disable-transitions>
            {{ organisation.account_status_type }}
          </el-tag>
        </h5>
        <p class="detail">Org Code: {{ checkData( organisation.org_code ) }}</p>
        <p class="detail">{{ checkData( organisation.user.name ) }}</p>
        <p class="detail">{{ checkData( organisation.contact_number ) }}</p>
        <p class="detail">{{ checkData( organisation.user.email ) }}</p>
        <p class="detail">{{ checkData( organisation.address.full_address ) }}</p>
      </el-col>

      <!-- Org Avatar + Rating -->
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" align="center">
        <div class="block">
          <el-avatar :size="200" :src="organisation.user.avatar"></el-avatar>
        </div>
        <div class="block">
          <el-rate
            v-model="orgRating"
            disabled
            text-color="#ff9900"
            id="org-rating">
          </el-rate>
        </div>
      </el-col>

      <!-- Buttons -->
      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" align="right">
        <el-button-group id="org-buttons">
          <!-- <el-button @click="editOrgBtn()">Edit</el-button> -->
          <el-button @click="initDialogMessage">Edit</el-button>
          <el-button @click="sendLeadUpdate()" v-if="this.organisation && this.organisation.metadata.manual_update">Send Lead Update</el-button>
          <el-button @click="dialogVisibleStatus = true">Status</el-button>
        </el-button-group>
      </el-col>
    </el-row>

    <!-- Tabs Section -->
    <el-row :gutter="24">
      <!-- Tabs -->
      <el-col :xs="24" :sm="24" :md="20" :lg="20" :xl="20">
        <el-tabs type="card">
          <!-- Assigned Leads -->
          <el-tab-pane label="Assigned Leads">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24" align="center">
                <AssignedLeads />
              </el-col>
            </el-row>
          </el-tab-pane>

          <!-- Comments Tab -->
          <el-tab-pane label="Comments">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24">
                <OrganizationComments />
              </el-col>
            </el-row>
          </el-tab-pane>

          <!-- Messages Tab -->
          <el-tab-pane label="Org Notifications">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24">
                <OrganizationNotificationHistory />
              </el-col>
            </el-row>
          </el-tab-pane>

          <!-- System Activity Tab -->
          <el-tab-pane label="System Activity">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24">
                <data-tables-server
                  :data="systemActitivies"
                  :total="systemActivitiesTotal"
                  :loading="systemActivitiesLoading"
                  :pagination-props="{ pageSizes: [10, 15, 20] }">
                    <el-table-column label="Activity" prop="activity">
                      <template slot-scope="{ row }">
                        {{ row.properties.title }} - {{ row.properties.description }}
                      </template>
                    </el-table-column>
                    <el-table-column label="Date Time" prop="created_at">
                      <template slot-scope="{ row }">
                        {{ row.created_at | moment( 'k:mm DD/MM/YYYY' ) }}
                      </template>
                    </el-table-column>
                  </data-tables-server>
              </el-col>
            </el-row>
          </el-tab-pane>

          <!-- Postcodes Tab -->
          <el-tab-pane label="Postcodes">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24">
                <OrganizationPostcodes />
              </el-col>
            </el-row>
          </el-tab-pane>

          <!-- Statistics Tab -->
          <el-tab-pane label="Statistics">
            <el-row :gutter="24" class="py-3">
              <el-col :span="24" align="center">
                <OrgStatsTable :org_ids="[this.organisation.id]" />
              </el-col>
            </el-row>
          </el-tab-pane>
        </el-tabs>
      </el-col>

      <!-- Maps -->
      <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
        <el-row :gutter="24">
          <el-col :span="24" align="right">
            <p class="detail">
              <span class="mr-3">Account Status:</span>
              <el-tag
                :type="organisation.org_status === 1 ? 'success' : 'danger'"
                disable-transitions>
                {{ organisation.org_status === 1 ? "Active" : "Inactive" }}
              </el-tag>
            </p>
            <p class="detail">Account Created: {{ organisation.created_at | moment("DD/MM/YYYY") }}</p>
          </el-col>

          <!-- Org Address On Google Maps -->
          <!-- <el-col :span="24">
            <GmapMap
              :center="{ lat: orgLocation.lat, lng: orgLocation.lng }"
              :zoom="7"
              map-type-id="roadmap"
              style="width: 500px; height: 300px"
            />
          </el-col> -->
        </el-row>
      </el-col>
    </el-row>

    <el-dialog
      title="Edit Status"
      v-dialogDrag
      ref="dialog__wrapper"
      :visible.sync="dialogVisibleStatus"
      width="40%"
      :show-close="false"
      append-to-body>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          class="demo-ruleForm"
          label-position="top"
          label-width="120px"
          :model="orgForm"
          :rules="orgFormRules"
          status-icon
          ref="orgForm"
        >
          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <div class="el-form-item">
                <label class="filter-labels d-block"> Org Status </label>
                <el-switch
                  v-model="orgForm.org_status"
                  @change="orgStatusChange"
                  active-text="Active"
                  inactive-text="Inactive">
                </el-switch>
              </div>
            </el-col>

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <div class="el-form-item">
                <label class="filter-labels d-block"> Escalation </label>
                <el-switch
                  v-model="orgForm.manual_update"
                  @change="manualUpdateChange"
                  active-text="Manual"
                  inactive-text="Auto">
                </el-switch>
                <ManualIcon :displayOnly="true" />
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="24">
            <!-- <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-checkbox v-model="orgForm.is_suspended" @change="showSendOrgNotification"
                  >Is Suspended</el-checkbox
                >
                <el-checkbox v-if="sendOrgNotification" v-model="orgForm.send_org_notification">Send Org SMS and Email Notification</el-checkbox>
              </el-form-item>
            </el-col> -->

            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <div class="el-form-item">
                <label class="filter-labels d-block"> Account Status </label>
                <el-switch
                  v-model="orgForm.is_suspended"
                  @change="showSendOrgNotification"
                  active-text="Suspended"
                  inactive-text="Unsuspended">
                </el-switch>
              </div>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="24" :xl="24">
              <div class="el-form-item" v-if="!showSendOrgNotification()">
                <el-radio-group v-model="orgForm.suspension_type">
                  <el-radio label="On Hold/Too Busy">On Hold/Too Busy</el-radio>
                  <el-radio label="Leave">Leave</el-radio>
                  <el-radio label="System">System</el-radio>
                </el-radio-group>
              </div>
            </el-col>
          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-checkbox v-if="sendOrgNotification" v-model="orgForm.low_priority">Low Priority</el-checkbox><br>
              <el-checkbox v-if="sendOrgNotification" v-model="orgForm.send_org_notification">Send Org SMS and Email Notification</el-checkbox>
            </el-col>
          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"><br>
            </el-col>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"><br>
            </el-col>
          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item
                v-show="false"
                label="Enable Manual Update"
                prop="manual_update"
               >
              <el-switch
                v-model="orgForm.manual_update"
                active-text="Enable"
                inactive-text="Disable">
              </el-switch>
              </el-form-item>

              <label style="font-size:14px; color: #606266" class="d-block">Lead Escalation Notification</label>
              <el-radio-group v-model="orgForm.notificationsSelection" @change="leadEscalationNotification" size="medium" :disabled="orgForm.manual_update">
                <el-radio-button label="both">Both</el-radio-button>
                <el-radio-button label="sms">SMS</el-radio-button>
                <el-radio-button label="notification">Email</el-radio-button>
              </el-radio-group>
            </el-col>

          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" align="end">
              <el-form-item>
                <el-button
                  dusk="organisation-save"
                  v-show="!orgForm.id"
                  type="primary"
                  @click="saveOrg('orgForm')"
                  >Submit</el-button
                >

                <el-button
                  v-show="orgForm.id"
                  :disabled="!isTouched"
                  type="primary"
                  @click="saveOrg('orgForm')"
                  >Update</el-button
                >

                <el-button
                  type="danger"
                  @click="dialogVisibleStatus = false"
                >
                  Cancel
                </el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-card>
    </el-dialog>

    <!-- <el-dialog
      title="Send Organisation a Message"
      :visible="sendOrgNotificationDialogVisible"
      :show-close="false"
      width="30%"
      append-to-body
    >
      <SendOrgNotificationForm :organisation="organisation" v-on:destroyDialog="destroyDialog" />
    </el-dialog> -->

    <el-dialog
      title="Organisation Details"
      v-dialogDrag
      ref="dialog__wrapper"
      :visible="sendOrgNotificationDialogVisible"
      :show-close="false"
      width="50%"
      append-to-body
    >
      <OrganisationForm @closeModal="destroyDialog" />
    </el-dialog>
  </el-card>
</template>

<script>
import { mapGetters } from 'vuex'
import LeadComments from "~/components/LeadComments.vue";
import AssignedLeads from "~/components/organisations/AssignedLeads.vue";
import OrganizationComments from "~/components/OrganizationComments.vue";
import OrganizationPostcodes from "~/components/OrganizationPostcodes.vue";
import OrganizationNotificationHistory from "~/components/OrganizationNotificationHistory.vue";
import OrganisationForm from "~/components/OrganisationForm.vue";
import ManualIcon from "~/components/ManualIcon.vue";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import { getLeadEscalationNotification, colorOrganisationStatus } from "~/helpers";
import Swal from 'sweetalert2'
import { DataTablesServer } from "vue-data-tables";
import SendOrgNotificationForm from "../leads/sendorgnotification";

export default {
  layout: 'master',

  middleware: ['auth'],

  components: {
    LeadComments,
    OrganizationComments,
    OrganizationPostcodes,
    ManualIcon,
    PostcodeIcon,
    DataTablesServer,
    SendOrgNotificationForm,
    OrganizationNotificationHistory,
    AssignedLeads,
    OrganisationForm
  },

  data: () => ({
    orgRating: 3.5,
    dialogVisibleStatus: false,
    sendOrgNotification: false,
    isTouched: false,
    isTouchedCounter: 0,
    sendOrgNotificationDialogVisible: false
  }),

  computed: mapGetters({
    organisation: 'organisations/organisation',
    organisationLoading: 'organisations/organisationLoading',
    orgLocation: 'organisations/orgLocation',
    orgForm: "organisations/orgForm",
    orgFormRules: "organisations/orgFormRules",
    systemActitivies: "organisations/systemActitivies",
    sendOrgNotificationForm: 'orghistory/sendOrgNotificationForm',
    sendOrgNotificationFormRules: 'orghistory/sendOrgNotificationFormRules',
    orgNotificationHistory: 'orghistory/orgNotificationHistory',
    systemActivitiesLoading: "organisations/systemActivitiesLoading",
    systemActivitiesTotal: "organisations/systemActivitiesTotal",
  }),

  beforeMount() {
    this.$store.dispatch( 'organisations/getOrganisation', { id: this.getId(), load: true } )
    this.editOrg()
  },

  methods: {
    colorOrganisationStatus: colorOrganisationStatus,

    sendLeadUpdate(){
      let org_id = this.organisation.id
      this.$store.dispatch('organisations/sendLeadUpdate', org_id)
        .then( ({success, message, errors}) => {
          if(success){
            Swal.fire({
              title: "Success!",
              text: message,
              type: "success"
            })
          }else{
            Swal.fire({
              title: "Failed!",
              text: message,
              type: "warning"
            })
          }
        })
    },
    /**
     * Get the ID from the URL
     */
    getId() {
      return this.$route.params.id
    },

    checkData( value ) {
      return value ? value : ''
    },

    editOrgBtn() {
      this.$router.push({ name: 'admin.organisations.update', params: { id: this.getId(), } })
    },

    manualUpdateChange() {
      if ( this.orgForm.manual_update ) {  // if manual update is enabled
        this.orgForm.notifications = []
        this.orgForm.notificationsSelection = ''

      } else { // if manual update is disabled
        this.orgForm.notifications = ['sms', 'notification', 'both']
        this.orgForm.notificationsSelection = 'both'
      }
    },

    leadEscalationNotification() {
      this.orgForm.notifications = getLeadEscalationNotification(this.orgForm.notificationsSelection)
    },

    showSendOrgNotification() {
      this.sendOrgNotification = !this.orgForm.is_suspended;
      return this.sendOrgNotification
    },

    orgStatusChange() {
      if ( !this.orgForm.org_status ) {
        if ( this.orgForm.has_active_lead ) {
          Swal.fire(
            'Oops!',
            'This Org currently has active leads assigned to it. You will need to reassign these leads to another Org or update the Level /Status of the lead before making this change.',
            'error'
          )
          this.orgForm.org_status = true
        }
      }
    },

    editOrg() {
      const orgId = this.getId();

      if (orgId) {
        this.$store.dispatch("organisations/editOrganisation", orgId);
      }
    },

    saveOrg(formName, next = null) {
      // dispatch form submit
      this.$store.dispatch("organisations/saveOrganisation").then(({ success, message, errors }) => {
        if (success) {
          this.isTouched = false
          this.$store.dispatch( 'organisations/getOrganisation', { id: this.getId(), load: false } )
          Swal.fire({
            title: "Success!",
            text: message,
            type: "success"
          }).then( () => {
            this.dialogVisibleStatus = false
          } )
        }
      })
    },

    formUpdated: function(newV, oldV) {
      if(this.orgForm.id){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    },

    initDialogMessage() {
      this.sendOrgNotificationDialogVisible = true
    },

    destroyDialog() {
      this.sendOrgNotificationDialogVisible = false
    }
  },

  created(){
    this.$watch('orgForm', this.formUpdated, {
      deep: true
    })
  },

}
</script>

<style lang="scss" scoped>
  .detail {
    margin-bottom: unset !important;
  }

  .mb-5 {
    margin-bottom : 4.5rem;
  }

  .mr-3 {
    margin-right: 1rem;;
  }

  .mb-3 {
    margin-bottom: 1rem;
  }

  .py-3 {
    padding-bottom : 1.5rem !important;
  }
</style>
