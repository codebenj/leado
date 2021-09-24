<template>
  <el-container class="gradient-pastel-blue w-100 h-100-vh">
    <el-row type="flex" class="w-100 jc-center ai-center">
      <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4" class="text-center">
        <img
          src="/app-assets/img/traleado-logo.png"
          class="auth-logo"
          :alt="appName"
          width="250"
        />
        <el-button
          class="m-t-md"
          type="primary"
          @click="$router.push({ name: 'login' })"
          :loading="loading"
          >Login</el-button
        >
      </el-col>
    </el-row>
  </el-container>
</template>

<script>
import MasterLayout from "~/layouts/master";
import { mapGetters } from "vuex";

export default {
  layout: "basic",
  middleware: "guest",

  metaInfo() {
    return { title: this.$t("home") };
  },

  data: () => ({
    appName: window.config.appName,
  }),

  components: {
    MasterLayout,
  },

  computed: mapGetters({
    authenticated: "auth/check",
    loading: "auth/loading",
  }),
};
</script>

<style scoped>
  .auth-logo{
    width: 100%;
    max-width: 100%;
  }
</style>