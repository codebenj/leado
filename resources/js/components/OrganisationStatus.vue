<template>
  <el-card class="box-card b-none" shadow="never" v-if="availabilityForm">
    <el-form
      :model="availabilityForm"
      status-icon
      label-position="top"
      :rules="availabilityformRules"
      ref="availabilityForm"
      label-width="120px"
    >
      <el-row :gutter="24">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <h1>New Leads</h1>
        </el-col>
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <p>Hello, You may use the settings below to control when you would like to receive New Leads.</p>
        </el-col>

        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-switch
            v-model="availabilityForm.is_available"
            active-text="Available"
            inactive-text="On Hold"
            active-color="#4CAF50"
            inactive-color="#F44336">
          </el-switch>
        </el-col>

      </el-row>

      <el-row :gutter="24" v-if="availabilityForm.is_available">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <p style="padding-top: 20px; padding-bottom:10px">New Leads can be assigned to you!</p>
        </el-col>
      </el-row>

      <el-row :gutter="24" v-else>

          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <p style="padding-top: 20px; padding-bottom:10px">Use the calendar below to determine how long you would like New Leads to be on Hold for.</p>
          </el-col>

          <el-col :span="24">
            <el-form-item
              label=""
              prop="available_when"
              :error="
                availabilityForm.errors.errors.available_when
                  ? availabilityForm.errors.errors.available_when[0]
                  : ''
              "
            >
            <el-date-picker
              :localTime="true"
              class="w-100"
              v-model="availabilityForm.available_when"
              type="date"
              placeholder="Pick a day"
              :editable="false"
              :picker-options="datePickerOptions"
            >
            </el-date-picker>
            </el-form-item>
          </el-col>

          <el-col :span="24">
            <el-form-item
              label="Choose a reason"
              prop="reason"
              :error="
                availabilityForm.errors.errors.reason
                  ? availabilityForm.errors.errors.reason[0]
                  : ''
              "
            >
              <el-radio-group v-model="availabilityForm.reason">
                <el-radio label="Too Busy">Too Busy</el-radio>
                <el-radio label="Leave">Leave</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>

      </el-row>

      <el-row :gutter="24">
        <el-col
          :xs="{ span: 13, offset: 5 }"
          :sm="{ span: 13, offset: 5 }"
          :md="{ span: 14, offset: 5 }"
          :lg="{ span: 11, offset: 7 }"
          :xl="{ span: 9, offset: 8 }"
          class="notice-button">
          <el-form-item>
            <el-button
              class="w-100"
              type="primary"
              @click="updateOrganisationStatus()">Update
            </el-button>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
  </el-card>

</template>

<script>
import Section from "~/components/Section";
import { mapGetters } from "vuex"
import Swal from "sweetalert2"

export default {
  props: {
    orgId: {
      type: Number,
      required: true
    }
  },

  // computed: mapGetters({
  //   availabilityForm: "organisations/orgFormAvailability",
  //   availabilityformRules: "organisations/orgFormAvailabilityRules",
  // }),
  computed:{
    ...mapGetters({
        availabilityForm: "organisations/orgFormAvailability",
        availabilityformRules: "organisations/orgFormAvailabilityRules",
    }),

    datePickerOptions(){
      return {
        disabledDate(date) {
          var today = new Date();
          return (date < today);
        }
      }
    },
  },

  data(){
    return{
      isAvailable: false
    }
  },

  methods: {
    updateOrganisationStatus(){
      this.$refs['availabilityForm'].validate((valid) => {
        if (valid) {
          this.$store
            .dispatch("organisations/saveOrganisationAvailabilityStatus")
            .then(({ success, message, errors }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  // text: message,
                  type: "success",
                  onClose: () => {
                    this.$store.dispatch("auth/fetchUser")
                    this.$store.dispatch('organisations/getOrganisation', { id: this.orgId, load: true })
                    this.closeMe()
                  },
                });
              }
            })
        } else {
          return false
        }
      })
    },

    closeMe(){
      this.$emit('closeMe')
    }
  },

  mounted(){
    this.$store.dispatch('organisations/getOrganisation', { id:this.orgId, load:true } );
    this.availabilityForm.org_id = this.orgId
  },
}
</script>

<style scoped>
body p{
  font-family: "SF UI Display Light";
  /* font-size: 12px;
line-height: 16px; */
}
.el-switch .el-switch__label .el-switch__label--left .is-active span{
  color: #F44336;
}
.el-switch__label.is-active{
  color: #F44336 !important;
}
</style>
