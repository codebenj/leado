<template>

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

          <el-row :gutter="24" class="text-right">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <label style="font-size:14px; color: #606266" class="d-block">Lead Escalation Notification</label>
              <el-radio-group v-model="orgForm.notificationsSelection" @change="leadEscalationNotification" size="medium" :disabled="orgForm.manual_update">
                <el-radio-button label="both">Both</el-radio-button>
                <el-radio-button label="sms">SMS</el-radio-button>
                <el-radio-button label="email">Email</el-radio-button>
              </el-radio-group>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Organisation Name"
                prop="name"
                :error="
                  orgForm.errors.errors.name
                    ? orgForm.errors.errors.name[0]
                    : ''
                "
              >
                <el-input dusk="org_name" type="text" v-model="orgForm.name"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Organisation Code"
                prop="org_code"
                :error="
                  orgForm.errors.errors.org_code
                    ? orgForm.errors.errors.org_code[0]
                    : ''
                "
              >
                <el-input dusk="org_code" type="text" v-model="orgForm.org_code"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="First Name"
                prop="first_name"
                :error="
                  orgForm.errors.errors.first_name
                    ? orgForm.errors.errors.first_name[0]
                    : ''
                "
              >
                <el-input dusk="org_first_name" type="text" v-model="orgForm.first_name"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Last Name"
                prop="last_name"
                :error="
                  orgForm.errors.errors.last_name
                    ? orgForm.errors.errors.last_name[0]
                    : ''
                "
              >
                <el-input dusk="org_last_name" type="text" v-model="orgForm.last_name"></el-input>
              </el-form-item>
            </el-col>

          </el-row>


          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Mobile Contact(SMS Notifications)"
                prop="contact_number"
                :error="
                  orgForm.errors.errors.contact_number
                    ? orgForm.errors.errors.contact_number[0]
                    : ''
                "
              >
                <!-- <el-input type="text" v-model="orgForm.contact_number"></el-input> -->
                <vue-tel-input
                  id="contact_number"
                  v-model="orgForm.contact_number"
                  :value="orgForm.contact_number"
                  v-bind:class="{ required: !isRequired }"
                  @blur="blur"
                  @input="input"
                />
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Landline Contact"
                prop="landline_contact"
                :error="
                  orgForm.errors.errors.landline_contact
                    ? orgForm.errors.errors.landline_contact[0]
                    : ''
                "
              >
                <vue-tel-input
                  id="landline_contact"
                  v-model="orgForm.landline_contact"
                  :value="orgForm.landline_contact"
                />
              </el-form-item>
            </el-col>
          </el-row>


          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Email"
                prop="email"
                :error="
                  orgForm.errors.errors.email
                    ? orgForm.errors.errors.email[0]
                    : ''
                "
              >
                <el-input dusk="org_email" type="email" v-model="orgForm.email"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Password"
                prop="password"
                :error="
                  orgForm.errors.errors.password
                    ? orgForm.errors.errors.password[0]
                    : ''
                "
              >
                <el-input
                  dusk="org_password"
                  type="password"
                  v-model="orgForm.password"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="Suburb"
                prop="suburb"
                :error="
                  orgForm.errors.errors.suburb
                    ? orgForm.errors.errors.suburb[0]
                    : ''
                "
              >
                <el-input dusk="org_suburb" type="text" v-model="orgForm.suburb"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  orgForm.errors.errors.postcode
                    ? orgForm.errors.errors.postcode[0]
                    : ''
                "
              >
                <el-input dusk="org_postcode" type="text" v-model="orgForm.postcode"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="state">
              <el-form-item
                label="State"
                prop="state"
                :error="
                  orgForm.errors.errors.state
                    ? orgForm.errors.errors.state[0]
                    : ''
                "
              >
                <!-- <el-input type="text" v-model="orgForm.state"></el-input> -->
                <el-select popper-class="state_popper" v-model="orgForm.state" placeholder="Select State">
                <el-option
                  v-for="item in states"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              </el-form-item>
            </el-col>

          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item
              label="Additional Org Details"
              prop="additional_details"
              :error="
                orgForm.errors.errors.additional_details
                  ? orgForm.errors.errors.additional_details[0]
                  : ''
              ">
              <el-input
                type="textarea"
                :autosize="{ minRows: 4 }"
                v-model="orgForm.additional_details"
                maxlength="500"
                show-word-limit
              >
              </el-input>
            </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <!-- <el-form-item> -->
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
                  v-on:click="closeModal"
                >
                  Cancel
                </el-button>
              <!-- </el-form-item> -->
            </el-col>
          </el-row>
        </el-form>
      </el-card>

