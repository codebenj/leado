<template>
  <el-form
    dusk="cecForm"
    :model="cecForm"
    status-icon
    label-position="top"
    :rules="cecFormRules"
    ref="cecForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-form-item label="Select one of the following:" prop="responseIndex">
          <!-- <el-radio v-for="(response, index) in responses" :key="index" @change="clearSubFields()"
						v-model="cecForm.responseIndex" :value="index" :label="index">
						{{ response.response }}
					</el-radio> -->
          <el-radio-group
            v-model="cecForm.responseIndex"
            @change="clearSubFields()"
          >
            <el-radio
              v-for="(response, index) in responses"
              class="cec-responses"
              :key="index"
              :value="index"
              :label="index"
            >
              {{ response.response }}
            </el-radio>
          </el-radio-group>
        </el-form-item>
      </el-col>

      <el-col :span="24" v-if="cecForm.responseIndex !== ''">
        <el-form-item label="Select one of the following:" prop="reason">
          <el-radio-group
            v-model="cecForm.reason"
            @input="changeReason"
            @change="changeRules"
          >
            <el-radio
              v-for="(reason, index) in responses[cecForm.responseIndex]
                .reasons"
              class="cec-reasons"
              :key="index"
              :value="index"
              :label="reason"
            >
              {{ reason }}
            </el-radio>
          </el-radio-group>
        </el-form-item>
      </el-col>

      <!-- I have contacted the Enquirer  and This lead has been LOST -->
      <el-col
        class="lost_reasons"
        :span="24"
        v-if="cecForm.reason && cecForm.reason == 'This lead has been LOST'"
      >
        <el-form-item
          label="What is the main reason that this lead was lost?"
          prop="other_reason"
        >
          <el-select
            popper-class="lost_reasons_popper"
            v-model="cecForm.other_reason"
            @change="changeRules"
            placeholder="Select Reason"
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
        v-if="cecForm.other_reason && cecForm.other_reason == 'Other'"
      >
        <el-form-item
          label="Please indicate your reason."
          prop="indicate_reason"
        >
          <el-input
            dusk="cec-indicate-reason"
            type="textarea"
            :autosize="{ minRows: 4 }"
            v-model="cecForm.indicate_reason"
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
          cecForm.other_reason &&
            cecForm.other_reason ==
              'Enquirer went with someone with a different system (not a ski-slope system).'
        "
      >
        <el-form-item label="What Sytem?" prop="what_system">
          <el-input
            type="textarea"
            :autosize="{ minRows: 4 }"
            v-model="cecForm.what_system"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <el-col
        :span="24"
        v-if="
          cecForm.reason &&
            (cecForm.reason == 'This lead is currently Work in Progress' ||
              cecForm.reason == 'This lead will be installed soon')
        "
      >
        <el-form-item
          label="This lead will now be IN PROGRESS, please choose how much more time you need:"
          prop="progress_period_date"
        >
          <el-date-picker
            :default-value="selectedLead.open_date"
            :picker-options="datePickerOptions"
            :localTime="true"
            :editable="false"
            class="w-100 pointer"
            value-format="yyyy-MM-dd"
            v-model="cecForm.progress_period_date"
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
            v-model="cecForm.selected_time"
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

      <!-- I haven't attempted to contacted Enquirer and "Other" reason -->
      <el-col :span="24" v-if="showComment">
        <el-form-item
          :label="commentLabel"
          prop="comments"
          :error="
            cecForm.errors.errors.comments
              ? cecForm.errors.errors.comments[0]
              : ''
          "
        >
          <el-input
            dusk="cec-comments"
            type="textarea"
            :autosize="{ minRows: 4 }"
            placeholder="Enter a message"
            v-model="cecForm.comments"
            maxlength="500"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <!-- I have tried to contact the Enquirer reason -->
      <el-col :span="24" v-if="showDate && toShowDate">
        <el-form-item
          label="This lead is currently Confirmed Enquiry Contacted,  please choose how much more time you need?"
          prop="tried_to_contact_date"
          :error="
            cecForm.errors.errors.tried_to_contact_date
              ? cecForm.errors.errors.tried_to_contact_date[0]
              : ''
          "
        >
          <el-date-picker
            :picker-options="datePickerOptionsCECARTried"
            :editable="false"
            value-format="yyyy-MM-dd 23:59:59"
            class="w-100 pointer"
            v-model="cecForm.tried_to_contact_date"
            type="date"
            placeholder="Pick a day"
          >
          </el-date-picker>
        </el-form-item>

        <el-form-item
          label="What time of the day should we notify you if this lead expires?"
          prop="selected_time"
        >
          <el-time-select
            v-model="cecForm.selected_time"
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

      <!-- I haven't attempted to contacted Enquirer and This lead has been WON and installed  -->
      <el-col :span="24" v-if="showWon">
        <el-form-item
          label="No of Metres of Gutter Edge:"
          prop="gutter_edge_meters"
          :error="
            cecForm.errors.errors.gutter_edge_meters
              ? cecForm.errors.errors.gutter_edge_meters[0]
              : ''
          "
        >
          <el-input
            dusk="gutter_edge_meters"
            type="number"
            v-model="cecForm.gutter_edge_meters"
          ></el-input>
        </el-form-item>
        <el-form-item
          label="No of Metres of Valley:"
          prop="valley_meters"
          :error="
            cecForm.errors.errors.valley_meters
              ? cecForm.errors.errors.valley_meters[0]
              : ''
          "
        >
          <el-input
            dusk="valley_meters"
            type="number"
            v-model="cecForm.valley_meters"
          ></el-input>
        </el-form-item>
        <el-form-item
          label="Date of Installation:"
          prop="installation_date"
          :error="
            cecForm.errors.errors.installation_date
              ? cecForm.errors.errors.installation_date[0]
              : ''
          "
        >
          <el-date-picker
            :picker-options="datePickerOptionsWon"
            :editable="false"
            class="w-100 pointer"
            v-model="cecForm.installation_date"
            type="month"
            placeholder="Pick a month"
          >
          </el-date-picker>
        </el-form-item>
      </el-col>
    </el-row>
    <el-row :gutter="24">
      <el-col :span="24">
        <el-button
          dusk="cec-submit"
          type="primary"
          class="w-100"
          @click="updateEscalation('cecForm')"
          :loading="loading"
          >Submit</el-button
        >
      </el-col>
    </el-row>
  </el-form>
