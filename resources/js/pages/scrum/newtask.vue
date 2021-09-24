<template>
  <el-form
    :model="taskForm"
    status-icon
    :rules="taskFormRules"
    label-position="top"
    ref="taskForm"
    label-width="120px"
  >
    <el-row :gutter="20">
      <el-col :span="24">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item
              prop="name"
              label="Task Name"
              :error="
                taskForm.errors.errors.reason
                  ? taskForm.errors.errors.reason[0]
                  : ''
              "
            >
              <el-input
                type="text"
                v-model="taskForm.name"
              >
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-col>

      <el-col :span="24">
        <el-form-item class="fl-right">
          <el-button
            type="primary"
            :loading="loading"
            @click="save()"
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
  name: "newTask",
  data: () => ({
    sendType: 'organisation'
  }),
  props: {
    // enquirerDetails: {
    //   type: Object
    // }
  },
  computed: mapGetters({
    taskForm: "scrum/taskForm",
    status: "scrum/status",
    taskFormRules: "scrum/taskFormRules",
    newTaskType: "scrum/newTaskType",
    loading: "scrum/loading"
  }),
  methods: {

    closeDialog() {
      this.$store.dispatch("scrum/setDialog", {
        close: true,
      });
    },
    save() {
      this.taskForm.status_id = this.newTaskType;
      this.$store.dispatch('scrum/saveTask').then(({ data, success, message, errors }) => {
        if ( success ) {

          Swal.fire({
            title: 'Success!',
            text: message,
            type: 'success',
          }).then(() => {
              this.closeDialog();
          });
        } else {
          console.log(errors)
        }
      })
    }
  },
  beforeMount() {
  }
};
</script>
