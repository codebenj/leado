<template>
  <el-container id="login" class="gradient-pastel-blue w-100 h-100-vh">
    <el-row type="flex" class="w-100 jc-center ai-center">
      <el-col :span="6">
        <el-card class="box-card">
          <div slot="header" class="clearfix text-center">
            <img
              src="/app-assets/img/traleado-logo.png"
              class="auth-logo mb-2"
              :alt="appName"
              width="250"
            />
          </div>
          <el-form
            :model="resetForm"
            status-icon
            :rules="resetRules"
            label-position="top"
            ref="resetForm"
            label-width="120px"
            class="demo-ruleForm"
          >
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item
                  label=""
                  prop="email"
                  :error="getError(resetErrors, 'email')"
                >
                  <el-input
                    type="email"
                    placeholder="Email Address"
                    v-model="resetForm.email"
                    readonly
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item
                  label=""
                  prop="password"
                  :error="getError(resetErrors, 'password')"
                >
                  <el-input
                    type="password"
                    placeholder="Password"
                    v-model="resetForm.password"
                    autocomplete="off"
                  ></el-input>
                </el-form-item>
              </el-col>

              <el-col :span="24">
                <el-form-item
                  label=""
                  prop="password_confirmation"
                  :error="getError(resetErrors, 'password')"
                >
                  <el-input
                    type="password"
                    placeholder="Password Confirmation"
                    v-model="resetForm.password_confirmation"
                    autocomplete="off"
                  ></el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row>
              <el-col :span="24">
                <el-button
                  class="w-100"
                  type="primary"
                  @click="reset('resetForm')"
                  :loading="loading"
                  
                  >Reset Password</el-button
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
import Swal from "sweetalert2";
import Form from "vform";
import { getError } from "~/helpers";
export default {
  name: "reset",
  layout: "basic",
  middleware: "guest",
  metaInfo() {
    return { title: this.$t("reset_password") };
  },

  data() {
    const validatePass = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please input the password"));
      } else {
        if (this.resetForm.password_confirmation !== "") {
          this.$refs.resetForm.validateField("password_confirmation");
        }
        callback();
      }
    };

    const validatePass2 = (rule, value, callback) => {
      if (value === "") {
        callback(new Error("Please input the password again"));
      } else if (value !== this.resetForm.password) {
        callback(new Error("Two inputs don't match!"));
      } else {
        callback();
      }
    };

    return {
      appName: window.config.appName,
      status: "",
      resetForm: new Form({
        token: "",
        email: "",
        password: "",
        password_confirmation: "",
      }),
      resetErrors: [],
      resetRules: {
        email: [
          {
            required: true,
            message: "Please input email address",
            trigger: "blur",
          },
          { type: "email", trigger: ["blur", "change"] },
        ],
        password: [
          { required: true, message: "Please input password", trigger: "blur" },
          { validator: validatePass, trigger: "blur" },
        ],
        password_confirmation: [
          {
            required: true,
            message: "Please input confirm password",
            trigger: "blur",
          },
          { validator: validatePass2, trigger: "blur" },
        ],
      },
      loading: false,
      countDown: 3,
    };
  },
  created() {
    this.resetForm.email = this.$route.query.email;
    this.resetForm.token = this.$route.params.token;
  },
  methods: {
    getError: getError,
    countDownTimer() {
      if (this.countDown > 0) {
        setTimeout(() => {
          this.countDown -= 1;
          this.countDownTimer();
        }, 1000);
      } else {
        if (this.countDown == 0) {
          Swal.close();
          this.$router.push({ name: "login" });
        }
      }
    },

    reset(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;

          this.resetForm
            .post("/api/password/reset")
            .then(({ data }) => {
              if (data.status) {
                this.countDownTimer();
                Swal.fire({
                  title: "Success!",
                  text:
                    data.status +
                    "\n You will be redirected to login page after you 3 seconds",
                  type: "success",
                  heightAuto: false,
                  showCancelButton: false,
                  showConfirmButton: false,
                });
              }
            })
            .catch((e) => {
            });
          this.resetForm.reset();
        } else {
          return false;
        }
      });
    },
  },
};
</script>