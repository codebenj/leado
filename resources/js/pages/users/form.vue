<template>
  <Section className="users" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          :model="userForm"
          status-icon
          :rules="userFormRules"
          label-position="top"
          ref="userForm"
          label-width="120px"
          class="demo-ruleForm"
        >
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="First Name"
                prop="first_name"
                :error="
                  userForm.errors.errors.first_name
                    ? userForm.errors.errors.first_name[0]
                    : ''
                "
              >
                <el-input type="text" v-model="userForm.first_name" @change="inputChange"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Last Name"
                prop="last_name"
                :error="
                  userForm.errors.errors.last_name
                    ? userForm.errors.errors.last_name[0]
                    : ''
                "
              >
                <el-input type="text" v-model="userForm.last_name" @change="inputChange"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Email"
                prop="email"
                :error="
                  userForm.errors.errors.email
                    ? userForm.errors.errors.email[0]
                    : ''
                "
              >
                <el-input type="email" v-model="userForm.email" @change="inputChange"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Password"
                prop="password"
                :error="
                  userForm.errors.errors.password
                    ? userForm.errors.errors.password[0]
                    : ''
                "
              >
                <el-input
                  @change="inputChange"
                  type="password"
                  v-model="userForm.password"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-checkbox v-model="userForm.is_admin"
                  @change="inputChange"
                  >Is Admin</el-checkbox
                >
              </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-button type="primary" @click="saveUser('userForm')" :disabled="!formUpdated"
                  >Submit</el-button
                >
                <el-button
                  type="danger"
                  @click="$router.push({ name: 'admin.users' })"
                >
                  Cancel
                </el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-card>
    </template>
  </Section>
</template>

<script>
import Section from "~/components/Section";
import Form from "vform";
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "UserForm",
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
  },
  data() {
    return {
      pageTitle: "Add New User",
      errors: [],
      formUpdated: false,
    };
  },

  computed: mapGetters({
    userForm: "users/userForm",
    userFormRules: "users/userFormRules",
  }),

  methods: {
    saveUser(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("users/saveUser")
            .then(({ success, message, errors }) => {
              if (success) {
                this.formUpdated = false;

                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    this.$router.push({ name: "admin.users" });
                  },
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    inputChange() {
      this.formUpdated = true
    },

    editUser() {
      const userId = this.$route.params.id ? this.$route.params.id : null;

      if (userId) {
        this.$store.dispatch("users/editUser", userId);
        this.pageTitle = "Edit User";
      }
    },

    resetForm(formName) {
      this.$refs[formName].resetFields();
    },
  },

  beforeMount() {
    this.$store.dispatch("users/clearForm");
    this.editUser();
  },
};
</script>
