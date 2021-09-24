<template>
  <Section className="admin-settings" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          :model="basicSettingsForm"
          status-icon
          :rules="basicSettingsFormRules"
          label-position="top"
          ref="basicSettingsForm"
          label-width="120px"
          class="basicForm"
        >

          <el-row :gutter="20" class="m-b-xl">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-upload
                class="brand-uploader"
                action="/api/v1/admin/setting/upload_logo"
                name="image"
                :show-file-list="false"
                :on-success="handleSuccess"
                :before-upload="beforeUpload"
              >
                <img v-if="imageUrl" :src="imageUrl" class="brand" />

                <span v-else>
                  <i class="el-icon-plus brand-uploader-icon"></i>
                </span>
              </el-upload>
              <el-tag v-if="!imageUrl" type="danger"
                >NOTE: THE FILE MUST BE PNG, JPEG OR JPG</el-tag
              >
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Company Name"
                prop="company_name"
                :error="
                  basicSettingsForm.errors.errors.company_name
                    ? basicSettingsForm.errors.errors.company_name[0]
                    : ''
                "
              >
                <el-input
                  @change="inputChange"
                  type="text"
                  v-model="basicSettingsForm.company_name"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">

              <el-row :gutter="24">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                  <el-form-item
                    label="Email Receivers"
                    prop="receivers"
                    :error="
                      basicSettingsForm.errors.errors.receivers
                        ? basicSettingsForm.errors.errors.receivers[0]
                        : ''
                    "
                  >
                    <el-input
                      @change="inputChange"
                      class="comments"
                      type="textarea"
                      :autosize="{ minRows: 4 }"
                      placeholder=" Emails (Separated by comma)"
                      v-model="basicSettingsForm.receivers"
                    >
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>

              <el-row :gutter="24">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">

                  <el-form-item
                    label="Day & Time to send 'Weekly Lead Update' email to 'Manual Organisaitons'"
                    prop="day_of_week"
                    :error="
                      basicSettingsForm.errors.errors.day_of_week
                        ? basicSettingsForm.errors.errors.day_of_week[0]
                        : ''
                    "
                  >
                    <el-select
                      v-model="basicSettingsForm.day_of_week"
                      placeholder="Select Day of the Week"
                      @change="dayOfWeekChange"
                      >

                      <el-option
                        v-for="item in daysOfTheWeek"
                        :key="item.value"
                        :label="item.label"
                        :value="item.value">
                      </el-option>
                    </el-select>
                  </el-form-item>

                  <el-form-item
                    prop="time"
                    :error="
                      basicSettingsForm.errors.errors.time
                        ? basicSettingsForm.errors.errors.time[0]
                        : ''
                    "
                  >
                    <!-- <el-select style="width:100px" v-model="basicSettingsForm.time" @change="inputChange" :disabled="isDisabledTime">
                      <el-option
                        v-for="ite in time"
                        :key="ite"
                        :label="ite"
                        :value="ite">
                      </el-option>
                    </el-select> -->

                    <el-time-select
                      @change="inputChange"
                      :disabled="isDisabledTime"
                      style="width: 150px"
                      v-model="basicSettingsForm.time"
                      :picker-options="{
                        start: '00:05',
                        step: '00:05',
                        end: '12:00'
                      }"
                      placeholder="Select time"
                      align="center">
                    </el-time-select>

                    <el-select style="width:100px" v-model="basicSettingsForm.am_pm" @change="inputChange" :disabled="isDisabledTime">
                      <el-option
                        v-for="item in amPm"
                        :key="item.label"
                        :label="item.label"
                        :value="item.value">
                      </el-option>
                    </el-select>
                  </el-form-item>

                </el-col>
              </el-row>

            </el-col>

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-row :gutter="24">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                  <el-form-item
                    label="Message for Enquirer Template"
                    prop="enquirer_message"
                    :error="
                      basicSettingsForm.errors.errors.enquirer_message
                        ? basicSettingsForm.errors.errors.enquirer_message[0]
                        : ''
                    "
                  >
                    <el-input
                      @change="inputChange"
                      class="comments"
                      type="textarea"
                      :autosize="{ minRows: 4 }"
                      placeholder=" Default Message for Enquirer"
                      v-model="basicSettingsForm.enquirer_message"
                      maxlength="160"
                      show-word-limit
                    >
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-button
                type="primary"
                :loading="loading"
                @click="saveBasicSettings('basicSettingsForm')"
                :disabled="!formUpdated"
                >Submit</el-button
              >
            </el-col>
          </el-row>

        </el-form>
      </el-card>
    </template>
  </Section>
</template>

<script>
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import Section from "~/components/Section";

export default {
  name: "BasicSettings",
  layout: "master",
  components: {
    Section
  },
  data: () => ({
    isDisabledTime: false,
    pageTitle: "Admin Settings",
    imageUrl: "",
    daysOfTheWeek: [
      {
        value: 0,
        label: 'None'
      },
      {
        value: 1,
        label: 'Monday'
      },
      {
        value: 2,
        label: 'Tuesday'
      },
      {
        value: 3,
        label: 'Wednesday'
      },
      {
        value: 4,
        label: 'Thursday'
      },
      {
        value: 5,
        label: 'Friday'
      },
      {
        value: 6,
        label: 'Saturday'
      },
      {
        value: 7,
        label: 'Sunday'
      },
    ],
    amPm:[
      {
        value: 'AM',
        label: 'AM'
      },
      {
        value: 'PM',
        label: 'PM'
      },
    ],
    time:['00:00', '1:00', '2:00', '3:00', '4:00', '5:00', '6:00', '7:00', '8:00', '9:00', '10:00', '11:00'],
    formUpdated: false,
  }),
  computed: mapGetters({
    settings: "settings/settings",
    loading: "settings/loading",
    basicSettingsForm: "settings/basicSettingsForm",
    basicSettingsFormRules: "settings/basicSettingsFormRules",
    logo: "settings/logo",
  }),
  methods: {
    dayOfWeekChange(value){
      if(value == 0){
        this.isDisabledTime = true
      }else{
        this.isDisabledTime = false
      }

      this.inputChange()
    },

    handleSuccess(res, file) {
      this.imageUrl = URL.createObjectURL(file.raw);
    },

    beforeUpload(file) {
      const allowTypes = ["image/jpeg", "image/png", "image/jpg"];
      const allowed = allowTypes.includes(file.type);

      if (!allowed) {
        Swal.fire({
          title: "Oops!",
          text: "File must be png, jpeg or jpg",
          type: "error",
        });
      }

      return allowed;
    },

    saveBasicSettings(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("settings/saveBasicSettings")
            .then(({ success, message, errors }) => {
              if (success) {

                this.formUpdated = false

                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }else{
              }
            });
        } else {
          return false;
        }
      });
    },

    inputChange() {
      this.formUpdated = true
    }
  },

  beforeMount() {
    this.$store
      .dispatch("settings/fetchSettings", {
        page: 1,
        pageSize: 100,
        filters: [],
      })
      .then(() => {
        this.imageUrl = this.logo;
        this.isDisabledTime = (this.basicSettingsForm.day_of_week == 0) ? true : false
      });
  },

  mounted(){
  }
};
</script>
