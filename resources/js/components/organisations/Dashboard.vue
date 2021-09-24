<template>
  <Section className="lead-dashboard" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg" :gutter="20">
          <el-col :span="4" :offset="19">
            <label for=""> </label>
            <el-input
              v-model="filters[3].value"
              placeholder="Search..."
              clearable
            />
          </el-col>
        </el-row>
        <data-tables-server
          :data="leads"
          :total="total"
          :loading="loading"
          :filters="filters"
          :pagination-props="{ pageSizes: [100, 50] }"
          :table-props="tableProps"
          @query-change="loadMore"
          @row-dblclick="viewLead"
        >
          <el-table-column label="Lead ID" prop="id" width="70">
            <template slot-scope="{ row }">
                {{ row.lead_id }}
              </template>
          </el-table-column>

          <el-table-column
            label="Enquirer"
            :formatter="enquirerFormatter"
            prop="lead.customer.first_name"
          />

          <el-table-column
            label="Post Code"
            prop="lead.customer.address.postcode"
          />

          <el-table-column label="Phone" prop="lead.customer.contact_number" />

          <el-table-column label="Date" prop="lead.created_at" align="center">
            <template slot-scope="{ row }">
              {{ row.lead.created_at | moment("k:mm DD/MM/YYYY") }}
            </template>
          </el-table-column>

          <el-table-column
            label="Escalation Level"
            prop="escalation_level"
            align="center"
            width="350"
          >
            <template slot-scope="{ row }">
              <el-tooltip
                :content="getTooltip(row)"
                :disabled="!getTooltip(row)"
                placement="top"
              >
                <el-tag
                  size="medium"
                  type="primary"
                  :class="row.color"
                  effect="dark"
                  disable-transitions
                >
                  {{ row.escalation_status }}
                  <countdown
                    v-if="expirationDate(row)"
                    :time="expirationDate(row)"
                  >
                    <template slot-scope="props">
                      {{ timerFormat(props) }}
                    </template>
                  </countdown>
                </el-tag>
              </el-tooltip>
            </template>
          </el-table-column>

          <el-table-column label="Sale" prop="lead.sale_string" align="center">
            <template slot-scope="{ row }">
              $ {{ row.lead_sale_string ? row.lead_sale_string : "0.00" }}
            </template>
          </el-table-column>

          <el-table-column
            label=""
            width="55"
            align="center"
            class-name="action b-none"
          >
            <template slot-scope="{ row }">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                  <i class="el-icon-caret-bottom"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-top-right"
                    @click.native="showEscalation(row)"
                    >Update Status</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="viewLead(row)"
                    >View Lead</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>
      <EscalationModal />
    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";
import EscalationModal from "../escalations/main";

export default {
  name: "OrganisationDashboard",
  layout: "master",
  components: {
    Section,
    DataTablesServer,
    EscalationModal,
  },

  data() {
    return {
      enquirerFormatter: function (row, col, val, index) {
        return `${row.lead.customer.first_name} ${row.lead.customer.last_name}`;
      },
      pageTitle: "Leads Dashboard",
      tableId: "leads_dashboard",
      hover: false,
      level: "",
      filterLevel: [
        {
          value: "",
          label: "All",
        },
        {
          value: "Unassigned",
          label: "Unassigned",
        },
        {
          value: "Accept or Decline",
          label: "Accept or Decline",
        },
        {
          value: "Confirm Enquirer Contacted",
          label: "Confirm Enquirer Contacted",
        },
        {
          value: "Finalized",
          label: "Finalized",
        },
        {
          value: "In Progress",
          label: "In Progress",
        },
        {
          value: "Won",
          label: "Won",
        },
        {
          value: "Lost",
          label: "Lost",
        },
      ],
      filters: [
        {
          prop: "escalation_level",
          value: "",
        },
        {
          prop: "organisation_id",
          value: "",
        },
        {
          prop: "postcode",
          value: "",
        },
        {
          prop: "search",
          value: "",
        },
      ],
      tableProps: {
        rowClassName: function (row, index) {
          const { color } = row.row;

          return color;
        },
      },
    };
  },

  computed: mapGetters({
    leads: "leads/leads",
    loading: "leads/loading",
    total: "leads/total",
    user: "auth/user",
    escalationModals: "leadescalation/escalationModals",
  }),

  methods: {
    async loadMore(queryInfo) {
      this.$store.dispatch("leads/fetchLeads", queryInfo);
    },

    handleSelectionChange() {},

    getTooltip(row) {
      const type = [1, 2].includes(this.user.role_id) ? 'admin' : 'org';
      return row.expiration_text && row.expiration_text[type] ? row.expiration_text[type] : ''
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
            escalationStatus == 'Declined' ||
            escalationStatus == 'Discontinued'
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
        case "Abandoned":
          return "declined";
        case "Discontinued":
          return "declined";
        case "Declined":
          return "declined";
        default:
          return "finished";
      }
    },

    showEscalation(row) {
      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: row,
        showEscalation: true,
        modal: this.modal(row.escalation_level, row.escalation_status),
      });
    },

    viewLead(row) {
      this.$router.push({
        name: "organisation.lead",
        params: {
          id: row.lead_id,
        },
      });
    },
  },

  mounted() {
    var channel = this.$echo.channel("traleado-global");

    channel.listen(".leadescalation.created", (data) => {
      this.$store.dispatch("leads/fetchLeads", [])
    });
  },
};
</script>
