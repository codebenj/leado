<template>
  <div class="text-center p-2 pt-0">
    <h3 class="text-left header-text mb-2">Custom Notifications</h3>

    <p class="detail text-left">Here you can customise an outgoing notification and send it to multiple Organsations at once.</p>

    <el-form
      :model="form"
      status-icon
      :rules="form_rules"
      label-position="top"
      ref="formCustomNotification"
      label-width="120px"
    >

    <el-row :gutter="24" class="mt-3">
      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">

        <el-form-item
          label="Notify"
          prop="notify"
          class="text-left"
          :error="
            form.errors.errors.notify
              ? form.errors.errors.notify[0]
              : ''
          "
        >

          <el-select v-model="form.notify" placeholder="Choose Group" @change="notifyChange">
            <el-option
              v-for="item in notify_groups"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>

        </el-form-item>



        <el-form-item
          label="Title"
          prop="title"
          class="text-left"
          :error="
            form.errors.errors.title
              ? form.errors.errors.title[0]
              : ''
          "
        >
          <el-input placeholder="Type here..." v-model="form.title"></el-input>

        </el-form-item>

      </el-col>

      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="">
        <el-form-item
          label="Notification Type"
          prop="notification_type"
          class="text-left"
          :error="
            form.errors.errors.notification_type
              ? form.errors.errors.notification_type[0]
              : ''
          "
        >
          <el-checkbox-group v-model="form.notification_type" @change="notificationTypeChange">
            <el-checkbox label="SMS"></el-checkbox><br>
            <el-checkbox label="Email"></el-checkbox><br>
            <el-checkbox label="Real Time" :disabled="real_time_disable"></el-checkbox>
          </el-checkbox-group>

        </el-form-item>

      </el-col>

    </el-row>

    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="">
        <el-form-item
            label="Body"
            prop="body"
            class="text-left"
            :error="
              form.errors.errors.body
                ? form.errors.errors.body[0]
                : ''
            "
          >

          <el-input
            type="textarea"
            :rows="2"
            placeholder="Type here..."
            v-model="form.body"
            :maxlength="body_max_length"
            show-word-limit
            class="fit-content">
          </el-input>

        </el-form-item>

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

    </el-form>

  </div>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import { Bus } from '~/app'

export default {
  name: "CustomNotificationModal",

  computed: mapGetters({
    form: 'notifications/custom_notification_form',
    form_rules: 'notifications/custom_notification_form_rules',
    loading: 'notifications/loading',
  }),

  data: () => ({
    real_time_disable: false,
    body_max_length: 3000,
    submitText: 'Send Notification',
    notify_groups: [
      {
        label: 'Orgs - All',
        value: 'orgs-all'
      },
      {
        label: 'Orgs - Auto',
        value: 'orgs-auto'
      },
      {
        label: 'Orgs - Manual',
        value: 'orgs-manual'
      },
      {
        label: 'Orgs - Critical Leads',
        value: 'orgs-critical-leads'
      },
      {
        label: 'Orgs - On Hold - Sys',
        value: 'orgs-on-hold-sys'
      },
      {
        label: 'Orgs - On Hold - Admin',
        value: 'orgs-on-hold-admin'
      },
      {
        label: 'Enquirers - All',
        value: 'enquirers-all'
      },
      {
        label: 'Enquirers - Supply & Install',
        value: 'enquirers-supply-install'
      },
      {
        label: 'Enquirers - Supply Only',
        value: 'enquirers-supply-only'
      },
      {
        label: 'Enquirers - General Enquiry',
        value: 'enquirers-general-enquiry'
      },
      {
        label: 'Users - All',
        value: 'users-all'
      },
      {
        label: 'Users - Admin Role',
        value: 'users-admin-role'
      },
      {
        label: 'Users - User Role',
        value: 'users-user-role'
      },
    ]
  }),

  methods: {
    notifyChange(e){
      if(e.includes("enquirers")){
        this.real_time_disable = true
      }else{
        this.real_time_disable = false
      }
    },

    notificationTypeChange(e){
      this.form_rules.body.splice(1, 1)
      if(e.includes("SMS")){
        this.body_max_length = 200

      }else{
        this.body_max_length = 3000
      }

      this.form_rules.body.push({ max: this.body_max_length, message: 'You have exceeded the maximum character limit of 200 characters for Notification Type: SMS', trigger: 'blur' },)
    },

    close() {
      this.$emit("handleCustomNotificationModalClose");
    },

    submit() {
      this.$refs['formCustomNotification'].validate((valid) => {
        if (valid) {
          this.$store
            .dispatch("notifications/sentNotifications")
            .then(({ success, message, errors }) => {
              if (success) {
                this.isTouched = false
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }
            });
        } else {
          return false
        }
      })
    },
  },
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
    margin-top: 15px;
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

  .full{
    width: 100%;
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
