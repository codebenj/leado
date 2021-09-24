<template>
  <Section className="lead-dashboard" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button class="fl-right" type="primary" @click="addNewOrg()"
        >New Customer Enquiry</el-button
      >
    </template>

    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg" :gutter="20">
          <el-col :span="4">
            <label for=""> Filter by Level </label>
            <el-select v-model="filters[0].value" placeholder="Select">
              <el-option
                v-for="FL in filterLevel"
                :key="FL.value"
                :label="FL.label"
                :value="FL.value"
              />
            </el-select>
          </el-col>
          <el-col :span="4">
            <label for=""> Filter by Organisation </label>
            <el-select v-model="filters[1].value" placeholder="Select">
              <el-option label="All" value="" />
              <el-option
                v-for="organisation in organisations"
                :key="organisation.id"
                :label="organisation.name"
                :value="organisation.id"
              />
            </el-select>
          </el-col>
          <el-col :span="4">
            <label for=""> Filter by Postcode </label>
            <el-input
              v-model="filters[2].value"
              placeholder="Postcode"
              clearable
            />
          </el-col>
          <el-col :span="4" :offset="7">
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
          :pagination-props="{ pageSizes: [10, 15, 20] }"
          :filters="filters"
          :table-props="tableProps"
          @selection-change="handleSelectionChange"
          @query-change="loadMore"
          @row-dblclick="editLead"
        >
          <el-table-column label="Lead ID" width="80" prop="lead.lead_id">
            <template slot-scope="{ row }">
              {{ row.lead_id }}
            </template>
          </el-table-column>

          <el-table-column label="Enquirer" prop="lead.customer.first_name">
            <template slot-scope="{ row }">
              <span v-if="row.lead">
                {{
                  `${row.lead.customer.first_name} ${row.lead.customer.last_name}`
                }}
              </span>
            </template>
          </el-table-column>

          <el-table-column
            label="Post Code"
            prop="lead.customer.address.postcode"
          />

          <el-table-column label="Organisation" prop="organisation.name" />

          <el-table-column label="Phone" prop="lead.customer.contact_number" />

          <el-table-column
            label="Escalation Level"
            prop="escalation_level"
            width="250"
          >
            <template slot-scope="scope">
              <el-tag
                size="medium"
                type="primary"
                :class="scope.row.color"
                effect="dark"
                disable-transitions
                >{{ scope.row.escalation_level }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Date" prop="lead.created_at">
            <template slot-scope="{ row }">
              {{ getDatetimeByTimezone(row.lead.created_at) }}
            </template>
          </el-table-column>

          <el-table-column
            label="Escalation Status"
            prop="escalation_status"
            width="350"
          >
            <template slot-scope="{ row }">
              <el-tooltip
                :content="getTooltip(row)"
                placement="top"
                :disabled="!getTooltip(row)"
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
                    @click.native="showLeadHistory(row)"
                    >Escalation</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-data-line"
                    @click.native="stats(row)"
                    >Stats</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editLead(row)"
                    >Edit</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-delete"
                    @click.native="deleteLead(row)"
                    >Delete</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>
    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";
import Swal from "sweetalert2";
import moment from "moment-timezone";

export default {
  name: "AdminDashboard",
  components: {
    Section,
    DataTablesServer
  },
  data() {
    return {
      pageTitle: "Leads Dashboard",
      tableProps: {
        rowClassName: function(row, index) {
          const { color } = row.row;

          return color;
        }
      },
      filterLevel: [
        {
          value: "",
          label: "All"
        },
        {
          value: "Unassigned",
          label: "Unassigned"
        },
        {
          value: "Accept or Decline",
          label: "Accept or Decline"
        },
        {
          value: "Confirm Enquirer Contacted",
          label: "Confirm Enquirer Contacted"
        },
        {
          value: "Finalized",
          label: "Finalized"
        },
        {
          value: "In Progress",
          label: "In Progress"
        },
        {
          value: "Won",
          label: "Won"
        },
        {
          value: "Lost",
          label: "Lost"
        }
      ],
      filters: [
        {
          prop: "escalation_level",
          value: ""
        },
        {
          prop: "organisation_id",
          value: ""
        },
        {
          prop: "postcode",
          value: ""
        },
        {
          prop: "search",
          value: ""
        }
      ]
    };
  },

  computed: mapGetters({
    user: "auth/user",
    leads: "leads/leads",
    organisations: "leads/organisations",
    loading: "leads/loading",
    total: "leads/total"
  }),

  methods: {
    async loadMore(queryInfo) {
      this.$store.dispatch("leads/fetchLeads", queryInfo);
    },

    handleSelectionChange() {},

    timerFormat(props) {
      if (props.days > 0)
        return `${props.days} days ${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.hours > 0)
        return `${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.minutes > 0) return `${props.minutes}m ${props.seconds}s`;
      else if (props.seconds > 0) return `${props.seconds}s`;
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

    getDatetimeByTimezone(str_datetime) {
      if (!str_datetime) return "---";
      const obj_datetime = moment(str_datetime).tz(this.user.address.timezone);

      return obj_datetime.format("HH:mm:ss DD/MM/YYYY");
    },

    showLeadHistory(row) {
      this.$router.push({
        name: "admin.leads.history",
        params: {
          id: row.lead.id
        }
      });
    },

    getTooltip(row) {
      const type = [1, 2].includes(this.user.role_id) ? "admin" : "org";
      return row.expiration_text && row.expiration_text[type]
        ? row.expiration_text[type]
        : "";
    },

    addNewOrg() {
      this.$store.dispatch("leads/addLead");
      this.$router.push({ name: "admin.leads.create" });
    },

    editLead(row) {
      this.$router.push({
        name: "admin.leads.update",
        params: {
          id: row.id
        }
      });
    },

    deleteLead(row) {
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
            .dispatch("leads/deleteLead", row.lead_id)
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

    stats(row) {
      const route = this.$router.resolve({
        path: `/admin/lead/${row.lead_id}/stats`
      });
      window.open(route.href, "_blank");
    }
  },

  beforeMount() {
    this.$store.dispatch("leads/fetchOrgAll");
  },

  mounted() {
    this.$echo
      .channel("traleado-global")
      .listen(".leadescalation.created", data => {
        // this.$store.dispatch("leads/fetchLeads", [])
      });
  }
};
</script>
