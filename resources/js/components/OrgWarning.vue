<template>
  <div>
        <template v-if="isNotice">
          <el-row :gutter="24">
            <el-col :span="20" :offset="2" class="notice-head">
              <img src="/app-assets/img/warning_icon.png" />
              <h1 class="notice-heading">Notice</h1>
            </el-col>
            <el-col :span="20" :offset="2" class="notice m-t">
              <p class="notice-details" v-html="warning" />
            </el-col>

            <el-col
              :xs="{ span: 13, offset: 5 }"
              :sm="{ span: 13, offset: 5 }"
              :md="{ span: 14, offset: 5 }"
              :lg="{ span: 11, offset: 7 }"
              :xl="{ span: 9, offset: 8 }"
              class="notice-button">
              <el-button
                  class="w-100"
                  type="primary"
                  @click="$emit( 'handleClose' )">Continue</el-button
                >
            </el-col>
          </el-row>
        </template>

        <template v-if="!isNotice">
          <el-row :gutter="24" class="notice-filter">
            <el-col :xs="0" :sm="2" :md="4" :lg="3" :xl="3" class="notice-img text-center" >
              <img src="/app-assets/img/warning_icon.png" />
            </el-col>
            <el-col :xs="24" :sm="22" :md="20" :lg="18" :xl="15" class="notice">
              <p v-html="notice" />
            </el-col>
          </el-row>
        </template>

    <!-- <template v-else>
      <el-row :gutter="24">
        <el-col :span="20" :offset="2" class="notice-head mb-3">
          <img src="/app-assets/img/warning_icon.png" />
          <h1 class="notice-heading">Notice</h1>
        </el-col>
        <el-col :span="24" class="m-t">
          <p class="text-center" style="mb-3 font-size:1rem">
            As per your request, new leads are currently not being assigned to you.
             Please contact Admin to continue receiving new leads.
          </p>
          <p class="text-center mb-0" style="font-size:1rem">office@leafstopper.com.au</p>
          <p class="text-center mb-1" style="font-size:1rem">02 8211 0211</p>

          <el-col :span="24" align="center" class="desktop-icons">
            <img src="/app-assets/img/phone.png" class="clickable" @click="iconAction( 'call' )" />
            <img src="/app-assets/img/email.png" class="clickable" @click="iconAction( 'email' )" />
          </el-col>
        </el-col>

        <el-col :span="24"
          class="notice-button text-center">
          <el-button
              class=""
              type="primary"
              @click="$emit( 'handleClose' )">Continue</el-button
            >
        </el-col>
      </el-row>
    </template> -->
  </div>
</template>

<script>
export default {
  name: 'OrgWarning',

  props: {
    isNotice: { type: Boolean, default: false }
  },

  data: () => ({
    title: 'NOTICE',
    warning: 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can <br> be assigned',
    notice: 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can be assigned'
  }),

  beforeMount() {
    console.log(this.isNotice)
    setInterval( () => {
      let width = window.innerWidth

      if ( width <= 1287 ) {
        this.warning = 'Please update the status of the highlighted leads on your dashboard so that new leads can be assigned'

      } else  if ( width <= 1366 ) {
        this.warning = 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can be assigned'

      } else if ( width >= 1600 ) {
        this.warning = 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can be assigned'

      } else {
        this.warning = 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can <br> be assigned'
      }

      if ( width <= 1100 ) {
        this.notice = 'Please update the status of the highlighted leads on your dashboard so that new leads can be assigned'

      } else {
        this.notice = 'Please update the status of the highlighted <br> leads on your dashboard so that new leads can be assigned'
      }
    }, 500 )
  },

  mounted: {
    //
  },
  methods: {
    iconAction( action ) {
      this.$emit( 'handleClose' );
      let contact_number = '02 8211 0211'
      let email = 'office@leafstopper.com.au'


      if ( action == 'call' ) {
        window.location.href=`tel:${contact_number}`
      }

      if ( action == 'email' ) {
        if ( ! email ) {
          Swal.fire({
            type: 'info',
            title: 'Opps',
            text: `Can't send email due to customer has no email.`
          })
        } else {
          window.location.href=`mailto:${email}`
        }
      }
    },
  },
}
</script>

<style lang="scss" scoped>
  .mb-0 {
    margin-bottom: 0;
  }

  .mb-3 {
    margin-bottom: 1.5rem;
  }

  .mb-1 {
    margin-bottom: .5rem;
  }

  .d-flex {
    display: flex;
  }

  .d-column {
    flex-direction: column;
  }

  .d-row {
    flex-direction: row;
  }

  .notice-head {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1em;

    & img {
      margin-left: -50px;
    }

    & .notice-heading {
      margin-bottom: 0 !important;
      margin-left: 10px;
      color: #303133;
    }
  }

  .desktop-icons {
    margin: auto;
  }

  .notice-row {
    margin-top: 1em;
  }

  .my-5 {
    margin-bottom: 2em
  }

  .notice {
    background: #FFF48A !important;
    border-radius: 5px;
    color: #4D4F53;
    display: flex;
    align-items: center;

    & p {
      font-size: 15px;
      margin-bottom: 0 !important;
      padding: 10px 0px;
      font-family: 'SF UI Display Light';
    }

    & p.notice-details {
      font-family: 'SF UI Display Light';
    }

    &.m-t {
      margin-top: 10px;
    }
  }
  .notice-button {
    margin-top: 20px;

    & button {
      padding: 15px;
      font-size: 15px;
      box-shadow: 0 5px 5px rgba(0, 0, 0, 0.1) !important;
      letter-spacing: .5px;
      width: 55%;
    }
  }

  .notice-filter {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    margin-top: 10px;
    gap: 10px;

    & .notice-img {
      margin-right: 10px;
      padding: 0px !important;
    }

    & img {
      width: 67px !important;
      align-self: flex-end;
      // margin-top: 15px;
    }
  }

  @media all and ( max-width: 325px ) {
    .notice-filter {
      gap: 5px !important;
      flex-direction: column !important;
      padding: 0px 12px !important;

      & .notice {
        width: 100% !important;
      }
    }
  }

  @media all and ( max-width: 375px ) {
    .notice-filter {
      gap: 15px !important;
      margin-top: 20px !important;
    }
  }

  @media all and ( max-width: 1024px ) {
    .notice-button {

    & button {
      width: 100%;
    }
  }

    .notice-filter {
      & .notice-img {
        & img {
          margin-top: 5px !important;
        }
      }
    }
  }

  @media all and ( max-width: 1100px ) {
    .notice-filter {
      & .notice-img {
        padding-left: 5px !important;
      }
    }
  }

  @media all and ( max-width: 1240px ) {
    /* .notice-filter .notice-img {
      align-self: center;
    } */

    .notice-filter img {
      width: 55px;
    }
  }

  @media all and ( min-width: 1900px ) {
    .notice-filter .notice {
      width: 60% !important;
    }
  }

  @media all and ( min-width: 1600px ) and ( max-widht: 1899px ) {
    .notice-filter .notice {
      width: 75% !important;
    }
  }

  @media all and ( min-width: 1440px ) and ( max-width: 1599px ) {
    .notice-filter {
      gap: unset !important;

      & .notice {
        width: 90% !important;
      }
    }
  }

  @media all and ( min-width: 1600px ) {
    .notice-filter .notice-img {
      margin-right: unset !important;
    }
  }

  @media all and ( min-width: 1900px ) {
    .notice-filter .notice-img img {
      // margin-top: 5px !important;
    }
  }
</style>
