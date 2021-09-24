<template>
  <el-container class="gradient-pastel-blue w-100 h-100-vh">
    <el-row type="flex" class="w-100 jc-center ai-center">
      <el-col :xs="20" :sm="20" :md="6" :lg="6" :xl="6">
        <el-card class="box-card">
          <div slot="header" class="clearfix text-center">
            <img
              src="/app-assets/img/traleado-logo.png"
              class="auth-logo mb-2"
              :alt="appName"
            />
          </div>
          <el-form
            :model="emailForm"
            status-icon
            :rules="emailRules"
            label-position="top"
            ref="emailForm"
            label-width="120px"
            class="demo-ruleForm"
          >
            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item
                  label=""
                  prop="email"
                  :error="emailErrors.email ? emailErrors.email : ''"
                >
                  <el-input
                    type="email"
                    placeholder="Email Address"
                    v-model="emailForm.email"
                  ></el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-button
                  class="w-100"
                  type="primary"
                  @click="send('emailForm')"
                  :loading="loading"
                  
                  >Send Password Reset Link</el-button
                >
              </el-col>
            </el-row>

            <el-divider></el-divider>

            <el-row>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="text-center">
                Already have an account?
                <el-link type="primary" @click="$router.push({ name: 'login' })"
                  >Login</el-link
                >
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </el-col>
    </el-row>
  </el-container>
</template>
<script>
import Form from "vform";
import Swal from "sweetalert2";

export default {
  name: "email",
  layout: "basic",
  middleware: "guest",

  metaInfo() {
    return { title: this.$t("reset_password") };
  },

  data: () => ({
    appName: window.config.appName,
    status: "",
    emailForm: new Form({
      email: "",
    }),
    emailErrors: [],
    emailRules: {
      email: [
        {
          required: true,
          message: "Please input email address",
          trigger: "blur",
        },
        {
          type: "email",
          message: "Invalid email address",
          trigger: ["blur", "change"],
        },
      ],
    },
    loading: false,
  }),

  methods: {
    send(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;

          this.emailForm
            .post("/api/password/email")
            .then((data) => {
              this.status = data.status;
              this.emailForm.reset();
              this.loading = false;

              Swal.fire({
                title: "Success!",
                text: data.data.status,
                type: "success",
                heightAuto: false,
                showCancelButton: false,
                showConfirmButton: false,
              });
            })
            .catch((e) => {
              const { errors } = this.emailForm.errors;
              this.emailErrors = errors;
              this.loading = false;
            });
        } else {
          return false;
        }
      });
    },
  },
};
</script>
<style>
  .auth-logo{
    width: 100%;
  }
</style>