</template>
<script>
import { mapGetters } from "vuex";
import moment, { now } from "moment";

export default {
  name: "ConfirmEnquirerContacted",
  data: () => ({
    name: "Confirm Enquirer Contacted",
    toShowDate: false
  }),
  computed: {
    ...mapGetters({
      responses: "leadescalation/responses",
      cecForm: "leadescalation/cecForm",
      lostLeadReasons: "leadescalation/lostLeadReasons",
      loading: "leadescalation/loading",
      selectedLead: "leadescalation/selectedLead",
      cecFormRules: "leadescalation/cecFormRules",
      user: "auth/user"
    }),

    showComment() {
      const reasonsWithComments = [
        "Other",
        "This lead will be installed soon",
        "This lead has been LOST",
        "This lead is currently Work in Progress",
        "DISCONTINUE this lead. Multiple attempts made. Could not connect with the Enquirer."
      ];

      return reasonsWithComments.includes(this.cecForm.reason);
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

    showDate() {
      if (this.cecForm.responseIndex !== "") {
        return (
          this.responses[this.cecForm.responseIndex].response ==
          "I have tried to contact the Enquirer"
        );
      }
      return false;
    },

    showWon() {
      return this.cecForm.reason == "This lead has been WON and installed";
    },

    commentLabel() {
      switch (this.cecForm.reason) {
        case "This lead has been LOST":
          return "Use this comment section to provide additional information you think might be needed:";
          // return "Do you have any extra feedback regarding this Lead and Enquirer?";
          break;

        case "Other":
          return "Please provide a reason for DISCONTINUING this lead:";
          break;

        default:
          return "Use this comment section to provide additional information you think might be needed:";
          // return "Comments";
          break;
      }
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
    datePickerOptionsCECARTried() {
      let today = new Date();
      let afterAWeek = new Date();

      return {
        disabledDate(date) {
          // Add 7 days allowed
          afterAWeek.setDate(today.getDate() + 7);

          return today > date || date > afterAWeek;
        }
      };
    }
  },
  methods: {
    clearSubFields() {
      this.cecForm.reason = "";
      this.cecForm.comments = "";
      this.cecForm.indicate_reason = "";
      this.cecForm.other_reason = "";
      this.cecForm.what_system = "";

      setTimeout(() => {
        this.$refs["cecForm"].clearValidate();
      });
    },

    changeReason(val) {
      this.cecForm.comments = "";
      this.cecForm.indicate_reason = "";
      this.cecForm.other_reason = "";
      this.cecForm.what_system = "";
    },

    changeRules(val) {
      if (
        val ==
        "DISCONTINUE this lead. Multiple attempts made. Could not connect with the Enquirer."
      ) {
        this.toShowDate = false;
      } else {
        this.toShowDate = true;
      }

      this.$refs["cecForm"].clearValidate();

      this.$store
        .dispatch("leadescalation/changeRules", {
          formName: "cecForm",
          formRuleName: "cecFormRules"
        })
        .then(() => this.$refs["cecForm"].clearValidate());
    },

    checkCommentsRequired() {
      if (
        this.cecForm.progress_period_date ||
        this.cecForm.escalation_status ==
          "Confirm Enquirer Contacted - Awaiting Response"
      ) {
        const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
        let date_start = moment(new Date());
        let date_end = this.cecForm.progress_period_date
          ? moment(this.cecForm.progress_period_date)
          : 0;

        let diffDays = date_end.diff(date_start, "days");

        if (diffDays >= 29) {
          this.cecFormRules.comments[0].required = true;
        } else {
          this.cecFormRules.comments[0].required = false;
        }
      }
    },

    updateEscalation(formName) {
      this.cecForm.update_type = "modal_update";
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
  }
};
</script>

<style lang="scss" scoped>
.el-radio {
  white-space: unset !important;
}
::v-deep {
  .pointer {
    input {
      cursor: pointer;
      }
  }
}
</style>
