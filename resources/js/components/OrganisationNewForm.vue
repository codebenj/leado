<template>
  <Section dusk="organisationForm" className="organisation-form">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          style="margin-top: -30px;"
          class="demo-ruleForm"
          label-position="top"
          label-width="120px"
          :model="orgForm"
          :rules="orgFormRules"
          status-icon
          ref="orgForm"
        >
          <el-row :gutter="20">

            <el-col :xl="{span: 5, offset: 9}" :lg="{span: 6, offset: 5}">
              <el-form-item
                label="Org Status"
                prop="org_status"
                :error="
                  orgForm.errors.errors.org_status
                    ? orgForm.errors.errors.org_status[0]
                    : ''
                "
              >
                <el-switch
                  v-model="orgForm.org_status"
                  active-text="Active"
                  inactive-text="Inactive">
                </el-switch>
              </el-form-item>
            </el-col>

            <el-col :xl="5" :lg="6">
              <el-form-item
                label="Org Status"
                prop="manual_update"
                :error="
                  orgForm.errors.errors.manual_update
                    ? orgForm.errors.errors.manual_update[0]
                    : ''
                "
              >
                <el-switch
                  v-model="orgForm.manual_update"
                  @change="manualUpdateChange"
                  active-text="Manual"
                  inactive-text="Auto">
                </el-switch>
                <ManualIcon :displayOnly="true" />
              </el-form-item>
            </el-col>

            <el-col :xl="5" :lg="7">
              <el-form-item
                label="Lead Escalation Notification"
                prop="notificationsSelection"
                :error="
                  orgForm.errors.errors.notificationsSelection
                    ? orgForm.errors.errors.notificationsSelection[0]
                    : ''
                "
              >
              <el-radio-group v-model="orgForm.notificationsSelection" @change="leadEscalationNotification" size="medium" :disabled="orgForm.manual_update">
                <el-radio-button label="both">Both</el-radio-button>
                <el-radio-button label="sms">SMS</el-radio-button>
                <el-radio-button label="notification">Email</el-radio-button>
              </el-radio-group>
              </el-form-item>
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

          <el-row :gutter="24">

            <!-- <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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
            </el-col> -->

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Mobile Contact(SMS Notifications)"
                prop="contact_number"
                :error="
                  orgForm.errors.errors.contact_number
                    ? orgForm.errors.errors.contact_number[0]
                    : ''
                "
              >
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

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
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

          <el-row :gutter="20" class="d-none">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Address"
                prop="address"
                :error="
                  orgForm.errors.errors.address
                    ? orgForm.errors.errors.address[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orgForm.address"></el-input>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item
                label="City"
                prop="city"
                :error="
                  orgForm.errors.errors.city
                    ? orgForm.errors.errors.city[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orgForm.city"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="24" class="d-none">
            <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
              <el-form-item
                label="Suburb"
                prop="suburb"
                :error="
                  orgForm.errors.errors.suburb
                    ? orgForm.errors.errors.suburb[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orgForm.suburb"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  orgForm.errors.errors.postcode
                    ? orgForm.errors.errors.postcode[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orgForm.postcode"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="State"
                prop="state"
                :error="
                  orgForm.errors.errors.state
                    ? orgForm.errors.errors.state[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orgForm.state"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
              <el-form-item label="Country" prop="country">
                <el-input type="text" v-model="orgForm.country"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="6" :lg="12" :xl="12">
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

            <!-- Pricing -->
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="Pricing"
                prop="pricing"
                :error="
                  orgForm.errors.errors.pricing
                    ? orgForm.errors.errors.pricing[0]
                    : ''
                "
              >
                <el-input
                  v-model="orgForm.pricing"
                  maxlength="50"
                  show-word-limit
                  clearable>
                </el-input>
              </el-form-item>
            </el-col>

            <!-- Priority -->
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="Priority"
                prop="priority"
                :error="
                  orgForm.errors.errors.priority
                    ? orgForm.errors.errors.priority[0]
                    : ''
                "
              >
                <el-select v-model="orgForm.priority" clearable placeholder="Select option" style="width: 80%">
                  <el-option
                    v-for="item in priorityList"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    <span style="float: left">{{ item.label }}</span>
                  </el-option>
                </el-select>
                <MainPriorityIcon :priority="orgForm.priority" :tooltip="orgForm.priority" :displayOnly="true" />

              </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
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
                  @click="closeOrganisationNewForm()"
                >
                  Cancel
                </el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-card>
    </template>
  </Section>
</template>

<script>
import ManualIcon from "~/components/ManualIcon.vue"
import Section from "~/components/Section"
import Swal from "sweetalert2"
import { mapGetters } from "vuex"
import { VueTelInput } from "vue-tel-input"
import MainPriorityIcon from "~/components/priorities/Main.vue"

export default {
  components: {
    Section,
    VueTelInput,
    ManualIcon,
    MainPriorityIcon
  },

  computed: {
    ...mapGetters({
      countries: "organisations/countries",
      orgForm: "organisations/orgForm",
      orgFormRules: "organisations/orgFormRules",
      states: "leads/statesList",
    }),
  },

  data: () => ({
    pageTitle: "Add New Organisation",
    isRequired: true,
    isPhoneNumberValidate: true,
    formatNumber: "",
    sendOrgNotification: false,
    isTouched: false,
    isTouchedCounter: 0,
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

  methods: {
    leadEscalationNotification() {
      //this.orgForm.notifications = getLeadEscalationNotification(this.orgForm.notificationsSelection)
    },

    closeOrganisationNewForm(){
      this.$emit('closeOrganisationNewForm')
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
                      this.closeOrganisationNewForm()
                    }
                  },
                });
              }else{
                Swal.fire({
                  title: "Oops!",
                  text: message,
                  type: "error"
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    showSendOrgNotification() {
      this.sendOrgNotification = !this.orgForm.is_suspended;
      return this.sendOrgNotification;
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

    blur() {
      this.isRequired = true;
      if (!this.isPhoneNumberValidate) {
        this.isRequired = false;
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

    formUpdated: function(newV, oldV) {
      if(this.orgForm.id){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    }
  },

  created(){
    this.$watch('orgForm', this.formUpdated, {
      deep: true
    })
  },

  beforeMount(){
  },

  mounted(){
    this.orgFormRules.pricing[0].required = false
    this.orgFormRules.priority[0].required = false

    console.log(this.orgFormRules)
  },
}
</script>

<style scoped>
.organisation-form .el-card .el-card__body{
  padding: 0px;
}
.el-dialog__body{
  padding: 0px 20px !important;
}
.el-dialog__header{
  padding: 0px 20px 10px !important;
}
.el-form-item{
  margin-bottom: 10px;
}
</style>>
