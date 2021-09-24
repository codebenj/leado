<template>
  <el-form
    :model="sendOrgNotificationForm"
    status-icon
    :rules="sendOrgNotificationFormRules"
    label-position="top"
    ref="sendOrgNotificationForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-form-item
          label="Message"
          prop="message"
          :error="
            sendOrgNotificationForm.errors.errors.reason
              ? sendOrgNotificationForm.errors.errors.reason[0]
              : ''
          "
        >
          <el-input
            dusk="send-notification"
            type="textarea"
            :autosize="{ minRows: 4 }"
            placeholder="Enter a message"
            v-model="sendOrgNotificationForm.message"
            maxlength="500"
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
                sendOrgNotificationForm.errors.errors.reason
                  ? sendOrgNotificationForm.errors.errors.reason[0]
                  : ''
              "
            >
              <el-checkbox
                v-if="checkCustomerEmail(organisation.user.email)"
                v-model="sendOrgNotificationForm.send_email"
                @change="validateNotif2('email')"
                :checked="sendOrgNotificationForm.send_email ? 'checked' : ''"
                >Email</el-checkbox
              >
              <el-checkbox
                v-if="checkCustomerNumber(organisation.contact_number)"
                v-model="sendOrgNotificationForm.send_sms"
                @change="validateNotif2('sms')"
                :checked="sendOrgNotificationForm.send_sms ? 'checked' : ''"
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
            @click="send('sendOrgNotificationForm')"
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
import { Bus } from '~/app'

export default {
  name: "SendOrgNotificationForm",

  props: {
    organisation: { type: Object }
  },

  computed: mapGetters({
    loading: "orghistory/loading",
    sendOrgNotificationForm: "orghistory/sendOrgNotificationForm",
    sendOrgNotificationFormRules: "orghistory/sendOrgNotificationFormRules",
    sendOrgNotificationDialogVisible: "orghistory/sendOrgNotificationDialogVisible"
  }),

  data: () => ({
    sendType: 'org_profile'
  }),

  mounted() {
    let params = {
      customer_email: this.organisation.user.email,
      customer_number: this.organisation.contact_number
    }

    this.$store.dispatch( "orghistory/validateNotif", { params: params } )
    this.$store.dispatch( "orghistory/setOrg", this.organisation )
  },

  methods: {
    validateNotif2(type) {
      this.$store.dispatch("orghistory/validateNotif2", type);
    },

    checkCustomerEmail(customerEmail) {
      if (customerEmail) {
        return true;
      } else {
        this.sendOrgNotificationForm.send_email = false;
        return false;
      }
    },

    checkCustomerNumber(customerNumber) {
      if (customerNumber) {
        return true;
      } else {
        this.sendOrgNotificationForm.send_sms = false;
        return false;
      }
    },

    send(formName) {
      this.sendOrgNotificationForm.send_to = this.sendType;
      this.$refs[formName].validate(valid => {
        if (valid) {
          // dispatch form submit
          let org_id = this.organisation['id'] ?? this.$route.params.id
          this.$store
            .dispatch(`orghistory/sendOrgMessageProfile`, org_id)
            .then(({ data, message, success }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: 'Org message successfully sent.',
                  type: "success"
                });
                this.closeDialog()
                Bus.$emit( 're-fetch-org-notif-history' )

                this.sendOrgNotificationForm.message = ''
                this.$store.dispatch( "orghistory/validateNotif", { params: params } )
              }
            });
        } else {
          return false;
        }
      });
    },

    closeDialog() {
      this.$emit( 'destroyDialog' )
    }
  }
};
</script>
