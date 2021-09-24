<template>
  <el-form
    :model="jobDetailsForm"
    status-icon
    :rules="jobDetailsFormRules"
    label-position="top"
    ref="jobDetailsForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <!-- <el-col :span="24">
        <el-form-item
          label="Metres of gutter edge"
          prop="meters_gutter_edge"
          :error="
            jobDetailsForm.errors.errors.meters_gutter_edge
              ? jobDetailsForm.errors.errors.meters_gutter_edge[0]
              : ''
          "
        >
          <el-input
            type="number"
            v-model.number="jobDetailsForm.meters_gutter_edge"
          ></el-input>
        </el-form-item>
      </el-col> -->

      <!-- <el-col :span="24">
        <el-form-item
          label="Metres of valley"
          prop="meters_valley"
          :error="
            jobDetailsForm.errors.errors.meters_valley
              ? jobDetailsForm.errors.errors.meters_valley[0]
              : ''
          "
        >
          <el-input
            type="number"
            v-model.number="jobDetailsForm.meters_valley"
          ></el-input>
        </el-form-item>
      </el-col> -->

      <el-col :span="24">
        <el-form-item
          label="Comments"
          prop="comments"
          :error="
            jobDetailsForm.errors.errors.comments
              ? jobDetailsForm.errors.errors.comments[0]
              : ''
          "
        >
          <el-input
            type="textarea"
            :autosize="{ minRows: 4 }"
            placeholder="Enter a message"
            v-model="jobDetailsForm.comments"
            maxlength="200"
            show-word-limit
          >
          </el-input>
        </el-form-item>
      </el-col>

      <el-col :span="24">
        <el-form-item class="fl-right">
          <el-button
            type="primary"
            :loading="loading"
            @click="saveJobDetails('jobDetailsForm')"
            >Submit</el-button
          >
          <el-button type="danger" @click="closeDialog()"> Cancel </el-button>
        </el-form-item>
      </el-col>
    </el-row>
  </el-form>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "JobDetailsForm",
  data: () => ({}),
  props: ['queryInfo', 'id'],
  computed: mapGetters({
    loading: "leadhistory/loading",
    jobDetailsForm: "leadhistory/jobDetailsForm",
    jobDetailsFormRules: "leadhistory/jobDetailsFormRules",
    sendNotificationDialogVisible: "leadhistory/sendNotificationDialogVisible",
  }),
  methods: {
    saveJobDetails(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leadhistory/saveJobDetails")
            .then(({ success, message, errors }) => {
              if (success) {
                const leadId = ( this.id ) ? this.id : this.$route.params.id ? this.$route.params.id : "";
                this.$store.dispatch("leadhistory/fetchJobDetails", {"queryInfo": this.queryInfo, "leadId": leadId});

                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
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
        form: "job_details",
      });
    },
  },
};
</script>
