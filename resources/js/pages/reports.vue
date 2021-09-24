<template>
  <Section className="reports" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg" :gutter="24">
          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="6"
            :lg="6"
            :xl="6"
          >
            <label class="filter-labels"> Filter by Type </label>
            <el-select
              v-model="filterReport"
              placeholder="Select"
              @change="filterLevel"
            >
              <el-option
                v-for="item in options"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-col>
          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="6"
            :lg="6"
            :xl="6"
          >
            <label class="filter-labels"> Filter by State</label>
            <el-select
              v-model="filters[0].value"
              placeholder="Select"
              @change="filterChange"
            >
              <el-option
                v-for="state in states"
                :key="state"
                :label="state"
                :value="state"
              />
            </el-select>
          </el-col>
          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="6"
            :lg="6"
            :xl="6"
            v-if="filterReport != 'organisation_status_breakdown'"
          >
            <label class="d-block filter-labels"> Filter by Date </label>
            <el-date-picker
              :localTime="true"
              v-model="filters[1].value"
              type="daterange"
              start-placeholder="Start Date"
              end-placeholder="End Date"
              @change="filterChangeDate"
            />
          </el-col>

          <el-col
            class="filter-spacings-at-column-type"
            :xs="24"
            :sm="24"
            :md="4"
            :lg="4"
            :xl="4"
          >
            <label class="d-block filter-labels">&nbsp;</label>
            <el-dropdown size="medium" trigger="click">
              <el-button type="primary">
                Download<i class="el-icon-arrow-down el-icon--right"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item @click.native="exportExcel"
                  >Excel</el-dropdown-item
                >
                <el-dropdown-item @click.native="exportPdf"
                  >PDF</el-dropdown-item
                >
              </el-dropdown-menu>
            </el-dropdown>
          </el-col>
        </el-row>

        <!-- Advertising Medium Breakdown  -->
        <el-row v-if="filterReport === 'advertising_medium_breakdown'">
          <el-table :data="AdvertisingMediumBreakdownData" show-summary sum-text="Totals">

            <el-table-column label="Medium" prop="sources" align="left">
              <template slot-scope="{ row }">
                <span>
                  {{ `${toProperName(row.sources)}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              v-for="AMB in AdvertisingMediumBreakdown"
              :key="AMB.label"
              :prop="AMB.prop"
              :label="AMB.label"
              :align="AMB.align"
            />

            <el-table-column label="State" prop="states" align="left">
              <template slot-scope="{ row }">
                <span>
                  {{ `${showUnique(row.states)}` }}
                </span>
              </template>
            </el-table-column>
          </el-table>
        </el-row>

        <!-- Organisation Status Breakdown -->
        <el-row v-if="filterReport === 'organisation_status_breakdown'">
          <el-table :data="OrganisationStatusBreakdownData" :span-method="organisationStatusBreakdownSpanMethod" show-summary sum-text="Totals" :summary-method="getOrganisationSummaries">
            <el-table-column
              v-for="OSB in OrganisationStatusBreakdown"
              :key="OSB.label"
              :prop="OSB.prop"
              :label="OSB.label"
              :align="OSB.align"
            />

            <el-table-column
              key="lead_count"
              prop="lead_count"
              label="Lead Count"
              align="center"
            >
              <template slot-scope="{ row }">
                <label v-if="row.status === 'Unresolved'">{{
                  row.unallocated_count
                }}</label>
                <label v-else-if="row.status === 'Won'">{{
                  row.won_count
                }}</label>
                <label v-else>{{ row.lost_count }}</label>
              </template>
            </el-table-column>

            <el-table-column
              key="percent"
              prop="percent"
              label="As %"
              align="center"
            >
              <template slot-scope="{ row }">
                <label v-if="row.status === 'Unresolved'">{{
                  row.percent_unallocated
                }}</label>
                <label v-else-if="row.status === 'Won'">{{
                  row.percent_won
                }}</label>
                <label v-else>{{ row.percent_lost }}</label>
              </template>
            </el-table-column>

            <el-table-column
              key="installed_meters"
              prop="installed_meters"
              label="Metres WON"
              align="center"
            >
              <template slot-scope="{ row }">
                <label v-if="row.status === 'Unallocated'"></label>
                <label v-else-if="row.status === 'Won'">{{ row.installed_meters }}</label>
                <label v-else></label>
              </template>
            </el-table-column>

          </el-table>
        </el-row>

        <!-- Leads Won Breakdown -->
        <el-row v-if="filterReport === 'leads_won_breakdown'">
          <el-table :data="LeadsWonBreakdownData" show-summary sum-text="Totals" :summary-method="getSummaries">

            <el-table-column label="Medium" prop="medium" align="left">
              <template slot-scope="{ row }">
                <span>
                  {{ `${toProperName(row.medium)}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              v-for="LWB in LeadsWonBreakdown"
              :key="LWB.prop"
              :prop="LWB.prop"
              :label="LWB.label"
              :align="LWB.align"
            />

            <el-table-column prop="states" label="State" align="center">
              <template slot-scope="{ row }">
                <span
                  v-for="(state, index) in row.states"
                  :key="`${state}_rates_${index}`"
                  class="d-block"
                >
                  {{ `${state}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              prop="'states_total',"
              label="State Total"
              align="center"
            >
              <template slot-scope="{ row }">
                <span
                  v-for="(state, index) in row.states_total"
                  :key="`${state}_total_${index}`"
                  class="d-block"
                >
                  {{ `${state}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              prop="'lead_states_won',"
              label="% of Won"
              align="center"
            >
              <template slot-scope="{ row }">
                <span
                  v-for="(state, index) in row.lead_states_won"
                  :key="`${state}_state_won_${index}`"
                  class="d-block"
                >
                  {{ `${state}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              prop="'lead_total_meters',"
              label="Est. Metres by State"
              align="center"
            >
              <template slot-scope="{ row }">
                <span
                  v-for="(meter, index) in row.lead_total_meters"
                  :key="`${meter}_meter_${index}`"
                  class="d-block"
                >
                  {{ `${meter}` }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              prop="'lead_total_meters_actual',"
              label="Metres WON by State"
              align="center"
            >
              <template slot-scope="{ row }">
                <span
                  v-for="(meter, index) in row.lead_total_meters_actual"
                  :key="`${meter}_meter_${index}`"
                  class="d-block"
                >
                  {{ `${meter}` }}
                </span>
              </template>
            </el-table-column>

          </el-table>
        </el-row>
      </el-card>
    </template>
  </Section>
</template>

<script>
import Section from "~/components/Section";
import { DataTables, DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import { showUnique, toProperName } from "~/helpers";

export default {
  name: "Reports",

  layout: "master",

  components: {
    Section,
    DataTables,
    DataTablesServer,
  },

  computed: mapGetters({
    AdvertisingMediumBreakdownData: "report/advertisingMediumBreakdown",
    OrganisationStatusBreakdownData: "report/organisationStatusBreakdown",
    LeadsWonBreakdownData: "report/leadsWonBreakdown",
    organisationStatusBreakdownArr: 'report/organisationStatusBreakdownArr',
    loading: "report/loading",
    images: "entry/images",
    states: "report/states",
  }),

  data() {
    return {
      pageTitle: "Reports",
      filterReport: "leads_won_breakdown",
      filterState: "",
      payload: {},
      filters: [
        {
          prop: "states",
          value: "",
        },
        {
          prop: "dates",
          value: "",
        },
      ],
      dateValue: "",
      //states: [],
      options: [
        {
          value: "leads_won_breakdown",
          label: "Leads Won Breakdown",
        },
        {
          value: "organisation_status_breakdown",
          label: "Organisation Status Breakdown",
        },
        {
          value: "advertising_medium_breakdown",
          label: "Advertising Medium Breakdown",
        }

      ],
      AdvertisingMediumBreakdown: [
        // {
        //   prop: "sources",
        //   label: "Medium",
        //   align: "left",
        // },
        {
          prop: "count_source",
          label: "Medium Count",
          align: "center",
        },
        {
          prop: "percentage",
          label: "% Leads in Medium",
          align: "center",
        },
        // {
        //   prop: "states",
        //   label: "State",
        //   align: "left",
        // },
      ],
      OrganisationStatusBreakdown: [
        {
          prop: "name",
          label: "Organisation",
          align: "left",
        },
        {
          prop: "lead_count",
          label: "Total Leads",
          align: "center",
        },
        {
          prop: "status",
          label: "Status",
          align: "center",
        },
        // {
        //   prop: "status_count",
        //   label: "Lead Count",
        //   align: "center",
        // },
        // {
        //   prop: "percent",
        //   label: "As %",
        //   align: "center",
        // },
      ],
      LeadsWonBreakdown: [
        // {
        //   prop: "medium",
        //   label: "Medium",
        //   align: "left",
        // },
        {
          prop: "total_leads",
          label: "Supply & Install Leads",
          align: "center",
        },
        {
          prop: "lead_won",
          label: "Leads Won",
          align: "center",
        },
        {
          prop: "percentage_won",
          label: "As %",
          align: "center",
        },
      ],
      pickerOptionsBefore: {
        disabledDate(time) {
          return time.getTime() > Date.now();
        },
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              picker.$emit("pick", new Date());
            },
          },
          {
            text: "Yesterday",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24);
              picker.$emit("pick", date);
            },
          },
          {
            text: "A week ago",
            onClick(picker) {
              const date = new Date();
              date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", date);
            },
          },
        ],
      },
    };
  },
  methods: {
    toProperName: toProperName,
    showUnique: showUnique,

    getOrganisationSummaries(param){
      const { columns, data } = param;
        const sums = [];

        columns.forEach((column, index) => {
          if (index === 0) {
            sums[index] = 'Totals';
            return;
          }

          if(index == 5){
            const values = data.map(element => {
              if(element.status == 'Won'){
                return element.installed_meters
              }
            });

            let total = 0
            values.forEach(function(value, index){

              if(value){
                total+=value
              }
            })

            sums[index] = total.toFixed(2);
            //sums[index] = total
          }else if(index == 1){
            let total = 0
            const won_count = data.filter(row => row.status == 'Won')
            const unallocated_count = data.filter(row => row.status == 'Unresolved')
            const lost_count = data.filter(row => row.status == 'Lost')

            won_count.forEach(function(value){
              total+=(!isNaN(value.won_count)) ? value.won_count : 0
            })

            unallocated_count.forEach(function(value){
              total+=(!isNaN(value.unallocated_count)) ? value.unallocated_count : 0
            })

            lost_count.forEach(function(value){
              total+=(!isNaN(value.lost_count)) ? value.lost_count : 0
            })

            sums[index] = total
          }
          else if(index == 3){
            sums[index] = ''
          }
          else{
            const values = data.map(item => Number(item[column.property]));
            if (!values.every(value => isNaN(value))) {
              let total =  values.reduce((prev, curr) => {
                const value = Number(curr);
                if (!isNaN(value)) {
                  return prev + curr;
                } else {
                  return prev;
                }
              }, 0);

              sums[index] = total;

            } else {
              sums[index] = '';
            }
          }
        });

        return sums;
    },

    getSummaries(param){
      const { columns, data } = param;

        const sums = [];
        columns.forEach((column, index) => {
          if (index === 0) {
            sums[index] = 'Totals';
            return;
          }

          if(index == 1 || index == 2){
            const values = data.map(item => Number(item[column.property]));

            if (!values.every(value => isNaN(value))) {
              sums[index] = values.reduce((prev, curr) => {
                const value = Number(curr);
                if (!isNaN(value)) {
                  return prev + curr;
                } else {
                  return prev;
                }
              }, 0);
            } else {
              sums[index] = 'N/A';
            }
          }else if(index == 51111){
            const values = data.map(element => {
              return element.states_total
            });

            let total = 0
            values.forEach(function(value, index){

              value.forEach(function(value, index){
                total+=value
              })

            })

            sums[index] = total;
          }

          else if(index == 7){
            const values = data.map(element => {
              return element.lead_total_meters
            });

            let total = 0
            values.forEach(function(value, index){

              value.forEach(function(value, index){
                total+=value
              })

            })

            //sums[index] = total.toFixed(2);
            sums[index] = total
          }
          else if(index == 8){
            const values = data.map(element => {
              return element.lead_total_meters_actual
            });

            let total = 0
            values.forEach(function(value, index){

              value.forEach(function(value, index){
                total+=value
              })

            })

            sums[index] = total.toFixed(2);
            //sums[index] = total
          }
          else{
            sums[index] = '';
          }
        });

        return sums;
    },

    organisationStatusBreakdownSpanMethod({ row, column, rowIndex, columnIndex }) {
      if (columnIndex === 0 || columnIndex === 1) {
            const _row = this.organisationStatusBreakdownArr[rowIndex];
            const _col = _row > 0 ? 1 : 0;
            return {
                  rowspan: _row,
                  colspan: _col
            }
      }
    },

    exportPdf() {
      let params = {};
      params.states = this.filters[0] ? this.filters[0].value : "";
      params.dates = this.filters[1] ? this.filters[1].value : "";
      params.export = "pdf";

      if (this.filterReport == "advertising_medium_breakdown") {
        this.$store
          .dispatch("report/exportAdvertisingMediumBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "advertising-medium-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      } else if (this.filterReport == "organisation_status_breakdown") {
        this.$store
          .dispatch("report/exportOrganisationStatusBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "organisation-status-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      } else if (this.filterReport == "leads_won_breakdown") {
        this.$store
          .dispatch("report/exportLeadWonBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "lead-status-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      }
    },

    exportExcel() {
      let params = {};
      params.states = this.filters[0] ? this.filters[0].value : "";
      params.dates = this.filters[1] ? this.filters[1].value : "";
      params.export = "excel";

      if (this.filterReport == "advertising_medium_breakdown") {
        this.$store
          .dispatch("report/exportAdvertisingMediumBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "advertising-medium-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      } else if (this.filterReport == "organisation_status_breakdown") {
        this.$store
          .dispatch("report/exportOrganisationStatusBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "organisation-status-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      } else if (this.filterReport == "leads_won_breakdown") {
        this.$store
          .dispatch("report/exportLeadWonBreakdown", params)
          .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", "lead-status-breakdown.xls");
            document.body.appendChild(link);
            link.click();
          });
      }
    },

    filterChange(value) {
      this.payload.filters[0].value = value;

      if (this.filterReport == "leads_won_breakdown") {
        this.$store.dispatch("report/fetchLeadsWonBreakdown", this.payload);
      } else if (this.filterReport == "organisation_status_breakdown") {
        this.$store.dispatch("report/fetchOrganisationStatusBreakdown", this.payload);
      } else if (this.filterReport == "advertising_medium_breakdown") {
        this.$store.dispatch("report/fetchAdvertisingMediumBreakdown",this.payload);
      }
    },

    filterChangeDate(value) {
      this.payload.filters[1].value = value;

      if (this.filterReport == "leads_won_breakdown") {
        this.$store.dispatch("report/fetchLeadsWonBreakdown", this.payload);
      } else if (this.filterReport == "organisation_status_breakdown") {
        this.$store.dispatch("report/fetchOrganisationStatusBreakdown", this.payload);
      } else if (this.filterReport == "advertising_medium_breakdown") {
        this.$store.dispatch("report/fetchAdvertisingMediumBreakdown", this.payload);
      }
    },

    filterLevel(value) {
      this.payload.filters = this.filters;

      if (value == "advertising_medium_breakdown") {
        this.$store.dispatch("report/fetchAdvertisingMediumBreakdown", this.payload);
      } else if (value == "organisation_status_breakdown") {
        this.filters[1].value = null
        this.$store.dispatch("report/fetchOrganisationStatusBreakdown", this.payload );
      } else if (value == "leads_won_breakdown") {
        this.$store.dispatch("report/fetchLeadsWonBreakdown", this.payload);
      }
    },

    advertising_medium_breakdown_search(query) {
      this.payload = query;
      this.$store.dispatch("report/fetchAdvertisingMediumBreakdown", query);
    },

    organisation_status_breakdown_search(query) {
      this.payload = query;
      this.$store.dispatch("report/fetchOrganisationStatusBreakdown", this.payload );
    },

    leads_won_breakdown_search(query) {
      this.payload = query;
      this.$store.dispatch("report/fetchLeadsWonBreakdown", this.payload);
    },
  },

  beforeMount() {
    this.payload.filters = this.filters;

    this.$store.dispatch("report/fetchLeadsWonBreakdown", this.payload);
  },
};
</script>

<style scoped>
.filter-labels {
  color: grey;
}

@media all and (max-width: 768px) {
  .filter-spacings-at-column-type {
    margin-top: 10px;
  }

  .filter-spacings-at-column-type:first-child {
    margin-top: 0px;
  }

  .filter-spacings-at-column-type:last-child {
    margin-top: -10px;
  }

  .el-range-editor {
    width: 100%;
  }
}
</style>
