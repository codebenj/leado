<template>
    <el-card class="box-card b-none page-header" shadow="never" id="organisation-page" v-if="organisation">
      <fa icon="times" fixed-width class="clickable drawerClose" @click="closeOrganisationProfile" />
      <!-- Profile Sections -->
      <el-row :gutter="24" class="mb-5" style="margin-left: 0 !important; margin-right:0 !important" v-if="organisation.user">
        <!-- Org Details -->
        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
          <h1>Org Overview</h1>
          <h5 class="detail">
            <span class="mr-3">{{ checkData( organisation.name ) }}</span>
            <span><ManualIcon :org="organisation" /></span>
            <span><MainPriorityIcon :priority="organisation.priority" :tooltip="organisation.priority" :displayOnly="true" /></span>
            <span v-if="!organisation.has_postcodes"><PostcodeIcon :displayOnly="true" /></span>
          </h5>
          <p class="detail" v-show="organisation.account_status_type_selection.length > 0">
            <el-tag type="danger" disable-transitions>
              {{ organisation.account_status_type_selection }}
            </el-tag>
          </p>
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
          <el-row>
            <el-col :span="24" align="right" style="padding-bottom:30px">
              <el-button-group id="org-buttons">
                <!-- <el-button @click="editOrgBtn()">Edit</el-button> -->
                <el-button @click="initDialogMessage">Edit</el-button>
                <el-button @click="sendLeadUpdate()" v-if="this.organisation && this.organisation.metadata.manual_update">Send Lead Update</el-button>
                <el-button @click="dialogVisibleStatus = true">Org Settings</el-button>
              </el-button-group>
            </el-col>
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
          </el-row>
        </el-col>
      </el-row>



      <!-- Tabs Section -->
      <el-row :gutter="24" class="mx-0 mt-3 drawer-container" style="margin-left: 0 !important; margin-right:0 !important">
        <!-- Tabs -->
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-tabs type="card">
            <!-- Assigned Leads -->
            <el-tab-pane label="Assigned Leads" v-if="this.orgId">
              <el-row :gutter="24" class="py-3">
                <el-col :span="24" align="center">
                  <AssignedLeads v-bind:org-id="this.orgId" />
                </el-col>
              </el-row>
            </el-tab-pane>

            <!-- Comments Tab -->
            <el-tab-pane label="Comments">
              <el-row :gutter="24" class="py-3">
                <el-col :span="24">
                  <OrganizationComments v-bind:org-id="this.orgId" />
                </el-col>
              </el-row>
            </el-tab-pane>

            <!-- Messages Tab -->
            <el-tab-pane label="Org Notifications">
              <el-row :gutter="24" class="py-3">
                <el-col :span="24">
                  <OrganizationNotificationHistory  v-bind:org-id="this.orgId" />
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
                  <OrganizationPostcodes v-bind:org-id="this.orgId" />
                </el-col>
              </el-row>
            </el-tab-pane>

            <!-- Statistics Tab -->
            <el-tab-pane label="Statistics">
              <el-row :gutter="24" class="py-3">
                <el-col :span="24" align="center">
                  <OrgStatsTable :org_id="this.orgId" :org_ids="[organisation]" :is_org_profile="true"/>
                </el-col>
              </el-row>
            </el-tab-pane>

            <!-- Reassigned Leads -->
            <el-tab-pane label="Historical Leads">
              <el-row :gutter="24" class="py-3">
                <el-col :span="24" align="center">
                  <ReassignedLeads v-bind:org-id="this.orgId" />
                </el-col>
              </el-row>
            </el-tab-pane>

          </el-tabs>
        </el-col>

        <!-- Maps -->
        <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
          <el-row :gutter="24">
            <!-- <el-col :span="24" align="right">
              <p class="detail">
                <span class="mr-3">Account Status:</span>
                <el-tag
                  :type="organisation.org_status === 1 ? 'success' : 'danger'"
                  disable-transitions>
                  {{ organisation.org_status === 1 ? "Active" : "Inactive" }}
                </el-tag>
              </p>
              <p class="detail">Account Created: {{ organisation.created_at | moment("DD/MM/YYYY") }}</p>
            </el-col> -->

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

      <template>
        <el-dialog
          v-dialogDrag
          ref="dialog__wrapper"
          :visible.sync="dialogVisibleStatus"
          :show-close="true"
          append-to-body
          width="35%"
          custom-class="organisation-status"
          >
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
                <h1>Org Settings</h1>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <div class="el-form-item">
                    <label class="filter-labels d-block"> Account Status </label>
                    <p class="description">This setting controls an Orgs Account Status. Once set to Inactive, the Org cannot access their Traleado account at all.</p>
                    <el-switch
                      v-model="orgForm.org_status"
                      @change="orgStatusChange"
                      active-text="Active"
                      inactive-text="Inactive"
                      active-color="#4CAF50"
                      inactive-color="#F44336"
                      >
                    </el-switch>
                  </div>

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> Escalation </label>
                    <p class="description">This setting controls the Orgs Escalation. If the Org does not use Traleado - set to Manual.</p>
                    <el-switch
                      v-model="orgForm.manual_update"
                      @change="manualUpdateChange"
                      active-text="Manual"
                      inactive-text="Auto">
                    </el-switch>
                    <ManualIcon :displayOnly="true" />

                    <el-radio-group v-model="orgForm.notificationsSelection" v-show="false" @change="leadEscalationNotification" size="medium" :disabled="orgForm.manual_update">
                      <el-radio-button label="both">Both</el-radio-button>
                      <el-radio-button label="sms">SMS</el-radio-button>
                      <el-radio-button label="notification">Email</el-radio-button>
                    </el-radio-group>
                  </div>

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> Priority </label>
                    <p class="description">Use this setting to assign a Priority to the Org. This is usually done based on performance.</p>

                    <el-select v-model="orgForm.priority" placeholder="Select" style="width: 80%">
                      <el-option
                        v-for="item in priorityList"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                        <span style="float: left">{{ item.label }}</span>
                      </el-option>
                    </el-select>
                    <MainPriorityIcon :priority="orgForm.priority" :tooltip="orgForm.priority" :displayOnly="true" />
                  </div>

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> Price </label>
                    <p class="description">Use this setting to assign a Price category to the Organisation.</p>
                    <el-input placeholder="Enter Price" v-model="orgForm.pricing" maxlength="50" show-word-limit style="width: 80%"></el-input>
                  </div>

                </el-col>

                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                  <!-- <div class="el-form-item">
                    <label class="filter-labels d-block"> Account Status </label>
                    <el-switch
                      v-model="orgForm.is_suspended"
                      @change="showSendOrgNotification"
                      active-text="Suspended"
                      inactive-text="Unsuspended">
                    </el-switch>
                  </div>

                  <div class="el-form-item" v-if="!showSendOrgNotification()">
                    <el-radio-group v-model="orgForm.suspension_type">
                      <el-radio label="On Hold/Too Busy">On Hold/Too Busy</el-radio>
                      <el-radio label="Leave">Leave</el-radio>
                      <el-radio label="System">System</el-radio>
                    </el-radio-group>
                  </div>

                  <div class="el-form-item" v-if="sendOrgNotification">
                    <el-checkbox v-model="orgForm.low_priority">Low Priority</el-checkbox><br>
                    <el-checkbox v-model="orgForm.send_org_notification">Send Org SMS and Email Notification</el-checkbox>
                  </div> -->

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> New Leads </label>
                    <label class="filter-labels d-block"> Org </label>
                    <p class="description">This setting is controlled by the Org. The two settings below override this setting.</p>
                    <div v-if="organisation.is_available == 0" class="not-available">
                      <p style="margin-bottom: 0px">On Hold <span @click="updateOrganisationStatus()" class="update">UPDATE</span></p>
                      <p class="description" style="font-size: 12px">Org requested On Hold Until {{ organisation.available_when | moment("DD/MM/YYYY")}}. Reason: {{ organisation.reason }}</p>
                    </div>
                    <div v-else class="available">
                      <p>Available <span @click="updateOrganisationStatus()" class="update">UPDATE</span></p>
                    </div>
                  </div>

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> System </label>
                    <p class="description">Traleado will automatically update this setting based on the status of the Orgs leads.</p>
                    <p v-if="organisation.system_status == 'On Hold'" class='not-available'>{{ organisation.system_status }}</p>
                    <p v-else class="available">{{ organisation.system_status }}</p>
                  </div>

                  <div class="el-form-item">
                    <label class="filter-labels d-block"> Admin </label>
                    <p class="description">As the Admin you can control this setting. This will override the two settings above.</p>

                    <el-switch
                      v-model="orgForm.on_hold"
                      active-text="On"
                      inactive-text="On Hold"
                      active-color="#4CAF50"
                      inactive-color="#F44336">
                    </el-switch>

                  </div>

                  <div class="el-form-item">
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

                    <!-- <el-button
                      type="danger"
                      @click="dialogVisibleStatus = false"
                    >
                      Cancel
                    </el-button> -->
                  </div>


                </el-col>


              </el-row>


            </el-form>
          </el-card>




          <el-dialog
            :visible.sync="organisationStatusDialogVisible"
            width="30%"
            v-dialogDrag
            ref="dialog__wrapper"
            :show-close="true"
            append-to-body
            :before-close="organisationStatusDialogHandleClose"
            custom-class="organisation-status">

            <OrganisationStatus :orgId="organisation.id" @closeMe="organisationStatusDialogHandleClose" />

          </el-dialog>


        </el-dialog>
      </template>

      <el-dialog
        title="Organisation Details"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible.sync="sendOrgNotificationDialogVisible"
        :show-close="false"
        width="50%"
        append-to-body
        >
        <OrganisationForm :org_id="this.orgId" @closeModal="destroyDialog" />
      </el-dialog>

      <template>
        <el-dialog v-dialogDrag ref="dialog__wrapper" width="30%" title="Add Organisation Comment" :visible.sync="addOrgCommentDialogVisible"
        :show-close="false"
        append-to-body>
          <el-form :model="orgCommentForm" status-icon :rules="orgCommentRules" label-position="top"  ref="orgCommentForm" label-width="120px">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Comment" prop="comment" :error="orgCommentForm.errors.errors.reason ? orgCommentForm.errors.errors.reason[0] : ''">
                  <el-input type="textarea" :autosize="{ minRows: 4 }" placeholder="Enter your comment..." v-model="orgCommentForm.comment" maxlength="500" show-word-limit></el-input>
                </el-form-item>
              </el-col>

              <el-col :span="24">
                <el-form-item class="fl-right">
                  <el-button type="primary" :loading="loadingSentComment" @click="add('orgCommentForm')">Submit</el-button>
                  <el-button type="danger" @click="addCommentClose()">Cancel</el-button>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-dialog>
      </template>

      <!-- <template v-if="historyDrawerNested">
        <el-drawer
          ref="drawerAdminNexted"
          :visible.sync="historyDrawerNested"
          :with-header="false"
          :destroy-on-close="true"
          size="60%"
          append-to-body
          :before-close="beforeClose"
        >
        <DrawerAdmin :id="historyDrawerNestedId" />
      </el-drawer>
      </template> -->

    </el-card>
  <!-- </el-row> -->