</template>

<script>

import Section from "~/components/Section";
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import { VueTelInput } from "vue-tel-input";
import { getLeadEscalationNotification } from "~/helpers";
import { DataTables } from "vue-data-tables";
import VueGoogleAutocomplete from "vue-google-autocomplete";
import OrganizationComments from "~/components/OrganizationComments.vue";
import ManualIcon from "~/components/ManualIcon.vue";

export default {
  name: "Organisation",
  layout: "master",
  middleware: "auth",

  props: {
    org_id: {
      type: Number,
      default: 0
    }
  },

  components: {
		DataTables,
    Section,
    VueTelInput,
    VueGoogleAutocomplete,
    OrganizationComments,
    ManualIcon
  },

  data: () => ({
    pageTitle: "Add New Organisation",
    isRequired: true,
    isPhoneNumberValidate: true,
    formatNumber: "",
    isLandlineNumberValidate: true,
    formatNumberLandline: "",
    sendOrgNotification: false,
    isTouched: false,
    isTouchedCounter: 0
  }),

  computed: {
    ...mapGetters({
      countries: "organisations/countries",
      orgForm: "organisations/orgForm",
      orgFormRules: "organisations/orgFormRules",
      states: "leads/statesList",
    }),
  },

  methods: {
    leadEscalationNotification() {
      this.orgForm.notifications = getLeadEscalationNotification(this.orgForm.notificationsSelection)
    },

    closeModal(){
      this.$emit('closeModal')
    },

    blur() {
      this.isRequired = true;
      if (!this.isPhoneNumberValidate) {
        this.isRequired = false;
      }
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
      return this.sendOrgNotification;
    },

    getAddressData(addressData, placeResultData, id) {
      this.orgForm.address_search = placeResultData.formatted_address
        ? placeResultData.formatted_address
        : "";

      this.orgForm.address = `${
        addressData.street_number ? addressData.street_number : ""
      } ${addressData.route ? addressData.route : ""}`;

      this.orgForm.suburb = addressData.locality ? addressData.locality : "";
      this.orgForm.city = addressData.administrative_area_level_2 ? addressData.administrative_area_level_2 : "";
      this.orgForm.state = this.getState(addressData, placeResultData);
      this.orgForm.postcode = addressData.postal_code
        ? addressData.postal_code
        : "";
      this.orgForm.country = addressData.country ? addressData.country : "";
    },

    getState(addressData, placeResultData) {
      const state = placeResultData.address_components.find(
        (address) =>
          address.short_name == addressData.administrative_area_level_1
      );

      return state ? state.long_name : "";
    },

    input(number, isValid, country) {
      if (isValid.isValid) {
        this.formatNumber = isValid.number.e164;
        this.isRequired = true;
        this.isPhoneNumberValidate = true;
        this.$refs["orgForm"].validate();
      } else {
        this.formatNumber = "";
        this.isRequired = false;
        this.isPhoneNumberValidate = false;
      }
    },

    saveOrg(formName, next = null) {
      if (!this.isPhoneNumberValidate) {
        this.orgForm.contact_number = "";
      } else {
        this.orgForm.contact_number = this.formatNumber
          ? this.formatNumber
          : this.orgForm.contact_number;
      }

      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("organisations/saveOrganisation")
            .then(({ success, message, errors }) => {
              if (success) {
                this.isTouched = false
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    if(next == null){
                      this.closeModal()
                    }
                  },
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    fetchStates() {
      this.$store.dispatch("organisations/fetchStates");
    },

    isEdit() {
      return this.$route.params.id ? this.$route.params.id : "";
    },

    editOrg() {
      //const orgId = this.isEdit();
      let orgId = this.org_id

      if (orgId) {
        this.$store.dispatch("organisations/editOrganisation", orgId);
        this.pageTitle = "Edit Organisation";
      }
    },

    getOptionText(index, state) {
      return index == 0 ? "Select State" : state;
    },

    formUpdated: function(newV, oldV) {
      if(this.orgForm.id){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    }
  },

  beforeRouteLeave (to, from, next) {
    if(this.isTouched){

      Swal.fire({
        title: 'Just Checking!',
        text: 'Your changes have not been updated. Would you like to update now?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.value) {
          this.saveOrg('orgForm', next)
          next()
        }else{
          next()
        }
      })

    }else{
      next()
    }
  },

  created(){
    this.$watch('orgForm', this.formUpdated, {
      deep: true
    })
  },

  beforeMount() {
    this.editOrg();
    this.fetchStates();
  },
};
</script>
