<template>
  <el-card class="box-card b-none page-header" shadow="never">
    <!-- <el-row :gutter="24">
      <el-col :span="8" :offset="6">
        <label class="d-block filter-labels text-left"> Filter by Days </label>
        <el-select
          v-model="filters[1].value"
          @change="filterDays"
          clearable
        >
          <el-option
            v-for="day in days"
            :key="day"
            :label="day + ' days'"
            :value="day">
          </el-option>
        </el-select>
      </el-col>
      <el-col :span="8" align="end">
        <label class="d-block filter-labels text-left"> Filter by Date </label>
        <el-date-picker
          v-model="filters[0]"
          type="daterange"
          start-placeholder="Start Date"
          end-placeholder="End Date"
          @change="dateChange"
        />
      </el-col>
    </el-row> -->

    <el-row :gutter="24">
      <el-col :span="24">
        <h5 class="m-b-sm" v-if="!is_org_profile">Org Stats </h5>

        <!-- OLD TABLE STRUCTURE -->
        <!-- <data-tables-server
          :data="leadOrgPostCodes"
          :total="leadOrgPostCodesTotal"
          :loading="leadOrgPostCodesLoading"
          style="width: 100%"
          id="org__stat_compare">


          <el-table-column label="Org" width="250" prop="org" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ row.name }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Leads Assigned" prop="leads" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkData( row.lead_count ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Won" prop="won" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkData( row.percent_won ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Lost" prop="lost" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkData( row.percent_lost ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Unallocated" prop="unallocated" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkData( row.percent_unallocated ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Pricing" prop="ytd" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkDataMeta( row.metadata, 'pricing' ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Priority" prop="ly" class-name="pl-0">
            <template slot-scope="{ row }">
              <span>{{ checkDataMeta( row.metadata, 'priority' ) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="" prop="tooltip" class-name="pl-0">
            <template slot-scope="{ row }">
              <el-tooltip class="item" effect="dark" placement="left">
                <div slot="content">
                  <p style="margin-bottom: 0rem !important; font-size: 15px; line-height: 1.5rem;">
                    Org code - {{ row.org_code }}
                    <br>
                    {{ row.email ? row.email : '-' }}
                    <br>
                    {{ row.contact_number ? row.contact_number : '-' }}
                  </p>
                </div>
                <fa icon="info-circle" />
              </el-tooltip>
            </template>
          </el-table-column>


        </data-tables-server> -->
        <!-- OLD TABLE STRUCTURE -->

        <data-tables-server
          :data="leadOrgData"
          :total="leadOrgPostCodesTotal"
          :page-size="20"
          :loading="leadOrgPostCodesLoading"
          @query-change='loadData'
          style="width: 100%"
          id="org__stat_compare">
          <el-table-column label="Org"
              :width=" is_org_profile ? 250 : 200" prop="org" class-name="org-table-column-head">
            <template slot-scope="{ row }">
              <span  @click="getOrgId(row.organisation_id)">
                {{ row.name }}
                <ManualIcon :org="row" />
              </span>
            </template>
          </el-table-column>

          <el-table-column label="Leads Assigned" class-name="org-table-column-head-title">
            <el-table-column
              prop="name"
              :label="leadOrgPostCodesMonths[0]"
              :width="40"
              class-name="org-table-column-head"
              v-if="!leadOrgPostCodesLoading">
              <template slot-scope="{ row }">
                <span>{{ checkMonths( row, 0 ) }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              :label="leadOrgPostCodesMonths[1]"
              :width="40"
              class-name="org-table-column-head"
              v-if="!leadOrgPostCodesLoading">
              <template slot-scope="{ row }">
                <span>{{ checkMonths( row, 1 ) }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              :label="leadOrgPostCodesMonths[2]"
              :width="40"
              class-name="org-table-column-head"
              v-if="!leadOrgPostCodesLoading">
              <template slot-scope="{ row }">
                <span>{{ checkMonths( row, 2 ) }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              label="Six Month"
              :width="is_org_profile ? 180 : 110"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ checkMonths( row, 3 ) }}</span>
              </template>
            </el-table-column>
          </el-table-column>

          <el-table-column label="Capacity" class-name="org-table-column-head-title">
            <el-table-column
              prop="name"
              label="Unresolved"
              :width="100"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ row.unallocated_count ? row.unallocated_count : 0 }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              label="Unresolved %"
              :width="is_org_profile ? 200 : 140"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ row.percent_unallocated ? row.percent_unallocated : '0.0%' }}</span>
              </template>
            </el-table-column>
          </el-table-column>

          <el-table-column label="Performance" class-name="org-table-column-head-title">
            <el-table-column
              prop="name"
              label="Won"
              :width="80"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ row.won_count ? computeWonLostPercentage(row.lost_count, row.won_count, 'won') + '%' : '0.0%' }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              label="Lost"
              :width="is_org_profile ? 130 : 80"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ row.lost_count ? computeWonLostPercentage(row.lost_count, row.won_count, 'lost') + '%' : '0.0%' }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              label="Pricing"
              :width="110"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>{{ checkDataMeta( row.metadata, 'pricing' ) }}</span>
              </template>
            </el-table-column>

            <el-table-column
              prop="name"
              label="Priority"
              :width="110"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <span>
                  <MainPriorityIcon :priority="row.priority" :tooltip="row.priority" :displayOnly="true" v-if="row.priority" />
                  <span v-else>-</span>
                </span>
              </template>
            </el-table-column>

            <el-table-column
              label=""
              prop="tooltip"
              class-name="org-table-column-head">
              <template slot-scope="{ row }">
                <el-tooltip class="item" effect="dark" placement="left">
                  <div slot="content">
                    <p style="margin-bottom: 0rem !important; font-size: 15px; line-height: 1.5rem;">
                      Org code - {{ row.org_code }}
                      <br>
                      {{ row.email ? row.email : '-' }}
                      <br>
                      {{ row.contact_number ? row.contact_number : '-' }}
                    </p>
                  </div>
                  <fa icon="info-circle" />
                </el-tooltip>
              </template>
            </el-table-column>
          </el-table-column>

        </data-tables-server>
      </el-col>
    </el-row>
    <el-row :gutter="24">
      <el-col :span="24">
        <el-table :data="orgStats" style="width: 100%; margin-top: 100px">
          <el-table-column
            prop="r0"
            label="Current"
            width="180">
          </el-table-column>
          <el-table-column
            prop="r1"
            label=""
            width="180">
          </el-table-column>
          <el-table-column
            prop="r2"
            label="Historical">
          </el-table-column>
          <el-table-column
            prop="r3"
            label="">
          </el-table-column>
          <el-table-column
            prop="r4"
            label="Events">
          </el-table-column>
          <el-table-column
            prop="r5"
            label=""
            align="center">
          </el-table-column>
          <el-table-column
            prop="r6"
            label=""
            align="center"
            >
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>
  </el-card>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import ManualIcon from "~/components/ManualIcon.vue";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import { Bus } from '~/app'

