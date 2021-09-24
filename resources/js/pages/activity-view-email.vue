<template>

    <el-row :gutter="24" style="margin-top:60px; width: 100%">

      <!-- <el-col :span="12" :offset="6">
        <div class="grid-content bg-purple">{{emailData}}</div>
      </el-col> -->

      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <el-row :gutter="20" style="text-align: center">
          <img :src="emailData['custom_logo'] ? emailData['custom_logo'] : '/images/traleado-logo.png'" class="logo" alt="Traleado Logo" width="200">
        </el-row>
      </el-col>


      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" style="box-shadow: rgba(0, 0, 0, 0.12) 0px 2px 4px, rgba(0, 0, 0, 0.04) 0px 0px 6px; padding:50px; padding-top: 30px; padding-bottom:30px">

        <el-row :gutter="24" style="padding-bottom:0px">
          <h1 class="center">{{ emailData['title'] }}</h1>
        </el-row>

        <el-row :gutter="24" v-if="emailData['message']">
          <p style="color: #718096;" v-html="emailData['message']">{{ emailData['message'] }}</p>
        </el-row>

        <el-row :gutter="24" v-if="emailData['lead_id']">
          <b>{{ $t('lead_id') }}: </b>{{ emailData['lead_id'] }}
        </el-row>

        <el-row :gutter="24" v-if="emailData['reason']">
          <b>{{ $t('email_reason') }}: </b>{{ emailData['reason'] }}
        </el-row>

        <el-row :gutter="24" v-if="emailData['comments']">
          <b>{{ $t('email_comments') }}: </b>{{ emailData['comments'] }}
        </el-row>

        <el-row :gutter="24" v-if="emailData['org'] && emailData['org']['lead_id']">
          <p>
          <b>{{ $t('email_org') }}</b> <br />
          {{ $t('lead_id') }}: {{ emailData['org']['lead_id'] }} <br />
          {{ $t('email_org_name') }}: {{ emailData['org']['name'] }} <br />
          {{ $t('email_org_phone') }}: {{ emailData['org']['contact_number'] }} <br />
          {{ $t('email_org_email') }}: {{ emailData['org']['email'] }}
          </p>
        </el-row>

        <el-row :gutter="24" v-if="emailData['inquirer']">
          <p>
          <b>{{ $t('email_inquirer') }}</b> <br />
          {{ $t('email_inquirer_name') }}: {{ emailData['inquirer']['name'] }}<br>
          {{ $t('email_inquirer_address') }}: {{ emailData['inquirer']['address'] }}<br>
          {{ $t('email_inquirer_email') }}: {{ emailData['inquirer']['email'] }}<br>
          </p>
        </el-row>

        <el-row :gutter="24" v-if="emailData['installed'] && emailData['installed']['meters_gutter_edge']">
          <p>
          <b>{{ $t('email_installed') }}</b> <br />
          {{ $t('email_meters_gutter_edge') }}: {{ emailData['installed']['meters_gutter_edge'] }} <br />
          {{ $t('email_meters_valley') }}: {{ emailData['installed']['meters_valley'] }} <br />
          </p>
        </el-row>

        <el-row :gutter="24" v-if="emailData['date_installed']">
          <p>
          <b>{{ $t('email_date_installed') }}</b> <br />
          <b>{{ $t('email_date_installed') }}: </b> {{ emailData['date_installed'] | moment("DD/MM/YYYY") }}<br />
          </p>
        </el-row>

        <el-row :gutter="24" v-if="emailData['lead_history_link']">
          <div class="center">
            <el-button class="center" @click="clickButton(emailData['lead_history_link'])" type="primary">{{ $t('email_lead_update_button') }}</el-button>
          </div>
          <div class="center">
            {{ $t('email_lead_update_message') }}
          </div>
        </el-row>

        <el-row :gutter="24" v-if="emailData['login_link']">
          <div class="center">
            <el-button class="center" @click="clickButton(emailData['login_link'])" type="primary">{{ $t('email_login_button') }}</el-button>
          </div>
        </el-row>

      </el-col>

      <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
        <el-row :gutter="24">
          <div class="center" style="margin-top:40px; color: #3d4852; font-size: 11px">
            © {{ year }} {{ appName }}. All rights reserved.
          </div>
        </el-row>
      </el-col>
    </el-row>

</template>

<script>
import { mapGetters } from "vuex";

export default {
  layout: "basic",
  computed: mapGetters({
    emailData: "activitylogs/emailData",
  }),

  data: () => ({
    appName: window.config.appName,
    year: new Date().getFullYear()
  }),

  methods:{
    clickButton(url){
      this.$router.push(url)
    },

    dateFormat(date){
      const now = new Date();
      const date_format = Date.parse(date);

      return date_format;
    }
  },

  beforeMount() {
    const activity_id = this.$route.params.activity_id ? this.$route.params.activity_id : null;

    this.$store.dispatch("activitylogs/fetchActivityEmailData", activity_id);
  },
}
</script>
<style scoped>
.main{
  box-shadow: rgba(0, 0, 0, 0.12) 0px 2px 4px, rgba(0, 0, 0, 0.04) 0px 0px 6px; padding:50px; padding-top: 30px; padding-bottom:30px
}
h1{
  box-sizing: border-box;
  font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
  color: #3d4852;
  font-size: 18px;
  font-weight: bold;
  margin-top: 0;
  padding-bottom: 0;
  text-align: center;
}
.el-row{
  padding-bottom: 10px;
}

p{
  box-sizing: border-box;
  font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
  font-size: 16px;
  line-height: 1.5em;
  margin-top: 0;
  text-align: left;
  color: #500050;
}

.center{
  text-align: center;
}
</style>
