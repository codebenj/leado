<template>
    <el-form :model="reassignForm" status-icon :rules="reassignFormRules" label-position="top"  ref="reassignForm" label-width="120px">
       <el-row :gutter="24">
         <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="reasons">
                  <el-form-item style="margin-bottom: 10px;" label="Reason" prop="reason" :error="reassignForm.errors.errors.reason ? reassignForm.errors.errors.reason[0] : ''">
                      <el-select
                          popper-class="reasons_popper"
                          v-model="reassignForm.reasonIndex"
                          placeholder="Select a Reason">
                          <el-option v-for="(reason, index) in reasons" :key="index" :value="index" :label="reason.reason">{{ reason.reason }}</el-option>
                      </el-select>
                  </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" style="margin-bottom: 10px;" class="type_filter">
                <label>Filter Type</label>
                <el-select
                  popper-class="type_filter_popper"
                  v-model="filterType"
                  placeholder="Select filter type"
                  @change="filterOrgs()"
                >
                  <el-option
                    v-for="(fType, index) in filterTypes"
                    :key="index"
                    :label="fType"
                    :value="fType"
                  >
                  </el-option>
                </el-select>
              </el-col>

              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="organisation">
                <label>Filter by Organisation</label>
                <el-select v-model="reassignForm.new_organization_id"
                  filterable
                  remote
                  clearable
                  placeholder="Select or Search"
                  popper-class="select-org-search">
                    <span v-for="organisation in filteredOrgs" :key="organisation.id">
                    <el-option
                      v-if="displayOrganization(organisation)"
                      :key="organisation.id"
                      :label="organisation.name"
                      :value="organisation.id"
                    >
                      <span>
                        {{ organisation.name }}
                        <ManualIcon :org="organisation" />
                        <MainPriorityIcon :priority="organisation.priority" :tooltip="organisation.priority" :displayOnly="true" />
                      </span>
                      <span v-if="organisation && !organisation.has_postcodes">
                        <PostcodeIcon :displayOnly="false" />
                      </span>
                      <span v-if="organisation.is_suspended == '1'" style="float: right; color: #DE1F21; font-size: 11px">{{ organisation.account_status_type_selection }}</span>
                    </el-option>
                    </span>
                  </el-select>

                  <!-- <el-form-item label="Organisation" v-if="organisations" prop="new_organization_id" :error="reassignForm.errors.errors.new_organization_id ? reassignForm.errors.errors.new_organization_id[0] : ''">
                      <el-select v-model="reassignForm.new_organization_id" placeholder="Select an organisation">
                          <span v-for="organisation in filteredOrgs" :key="organisation.id">
                              <el-option v-if="displayOrganization(organisation)" :value="organisation.id" :label="organisation.name" :disabled="organisation.is_suspended == 1">
                                <span>
                                  {{ organisation.name }}
                                  <ManualIcon :org="organisation" />
                                </span>
                              </el-option>
                          </span>
                      </el-select>
                  </el-form-item> -->

              </el-col>

            </el-row>
         </el-col>
         <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"  v-if="isShowOrgStatsTable">
           <OrgStatsTable :org_ids.sync="filter_organizations" />
         </el-col>
          <!-- <el-col style="margin-top: 5px;" :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <label>Send notifications to the newly assigned Organisation.</label>
              <el-checkbox style="margin-left: 2px;" v-model="reassignForm.send_email" :checked="reassignForm.send_email ? 'checked' : ''">Email</el-checkbox>
              <el-checkbox style="margin-left: 2px;" v-model="reassignForm.send_sms" :checked="reassignForm.send_sms ? 'checked' : ''">SMS</el-checkbox>
          </el-col> -->

          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"  style="margin-top: 5px;">
              <el-form-item class="fl-right">
                  <el-button dusk="reassigned-submit" type="primary" :disabled="!checkFields()" @click="outgoingNotification()">Continue</el-button>
                  <el-button type="danger" @click="closeDialog()">
                          Cancel
                  </el-button>
              </el-form-item>
          </el-col>
       </el-row>

      <el-dialog
        :visible="showOutgoingNotifications"
        v-dialogDrag
        ref="dialog__wrapper"
        :show-close="false"
        append-to-body
        width="30%"
      >
        <OutgoingNotificationModal
          :form="reassignForm"
          :org_data="getOrgDetails()"
          :type="'reassign_lead'"
          :loading="loading"
          @saveLead="reassignLead('reassignForm')"
          :key="JSON.stringify(getOrgDetails())"
        />
      </el-dialog>
    </el-form>
</template>
<script>
import Swal from 'sweetalert2'
import { mapGetters } from 'vuex';
import ManualIcon from "~/components/ManualIcon.vue";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import OrgStatsTable from '~/components/OrgStatsTable'
import { Bus } from '~/app'
import OutgoingNotificationModal from '~/components/notifications/OutgoingNotificationModal'

