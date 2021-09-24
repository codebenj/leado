<template>
  <Section className="lead-history" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button type="primary" class="fl-right" @click="updateStatus()"
        >Update Status</el-button
      >
    </template>
    <template v-slot:content>
      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
          <el-card class="box-card m-card-1" v-if="lead">
            <el-tag type="primary" class="dark-blue-gray">Lead Details</el-tag>
            <p class="mb0"><b>Name:</b>&nbsp;&nbsp;{{ `${lead.customer.first_name} ${lead.customer.last_name}` }}</p>
            <p class="detail"><b>Contact Number:</b>&nbsp;&nbsp;{{ `${lead.customer.contact_number}` }}</p>
            <p class="detail mb"><b>Address:</b>&nbsp;&nbsp;{{ `${lead.customer.address.full_address}` }}</p>
          </el-card>
        </el-col>
      </el-row>

       <!-- actual meters -->
      <MetersForm v-if="metersForm.id"/>

      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-none" v-if="histories" shadow="never">
            <div slot="header" class="clearfix">
              <h4>
                Lead History
              </h4>
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

              <el-table-column label="Reason" prop="reason">
                <template slot-scope="{ row }">
                  <label v-if="row.escalation_status == 'Won'">{{ row.metadata.response }}</label>
                  <label v-else>{{ row.reason }}</label>
                  <br />
                  <label v-if="row.escalation_status == 'Won'"><b v-if="row.gutter_edge_meters">Gutter Edge: {{ row.gutter_edge_meters }}m</b> <b v-if="row.valley_meters">Valley: {{ row.valley_meters }}m</b></label>

                  <el-tooltip
                    v-if="row.comments"
                    :content="row.comments"
                    placement="top"
                  >
                    <i class="el-icon-s-comment"></i>
                  </el-tooltip>
                </template>
              </el-table-column>

              <el-table-column label="Date" prop="created_at">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                </template>
              </el-table-column>
            </data-tables>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="24" class="m-b-md">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-card class="box-card b-none" v-if="histories" shadow="never">
            <el-row :gutter="20" class="m-b-md">
              <h4 style="float:left">
                Job Details
              </h4>
                <el-button
                  type="primary"
                  class="fl-right m-r-sm"
                  @click="addNewJobDetails"
                  >Add Job Details</el-button
                >

                <el-form
                  :model="saleForm"
                  :rules="saleRules"
                  status-icon
                  label-position="top"
                  ref="saleForm"
                  label-width="120px"
                  class="fl-right m-r-md"
                  style="margin-top:-10px"
                >
                  <el-form-item
                    label=""
                    prop="reason"
                    :error="
                      saleForm.errors.errors.sale
                        ? saleForm.errors.errors.sale[0]
                        : ''
                    "
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

            </el-row>

            <data-tables-server
              :data="jobDetails"
              :total="total"
              :loading="loading"
              :pagination-props="{ pageSizes: [10, 15, 20, 50, 100] }"
              @query-change="loadMore"
            >
              <!-- <el-table-column
                label="Metres of Gutter Edge"
                prop="meters_gutter_edge"
              />

              <el-table-column label="Metres of Valley" prop="meters_valley" /> -->

              <el-table-column label="Comment" prop="comments" />

              <el-table-column label="Date" prop="created_at">
                <template slot-scope="{ row }">
                  {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
                </template>
              </el-table-column>

              <el-table-column label="" width="55">
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
          </el-card>
        </el-col>
      </el-row>

      <el-dialog
        :title="dialogTitle"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible.sync="jobDetailsDialogVisible"
        :show-close="false"
        append-to-body
        :close-on-click-modal="false"
        width="30%"
      >
        <JobDetailsForm v-bind:queryInfo="queryInfo" />
      </el-dialog>

      <EscalationModal />
    </template>
  </Section>
</template>

<script>
import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import JobDetailsForm from "./jobdetails";
import EscalationModal from "~/components/escalations/main";
import Section from "~/components/Section";
import MetersForm from "~/pages/admin/leads/meters.vue";

export default {
  name: "LeadHistory",
  layout: "master",
  components: {
    DataTables,
    DataTablesServer,
    Section,
    JobDetailsForm,
    EscalationModal,
    MetersForm
  },

  data() {
    return {
      tableProps: {
        rowClassName: function (row, index) {
          const { color } = row.row;

          return color;
        },
      },

      pageTitle: "Lead Overview",
      dialogTitle: "Add New Job Details",
      lead_id: "",
      queryInfo: {}
    };
  },

  computed: mapGetters({
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
  }),

  methods: {
    async loadMore(queryInfo) {
      const leadId = this.$route.params.id ? this.$route.params.id : "";
      this.queryInfo = queryInfo

      this.$store.dispatch("leadhistory/fetchJobDetails", {
        'queryInfo': this.queryInfo,
        'leadId': leadId,
      });
    },

    modal(escalationLevel, escalationStatus) {
      switch (escalationLevel) {
        case "Accept Or Decline":
          if (
            escalationStatus == "Declined-Lapsed" ||
            escalationStatus == "Declined"
          ) {
            return "declined";
          }

          return "aod";
        case "Confirm Enquirer Contacted":
          if (
            escalationStatus == "Declined" ||
            escalationStatus == "Discontinued"
          ) {
            return "declined";
          }

          return "cec";
        case "In Progress":
          return "inprogress";
        case "Won":
          return "won";
        case "Lost":
          return "lost";
        default:
          return "finished";
      }
    },

    updateStatus() {
      // Pass special value
      this.active_escalation.lead_ip_first_extension_created_date =
        this.lead.lead_ip_first_extension_created_date ? this.lead.lead_ip_first_extension_created_date : null

      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: this.active_escalation,
        showEscalation: true,
        modal: this.modal(
          this.active_escalation.escalation_level,
          this.active_escalation.escalation_status
        ),
      });
    },

    addNewJobDetails() {
      const leadId = this.$route.params.id ? this.$route.params.id : "";
      this.$store.dispatch("leadhistory/editJobDetails", {
        leadId,
        close: true,
        form: "job_details",
      });
    },

    editJobDetails({ row }) {
      // dispatch the modal open
      this.dialogTitle = "Edit Job Details";
      this.$store.dispatch("leadhistory/editJobDetails", {
        data: row,
        close: true,
        form: "job_details",
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
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.value) {
          return this.$store
            .dispatch("leadhistory/deleteJobDetails", row.id)
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }
            });
        }
      });
    },

    saveSale(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leadhistory/saveSale")
            .then(({ success, message, errors }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    getLeadHistory() {
      const leadId = this.$route.params.id ? this.$route.params.id : "";

      if (leadId) {
        this.lead_id = leadId;
        this.$store.dispatch("leadhistory/getLeadHistory", leadId);
      }
    },
  },

  beforeMount() {
    this.getLeadHistory();
  },
};
</script>

<style scoped>
.m-card-1{
  margin-left: 20px;
}

@media all and (max-width: 992px){
  .m-card-1{
    margin: 10px auto;
  }
}
</style>