export default {
  name: 'OrgStatsTable',

  components: {
    DataTablesServer,
    ManualIcon,
    MainPriorityIcon
  },

  props: {
    org_id: { type: Number, default: 0 },
    org_ids: { type: Array, default: [] },
    is_org_profile: { type: Boolean, default: false },
  },

  computed: {
		...mapGetters({
			leadOrgPostCodes: 'orgcompare/leadOrgPostCodes',
      leadOrgPostCodesLoading: 'orgcompare/leadOrgPostCodesLoading',
      leadOrgPostCodesTotal: 'orgcompare/leadOrgPostCodesTotal',
      leadOrgPostCodesMonths: 'orgcompare/leadOrgPostCodesMonths',
      filter_organizations: "organisations/filter_organizations",
      orgStats: 'organisations/leadStats'
		}),

    organisations(){
      let orgids = []

      if( this.org_ids.length === 0){
        this.org_ids_local.forEach(function(org){
          orgids.push(org)
        })
      }else{
        this.org_ids.forEach(function(org){
          orgids.push(org)
        })
      }

      return orgids
    }
	},

  mounted() {
  },

  data: () => ({
    org_ids_local: [],
    filters: [
      {
        value: '',
        label: 'Filter by Date'
      },
      {
        value: '',
        label: 'Filter by Days'
      }
    ],
    leadOrgData: null,
    days: [ 30, 60, 90, 180 ],
  }),

  async beforeMount() {
    await this.dateChange()

  },

  mounted() {
    Bus.$on( 'reload-org-stat', ids => {
      this.org_ids_local = ids
      this.dateChange()
    })
  },

  methods: {
    getOrgId(org_id){
      this.$emit('getOrgId', org_id)
    },

    async loadData(queryInfo) {
      this.filterData(queryInfo)
    },

    filterData(queryInfo) {
      const splitLeadOrg = this.splitLeadOrgData(this.leadOrgPostCodes, queryInfo.pageSize);
      this.leadOrgData = splitLeadOrg[queryInfo.page -1 ]
    },

    splitLeadOrgData(arr, size) {
      let valArr = [];
      for(var i = 0; i < arr.length; i += size) {
        valArr.push(arr.slice(i, i+size));
      }
      return valArr;
    },

    checkMonths( row, index ) {
      let val = index == 3 ? 'month_six' : 'month_' + this.leadOrgPostCodesMonths[index].toLowerCase()
      val = row[val]

      return val ? val : '0'
    },

    checkData( value ) {
      return value ? value : '0%'
    },

    checkDataMeta( value, field ) {
      if ( value == null ) return '-'

      let val = value[field]
      if ( val == null || val == undefined || val == '' ) return '-'

      return val
    },

    async dateChange() {
      this.filters[1].value = ''
      await this.$store.dispatch( 'orgcompare/fetchLeadOrgPostCodes', { filters: this.filters, org_ids: this.organisations } )
      this.filterData({ page: 1, pageSize: 20 })
    },

    filterDays() {
      let days = this.filters[1].value
      this.filters[0] = []

      this.$store.dispatch( 'orgcompare/fetchLeadOrgPostCodes', { filters: this.filters, org_ids: this.organisations, days: days } )
    },

    computeWonLostPercentage(lost_count, won_count, type) {
      const val = type === 'lost' ? lost_count : won_count;
      const _total = 100 * (val/(lost_count + won_count))
      return _total.toFixed(2);
    },
  },
}
</script>

<style scoped lang="scss">
  ::v-deep {
    #org__stat_compare .org-table-column-head-title div.cell {
      font-weight: 600 !important;
      font-size: 1rem !important;
      -webkit-font-smoothing: antialiased;
      font-family: "SF UI Display Light";
    }
    .el-table th > .cell {
      font-weight: normal !important;
      -webkit-font-smoothing: antialiased;
      font-family: "SF UI Display Light";
    }
    #org__stat_compare .el-table__header .org-table-column-head-title, #org__stat_compare .org-table-column-head {
      font-weight: normal !important;
    }
    .el-pagination{
      display: none;
    }
  }
</style>