export default {
	name: 'ReassignForm',
	data: () => ({
    payload: {},
    filterTypes: ["None", "Postcode", "State"],
    filterType: "",
    filteredOrgs: [],
    isShowOrgStatsTable: false
	}),
  components: {
    ManualIcon,
    PostcodeIcon,
    MainPriorityIcon,
    OrgStatsTable,
    OutgoingNotificationModal
  },
	computed: {
    ...mapGetters({
      // organisations: 'leadhistory/organizations',
      organisations: "leads/organisations",
      histories: 'leadhistory/histories',
      reasons: 'leadhistory/reasons',
      loading: 'leadhistory/loading',
      reassignForm: 'leadhistory/reassignForm',
      reassignFormRules: 'leadhistory/reassignFormRules',
      reassignDialogVisible: 'leadhistory/reassignDialogVisible',
      leadForm: 'leads/leadForm',
      postcodes: "organisations/postcodes",
      lead_id: 'leadhistory/lead_id',
      active_escalation: 'leadhistory/active_escalation',
      customer: 'leadhistory/customer',
      filter_organizations: "organisations/filter_organizations",
      showOutgoingNotifications: "notifications/showOutgoingNotifications"
    }),

    showOption(organisation){
      return this.histories[0]  && this.histories[0].organisation.id !== organisation.id
    },
  },
	methods: {
    checkFields() {
      let formVal = (this.reassignForm.new_organization_id != '' && this.reassignForm.reasonIndex != null) && this.filterType !=''
      return formVal
    },
    reassignLead(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.payload.lead_id = this.lead_id
          this.payload.organisation_id = this.active_escalation.organisation_id ?? 0
          // dispatch form submit
          this.$store.dispatch('leadhistory/reassignLead', this.payload).then(({ data, success, message, errors }) => {
            if ( success ) {
              this.$store.dispatch('leadhistory/getLeadHistory', data.lead_id)

              Swal.fire({
                title: 'Success!',
                text: message,
                type: 'success',
              }).then(() => {
                 this.closeOutgoingNotifications();
              });
            }
          })
        }
        else {
          return false;
        }
      });
    },

    closeDialog() {
      this.$store.dispatch('leadhistory/setDialog', {
        close: false,
        form: 'reassign'
      })
    },

    filterOrgs() {
      const field = this.filterType.toLowerCase();
      this.reassignForm.new_organization_id = ""
      this.filteredOrgs = [];
      if(field == 'state'){
        this.filteredOrgs = this.organisations.filter(
          org => org.address[field] === this.active_escalation.lead.customer.address[field]
        )

        this.isShowOrgStatsTable = false;
      }else if(field == 'postcode'){
        this.filteredOrgs = this.filter_organizations

        this.isShowOrgStatsTable = true
      } else if(field == 'none'){
        this.filteredOrgs = this.organisations;

        this.isShowOrgStatsTable = false;
      }

      Bus.$emit( 'reload-org-stat', this.filteredOrgs )
    },

    async generatePostcodeOrganization(){
      let postcodes = this.postcodes.filter(
        (postcodes_) => postcodes_.postcode === this.leadForm['postcode']
      )

      let organisation_ids = this.extractValue(postcodes, 'organisation_id')
      await this.$store.dispatch("organisations/fetchFilterOrganizations", organisation_ids)
    },

    extractValue(arr, prop) {
      let extractedValue = arr.map(item => item[prop]);
      return extractedValue;
    },

    displayOrganization(organization){
      let alreadyAssigned = false
      let previous_organization = this.histories.filter(obj => obj.organisation_id == organization.id)

      if(organization.org_status == 0){
        return false
      }

      if(previous_organization.length > 0){
        alreadyAssigned = true
      }

      if(!alreadyAssigned){
        return true
      }

      return false
    },


    getOrgDetails() {
      let org_id = this.reassignForm.new_organization_id

      const selectedOrg = this.organisations.find(
        (org) => org.id === org_id
      );

      if (selectedOrg) {
        return {
          name: selectedOrg.name,
          email: selectedOrg.user.email,
          code: selectedOrg.org_code,
          number: selectedOrg.contact_number,
          manual_update: selectedOrg.metadata.manual_update,
        }
      }
    },

    outgoingNotification() {
      this.$store.dispatch("notifications/setOutgoingModalState", {
          outgoingModalState: true
        });
    },

    closeOutgoingNotifications() {
      this.$store.dispatch("notifications/setOutgoingModalState", {
          outgoingModalState: false
        });
    },
  },


  beforeMount(){

    this.$store.dispatch("organisations/fetchOrganisationPostcodes");

    this.generatePostcodeOrganization();
    this.filteredOrgs = this.filter_organizations
  }
}
</script>

<style>
.select-org-search .el-select-dropdown__wrap {
  max-height: 250px !important;
}
</style>
