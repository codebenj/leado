<template>
  <div>
    <data-tables-server
      :data="currentLeads"
      :total="currentLeadsTotal"
      :loading="loading"
      :pagination-props="{ pageSizes: [10, 15, 20] }"
      @query-change="loadMore"
      @row-click="clickRow"
    >

      <el-table-column
        label=""
        width="55"
        align="center"
        class-name="not-drawer left-dropdown"
        v-if="isMobile"
      >
        <template slot-scope="{ row }">
          <leads-action
            :row="row"
            :user="user"
            :isManualUpdateEnabled="isManualUpdateEnabled(row.organisation)"
            v-on:singleClickRow="singleClickRow"
          ></leads-action>
        </template>
      </el-table-column>

      <el-table-column label="Lead ID" width="120" prop="lead.lead_id" align="center">
        <template slot-scope="{ row }">
          <span v-if="row.lead">
            {{ (row.lead.lead_id) ? row.lead.lead_id : row.lead_id }}
          </span>
        </template>
      </el-table-column>

      <el-table-column
        width="180"
        label="Enquirer"
        prop="lead.customer.first_name"
      >
        <template slot-scope="{ row }">
          <span v-if="row.lead">
            {{
              `${row.lead.customer.first_name} ${row.lead.customer.last_name}`
            }}
          </span>
        </template>
      </el-table-column>

      <el-table-column
        width="130"
        label="Suburb"
        prop="lead.customer.address.suburb"
      >
        <template slot-scope="{ row }">
          <span v-if="row.lead">
            {{ row.lead.customer.address.suburb }}
          </span>
        </template>
      </el-table-column>

      <el-table-column
        width="80"
        label="Post Code"
        prop="lead.customer.address.postcode"
      >
        <template slot-scope="{ row }">
          <span v-if="row.lead">
            {{ row.lead.customer.address.postcode }}
          </span>
        </template>
      </el-table-column>

      <el-table-column
        label="Phone"
        prop="lead.customer.contact_number"
        width="125"
        align="center"
      >

        <template slot-scope="{ row }">
          <span v-if="user && row.lead" v-show="hideEnquirerInformation(row.escalation_status, user)">
            {{ row.lead.customer.contact_number }}
          </span>
        </template>

      </el-table-column>

      <el-table-column
        label="Escalation Level"
        prop="escalation_level"
        width="auto"
        align="center"
      >
        <template slot-scope="{ row }">
          <el-tag
            size="medium"
            type="primary"
            class="escalation"
            :class="row.color"
            effect="dark"
            disable-transitions
          >
            {{ row.escalation_level }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column
        label="Escalation Status"
        prop="escalation_status"
        width="170"
        align="center"
      >
        <template slot-scope="{ row }">
          <el-tooltip
            :content="getTooltip(row)"
            placement="top"
            :disabled="!getTooltip(row)"
            popper-class="font-size-14"
          >
            <el-tag
              size="medium"
              type="primary"
              class="escalation"
              :class="row.color"
              effect="dark"
              disable-transitions
            >
              {{ row.escalation_status }}

              <countdown
                class="d-block"
                :auto-start="pauseTimers()"
                v-if="expirationDate(row)"
                :time="expirationDate(row)"
              >
                <template v-if="hideIfPaused()" slot-scope="props">
                  {{ timerFormat(props) }}
                </template>
              </countdown>
            </el-tag>
          </el-tooltip>
        </template>
      </el-table-column>

      <el-table-column label="Date Added" prop="created_at" align="center">
        <template slot-scope="{ row }">
          {{ row.created_at | moment("k:mm DD/MM/YYYY") }}
        </template>
      </el-table-column>

      <el-table-column
        label="Assigned User"
        prop="lead.lead_id"
        align="center"
        width="110px"
        v-if="isAssignedRoles(user, ['administrator'])"
        class-name="not-drawer"
      >
        <template slot-scope="{ row }" v-if="row.lead && users[0]">
          <AssignUsersPopover :id="row.lead_id" :assigned_users="checkAssignedUsers( row )" :users="users" :type="'index'" />
        </template>
      </el-table-column>

      <el-table-column
        label=""
        width="55"
        align="center"
        class-name="action b-none not-drawer"
      >
        <template slot-scope="{ row }">
          <leads-action
            :row="row"
            :user="user"
            :isManualUpdateEnabled="isManualUpdateEnabled(row.organisation)"
            v-on:singleClickRow="singleClickRow"
          ></leads-action>
        </template>
      </el-table-column>

		</data-tables-server>

    <!-- <template v-if="historyDrawer"> -->
      <el-drawer
          ref="drawerAdmin1"
          :visible.sync="historyDrawer"
          :with-header="false"
          :destroy-on-close="true"
          size="60%"
          append-to-body
          :before-close="beforeClose"
        >
        <DrawerAdmin :id="historyDrawerId" v-on:closeDrawer="closeDrawer" />
      </el-drawer>
    <!-- </template> -->

	</div>
</template>

<script>
import { mapGetters } from 'vuex'
import DrawerAdmin from "~/components/DrawerAdmin"
import { DataTablesServer } from "vue-data-tables"
import { isAssignedRoles, hideEnquirerInformation, isManualUpdateEnabled } from "~/helpers"
import Cookies from 'js-cookie'
import { Bus } from '~/app'
import AssignUsersPopover from "~/components/AssignUsersPopover"
import LeadsAction from "~/components/dashboard/actions";

export default {
  name: "AssignedLeads",

  props: {
    orgId: Number
  },
  name: "AssignedLeads",
  layout: "master",

  components: {
    DataTablesServer,
    DrawerAdmin,
    AssignUsersPopover,
    LeadsAction,
  },

  computed: mapGetters({
    user: "auth/user",
    currentLeads: 'organisations/current_leads',
    loading: 'organisations/loading_current_leads',
    is_paused: "leads/is_paused",
    leads: "leads/leads",
    currentLeadsTotal: "organisations/currentLeadsTotal"
  }),

  data(){
    return {
      historyDrawer: false,
      historyDrawerId: null,
      timer: null,
      userLoading: true,
      users: [],
      isMobile: false
    }
  },

  methods:{
    hideEnquirerInformation: hideEnquirerInformation,
    isAssignedRoles: isAssignedRoles,
    isManualUpdateEnabled: isManualUpdateEnabled,

    async loadMore(queryInfo){
      this.$store.dispatch("organisations/fetchCurrentLeads", {id: this.orgId, queryInfo: queryInfo} )
    },

    clickRow( row, column ) {
      let nots = [ 'action b-none not-drawer', 'not-drawer', 'not-drawer left-dropdown' ]
      let index = nots.indexOf( column.className )

      if ( index == -1 ) {
        this.timer = setTimeout( () => {
          this.singleClickRow( row )
          this.clicks = 0
        }, 500 )
      }
    },

    singleClickRow( row ) {
      let lead_id = row.lead_id

      this.$store.dispatch("leadhistory/openLeadOverviewNested", lead_id);
    },

    checkAssignedUsers( row ) {
      return row.lead.user_ids ?? '[]'
    },

    checkProp() {
      return this.userLoading ? false : true
    },

    closeDrawer( id ) {
      Cookies.remove( 'lead_id' )
      Bus.$emit( 'reload-assigned', id )

      this.historyDrawer = false
      this.historyDrawerId = null

      console.log('AssignedLeads.closeDrawer')
    },

    beforeClose() {
      Cookies.remove( 'lead_id' )
      Bus.$emit( 'reload-assigned', this.historyDrawerId )

      this.historyDrawer = false
      this.historyDrawerId = null

      console.log('AssignedLeads.beforeClose')
    },

    getTooltip(row) {
      const type = [1, 2].includes(this.user.role_id) ? "admin" : "org";
      return row.expiration_text && row.expiration_text[type]
        ? row.expiration_text[type]
        : "";
    },

    pauseTimers(){
      return true
    },

    expirationDate(row) {
      return row.time_left ? row.time_left : 0;
    },

    hideIfPaused(){
      return true
    },

    timerFormat(props) {
      if (props.days > 0)
        return `${props.days} days ${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.hours > 0)
        return `${props.hours}h ${props.minutes}m ${props.seconds}s`;
      else if (props.minutes > 0) return `${props.minutes}m ${props.seconds}s`;
      else if (props.seconds > 0) return `${props.seconds}s`;
    },
  },

  mounted(){
    if(this.org_id){
      this.$store.dispatch("organisations/fetchCurrentLeads", {id: this.org_id, queryInfo: [] });
    }
  }
}
</script>

<style>
.drawer-container{
  text-align: left;
}
.el-drawer__body{
  overflow-y: auto;
}
.el-table__row td{
  padding-left: 0px;
}
</style>