</template>

<script>
import { mapGetters } from 'vuex'
import LeadComments from "~/components/LeadComments.vue";
import AssignedLeads from "~/components/organisations/AssignedLeads.vue";
import ReassignedLeads from "~/components/organisations/ReassignedLeads.vue";
import OrganizationComments from "~/components/OrganizationComments.vue";
import OrganisationForm from "~/components/OrganisationForm.vue";
import OrganizationPostcodes from "~/components/OrganizationPostcodes.vue";
import PriorityLow from "~/components/priorities/Low.vue";
import PriorityMedium from "~/components/priorities/Medium.vue";
import PriorityHigh from "~/components/priorities/High.vue";
import OrganizationNotificationHistory from "~/components/OrganizationNotificationHistory.vue";
import ManualIcon from "~/components/ManualIcon.vue";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import { getLeadEscalationNotification, colorOrganisationStatus } from "~/helpers";
import Swal from 'sweetalert2'
import { DataTablesServer } from "vue-data-tables";
import SendOrgNotificationForm from "~/pages/admin/leads/sendorgnotification";
import OrganisationStatus from '~/components/OrganisationStatus'
import OrgStatsTable from '~/components/OrgStatsTable'
import DrawerAdmin from "~/components/DrawerAdmin"

export default {
  props: {
    orgId: Number
  },

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
    OrganisationForm,
    PriorityLow, PriorityMedium, PriorityHigh,
    OrganisationStatus,
    MainPriorityIcon,
    ReassignedLeads,
    OrgStatsTable,
    DrawerAdmin
  },

  data: () => ({
    organisationStatusDialogVisible: false,
    orgRating: 3.5,
    dialogVisibleStatus: false,
    sendOrgNotification: false,
    isTouched: false,
    isTouchedCounter: 0,
    sendOrgNotificationDialogVisible: false,
    priority: '',
    pricing: '',
    priorityList: [
      {
        value: 'None',
        label: 'None'
      },
      {
        value: 'Low',
        label: 'Low'
      },
      {
        value: 'Medium',
        label: 'Medium'
      },
      {
        value: 'High',
        label: 'High'
      },
    ]
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
    orgCommentForm: 'organisations/orgCommentForm',
    orgCommentRules: 'organisations/orgCommentRules',
    addOrgCommentDialogVisible: "organisations/addOrgCommentDialogVisible",
    loadingSentComment: "organisations/loadingSentComment",
    historyDrawerNested: 'leadhistory/historyDrawerNested',
    historyDrawerNestedId: 'leadhistory/historyDrawerNestedId'
  }),

  beforeMount() {
    this.$store.dispatch( 'organisations/getOrganisation', { id: this.getId(), load: true } )
    this.editOrg()
  },

  methods: {
    colorOrganisationStatus: colorOrganisationStatus,

    addComment() {
			this.$store.dispatch("organisations/setDialog", { close: true, form: "add_organisation_comment" });
		},

		addCommentClose() {
			this.$store.dispatch("organisations/setDialog", { close: false, form: "add_organisation_comment" });
		},

    closeDrawer(id) {
      //Cookies.remove("lead_id")
      //Bus.$emit("reload-assigned", id)
      //this.$store.dispatch("leadhistory/closeLeadOverview")
      //this.$refs.drawerAdmin.closeDrawer()
      console.log('index.closeDrawer')
    },

    beforeClose(done){
      //Cookies.remove("lead_id")
      //Bus.$emit("reload-assigned", id)
      //this.$refs.drawerAdmin.closeDrawer()
      //this.$store.dispatch("leadhistory/closeLeadOverview")
    },

    organisationStatusDialogHandleClose(){
      this.organisationStatusDialogVisible = false
    },

    add(formName) {
			this.$refs[formName].validate((valid) => {
				if(valid){
					this.$store.dispatch('organisations/addOrgComment').then(({ msg, data, success }) => {
						if(success){
							Swal.fire({
								title: 'Success',
								text: msg,
								type: 'success'
							})
						}
						else{
							return false;
						}
					})
				}
			})
		},

    updateOrganisationStatus(){
      this.$store.dispatch('organisations/getOrganisation', {id:this.getId(), load:true })
      this.organisationStatusDialogVisible = true
    },

    closeOrganisationProfile() {
      this.$emit('closeOrganisationProfile')
    },

    sendLeadUpdate(){
      let org_id = this.orgId

      Swal.fire({
        title: "Please Confirm",
        text: "You are about to send an outgoing email to this Organisation requesting a status update on Unresolved Leads.",
        type: "warning",
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Send it!",
        confirmButtonColor: "#3085d6",
        reverseButtons: true
      }).then(result => {
        if (result.value) {
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
        }
      });
    },
    /**
     * Get the ID from the URL
     */
    getId() {
      //return this.$route.params.id
      return this.orgId
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

<style scoped>
  .el-switch__label .el-switch__label--left .is-active{
    color: red !important;
  }
  .organisation-status{
    border-radius: 25px;
    font-family: "SF UI Display Light";
    color: #303133;
  }
  .el-dialog{
    border-radius: 25px !important;
  }
  .el-dialog__header{
    display: none !important;
  }
  .el-dialog__body{
    padding-top:0px !important;
  }
  .el-card__body{
    padding-top:0px !important;
  }
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

  .description{
    font-family: "SF UI Display Light";
    font-style: normal;
    font-weight: normal;
    font-size: 12px;
    line-height: 16px;
    margin-bottom: 10px;
  }
  .update{
    font-family: "SF UI Display Light";
    font-style: normal;
    font-weight: normal;
    color: #409EFF;
    margin-left: 20px;
    cursor: pointer;
    font-size: 14px;
    line-height: 16px;
  }
  .not-available{
    color: #F44336;
  }
  .available{
    color: #4CAF50;
  }

  .filter-labels, .d-block{
    font-style: normal;
    font-weight: bold;
    font-size: 14px;
    line-height: 14px;
  }

</style>
