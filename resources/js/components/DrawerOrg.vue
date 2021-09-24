<template>
  <el-row :gutter="24" class="mx-0 mt-3 drawer-container" v-if="active_escalation">
    <el-card class="box-card b-none pb0" shadow="never">
      <fa icon="times" fixed-width class="clickable drawerClose" @click="closeDrawer" />
      <el-col :span="24" align="start" class="mobile-icons p-0" v-if="hideEnquirerInformation(active_escalation.escalation_status, user)">
        <img src="/app-assets/img/phone.png" class="clickable" v-if="hasData( 'call' )" @click="iconAction( 'call' )" />
        <img src="/app-assets/img/sms.png" class="clickable" v-if="hasData( 'sms' )" @click="iconAction( 'sms' )" />
        <img src="/app-assets/img/email.png" class="clickable" v-if="hasData( 'email' )" @click="iconAction( 'email' )" />
      </el-col>
      <el-col :xs="24" :sm="24" :md="14" :lg="14" :xl="14" class="mt-4">
        <h1>
          <span>{{ title }}</span>
        </h1>
      </el-col>
      <el-col :span="10" align="end" class="desktop-icons" v-if="hideEnquirerInformation(active_escalation.escalation_status, user)">
        <img src="/app-assets/img/phone.png" class="clickable" v-if="hasData( 'call' )" @click="iconAction( 'call' )" />
        <img src="/app-assets/img/sms.png" class="clickable" v-if="hasData( 'sms' )" @click="iconAction( 'sms' )" />
        <img src="/app-assets/img/email.png" class="clickable" v-if="hasData( 'email' )" @click="iconAction( 'email' )" />
      </el-col>
        <el-col :xs="24" :sm="24" :md="( active_escalation ) ? 16 : 24" :lg="( active_escalation ) ? 17 : 24" :xl="( active_escalation ) ? 17 : 24">
          <!-- Escalation Tags -->
          <el-row :gutter="24" class="mb-3 mt-5">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="el-tags-flex">
              <el-tag
                size="medium"
                type="primary"
                class="zoom-25"
                v-if="active_escalation"
                :class="active_escalation.color"
                effect="dark"
                disable-transitions
                >{{ active_escalation.escalation_level }}</el-tag
              >

              <el-tooltip
                v-if="active_escalation"
                :content="getTooltip(active_escalation)"
                placement="top"
                :disabled="!getTooltip(active_escalation)"
                popper-class="font-size-14"
              >
                <el-tag
                  size="medium"
                  type="primary"
                  class="escalation zoom-25 ml-1"
                  v-if="active_escalation"
                  :class="active_escalation.color"
                  effect="dark"
                  disable-transitions
                >
                  {{ active_escalation.escalation_status }}

                  <countdown
                    v-if="expirationDate(active_escalation)"
                    :time="expirationDate(active_escalation)"
                  >
                    <template slot-scope="props">
                      {{ timerFormat(props) }}
                    </template>
                  </countdown>
                </el-tag>
              </el-tooltip>
            </el-col>
          </el-row>

          <el-col :xs="24" :sm="24" :md="18" :lg="10" :xl="10" >
            <el-row :gutter="24" v-if="user && active_escalation" v-show="hideEnquirerInformation(active_escalation.escalation_status, user)">
              <!-- Enquirere Details -->
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="lead" class="detail-details mb-3">
                <span class="drawer-tabs-title">Enquirer Details</span>
                <p class="detail">{{ checkData( lead.customer.first_name ) + " " + checkData( lead.customer.last_name ) }}</p>
                <p class="detail">{{ checkData( lead.customer.contact_number ) }}</p>
                <p class="detail">{{ checkData( lead.customer.email ) }}</p>
                <p class="detail">{{ checkData( lead.customer.address.suburb ) + " " + checkData( lead.customer.address.address ) }}</p>
                <p class="detail">{{ checkData( lead.customer.address.state ) + " " + checkData( lead.customer.address.postcode ) }}</p>
              </el-col>
            </el-row>
          </el-col>

          <!-- Details & History Content -->
          <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" class="no-padding" v-if="active_escalation">
            <p class="detail mt-4 mt0-b3">Lead ID #{{ ( lead.lead_id ) ? parseInt( lead.lead_id ) : id }}</p>
          </el-col>
        </el-col>
      <el-col :xs="24" :sm="24" :md="8" :lg="6" :xl="6" class="update-btn">
        <div type="flex" justify="end" class="mb-1">
          <el-button
            dusk="update-status"
            class="w-100 lead-btn-top"
            type="primary"
            @click="updateStatus()"
          >Update Status</el-button
          >
        </div>
      </el-col>
    </el-card>
    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
      <el-card class="box-card b-none tab-pane-content pt0" shadow="never">
        <el-tabs type="card" id="drawer-tabs">
          <el-tab-pane label="Lead History" class="py-3 tabs-pane">
            <el-row>
              <!-- Meters Form -->
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12" class="detail-details" v-if="metersForm.id">
                <!-- actual meters -->
                <MetersForm :isDrawer="true" />
              </el-col>
              <SliderGesture v-if="user.user_role.name == 'organisation' && isMobile" />

              <!-- lead history -->
              <el-row :gutter="24" class="m-b-md">
                <el-col
                  :xs="24"
                  :sm="24"
                  :md="24"
                  :lg="24"
                  :xl="24"
                  v-if="histories"
                >
                  <div class="clearfix">
                    <span class="drawer-tabs-title">
                      Lead History
                    </span>
                  </div>

                  <data-tables
                    :data="histories"
                    :total="histories.length"
                    :table-props="tableProps"
                    :pagination-props="{ pageSizes: [10, 15, 20] }"
                  >
                    <el-table-column
                      label="Escalation Level"
                      prop="escalation_level"
                      width="250"
                      class-name="org-table-head"
                    >
                      <template slot-scope="{ row }">
                        <el-tag
                          size="medium"
                          type="primary"
                          :class="row.color"
                          effect="dark"
                          disable-transitions
                          >{{ row.escalation_level }}</el-tag
                        >
                      </template>
                    </el-table-column>

                    <el-table-column
                      label="Escalation Status"
                      prop="escalation_status"
                      width="250"
                      class-name="org-table-head"
                    >
                      <template slot-scope="{ row }">
                        <el-tag
                          size="medium"
                          type="primary"
                          :class="row.color"
                          effect="dark"
                          disable-transitions
                          >{{ row.escalation_status }}</el-tag
                        >
                      </template>
                    </el-table-column>

                    <el-table-column
                      label="Reason"
                      prop="reason"
                      width="250"
                      class-name="org-table-head">
                      <template slot-scope="{ row }">
                        <label v-if="row.escalation_status == 'Won'">{{
                          row.metadata.response
                        }}</label>
                        <label v-else>{{ reasons(row) }}</label>
                        <br />
                        <label v-if="row.escalation_status == 'Won'"
                          ><b v-if="row.gutter_edge_meters"
                            >Gutter Edge: {{ row.gutter_edge_meters }}m</b
                          >
                          <b v-if="row.valley_meters"
                            >Valley: {{ row.valley_meters }}m</b
                          ></label
                        >
                        <el-tooltip
                          v-if="row.comments"
                          :content="row.comments"
                          placement="top"
                        >
                          <i class="el-icon-s-comment"></i>
                        </el-tooltip>
                      </template>
                    </el-table-column>

                    <el-table-column
                      label="Date"
                      prop="created_at"
                      width="250"
                      class-name="org-table-head">
                      <template slot-scope="{ row }">
                        {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                      </template>
                    </el-table-column>
                  </data-tables>
                </el-col>
              </el-row>
            </el-row>
          </el-tab-pane>
          <el-tab-pane label="Job Details" class="py-3">
            <template v-if="histories">
              <el-row :gutter="24" class="m-b-md mt-3">
                <el-col
                  :xs="24"
                  :sm="24"
                  :md="24"
                  :lg="24"
                  :xl="24"
                >
                <span class="drawer-tabs-title">Job Details</span>
                </el-col>
                <el-col
                  :xs="24"
                  :sm="24"
                  :md="24"
                  :lg="24"
                  :xl="24"
                  class="details-form"
                >
                    <el-button
                      type="primary"
                      class="fl-right"
                      @click="addNewJobDetails"
                    >Add Job Details</el-button>

                    <el-form
                      :model="saleForm"
                      :rules="saleRules"
                      status-icon
                      label-position="top"
                      ref="saleForm"
                      label-width="120px"
                      class="fl-right m-r-md"
                      style="margin-top:-10px"
                      id="saleForm"
                    >
                    <el-form-item
                      label=""
                      prop="reason"
                      :error="
                      saleForm.errors.errors.sale ? saleForm.errors.errors.sale[0] : '' "
                    >
                      <el-input
                        type="number"
                        v-model="saleForm.sale"
                        placeholder="0.00"
                      >
                        <template slot="prepend">$</template>
                        <template slot="append">
                            <el-button type="primary" @click="saveSale('saleForm')"
                            >Save</el-button
                            >
                        </template>
                      </el-input>
                    </el-form-item>
                  </el-form>
                </el-col>
              </el-row>

              <data-tables-server
                :data="jobDetails"
                :total="total"
                :loading="loading"
                :pagination-props="{ pageSizes: [10, 15, 20, 50, 100] }"
                @query-change="loadMore"
              >

                <el-table-column
                  label="Comment"
                  prop="comments"
                  class-name="org-table-head" />

                <el-table-column
                  label="Date"
                  prop="created_at"
                  class-name="org-table-head">
                  <template slot-scope="{ row }">
                    {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                  </template>
                </el-table-column>

                <el-table-column
                  label=""
                  width="55"
                  class-name="org-table-head">
                  <template slot-scope="row">
                    <el-dropdown trigger="click">
                      <span class="el-dropdown-link">
                        <i class="el-icon-more"></i>
                      </span>
                      <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item
                          icon="el-icon-edit"
                          @click.native="editJobDetails(row)"
                          >Edit</el-dropdown-item
                        >
                        <el-dropdown-item
                          icon="el-icon-delete"
                          @click.native="deleteJobDetails(row)"
                          >Delete</el-dropdown-item
                        >
                      </el-dropdown-menu>
                    </el-dropdown>
                  </template>
                </el-table-column>
              </data-tables-server>
            </template>
          </el-tab-pane>
        </el-tabs>
      </el-card>
    </el-col>

    <template v-if="user.user_role.name == 'organisation'">
      <el-dialog
        v-dialogDrag
        ref="dialog__wrapper"
        :title="dialogTitle"
        :visible.sync="jobDetailsDialogVisible"
        :show-close="false"
        :close-on-click-modal="false"
        append-to-body
        width="30%"
      >
        <JobDetailsForm v-bind:queryInfo="queryInfo" :id="id" />
      </el-dialog>
    </template>
    <EscalationModal />

    <el-dialog
      v-dialogDrag
      ref="dialog__wrapper"
      :visible.sync="dialogIconActionVisible"
      width="30%"
      append-to-body>
      <h5 class="text-center">Send {{ dialogIconActionType }}</h5>

      <el-row :gutter="20">
        <el-form :model="iconForm" :rules="iconFormRules" status-icon label-position="top" ref="iconForm" label-width="120px">
          <el-col :span="24">
            <el-form-item label="Message" prop="message">
              <el-input
                type="textarea"
                :autosize="{ minRows: 4 }"
                placeholder="Enter a message"
                v-model="iconForm.message"
                maxlength="255"
                show-word-limit>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="24">
              <el-button type="primary" class="w-100" @click="send()" :loading="loadingEscalation">Send</el-button>
          </el-col>
        </el-form>
      </el-row>
    </el-dialog>
  </el-row>
</template>
<script>

import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import JobDetailsForm from "./../pages/organisation/jobdetails";
import EscalationModal from "~/components/escalations/main";
import Section from "~/components/Section";
import MetersForm from "~/pages/admin/leads/meters.vue";
import { hideEnquirerInformation } from "~/helpers";

import SliderGesture from '~/components/SliderGesture'

export default {
  name: "DrawerOrg",

  props: {
    id: { type: Number, default: null },
    title: { type: String, default: "Lead Overview" }
  },

  components: {
    DataTables,
    DataTablesServer,
    Section,
    JobDetailsForm,
    EscalationModal,
    MetersForm,
    SliderGesture,
  },

  data() {
    return {
      tableProps: {
        rowClassName: function(row, index) {
          const { color } = row.row;

          return color;
        }
      },

      pageTitle: "Lead Overview",
      dialogTitle: "Add New Job Details",
      lead_id: "",
      queryInfo: {},

      dialogVisible: false,

      dialogIconActionType: null,
      dialogIconActionVisible: false,

      isMobile: false
    };
  },

  computed: mapGetters({
    user: "auth/user",
    lead: "leadhistory/lead",
    histories: "leadhistory/histories",
    jobDetails: "leadhistory/jobDetails",
    metersForm: "leadhistory/metersForm",
    total: "leadhistory/total",
    loading: "leadhistory/loading",
    saleForm: "leadhistory/saleForm",
    saleRules: "leadhistory/saleRules",
    jobDetailsDialogVisible: "leadhistory/jobDetailsDialogVisible",
    active_escalation: "leadhistory/active_escalation",
    iconForm: "leadescalation/iconForm",
    iconFormRules: 'leadescalation/iconFormRules',
    loadingEscalation: "leadescalation/loading",
  }),

  methods: {
    reasons(row){
      if ( row.reason == 'This lead has been LOST' ) {
        let reason = row.metadata['other_reason'];
        reason += row.metadata.indicate_reason === '' ? ' - ' + row.metadata.indicate_reason : '';
        return reason
      } else if ( row.reason == 'This lead is currently Work in Progress' ) {
        if ( row.metadata.other_reason == 'Other' ) {
          return row.metadata.indicate_reason

        } else {
          return row.metadata.other_reason
        }

      } else {
        return row.reason
      }
    },

    async loadMore(queryInfo) {
      const leadId = this.id;
      this.queryInfo = queryInfo;

      this.$store.dispatch("leadhistory/fetchJobDetails", {
        queryInfo: this.queryInfo,
        leadId: leadId
      });
    },

    modal(escalationLevel, escalationStatus) {
      switch (escalationLevel) {
        case "Accept Or Decline":
          if (escalationStatus == "Declined-Lapsed") {
            return "declined-lapse";
          }
          else if(escalationStatus == "Declined"){
            return "declined";
          }
          return "aod";
        case "Confirm Enquirer Contacted":
          if (escalationStatus == "Declined"){
            return "cec-declined";
          }
          else if(escalationStatus == "Discontinued"){
            return "discontinued";
          }
          return "cec";
        case "In Progress":
          return "inprogress";
        case "Won":
          return "won";
        case "Lost":
          return "lost";
        case "Abandoned":
          return "abandoned";
        case "Discontinued":
          return "discontinued";
        case "Declined":
          return "declined";
        default:
          return "finished";
      }
    },

    updateStatus() {
      // Pass special value
      this.active_escalation.lead_ip_first_extension_created_date = this.lead
        .lead_ip_first_extension_created_date
        ? this.lead.lead_ip_first_extension_created_date
        : null;

      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: this.active_escalation,
        showEscalation: true,
        user: this.user,
        modal: this.modal(
          this.active_escalation.escalation_level,
          this.active_escalation.escalation_status
        )
      });
    },

    addNewJobDetails() {
      const leadId = this.id;
      this.$store.dispatch("leadhistory/editJobDetails", {
        leadId,
        close: true,
        form: "job_details"
      });
    },

    editJobDetails({ row }) {
      // dispatch the modal open
      this.dialogTitle = "Edit Job Details";
      this.$store.dispatch("leadhistory/editJobDetails", {
        data: row,
        close: true,
        form: "job_details"
      });
    },

    deleteJobDetails({ row }) {
      Swal.fire({
        title: "Are you sure to delete this?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then(result => {
        if (result.value) {
          return this.$store
            .dispatch("leadhistory/deleteJobDetails", row.id)
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success"
                });
              }
            });
        }
      });
    },

    saveSale(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leadhistory/saveSale")
            .then(({ success, message, errors }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success"
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    getLeadHistory() {
      const leadId = this.id;

      if (leadId) {
        this.lead_id = leadId;
        this.$store.dispatch("leadhistory/getLeadHistory", leadId);
      }
    },

    closeDrawer() {
      this.$emit("closeDrawer", this.id);
    },

    getTooltip(row) {
      const type = [1, 2].includes(this.user.role_id) ? "admin" : "org";
      return row.expiration_text && row.expiration_text[type]
        ? row.expiration_text[type]
        : "";
    },

    timerFormat(props) {
      if (props.days > 0)
        return `${props.days} days ${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.hours > 0)
        return `${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.minutes > 0) return `${props.minutes}m ${props.seconds}s`;
      else if (props.seconds > 0) return `${props.seconds}s`;
    },

    expirationDate(row) {
      const now = new Date();
      const expirationDate = Date.parse(row.expiration_date);
      const expirationValue = expirationDate > now ? expirationDate - now : 0;

      return expirationValue;
    },

    iconAction( action ) {
      let contact_number = this.active_escalation.lead.customer.contact_number
      let email = this.active_escalation.lead.customer.email

      if ( action == 'call' ) {
        window.location.href=`tel:${contact_number}`
      }

      if ( action == 'sms' ) {
        window.location.href=`sms:${contact_number}`
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

      /* this.dialogIconActionType = action
        this.dialogIconActionVisible = true
        this.$store.dispatch( 'leadescalation/setIconForm', { contact_number: contact_number, email: email } )
        this.$store.dispatch( 'leadescalation/setIconFormType', action ) */
    },

    send() {
      let action = this.dialogIconActionType
      let title = ( action == 'sms' ) ? 'SMS has been sent!' : 'Email has been sent!'

      this.$refs.iconForm.validate( ( valid ) => {
        if ( valid ) {
          this.$store.dispatch( 'leadescalation/sendIconAction' ).then( res => {
            Swal.fire({
              type: 'success',
              title: 'Success',
              text: title
            })

            this.dialogIconActionVisible = false
          } )
        } else {
          return false
        }
      } )

    },

    checkData( value ) {
      return value ? value : ''
    },

    hasData( type ) {
      let contact_number = this.active_escalation.lead.customer.contact_number
      let email = this.active_escalation.lead.customer.email

      if ( type == 'call' || type == 'sms' ) {
        return contact_number ? true : false
      } else {
        return email ? true : false
      }
    },

    hideEnquirerInformation: hideEnquirerInformation
  },

  beforeMount() {
    this.getLeadHistory();

    setInterval( () => {
      let width = window.screen.width
      if ( width <= 768 ) {
        this.isMobile = true
      } else {
        this.isMobile = false
      }
    }, 500 )
  },
};
</script>

<style lang="scss" scoped>
.update-btn {
  float: right;
  padding-right: 20px !important;
}

.el-tooltip__popper {
  font-size: 14px !important;
}

.mb {
  margin-bottom: 1rem;
}

::v-deep {
  .mb0, .mb0 .el-card__body {
    margin-bottom: 0 !important;
  }

  .mt0, .mt0 .el-card__body {
    margin-top: 0 !important;
  }

  .pb0, .pb0 .el-card__body {
    padding-bottom: 0px !important;
  }

  .pt0, .pt0 .el-card__body {
    padding-top: 0px !important;
  }
}

.m-card-1{
  margin-left: 20px;
}

.mx-0 {
  margin: 0px 7px !important;
}

.mt-3 {
  margin-top: 1em;
}

.mt-4 {
  margin-top: 1.5em;
}

.mt-5 {
  margin-top: 2em;
}

.mt-7 {
  margin-top: 3em;
}

.mt-1 {
  margin-top: 0.5em;
}

.mb-1 {
  margin-bottom: 0.5em;
}

.py-3 {
  padding: 0.5em 0em;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-5 {
  margin-bottom: 2rem !important;
}

.mx-1 {
  margin-left: 0.5rem;
  margin-right: 0.5rem;
}

.el-tags-flex {
  display: flex;
  align-items: center;
}

.ml-1 {
  margin-left: .5em;
}

.p-0 {
  padding: 0 !important;
}

.el-tags-col {
  padding-left: unset !important;
  padding-right: unset !important;
}

.desktop-icons {
  width: fit-content;
  float: right;
  position: relative;
}

.mobile-icons {
  display: block;
  text-align: right;
}

.desktop-icons::after {
  content: "";
  width: 100%;
  position: absolute;
  bottom: 0;
  right: 0;
  border-bottom: 1px solid #DCDFE6;
}

@media ( min-width: 481px ) and ( max-width: 1100px ) {
  // .no-metters-col {
  //   width: 50%;
  // }

  .tab-pane-content {
    margin-top: 20px;
  }
}

@media all and ( max-width: 991px ) {
  .el-tags-col {
    margin-top: 1em;
  }

  .el-tags-col .zoom-25 {
    width: 100% !important;
  }

  .tab-pane-content {
    margin-top: 20px;
  }

  .mt0-b3 {
    margin-top: 0 !important;
    margin-bottom: 2rem;
  }
}

@media ( min-width: 769px ) and ( max-width: 906px ) {
  #saleForm {
    margin-right: unset !important;
    margin-top: unset !important;
  }
}

@media all and (max-width: 768px) {
  .m-card-1,
  .m-card-2,
  .m-card-3 {
    margin: 10px 20px;
  }
}

@media all and ( min-width: 769px ) {
  .desktop-icons {
    display: block;
  }

  .mobile-icons {
    display: none;
  }
}

@media all and ( max-width: 769px ) {
  .desktop-icons {
    display: none;
  }

  .mobile-icons {
    display: block;
    text-align: right;
  }
}

@media (min-width: 581px) and (max-width: 767px) {
  // .detail-details {
  //   width: 50% !important;
  // }
}

@media all and (max-width: 426px) {
  .r-btn-reset {
    float: none;
  }

  .clearfix h4 {
    display: flex !important;
    flex-direction: column !important;
  }

  .clearfix h4 button {
    width: 100% !important;
    margin-top: 1rem !important;
  }
}

@media screen and ( max-width: 543px ) {
  #saleForm {
    margin-top: .5rem !important;
    margin-right: unset !important;
    width: 100%;
  }
}
</style>
