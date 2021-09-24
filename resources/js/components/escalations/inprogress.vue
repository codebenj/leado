<template>
  <el-form
    dusk="inProgressForm"
    :model="inProgressForm"
    :rules="inProgressFormRules"
    status-icon
    label-position="top"
    ref="inProgressForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-form-item
          label="Select one of the following:"
          prop="responses"
          :error="
            inProgressForm.errors.errors.escalation_level ||
            inProgressForm.errors.errors.escalation_status
              ? 'Please select response'
              : ''
          "
        >
          <el-radio
            v-for="(response, index) in inprogressResponses"
            class="in-progress-responses"
            :key="index"
            @change="clearSubFields()"
            v-model="inProgressForm.response"
            :value="response"
            :label="response"
          >
            {{ response }}
          </el-radio>
        </el-form-item>
      </el-col>

      <el-col
        class="lost_reasons"
        :span="24"
        v-if="
          inProgressForm.response &&
            inProgressForm.response == 'This lead is currently Work in Progress'
        "
      >
        <el-form-item label="Select one of the following:" prop="other_reason">
          <el-select
            popper-class="lost_reasons_popper"
            v-model="inProgressForm.other_reason"
            placeholder="Select Reason"
            @change="changeRules"
          >
            <el-option
              v-for="(workInprogress, index) in workInprogressReasons"
              :key="index"
              :label="workInprogress"
              :value="workInprogress"
              >{{ workInprogress }}</el-option
            >
          </el-select>
        </el-form-item>
      </el-col>

      <!-- Other reason && Enquirer went with someone with a different system (not a ski-slope system). -->
      <el-col
        :span="24"
        v-if="
          inProgressForm.other_reason &&
            inProgressForm.other_reason == 'Other' &&
            inProgressForm.response == 'This lead is currently Work in Progress'
        "
      >
        <el-form-item
          label="Please indicate your reason."
          prop="indicate_reason"
        >
          <el-input
            dusk="in-progress-indicate-reason"
            type="textarea"
            :autosize="{ minRows: 4 }"
            v-model="inProgressForm.indicate_reason"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <el-col :span="24" v-if="showProgressPeriodDate">
        <el-form-item
          label="This lead is currently IN PROGRESS, please choose how much more time you need?"
          prop="progress_period_date"
        >
          <el-date-picker
            :default-value="selectedLead.open_date"
            :picker-options="datePickerOptions"
            :localTime="true"
            :editable="false"
            value-format="yyyy-MM-dd"
            class="w-100 pointer"
            v-model="inProgressForm.progress_period_date"
            type="date"
            placeholder="Pick a day"
            @change="checkCommentsRequired()"
          >
          </el-date-picker>
        </el-form-item>

        <el-form-item
          label="What time of the day should we notify you if this lead expires?"
          prop="selected_time"
        >
          <el-time-select
            v-model="inProgressForm.selected_time"
            :picker-options="{
              start: '00:00',
              step: '00:15',
              end: '23:45'
            }"
            class="w-100 pointer"
            default-value="14:00"
            value-format="HH:mm"
            placeholder="Select time"
            :editable="false"
          >
          </el-time-select>
        </el-form-item>
      </el-col>

      <!-- I have contacted Enquirer  and This lead has been LOST -->
      <el-col
        class="lost_reasons"
        :span="24"
        v-if="
          inProgressForm.response &&
            inProgressForm.response == 'This lead has been LOST'
        "
      >
        <el-form-item
          label="What is the main reason that this lead was lost?"
          prop="other_reason"
        >
          <el-select
            popper-class="lost_reasons_popper"
            v-model="inProgressForm.other_reason"
            placeholder="Select Reason"
            @change="changeRules"
          >
            <el-option
              v-for="(lostLeadReason, index) in lostLeadReasons"
              :key="index"
              :label="lostLeadReason"
              :value="lostLeadReason"
              >{{ lostLeadReason }}</el-option
            >
          </el-select>
        </el-form-item>
      </el-col>

      <!-- Other reason && Enquirer went with someone with a different system (not a ski-slope system). -->
      <el-col
        :span="24"
        v-if="
          inProgressForm.other_reason &&
            inProgressForm.other_reason == 'Other' &&
            inProgressForm.response !==
              'This lead is currently Work in Progress'
        "
      >
        <el-form-item
          label="Please indicate your reason."
          prop="indicate_reason"
        >
          <el-input
            dusk="in-progress-indicate-reason"
            type="textarea"
            :autosize="{ minRows: 4 }"
            v-model="inProgressForm.indicate_reason"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <!-- Other reason && Enquirer went with someone with a different system (not a ski-slope system). -->
      <el-col
        :span="24"
        v-if="
          inProgressForm.other_reason &&
            inProgressForm.other_reason ==
              'Enquirer went with someone with a different system (not a ski-slope system).'
        "
      >
        <el-form-item label="What Sytem?" prop="what_system">
          <el-input
            dusk="what-system"
            type="textarea"
            :autosize="{ minRows: 4 }"
            v-model="inProgressForm.what_system"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <!-- I haven't attempted to contacted Enquirer and "Other" reason -->
      <el-col :span="24" v-if="showComment">
        <el-form-item
          :label="commentLabel"
          prop="comments"
          :error="
            inProgressForm.errors.errors.comments
              ? inProgressForm.errors.errors.comments[0]
              : ''
          "
        >
          <el-input
            dusk="extra-feedback"
            type="textarea"
            :autosize="{ minRows: 4 }"
            placeholder="Enter a message"
            v-model="inProgressForm.comments"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <!-- I haven't attempted to contacted Enquirer and This lead has been WON and installed  -->
      <el-col :span="24" v-if="showWon">
        <el-form-item
          label="No of Metres of Gutter Edge:"
          prop="gutter_edge_meters"
          :error="
            inProgressForm.errors.errors.gutter_edge_meters
              ? inProgressForm.errors.errors.gutter_edge_meters[0]
              : ''
          "
        >
          <el-input
            dusk="gutter_edge_meters"
            type="number"
            v-model="inProgressForm.gutter_edge_meters"
          ></el-input>
        </el-form-item>
        <el-form-item
          label="No of Metres of Valley:"
          prop="valley_meters"
          :error="
            inProgressForm.errors.errors.valley_meters
              ? inProgressForm.errors.errors.valley_meters[0]
              : ''
          "
        >
          <el-input
            dusk="valley_meters"
            type="number"
            v-model="inProgressForm.valley_meters"
          ></el-input>
        </el-form-item>
        <el-form-item
          label="Date of Installation:"
          prop="installation_date"
          :error="
            inProgressForm.errors.errors.installation_date
              ? inProgressForm.errors.errors.installation_date[0]
              : ''
          "
        >
          <el-date-picker
            :picker-options="datePickerOptionsWon"
            class="w-100 pointer"
            v-model="inProgressForm.installation_date"
            type="month"
            placeholder="Pick a month"
            :localTime="true"
            :editable="false"
          >
          </el-date-picker>
        </el-form-item>
      </el-col>
    </el-row>
    <el-row :gutter="24">
      <el-col :span="24">
        <el-button
          dusk="in-progress-submit"
          type="primary"
          class="w-100"
          @click="updateEscalation('inProgressForm')"
          :loading="loading"
          >Submit</el-button
        >
      </el-col>
    </el-row>
  </el-form>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "InProgress",
  data: () => ({
    name: "InProgress"
  }),
  computed: {
    ...mapGetters({
      loading: "leadescalation/loading",
      inProgressForm: "leadescalation/inProgressForm",
      inProgressFormRules: "leadescalation/inProgressFormRules",
      lostLeadReasons: "leadescalation/lostLeadReasons",
      inprogressResponses: "leadescalation/inprogressResponses",
      workInprogressReasons: "leadescalation/workInprogressReasons",
      selectedLead: "leadescalation/selectedLead",
      leads: "leads/leads",
      user: "auth/user"
    }),

    showComment() {
      const reasonsWithComments = [
        "This lead is currently Work in Progress",
        "This lead will be installed soon",
        "This lead has been LOST"
      ];

      return reasonsWithComments.includes(this.inProgressForm.response);
    },

    datePickerOptions() {
      const lead = this.selectedLead;

      return {
        disabledDate(date) {
          const min = Date.parse(lead.min_extension);
          const max = new Date(lead.max_extension);
          const firstIpExtCreatedDate = lead.lead_ip_first_extension_created_date
            ? new Date(lead.lead_ip_first_extension_created_date)
            : null;

          if (firstIpExtCreatedDate) {
            const new_max = new Date(
              firstIpExtCreatedDate.setFullYear(
                firstIpExtCreatedDate.getFullYear() + 1
              )
            );

            if (max >= new_max) {
              return !(date >= min && date <= new_max);
            }
          }

          return !(date >= min && date <= max);
        }
      };
    },

    //allow dates - today and previous dates
    datePickerOptionsWon() {
      var today = new Date();

      return {
        disabledDate(date) {
          return !(date < today);
        }
      };
    },

    showWon() {
      return (
        this.inProgressForm.response == "This lead has been WON and installed"
      );
    },

    showProgressPeriodDate() {
      const responses = [
        "This lead is currently Work in Progress",
        "This lead will be installed soon"
      ];

      return responses.includes(this.inProgressForm.response);
    },

    commentLabel() {
      return "Use this comments section to provide additional information you think might be needed:";
      switch (this.inProgressForm.response) {
        case "This lead has been LOST":
          return "Do you have any extra feedback regarding this Lead and Enquirer?";
          break;
        default:
          return "Comments";
          break;
      }
    }
  },
  methods: {
    showPicker(event) {
      event.showPicker();
    },

    clearSubFields() {
      this.inProgressForm.comments = "";
      this.inProgressForm.indicate_reason = "";
      this.inProgressForm.other_reason = "";
      this.inProgressForm.what_system = "";
      this.inProgressForm.progress_period_date = "";
      this.inProgressForm.gutter_edge_meters = "";
      this.inProgressForm.valley_meters = "";

      if (
        this.inProgressForm.response ==
          "This lead is currently Work in Progress" ||
        this.inProgressForm.response == "This lead has been LOST"
      ) {
        this.inProgressFormRules.other_reason[0].required = true;
      } else {
        this.inProgressFormRules.other_reason[0].required = false;
      }

      this.changeRules();

      if (
        this.inProgressForm.errors.errors.escalation_level ||
        this.inProgressForm.errors.errors.escalation_status
      ) {
        this.inProgressForm.errors.errors.escalation_level = false;
        this.inProgressForm.errors.errors.escalation_status = false;
      }
    },

    changeRules() {
      this.$store
        .dispatch("leadescalation/changeRules", {
          formName: "inProgressForm",
          formRuleName: "inProgressFormRules"
        })
        .then(() => this.$refs["inProgressForm"].clearValidate());
    },

    checkCommentsRequired() {
      if (
        this.inProgressForm.progress_period_date ||
        this.inProgressForm.escalation_status ==
          "In Progress - Awaiting Response"
      ) {
        const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
        let date_start = this.selectedLead.progress_period_date
          ? new Date(this.selectedLead.progress_period_date)
          : new Date(this.selectedLead.created_at);
        let diffDays = Math.round(
          Math.abs(
            (date_start - this.inProgressForm.progress_period_date) / oneDay
          )
        );

        if (diffDays >= 29) {
          this.inProgressFormRules.comments[0].required = true;
        } else {
          this.inProgressFormRules.comments[0].required = false;
        }
      }
    },
    updateEscalation(formName) {
      const preventSubmission = [];

      const preventResponse = [
        "This lead is currently Work in Progress",
        "This lead will be installed soon"
      ];

      if (
        preventSubmission.includes(this.selectedLead.escalation_status) &&
        preventResponse.includes(this.inProgressForm.response)
      ) {
        this.$store.dispatch("leadescalation/showConfirmationModal", {
          extended: true,
          hasDeclined: false
        });
        return;
      }

      this.inProgressForm.update_type = "modal_update";
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.$store
            .dispatch("leadescalation/updateLeadEscalation", formName)
            .then(({ data, errors, originalLeadId }) => {
              const { success, message } = data;
              if (success) {
                this.$store.dispatch("leadescalation/showConfirmationModal", {
                  extended: false,
                  hasDeclined: false
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
  },
  mounted() {}
};
</script>


<style lang="scss" scoped>
  ::v-deep {
    .pointer {
      input {
        cursor: pointer;
        }
    }
  }
</style>
