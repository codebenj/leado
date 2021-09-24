<template>
  <div>
    <h5 class="text-center">Update Lead Status</h5>
    <p class="text-center">This will cost you $0 to accept the lead.</p>

    <el-row :gutter="20" v-if="!showDeclineForm">
      <el-form
        :model="acceptForm"
        status-icon
        label-position="top"
        ref="acceptForm"
        label-width="120px"
      >
        <el-col :span="12">
          <el-button
            dusk="accepted-lead"
            type="primary"
            class="w-100"
            :loading="loading"
            @click="updateEscalation('acceptForm')"
          >
            Accept
          </el-button>
        </el-col>

        <el-col :span="12">
          <el-button
            dusk="declined-lead"
            type="danger"
            class="w-100"
            @click="showDecline()"
            :loading="loading"
          >
            Decline
          </el-button>
        </el-col>
      </el-form>
    </el-row>

    <el-row :gutter="20" v-if="showDeclineForm">
      <el-form
        :model="declineForm"
        :rules="declineFormRules"
        status-icon
        label-position="top"
        ref="declineForm"
        label-width="120px"
      >
        <el-col :span="24" class="decline_reason">
          <el-form-item label="Reason" prop="reason">
            <el-select
              popper-class="decline_reason_popper"
              v-model="declineForm.reason"
              placeholder="Select Reason"
              v-on:change="changeReason"
            >
              <el-option
                v-for="(declineReason, index) in declineReasons"
                :key="index"
                :label="declineReason"
                :value="declineReason"
                >{{ declineReason }}</el-option
              >
            </el-select>
          </el-form-item>
        </el-col>

        <el-col :span="24" v-if="declineForm.reason == 'Other'">
          <el-form-item label="Please indicate your reason." prop="indicate_reason">
            <el-input
              dusk="indicate_reason"
              type="textarea"
              :autosize="{ minRows: 4 }"
              placeholder="Enter a message"
              v-model="declineForm.indicate_reason"
              maxlength="500"
              show-word-limit
            >
            </el-input>
          </el-form-item>
        </el-col>

        <el-col :span="12">
          <el-button
            type="primary"
            class="w-100"
            @click="showDecline()"
            :loading="loading"
            >Cancel</el-button
          >
        </el-col>
        <el-col :span="12">
          <el-button
            dusk="declined-lead-save"
            type="danger"
            class="w-100"
            @click="updateEscalation('declineForm')"
            :loading="loading"
            >Decline</el-button
          >
        </el-col>
      </el-form>
    </el-row>
  </div>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "AcceptOrDecline",
  data: () => ({
    name: "Accept Or Decline",
    showDeclineForm: false
  }),

  computed: mapGetters({
    acceptForm: "leadescalation/acceptForm",
    declineForm: "leadescalation/declineForm",
    declineFormRules: "leadescalation/declineFormRules",
    declineReasons: "leadescalation/declineReasons",
    loading: "leadescalation/loading"
  }),

  methods: {
    showDecline() {
      this.showDeclineForm = !this.showDeclineForm;
    },

    changeReason(reason) {
      //   if(reason == 'Other'){
      //     this.declineFormRules.comments[0].required = true
      //   }else{
      //     this.declineFormRules.comments[0].required = false
      //   }
      this.declineFormRules.comments[0].required = false;
    },

    updateEscalation(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.$store
            .dispatch("leadescalation/updateLeadEscalation", formName)
            .then(({ data, errors, originalLeadId }) => {
              const { success, message } = data;
              if (success) {
                let hasDeclined = formName == "declineForm" ? true : false;
                this.$store.dispatch("leadescalation/showConfirmationModal", {
                  extended: false,
                  hasDeclined: hasDeclined
                });
                this.$store.dispatch(
                  "leadhistory/getLeadHistory",
                  originalLeadId
                );
                this.$store.dispatch("leads/updateLeads", {
                  lead: data.data,
                  originalLeadId
                });
                this.$store.dispatch("leads/fetchLeads", []);
              }
            });
        } else {
          return false;
        }
      });
    }
  }
};
</script>
