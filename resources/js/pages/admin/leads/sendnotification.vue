<template>
  <el-form
    :model="sendNotificationForm"
    status-icon
    :rules="sendNotificationFormRules"
    label-position="top"
    ref="sendNotificationForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-form-item
          label="Message"
          prop="message"
          :error="
            sendNotificationForm.errors.errors.reason
              ? sendNotificationForm.errors.errors.reason[0]
              : ''
          "
        >
          <el-input
            dusk="send-notification"
            type="textarea"
            :autosize="{ minRows: 4 }"
            placeholder="Enter a message"
            v-model="sendNotificationForm.message"
            maxlength="160"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <el-col :span="24">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item
              prop="notification_process"
              :error="
                sendNotificationForm.errors.errors.reason
                  ? sendNotificationForm.errors.errors.reason[0]
                  : ''
              "
            >
              <el-checkbox
                v-if="checkCustomerEmail(sendType == 'organisation' ? activeEscalation.organisation.organisation_users[0].user.email : lead.customer.email)"
                v-model="sendNotificationForm.send_email"
                @change="validateNotif2('email')"
                :checked="sendNotificationForm.send_email ? 'checked' : ''"
                >Email</el-checkbox
              >
              <el-checkbox
                v-if="checkCustomerNumber(sendType == 'organisation' ? activeEscalation.organisation.contact_number : lead.customer.contact_number)"
                v-model="sendNotificationForm.send_sms"
                @change="validateNotif2('sms')"
                :checked="sendNotificationForm.send_sms ? 'checked' : ''"
                >SMS</el-checkbox
              >
            </el-form-item>
          </el-col>
          <!-- <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl="4">
                        <el-form-item
                        v-if="checkCustomerNumber(lead.customer.contact_number)" prop="customer_number" :error="sendNotificationForm.errors.errors.reason ? sendNotificationForm.errors.errors.reason[0] : ''">
                            <el-checkbox v-model="sendNotificationForm.send_sms" @change="validateNotif2('sms')" :checked="sendNotificationForm.send_sms ? 'checked' : ''">SMS</el-checkbox>
                        </el-form-item>
                    </el-col> -->
        </el-row>
      </el-col>

      <el-col :span="24">
        <el-form-item class="fl-right">
          <el-button
            dusk="send-notification-save"
            type="primary"
            :loading="loading"
            @click="send('sendNotificationForm')"
            >Submit</el-button
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
  name: "SendNotificationForm",
  data: () => ({}),
  props: {
    sendType: {
      type: String
    },
    activeEscalation: {
      type: Object
    }
  },
  computed: mapGetters({
    loading: "leadhistory/loading",
    lead: "leadhistory/lead",
    sendNotificationForm: "leadhistory/sendNotificationForm",
    sendNotificationFormRules: "leadhistory/sendNotificationFormRules",
    sendNotificationDialogVisible: "leadhistory/sendNotificationDialogVisible"
  }),
  methods: {
    validateNotif2(type) {
      this.$store.dispatch("leadhistory/validateNotif2", type);
    },
    checkCustomerEmail(customerEmail) {
      if (customerEmail) {
        return true;
      } else {
        this.sendNotificationForm.send_email = false;
        return false;
      }
    },
    checkCustomerNumber(customerNumber) {
      if (customerNumber) {
        return true;
      } else {
        this.sendNotificationForm.send_sms = false;
        return false;
      }
    },
    send(formName) {
      this.sendNotificationForm.send_to = this.sendType;
      const sendMessage = this.sendType === "inquirer" ? "sendEnquirerMessage" : "sendOrgMessage";
      let text = sendMessage === 'sendEnquirerMessage' ? 'Enquirer message successfully sent.' : 'Org message successfully sent.'
      this.$refs[formName].validate(valid => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch(`leadhistory/${sendMessage}`)
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
        form: "send_notification"
      });
    }
  },
  beforeMount() {
    console.log(this.sendType);
    let params = {
      customer_email: this.lead.customer.email,
      customer_number: this.lead.customer.contact_number
    };
    this.$store.dispatch("leadhistory/validateNotif", { params: params });
  }
};
</script>
