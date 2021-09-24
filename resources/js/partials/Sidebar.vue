<template>
  <el-aside id="cs-aside" width="120">
    <el-menu
      id="cs-menu"
      background-color="#f2f6fc"
      text-color="#303133"
      active-text-color="#303133"
      :router="true"
      :default-active="$route.path"
    >
      <el-menu-item class="logo-item close-aside-button-container">
        <span id="close-aside-button" @click="closeAside">&times;</span>
      </el-menu-item>
      <el-menu-item
        class="logo-item"
        index="1"
        :route="{ name: 'dashboard' }"
      >
        <!-- logo here -->
        <template slot="title">
          <div class="sidebar-header">
            <div class="logo clearfix">
              <a href="/" class="logo-text">
                <div class="logo-img">
                  <img
                    src="/app-assets/img/traleado-logo-small.svg"
                    :alt="appName"
                  />
                </div>
              </a>
            </div>
          </div>
        </template>
      </el-menu-item>
      <!-- ends logo here -->

      <!-- dynamic menu starts here -->
      <el-menu-item
      class="px-0"
        v-for="(menu, idx) in navigation.side"
        :key="idx"
        @click.native="
          $router.push({ name: menu.routeName }).catch((err) => {})
        "
      >
        <!-- <i :class="menu.iconClass"></i> -->
        <img class="svg-icons" :src="`/app-assets/img/ico/${menu.imgIcon}`" />

        <span class="m-l-md">{{ menu.name }}</span>
      </el-menu-item>
      <!-- ends dynamic -->
    </el-menu>
  </el-aside>
</template>
<script>
import NotificationWidget from "~/partials/NotificationWidget";
import { mapGetters } from "vuex";
import $ from "jquery";

export default {
  name: "Sidebar",
  components: {
    NotificationWidget,
  },

  data: () => ({
    appName: window.config.appName,
  }),

  computed: mapGetters({
    navigation: "auth/navigation",
  }),

  mounted() {
    this.$store.dispatch("auth/fetchUserMenus");

    if(screen.width <= 1023){
      var el_main = document.querySelector(".el-main");
      el_main.onclick = function(){
        var x = document.getElementById("cs-aside");
        var y = document.getElementById("cs-menu");
        var z = document.getElementById("close-aside-button");
        x.style.transition = "width .3s";
        y.style.transition = "width .3s";
        z.style.display = "none";

        x.style.width = "0px";
        y.style.width = "0px";
      }
    }
  },
  methods: {
    closeAside(){
      var x = document.getElementById("cs-aside");
      var y = document.getElementById("cs-menu");
      var z = document.getElementById("close-aside-button");
      x.style.transition = "width .3s";
      y.style.transition = "width .3s";
      z.style.display = "none";

      x.style.width = "0px";
      y.style.width = "0px";
    },
    async logout() {
      // Log out the user.
      await this.$store.dispatch("auth/logout");

      // Redirect to login.
      this.$router.push({ name: "login" });
    },

    handleOpen(key, keyPath) {
      // console.log(key, keyPath);
    },

    handleClose(key, keyPath) {
      // console.log(key, keyPath);
    },
  },
};

window.onresize = function(){
  var x = document.getElementById("cs-aside");
  var y = document.getElementById("cs-menu");
  var z = document.getElementById("close-aside-button");
  z.style.display = "none";

  if(screen.width <= 1024){
    x.style.width = "0px";
    y.style.width = "0px";
  }
  else if(screen.width > 1024){
    x.style.width = "120px";
    y.style.width = "120px";
  }
}
</script>

<style scoped>
  #close-aside-button{
    display: none;
    font-size: 34px !important;
  }

  .el-menu .el-menu-item:first-of-type {
      margin-bottom: 0px;
  }

  .el-menu .el-menu-item {
      font-size: 17px;
      display: flex;
      font-weight: normal;
      padding: 0px !important;
  }

  .el-menu .el-menu-item img {
      margin: auto;
  }

  .logo-item{
    padding: 0px !important;
    margin-bottom: 8px;
  }

  .el-menu .el-menu-item span {
      margin: auto;
      line-height: 30px;
  }
</style>
