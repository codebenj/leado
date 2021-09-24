<template>
  <el-header height="60px">
    <span id="open-aside-button" @click="openAside">☰</span>
    <span class="username fl-left" v-if="user">
      {{ user.first_name }} {{ user.last_name }}
    </span>

    <el-dropdown
      trigger="click"
      class="jc-center ai-center notification-dropdown clickable"
      @visible-change="readAllNotification"
    >
      <el-badge
        :value="unReadNotification.length"
        :max="99"
        class="badge-notification"

      >
        <img class="svg-icons" src="/app-assets/img/ico/bell.svg" />
      </el-badge>
      <el-dropdown-menu slot="dropdown" class="notification-menu">
        <el-dropdown-item

          v-loading="loading"
          class="notification-item"
          v-for="(notification, index) in unReadNotification"
          :class="addReadOrUnreadClass(notification)"
          :key="index"
          @click.native="viewHistory(notification)"
        >
          <div class="noti-wrapper">
            <el-tag
              size="medium"
              type="primary"
              :class="`${badgeColor(
                notification.metadata.lead_escalation_color
              )}`"
              effect="dark"
              disable-transitions
              >{{ notification.metadata.lead_escalation_status }}</el-tag
            >
            <p class="text-uppercase m-none">
              <strong>{{ notification.title }}</strong>
            </p>
            <p class="description">
              <small>{{ notification.description }}</small>
            </p>
            <p class="m-none">
              <small>{{
                notification.created_at | moment("k:mm DD/MM/YYYY")
              }}</small>
            </p>
          </div>
        </el-dropdown-item>
        <el-dropdown-item class="d-none read-all">Action 1</el-dropdown-item>

        <a
          @click="readAll"
          class="noti-footer primary text-center d-block clickable"
        >
          <small>Read All Notifications</small>
        </a>
      </el-dropdown-menu>
    </el-dropdown>

    <el-dropdown trigger="click" class="clickable">
      <span class="avatar avatar-online">
        <img v-if="user" id="navbar-avatar" :src="user.avatar" alt="avatar" />
      </span>

      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item
          v-for="(menu, idx) in navigation.top"
          :key="idx"
          @click.native="goTo(menu.routeName)"
        >
          <i
            :class="menu.iconClass"
            class="top-menu-icon"
          ></i>

          <span>{{ menu.name }}</span>
        </el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>
  </el-header>
</template>

<script>
import NotificationWidget from "~/partials/NotificationWidget";
import { mapGetters } from "vuex";
import $ from "jquery";
import Cookies from "js-cookie";
import { Bus } from '~/app'

export default {
  name: "Navbar",
  components: {
    NotificationWidget
  },
  computed: {
    ...mapGetters({
      navigation: "auth/navigation",
      user: "auth/user",
      notifications: "notifications/notifications",
      loading: "notifications/loading",
      unReadNotification: "notifications/unReadNotification",
    }),

    readAllNotificationRoute() {
      const admin = [1, 2];
      const others = [3, 4];

      return {
        name: admin.includes(this.user.role_id)
          ? "admin.notifications"
          : "organisation.notifications",
      };
    },

  },
  data() {
    return {
      isIOS: false,
    };
  },
  methods: {
    openAside(){
      var x = document.getElementById("cs-aside");
      var y = document.getElementById("cs-menu");
      var z = document.getElementById("close-aside-button");
      x.style.transition = "width .3s";
      y.style.transition = "width .3s";
      z.style.display = "block";

      x.style.width = "150px";
      y.style.width = "150px";
    },
    readAll() {
      $('.read-all').click();
      this.$router.push(this.readAllNotificationRoute)
    },

    addReadOrUnreadClass(notification) {
      return notification.is_read == 0 ? `unread` : `read`;
    },

    badgeColor(color) {
      return color == "gray" ? "purple" : color;
    },

    async logout() {
      // Log out the user.
      await this.$store.dispatch("auth/logout");

      // Redirect to login.
      this.$router.push({ name: "login" });
    },

    goTo(routeName) {
      if (routeName === "logout") {
        this.logout();
      } else {
        this.$router.push({ name: routeName });
      }
    },

    updateNotification() {
      this.$store.dispatch("notifications/fetchUnreadNotification");
    },

    readAllNotification(event) {
      if (!event) {
        this.$store.dispatch("notifications/readAllNotification");
      }
    },

    viewHistory(notification) {
      if (this.user && notification.metadata && notification.metadata.lead_id) {
        this.$store.dispatch("notifications/readNotification", notification.id);

        if ( this.$route.name == 'dashboard' ) {
          Bus.$emit( 'init_drawer', notification.metadata.lead_id )
        } else {
          Cookies.set( 'lead_id', notification.metadata.lead_id )
          this.$router.push({ name: 'dashboard' })
        }


        /* const adminUser = [1, 2];
        const otherUser = [3, 4];

        const name = adminUser.includes(this.user.role_id)
          ? "admin.leads.history"
          : "organisation.lead";

        this.$router.push({
          name,
          params: {
            id: notification.metadata.lead_id,
          },
        }).catch(err => {}); */

        //dispatch read the notification here
      }
    },

    iOS() {
      let arrs = [
        'iPad Simulator',
        'iPhone Simulator',
        'iPod Simulator',
        'iPad',
        'iPhone',
        'iPod'
      ]

      let iOS = arrs.includes( navigator.platform ) || ( navigator.userAgent.includes( "Mac" ) && "ontouchend" in document )

      if ( iOS ) {
        // Add custom class to body #app for iOS only
        document.querySelector( 'body div#app' ).classList.add( 'ios-device' )

        // add Class to body
        $( 'body' ).addClass( 'ios-body-device' )
      } else {
        // remove Class to body
        $( 'body' ).removeClass( 'ios-body-device' )
      }
      $( 'body' ).addClass( 'ios-body-device' )
    }
  },

  beforeMount() {
    this.$store.dispatch("notifications/fetchUnreadNotification");
  },

  mounted() {

    this.$echo.channel("traleado-global").listen(".notification.created", (data) => {
      if(this.user.user_role && (this.user.user_role.name == 'super admin' || this.user.user_role.name == 'administrator')){
        if(data.notification.metadata.notification_type == 'notification' && data.notification.metadata.to == 'admin'){
          this.unReadNotification.unshift(data.notification)

          this.$notify({
            title: data.notification.title,
            dangerouslyUseHTMLString: true,
            message: '<small>'+data.notification.description+'</small>',
            type: 'info'
          });

        }
      }else if(this.user.user_role && this.user.user_role.name == 'organisation' && data.notification.metadata.to == 'organization' && this.user.organisation_user){
        if(data.notification.metadata.notification_type == 'notification' && this.user.organisation_user.organisation_id == data.notification.metadata.organisation_id){
          this.unReadNotification.unshift(data.notification)

          this.$notify({
            title: data.notification.title,
            dangerouslyUseHTMLString: true,
            message: '<small>'+data.notification.description+'</small>',
            type: 'info'
          });

        }
      }
    });

    this.$echo
      .channel("traleado-global")
      .listen(".notification.custom_"+this.user.id, (data) => {

        this.$notify({
          title: data.data.title,
          dangerouslyUseHTMLString: true,
          message: '<small>'+data.data.body+'</small>',
          type: 'info'
        });

      }
    );


    this.iOS()
  },
};
</script>

<style scoped>
  #open-aside-button{
    cursor: pointer;
  }

  .username{
    font-weight: 100;
    font-size: 17px;
    margin-left: 105px;
  }

  .notification-item{
    background-color: #f2f6fc;
  }

  .description{
    font-size: 16px;
  }

  @media all and (max-width: 768px){
    .username{
      margin-left: auto;
    }
  }

  @media all and (max-width: 664px){
    .notification-menu{
      left: 0px !important;
      max-width: 100%;
    }
  }
</style>
