<template>
  <el-container id="login" class="gradient-pastel-blue w-100 h-100-vh" dusk="loginForm">
    <el-row type="flex" class="w-100 jc-center ai-center">
      <el-col :xs="20" :sm="20" :md="8" :lg="8" :xl="8">
        <el-card class="box-card">
          <div slot="header" class="clearfix text-center">
            <img id="login-logo"
              src="/app-assets/img/traleado-logo.png"
              class="auth-logo mb-2"
              :alt="appName"
            />
          </div>
          <el-form
            :model="loginForm"
            status-icon
            :rules="loginRules"
            label-position="top"
            ref="loginForm"
            label-width="120px"
            class="demo-ruleForm"
          >
            <el-row :gutter="24" dusk="form-item-login-email">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item
                  label=""
                  prop="email"
                  :error="
                    loginForm.errors.errors.email
                      ? loginForm.errors.errors.email[0]
                      : ''
                  "
                >
                  <el-input
                    type="email"
                    placeholder="Email Address"
                    v-model="loginForm.email"
                    autocomplete="off"
                    dusk="login-email"
                  ></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item
                  label=""
                  prop="password"
                  :error="
                    loginForm.errors.errors.password
                      ? loginForm.errors.errors.password[0]
                      : ''
                  "
                >
                  <el-input
                    type="password"
                    placeholder="Password"
                    v-model="loginForm.password"
                    autocomplete="off"
                    @keyup.enter.native="triggerLogin"
                    dusk="login-password"
                  ></el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-button
                  class="w-100 text-uppercase"
                  type="primary"
                  @click="login('loginForm')"
                  :loading="loading"
                  dusk="login-button"
                  >Login</el-button
                >
              </el-col>
            </el-row>

            <el-divider></el-divider>

            <el-row>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="text-center">
                Forgot Password?
                <el-link
                  type="primary"
                  @click="$router.push({ name: 'password.request' })"
                  >Reset</el-link
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
import { getError, getRoleRoute } from "~/helpers";
import { mapGetters } from "vuex";

export default {
  name: "login",
  layout: "basic",
  middleware: "guest",

  metaInfo() {
    return { title: this.$t("login") };
  },

  computed: mapGetters({
    loginForm: "auth/loginForm",
    loginRules: "auth/loginRules",
    loading: "auth/loading",
    user: "auth/user",
  }),

  data: () => ({
	  remember: false,
      appName: window.config.appName,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    }
  ),

  methods: {
    getError: getError,
    getRoleRoute: getRoleRoute,

    triggerLogin(value){
      this.login('loginForm')
    },

    async login(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store.dispatch("auth/loginUser").then((data) => {
            if (data) {
              // Save the token.
              this.$store.dispatch("auth/saveToken", {
                token: data.token,
                remember: this.remember,
              });

              // Fetch the user.
              this.$store.dispatch("auth/fetchUser").then(() => {
                // Fetch the user roles
                this.$store.dispatch("auth/fetchUserRoles").then((roles) => {
                  const { success, data } = roles;

                  if (success) {
                    this.$laravel.setRoles(data);
                  }
                });

                // Fetch the user permissions
                this.$store
                  .dispatch("auth/fetchUserPermissions")
                  .then((permissions) => {
                    const { success, data } = permissions;

                    if (success) {
                      this.$laravel.setPermissions(data);
                    }
                  });

                this.loginForm.reset();
                // Redirect home.
                this.$router.push({ name: 'dashboard' });
              });
            }
          });
        } else {
          return false;
        }
      });
    },
  },

  mounted(){
    this.loginForm.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
  }
};
</script>

<style scoped>
  #login{
    min-width: 320px;
  }

  #login-logo{
    width: 50%;
    /* max-width: 100%; */
  }

  *{
    transition: width .3s;
  }
</style>
