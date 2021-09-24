<template>
  <el-form
    :model="rolesForm"
    status-icon
    :rules="rolesFormRules"
    label-position="top"
    ref="rolesForm"
    label-width="120px"
  >
    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <el-form-item
          label="Name"
          prop="name"
          :error="
            rolesForm.errors.errors.name ? rolesForm.errors.errors.name[0] : ''
          "
        >
          <el-input type="text" v-model="rolesForm.name"  @change="inputChange"></el-input>
        </el-form-item>
      </el-col>
    </el-row>

    <el-row :gutter="24">
      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
        <el-form-item
          label="Permissions"
          prop="permissions"
          :error="
            rolesForm.errors.errors.permissions
              ? rolesForm.errors.errors.permissions[0]
              : ''
          "
        >
          <el-select
            v-model="rolesForm.permissions"
            multiple
            filterable
            default-first-option
            placeholder="Choose permissions to add"
            @change="inputChange"
          >
            <el-option
              v-for="(permission, index) in permissions"
              :key="index"
              :label="permission"
              :value="permission"
            >
            </el-option>
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
            @click="saveRole('rolesForm')"
            :disabled="!formUpdated"
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
  name: "RolesForm",
  data: () => ({
    formUpdated: false,
  }),
  computed: mapGetters({
    loading: "roles/loading",
    rolesForm: "roles/rolesForm",
    rolesFormRules: "roles/rolesFormRules",
    permissions: "permissions/permissions",
  }),
  methods: {
    saveRole(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("roles/saveRole")
            .then(({ success, message, errors }) => {
              if (success) {
                this.formUpdated = false
                this.$store.dispatch("roles/setDialog", false);

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
      this.$store.dispatch("roles/setDialog", false);
    },

    inputChange() {
      this.formUpdated = true
    }
  },
  mounted() {},
};
</script>