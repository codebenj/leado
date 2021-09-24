<template>
  <div class="text-center p-2 pt-0">
    <h3 class="text-center header-text mb-2">Outgoing Notifications</h3>

    <p class="detail">You are about to assign this lead to an Org. Select which notification types you would like to send.</p>

    <p class="detail mt-3" v-if="type != 'new_lead' && org_data.manual_update">There are no outgoing notifications for this selection. This might be due to all Manual Organisation notifications being disabled.</p>
    <el-row :gutter="24" class="mt-3">
      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" v-if="type == 'new_lead'">
        <div class="m-auto content-checkbox">
          <h6 class="text-bold">Enquirer</h6>
          <div v-if="form.contact_number"><el-checkbox label="SMS" v-model="form.enquirer_send_sms"></el-checkbox></div>
          <div v-if="form.email"><el-checkbox label="Email" v-model="form.enquirer_send_email"></el-checkbox></div>
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="" v-if="!org_data.manual_update">
        <div class="m-auto content-checkbox text-left">
          <h6 class="text-bold">{{ type != 'new_lead' ? 'New Organisation' : 'Organisation' }}</h6>
          <div v-if="org_data.number"><el-checkbox label="SMS"  v-model="form.send_sms"></el-checkbox></div>
          <div v-if="org_data.email"><el-checkbox label="Email"  v-model="form.send_email"></el-checkbox></div>
          <div class="disabled-real-time"><el-checkbox label="Real-time" checked disabled ></el-checkbox></div>
        </div>
      </el-col>
    </el-row>
    <el-row :gutter="24" class="mt-3" >
      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="confirmation-buttons">
        <el-button
          dusk="confirmation-done"
          type="primary"
          class="w-100 mt-1"
          @click="close()"
          plain
          >Cancel</el-button
        >
      </el-col>

      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="confirmation-buttons">
        <el-button
          dusk="confirmation-done"
          type="primary"
          class="w-100 mt-1"
          @click="submit()"
          :loading="loading"
          >{{ submitText }}</el-button
        >
      </el-col>
    </el-row>
  </div>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import { Bus } from '~/app'

export default {
  name: "OutgoingNotificationModal",

  props: {
    form: {
      default: null
    },

    type: {
      type: String,
      default: 'new_lead'
    },

    org_data: {
      default: null
    },

    loading: {
      default: null
    },
  },

  computed: mapGetters({
  }),
  methods: {
    close() {
    this.$store.dispatch("notifications/setOutgoingModalState", {
        outgoingModalState: false
      });
    },

    submit() {
      this.$emit('saveLead')
    },

    configureLeadForm() {
      if (!this.form.contact_number) {
        this.form.enquirer_send_sms = false;
      }

      if (!this.form.email) {
        this.form.enquirer_send_email = false;
      }

      if (!this.org_data.number) {
        this.form.send_sms = false;
      }

      if (!this.org_data.email) {
        this.form.send_email = false;
      }

      this.configureOrgData();
    },
    configureOrgData() {
      if(this.org_data.manual_update) {
        this.form.send_sms = false;
        this.form.send_email = false;
      } else {
        this.form.send_sms = true;
        this.form.send_email = true;
      }
    },
    initializeData() {
      if (this.type == 'new_lead') {
        this.configureLeadForm();
        this.submitText = 'Assign and Send'

      } else {
        this.configureOrgData();
        this.submitText = 'Reassign and Send'
      }
    }
  },
  beforeMount() {
    this.initializeData();
  },
  // mounted() {
  //   Bus.$on( 'reload-outgoing-notification', () => {
  //     this.initializData()
  //   })
  // }
};
</script>

<style lang="scss" scoped>
  .header-text {
    color: #303133;
  }

  .p-2 {
    padding: 2rem;
  }

  .pt-0 {
    padding-top: 0 !important;
  }

  .m-auto {
    margin: auto;
  }

  .content-checkbox {
    width: fit-content;
  }

  .mt-3 {
    margin-top: 2.5rem;
  }

  .mt-1 {
    margin-top: 1rem;
  }

  .mb-2 {
    margin-bottom: 1.5rem;
  }

  ::v-deep {
    .disabled-real-time {
      .is-checked {
        .el-checkbox__label {
          color: #409EFF !important;
        }
        .el-checkbox__inner {
          background-color: #409EFF !important;
          border-color: #409EFF !important;
        }
        .el-checkbox__inner::after {
          border-color: #fff !important;
        }
      }
    }
  }
</style>
