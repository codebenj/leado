<template>
  <el-form
    :model="sendEnquirerDetailsForm"
    status-icon
    :rules="sendEnquirerDetailsFormRules"
    label-position="top"
    ref="sendEnquirerDetailsForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item
              prop="enquiry_process"
              :error="
                sendEnquirerDetailsForm.errors.errors.reason
                  ? sendEnquirerDetailsForm.errors.errors.reason[0]
                  : ''
              "
            >
              <el-checkbox
                v-if="checkCustomerEmail(activeEscalation.organisation.organisation_users[0].user.email)"
                v-model="sendEnquirerDetailsForm.send_email"
                @change="validateEnquirerDetails2('email')"
                :checked="sendEnquirerDetailsForm.send_email ? 'checked' : ''"
                >Email</el-checkbox
              >
              <el-checkbox
                v-if="checkCustomerNumber(activeEscalation.organisation.contact_number)"
                v-model="sendEnquirerDetailsForm.send_sms"
                @change="validateEnquirerDetails2('sms')"
                :checked="sendEnquirerDetailsForm.send_sms ? 'checked' : ''"
                >SMS</el-checkbox
              >
            </el-form-item>
          </el-col>
        </el-row>
      </el-col>

      <el-col :span="24">
        <el-form-item class="fl-right">
          <el-button
            dusk="send-notification-save"
            type="primary"
            :loading="loading"
            @click="send('sendEnquirerDetailsForm')"
            >Send</el-button
          >
          <el-button type="danger" @click="closeDialog()">
            Cancel
          </el-button>
        </el-form-item>
      </el-col>
    </el-row>
  </el-form>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "sendEnquirerDetailsForm",
  data: () => ({
    sendType: 'organisation'
  }),
  props: {
    activeEscalation: {
      type: Object
    },
    enquirerDetails: {
      type: Object
    }
  },
  computed: mapGetters({
    loading: "leadhistory/loading",
    lead: "leadhistory/lead",
    sendEnquirerDetailsForm: "leadhistory/sendEnquirerDetailsForm",
    sendEnquirerDetailsFormRules: "leadhistory/sendEnquirerDetailsFormRules",
		sendEnquirerDetailsDialogVisible: "leadhistory/sendEnquirerDetailsDialogVisible",
  }),
  methods: {
    validateEnquirerDetails2(type) {
      this.$store.dispatch("leadhistory/validateEnquirerDetails2", type);
    },
    checkCustomerEmail(customerEmail) {
      if (customerEmail) {
        return true;
      } else {
        this.sendEnquirerDetailsForm.send_email = false;
        return false;
      }
    },
    checkCustomerNumber(customerNumber) {
      if (customerNumber) {
        return true;
      } else {
        this.sendEnquirerDetailsForm.send_sms = false;
        return false;
      }
    },
    send(formName) {
      this.sendEnquirerDetailsForm.send_to = this.sendType;
      let text = 'Enquirer Details successfully sent to Org.'
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.$store
            .dispatch(`leadhistory/sendOrgEnquirerDetails`)
            .then(({ data, message, success }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: text,
                  type: "success"
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    closeDialog() {
      this.$store.dispatch("leadhistory/setDialog", {
        close: false,
        form: "send_enquirer_details"
      });
    }
  },
  beforeMount() {
    let params = {
      organisation_email: this.activeEscalation.organisation.organisation_users[0].user.email,
      organisation_number: this.activeEscalation.organisation.contact_number
    };
    this.$store.dispatch("leadhistory/validateEnquirerDetails", { params: params });
  }
};
</script>
