<template>
    <div class="tag-container" :style="{ background: setColor(color) }">
        <div v-if="escalation_level" class="escalation-text">
            {{escalation_level}}
        </div>

        <div v-else>
          <div class="escalation-text" v-if="(checkLeadType(active_escalation) || !active_escalation.expiration_date) && escalation_status !== 'Declined'">
            {{escalation_status}}
          </div>

          <el-row type="flex" justify="center" :gutter="24" v-else>
            <el-col :span="7" class="">
              <div class="escalation-text" >
                Waiting
              </div>
            </el-col>
            <el-col :span="1">
              <div class="vl" >
              </div>
            </el-col>
            <el-col :span="13">
              <countdown
                :time="expirationDate(active_escalation)"
              >
                <template slot-scope="props">
                  <div class="timer-content">
                    <div class="timer-item">
                      <p>{{ timerFormat(props.days) }}
                      </p>
                      <span>days</span>
                    </div>
                    <div class="timer-sep">
                      :
                    </div>
                    <div class="timer-item">
                      <p>{{ timerFormat(props.hours) }}
                      </p>
                      <span>hrs</span>
                    </div>
                    <div class="timer-sep">
                      :
                    </div>
                    <div class="timer-item">
                      <p>{{ timerFormat(props.minutes) }}
                      </p>
                      <span>mins</span>
                    </div>
                    <div class="timer-sep">
                      :
                    </div>
                    <div class="timer-item">
                      <p>{{ timerFormat(props.seconds) }} </p>
                      <span>sec</span>
                    </div>
                  </div>
                </template>
              </countdown>
            </el-col>
          </el-row>
        </div>
    </div>
</template>

<script>
import moment, { now } from "moment-timezone";
import { mapGetters } from "vuex";

export default {
  name: 'EscalationTag',

  middleware: 'auth',

  props: {
    color: { type: String, default: '' },
    escalation_level: { type: String, default: '' },
    escalation_status: { type: String, default: '' },
    show_timer: { type: Boolean, default: false },
    active_escalation: { default: null }
  },
  computed: {
    ...mapGetters({
      leadForm: "leads/leadForm",
      leadFormRules: "leads/leadFormRules",
      loading: "leads/loading",
      leadTypes: "leads/leadTypes",
      user: "auth/user",
      lead: "leadhistory/lead",
      histories: "leadhistory/histories",
      metersForm: "leadhistory/metersForm",
      notifications: "leadhistory/notifications",
      org_notifications: "leadhistory/org_notifications",
      reassignDialogVisible: "leadhistory/reassignDialogVisible",
      sendNotificationDialogVisible: "leadhistory/sendNotificationDialogVisible",
      sendEnquirerDetailsDialogVisible: "leadhistory/sendEnquirerDetailsDialogVisible",
      activeEscalation: "leadhistory/active_escalation",
      escalationModals: "leadescalation/escalationModals"
    }),

    currentLead(){
      console.log('computed.currentLead')
    },
  },

  data: () => ({
  }),


  beforeMount() {
  },
  methods: {
    setColor(color) {
      switch (color) {
        case 'blue':
          return '#EBF4F9'

        case 'orange':
          return '#FFEFE6'

        case 'yellow':
          return '#FEFAE7'

        case 'red':
          return '#F9E9E9'

        case 'green':
          return '#EBFAF3'

        case 'purple':
          return '#EFECF3'
          
        case 'black':
        default:
          return '#EAEAEA'
      }
    },

    timerFormat(time) {
      let timeLength =  (''+time).length;
      if (time) {
        if (timeLength < 2) {
          return '0' + time
        } else {
          return time
        }
      } else {
        return '00';
      }
    },

    expirationDate(row) {
      const now = moment(new Date()).tz(this.user.address.timezone);
      const expirationDate = moment(row.expiration_date).tz(
        this.user.address.timezone
      );
      const expirationValue =
        expirationDate.diff(now) > 0 ? expirationDate.diff(now) : 0;

      return expirationValue;
    },

    checkLeadType(escalation) {
      console.log(escalation)
      if (escalation.lead.customer_type !== 'Supply & Install') {
        return true
      } else {
        return false
      }
    }

  }
};
</script>

<style lang="scss">
  .tag-container {
    min-width: 100%;
    padding: 10px 6px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    text-align: center;
  }

  .vl {
    content: '';
    width: 0;
    height: 100%;
    border: 1px solid #303133;
    top: 0;
  }
  .escalation-text {
    font-size: 14px;
    height: 100%;
    margin: auto;
    justify-content: center;
    align-items: center;
    display: flex;
  }
  .timer-content {
    display: inline-flex;
    .timer-item {
      min-width: 1.5rem;
      padding: 0px 2px;
      position: relative;
      p {
        font-size: 16px;
        margin-bottom: 22px;
      }

      span {
        font-family: roboto;
        font-size: 10px;
        font-weight: 300;
        position: absolute;
        margin-left: auto;
        margin-right: auto;
        left: 0;
        right: 0;
        bottom: 0;
        text-align: center;
      }
    }
    .timer-sep {
      font-size: 16px;
      padding: 0px 6px;
    }
  }
</style>
