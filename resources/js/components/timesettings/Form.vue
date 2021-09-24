<template>
  <el-form
    label-position="top"
    label-width="120px"
    :model="timesettingForm"
    ref="timesettingForm"
    status-icon

  >
    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <el-form-item
          prop="type"
          :error="
            timesettingForm.errors.errors.name
              ? timesettingForm.errors.errors.name[0]
              : ''
          "
        >
          <el-switch
            v-model="timesettingForm.type"
            active-text="Recurring"
            inactive-text="One-Time"
            @change="changeRules"
          >
          </el-switch>
        </el-form-item>
      </el-col>

      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <el-form-item
          label="Name"
          prop="name"
          :error="
            timesettingForm.errors.errors.name
              ? timesettingForm.errors.errors.name[0]
              : ''
          "
        >
          <el-input type="text" v-model="timesettingForm.name"></el-input>
        </el-form-item>
      </el-col>
    </el-row>

    <el-row :gutter="24" v-if="!timesettingForm.type">
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label="PAUSE ON"
          prop="start_date"
          :error="
            timesettingForm.errors.errors.start_date
              ? timesettingForm.errors.errors.start_date[0]
              : ''
          "
        >
          <el-date-picker
            v-model="timesettingForm.start_date"
            type="date"
            value-format="yyyy-MM-dd"
            placeholder="Pick a start date"
          >
          </el-date-picker>
        </el-form-item>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label=" "
          prop="start_time"
          :error="
            timesettingForm.errors.errors.start_time
              ? timesettingForm.errors.errors.start_time[0]
              : ''
          "
        >
          <el-time-picker
            v-model="timesettingForm.start_time"
            format="hh:mm A"
            value-format="HH:mm"
            placeholder="Pick a start time"
          >
          </el-time-picker>
        </el-form-item>
      </el-col>
    </el-row>

    <el-row :gutter="24" v-if="!timesettingForm.type">
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label="RESUME ON"
          prop="stop_date"
          :error="
            timesettingForm.errors.errors.stop_date
              ? timesettingForm.errors.errors.stop_date[0]
              : ''
          "
        >
          <el-date-picker
            v-model="timesettingForm.stop_date"
            value-format="yyyy-MM-dd"
            placeholder="Pick a stop date"
          >
          </el-date-picker>
        </el-form-item>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label=" "
          prop="stop_time"
          :error="
            timesettingForm.errors.errors.stop_time
              ? timesettingForm.errors.errors.stop_time[0]
              : ''
          "
        >
          <el-time-picker
            v-model="timesettingForm.stop_time"
            format="hh:mm A"
            value-format="HH:mm"
            placeholder="Pick a stop time"
          >
          </el-time-picker>
        </el-form-item>
      </el-col>
    </el-row>

    <el-row :gutter="24" v-if="timesettingForm.type">
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label="PAUSE ON"
          prop="start_day"
          :error="
            timesettingForm.errors.errors.start_day
              ? timesettingForm.errors.errors.start_day[0]
              : ''
          "
        >
          <el-select
            v-model="timesettingForm.start_day"
            placeholder="Pick Pause Day"
          >
            <el-option
              v-for="(day, index) in days"
              :key="index"
              :label="day.toUpperCase()"
              :value="day"
            >
            </el-option>
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
        <el-form-item
          label="RESUME ON"
          prop="stop_day"
          :error="
            timesettingForm.errors.errors.stop_day
              ? timesettingForm.errors.errors.stop_day[0]
              : ''
          "
        >
          <el-select
            v-model="timesettingForm.stop_day"
            placeholder="Pick Resume Day"
          >
            <span v-for="(day, index) in days" :key="index">
              <el-option
                :label="day.toUpperCase()"
                :value="day"
                v-if="day !== timesettingForm.start_day"
              >
              </el-option>
            </span>
          </el-select>
        </el-form-item>
      </el-col>
    </el-row>

    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item class="fl-right">
                <el-button
                  type="primary"
                  :loading="loading"
                  @click="saveTimeSetting('timesettingForm')"
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
  name: "TimeSettingForm",
  data: () => ({
    title: "Add New Time Setting",
    days: [
      "sunday",
      "monday",
      "tuesday",
      "wednesday",
      "thursday",
      "friday",
      "saturday",
    ],
  }),
  computed: mapGetters({
    timesettings: "timesetting/timesettings",
    loading: "timesetting/loading",
    timesettingForm: "timesetting/timesettingForm",
    timesettingFormRules: "timesetting/timesettingFormRules",
  }),
  methods: {
    saveTimeSetting(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store.dispatch('timesetting/saveTimesetting')
            .then(({ success, message, errors }) => {
              if ( success ) {
                  this.$store.dispatch('timesetting/setDialog', false)

                  Swal.fire({
                      title: 'Success!',
                      text: message,
                      type: 'success',
                  })
              } else if ( errors && errors.error ) {
                  Swal.fire({
                      title: 'Oops!',
                      text: errors.error,
                      type: 'error',
                  })
              }
            })
        } else {
          return false;
        }
      });
    },

    changeRules() {
      this.$store.dispatch("timesetting/changeTimesettingRules");
    },

    closeDialog() {
      this.$store.dispatch('timesetting/setDialog', false)
    }
  },
  mounted() {
    this.$store.dispatch("timesetting/fetchTimesettings");
  },
};
</script>