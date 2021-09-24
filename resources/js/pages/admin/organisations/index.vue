<template>
  <Section dusk="organisationPage" className="organisation" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button-group class="fl-right r-btn-reset">
        <el-button dusk="addOrganisation" type="primary" @click="addNewOrg()"
          >Add New Organisation</el-button
        >
        <el-button type="primary" v-on:click="delete_organisations"
          >Delete</el-button
        >
        <el-button type="primary" v-on:click="export_organisations"
          >Export</el-button
        >
        <!--
          need to hide since this is a complete function
          <el-button
          type="primary"
          @click="$router.push({ name: 'admin.organisations.import' })"
          >Import</el-button>
        -->
      </el-button-group>
    </template>

    <template v-slot:content>
       <el-tabs type="card" v-model="activeTab" @tab-click="changeTab">
        <el-tab-pane name="active" label="Active">
          <el-card class="box-card b-none" shadow="never">
            <el-row v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])" style="margin-bottom: 30px;" :gutter="24">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-switch
                v-model="isQuerySavedActive"
                @change="queryModActive"
                active-text="Save Filter"
                inactive-text="Clear Filter"
                ></el-switch>
              </el-col>
            </el-row>
            <orgs-filter
              :isAdmin="
                isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
              "
              :filters.sync="filtersActive"
            ></orgs-filter>
            <data-tables-server
              :data="organisations"
              :total="total"
              :loading="loading"
              :filters="filtersActive"
              :pagination-props="{ pageSizes: [100, 50] }"
              @selection-change="handleSelectionChange"
              @query-change="loadMore"
              @row-click="clickRow"
            >
              <el-table-column type="selection" width="40"> </el-table-column>

              <el-table-column width="130" label="Org Code" prop="org_code" />

              <el-table-column width="300" label="Company Name" prop="name">
                <template slot-scope="{ row }">
                  <span>
                    {{ row.name || '' }}
                    <ManualIcon :org="row" />
                  </span>
                  <span>
                    <MainPriorityIcon :priority="row.priority" :tooltip="row.priority" :displayOnly="true" />
                  </span>
                  <span v-if="!row.has_postcodes">
                    <PostcodeIcon :displayOnly="true" />
                  </span>
                </template>
              </el-table-column>

              <el-table-column width="150" label="Contact No." prop="contact_number" />
              <el-table-column
                label="Primary Email"
                prop="user.email"
                width="300"
              />
              <el-table-column width="130" label="Suburb" prop="address.suburb" />

              <el-table-column width="130" label="State" prop="address.state" />

              <el-table-column width="80" label="Pcode" prop="address.postcode" />

              <el-table-column width="100" label="Unresolved" prop="pending_leads" />

              <el-table-column width="100" label="Admin Notified" prop="admin_notified" />

              <el-table-column width="180" label="New Leads" prop="is_suspended" align="center">
                <template slot-scope="{ row }">
                  <el-tag v-show="row.account_status_type_selection.length == 0" type="success" disable-transitions>
                    On
                  </el-tag>
                  <el-tag v-show="row.account_status_type_selection.length > 0" type="danger" disable-transitions>
                    {{ row.account_status_type_selection }}
                  </el-tag>
                </template>
              </el-table-column>

              <el-table-column
                label=""
                width="55"
                align="center"
                class-name="action b-none not-drawer"
              >
                <template slot-scope="{ row }">
                  <el-dropdown trigger="click">
                    <span class="el-dropdown-link">
                      <i class="el-icon-caret-bottom"></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                      <el-dropdown-item
                        icon="el-icon-edit"
                        @click.native="editOrg(row)"
                        >Edit</el-dropdown-item
                      >
                      <el-dropdown-item
                        icon="el-icon-delete"
                        @click.native="deleteOrg(row)"
                        >Delete</el-dropdown-item
                      >
                    </el-dropdown-menu>
                  </el-dropdown>
                </template>
              </el-table-column>
            </data-tables-server>
          </el-card>
        </el-tab-pane>
        <el-tab-pane name="inactive" label="Inactive">
          <el-card class="box-card b-none" shadow="never">
            <el-row v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])" style="margin-bottom: 30px;" :gutter="24">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-switch
                v-model="isQuerySavedInactive"
                @change="queryModInactive"
                active-text="Save Filter"
                inactive-text="Clear Filter"
                ></el-switch>
              </el-col>
            </el-row>
            <orgs-filter
              :isAdmin="
                isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
              "
              :filters.sync="filtersInactive"
            ></orgs-filter>
            <data-tables-server
              :data="inactiveOrganisations"
              :total="inactiveTotal"
              :loading="loading"
              :filters="filtersInactive"
              :pagination-props="{ pageSizes: [100, 50] }"
              @selection-change="handleSelectionChange"
              @query-change="loadMoreInactive"
              @row-dblclick="editOrg"
              @row-click="clickRow"
            >
              <el-table-column type="selection" width="40"> </el-table-column>

              <el-table-column width="130" label="Org Code" prop="org_code" />

              <el-table-column width="300" label="Company Name" prop="name">
                <template slot-scope="{ row }">
                  <span>
                    {{ row.name || '' }}

                    <ManualIcon :org="row" />
                  </span>
                  <span>
                    <MainPriorityIcon :priority="row.priority" :tooltip="row.priority" :displayOnly="true" />
                  </span>
                </template>
              </el-table-column>

              <el-table-column width="150" label="Contact No." prop="contact_number" />

              <el-table-column
                label="Primary Email"
                prop="user.email"
                width="300"
              />

              <el-table-column width="130" label="Suburb" prop="address.suburb" />

              <el-table-column width="130" label="State" prop="address.state" />

              <el-table-column width="140" label="Pcode" prop="address.postcode" />

              <el-table-column width="130" label="Account Status" prop="is_suspended">
                <template slot-scope="{ row }">
                  <el-tag v-show="row.account_status_type_selection.length == 0" type="success" disable-transitions>
                    On
                  </el-tag>
                  <el-tag v-show="row.account_status_type_selection.length > 0" type="danger" disable-transitions>
                    {{ row.account_status_type_selection }}
                  </el-tag>
                </template>
              </el-table-column>

              <el-table-column
                label=""
                width="55"
                align="center"
                class-name="action b-none not-drawer"
              >
                <template slot-scope="{ row }">
                  <el-dropdown trigger="click">
                    <span class="el-dropdown-link">
                      <i class="el-icon-caret-bottom"></i>
                    </span>
                    <el-dropdown-menu slot="dropdown">
                      <el-dropdown-item
                        icon="el-icon-edit"
                        @click.native="editOrg(row)"
                        >Edit</el-dropdown-item
                      >
                      <el-dropdown-item
                        icon="el-icon-delete"
                        @click.native="deleteOrg(row)"
                        >Delete</el-dropdown-item
                      >
                    </el-dropdown-menu>
                  </el-dropdown>
                </template>
              </el-table-column>
            </data-tables-server>
          </el-card>
        </el-tab-pane>
      </el-tabs>

      <template v-if="showOrganisationProfile && user.user_role.name == 'administrator'">
        <el-drawer
          :visible.sync="showOrganisationProfile"
          :with-header="false"
          size="80%"
          :destroy-on-close="true"
          :before-close="closeOrganisationProfile"
          :append-to-body="true"
        >
        <OrganisationProfile v-bind:org-id="orgId" v-on:closeOrganisationProfile="closeOrganisationProfile" />

        </el-drawer>
      </template>

      <template v-if="historyDrawerNested && user.user_role.name == 'administrator'">
        <el-drawer
          ref="drawerAdminNexted"
          :visible.sync="historyDrawerNested"
          :with-header="false"
          :destroy-on-close="true"
          size="60%"
          append-to-body
          :before-close="beforeClose"
        >
        <DrawerAdmin :id="historyDrawerNestedId" />
        </el-drawer>
      </template>

      <template>
        <el-dialog
          title="Add New Organisation"
          v-dialogDrag
          :visible.sync="showOrganisationFormModal"
          width="70%"
          :destroy-on-close="false"
          :append-to-body="true"
          :before-close="closeOrganisationNewForm">
          <OrganisationNewForm v-on:closeOrganisationNewForm="closeOrganisationNewForm" />
        </el-dialog>
      </template>

    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import { DataTablesServer } from "vue-data-tables";
import { mapGetters } from "vuex";
import { isAssignedRoles, capitalize, isManualUpdateEnabled, colorOrganisationStatus } from "~/helpers";
import Swal from "sweetalert2";
import ManualIcon from "~/components/ManualIcon.vue";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import OrgsFilter from "~/components/dashboard/orgfilters";
import OrganisationProfile from "~/components/OrganisationProfile";
import Cookies from 'js-cookie'
import { Bus } from '~/app'
import DrawerAdmin from "~/components/DrawerAdmin"
import MainPriorityIcon from "~/components/priorities/Main.vue";
import OrganisationNewForm from "~/components/OrganisationNewForm"

export default {
  name: "Organisations",
  components: {
    Section,
    DataTablesServer,
    ManualIcon,
    OrgsFilter,
    PostcodeIcon,
    OrganisationProfile,
    DrawerAdmin,
    MainPriorityIcon,
    OrganisationNewForm
  },
  layout: "master",

  middleware: ["auth"],

  data: () => ({
    orgId: 0,
    //showOrganisationProfile: false,
    pageTitle: "Organisations",
    filtersActive: [
      {
        props: "search",
        value: "",
      },
      {
        props: "org_postcode",
        value: "",
      },
      {
        props: "org_state",
        value: "",
      },
      {
        props: "org_suspended",
        value: ""
      },
      {
        props: "org_type",
        value: ""
      }
    ],
    filtersInactive: [
      {
        props: "search",
        value: "",
      },
      {
        props: "org_postcode",
        value: "",
      },
      {
        props: "org_state",
        value: "",
      },
      {
        props: "org_suspended",
        value: ""
      },
      {
        props: "org_type",
        value: ""
      }
    ],
    organisationIds: [],
    payloadActive: {},
    payloadInactive: {},
    activeTab: 'active',
    isQuerySavedActive: null,
    isQuerySavedInactive: null,
    clicks: 0,
    timer: null,
    showOrganisationFormModal: false
  }),

  computed: mapGetters({
    user: "auth/user",
    organisations: "organisations/organisations",
    inactiveOrganisations: "organisations/inactiveOrganisations",
    total: "organisations/total",
    inactiveTotal: "organisations/inactiveTotal",
    loading: "organisations/loading",
    addOrgCommentDialogVisible: "organisations/addOrgCommentDialogVisible",
    orgCommentForm: 'organisations/orgCommentForm',
    orgCommentRules: 'organisations/orgCommentRules',
    historyDrawer: 'leadhistory/historyDrawer',
    historyDrawerId: 'leadhistory/historyDrawerId',
    nestedDrawer: 'leadhistory/nestedDrawer',
    historyDrawerNested: 'leadhistory/historyDrawerNested',
    historyDrawerNestedId: 'leadhistory/historyDrawerNestedId',
    showOrganisationProfile: 'organisations/showOrganisationProfile',
    showOrganisationProfileId: 'organisations/showOrganisationProfileId',
  }),

  methods: {
    capitalize: capitalize,

    isManualUpdateEnabled: isManualUpdateEnabled,

    isAssignedRoles: isAssignedRoles,

    colorOrganisationStatus: colorOrganisationStatus,

    closeOrganisationNewForm(){
      this.showOrganisationFormModal = false
    },

    closeDrawer() {
      this.$store.dispatch("leadhistory/closeLeadOverview").then(_ => {}).catch(_ => {})
    },

    beforeClose(){
      console.log('organisation.index.beforeClose')
      this.$store.dispatch("leadhistory/closeLeadOverviewNested")
    },

    handleAdminDrawerClose() {
      this.$store.dispatch("leadhistory/closeLeadOverview").then(_ => {}).catch(_ => {})
    },

    addComment() {
			this.$store.dispatch("organisations/setDialog", { close: true, form: "add_organisation_comment" });
		},
		addCommentClose() {
			this.$store.dispatch("organisations/setDialog", { close: false, form: "add_organisation_comment" });
		},

    add(formName) {
			this.$refs[formName].validate((valid) => {
				if(valid){
					this.$store.dispatch('organisations/addOrgComment').then(({ msg, data, success }) => {
						if(success){
							Swal.fire({
								title: 'Success',
								text: msg,
								type: 'success'
							})
						}
						else{
							return false;
						}
					})
				}
			})
		},

    delete_organisations() {
      if (this.organisationIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select organisation to delete",
          type: "error",
        });
        return;
      }

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
          this.$store
            .dispatch("organisations/deleteOrganisations", this.organisationIds)
            .then(({ success, message }) => {
              if (success) {
                this.$store.dispatch(
                  "organisations/fetchOrganisations",
                  this.payloadActive
                );
                this.organisationIds = [];
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

    export_organisations() {
      if (this.organisationIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select organisation to export",
          type: "error",
        });
        return;
      }

      this.$store
        .dispatch("organisations/export", this.organisationIds)
        .then((response) => {
          const url = window.URL.createObjectURL(new Blob([response]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "organisations.xlsx");
          document.body.appendChild(link);
          link.click();
        });
    },

    async loadMore( queryInfo ) {
      if ( this.isQuerySavedActive == true ) {
        this.queryModActive()
      }

      var savedQueryString = localStorage.getItem( "savedOrgQueryActive" )

      if( savedQueryString != null ) {

        if( savedQueryString != queryInfo ){
          this.payloadActive = queryInfo;

        } else {
          this.payloadActive = JSON.parse( localStorage.getItem( "savedOrgQueryActive" ) )
        }

      } else{
        this.payloadActive = queryInfo
      }

      // clear timeout variable
      clearTimeout( this.timeoutActive )

      var self = this;

      this.timeoutActive = setTimeout( function () {
        // enter this block of code after 1 second
        // handle stuff, call search API etc.
        self.$store.dispatch("organisations/fetchOrganisations", self.payloadActive);
      }, 1000 )
    },

    async loadMoreInactive(queryInfo) {
      if ( this.isQuerySavedInactive == true ) {
        this.queryModInactive()
      }

      var savedQueryString = localStorage.getItem( "savedOrgQueryInactive" )

      if( savedQueryString != null ) {

        if( savedQueryString != queryInfo ){
          this.payloadInactive = queryInfo;

        } else {
          this.payloadInactive = JSON.parse( localStorage.getItem( "savedOrgQueryInactive" ) )
        }

      } else{
        this.payloadInactive = queryInfo
      }

      // clear timeout variable
      clearTimeout( this.timeoutInactive )

      var self = this;

      this.timeoutInactive = setTimeout( function () {
        // enter this block of code after 1 second
        // handle stuff, call search API etc.
        self.$store.dispatch("organisations/fetchInactiveOrganisations", self.payloadInactive);
      }, 1000 )
    },

    changeTab(tab, event) {
      const status = tab.$options.propsData.name
    },

    handleSelectionChange(val) {
      this.organisationIds = [];
      for (var i = 0; i < val.length; i++) {
        this.organisationIds.push(val[i].id);
      }
    },

    addNewOrg() {
      this.$store.dispatch("organisations/addOrganisation").then(() => {
        this.showOrganisationFormModal = true
      });
    },

    clickRow( row, column ) {
      let nots = [ 'action b-none not-drawer', 'not-drawer', 'not-drawer left-dropdown' ]
      let index = nots.indexOf( column.className )

      if ( index == -1 ) {
        this.clicks++
        if ( this.clicks == 1 ) {
          this.timer = setTimeout( () => {
            this.showOrg( row )
            this.clicks = 0
          }, 500 )
        } else {
          clearTimeout( this.timer )
          this.editOrg( row )
          this.clicks = 0
        }
      }
    },

    showOrg( row ) {
      this.orgId = row.id

      this.$store.dispatch('organisations/getOrganisation', { id: row.id, load: true }).then(({ success, message, errors }) => {
        if (success) {
          this.updateAllDataInOrganisationProfile(row.id)
          this.$store.dispatch("organisations/openOrganisationOverview", this.orgId)
        }
      })
    },

    updateAllDataInOrganisationProfile(org_id){
      let queryInfo = {'pageNo': 1, 'pageSize': 10, 'orgId' : org_id}

      this.$store.dispatch("organisations/fetchCurrentLeads", org_id)

      this.$store.dispatch("organisations/fetchReassignedLeads", org_id)

      this.$store.dispatch('organisations/getLeadStats', org_id)
      console.log('updateAllDataInOrganisationProfile')

      this.$store.dispatch("organisations/fetchOrgComments", {
        org_id,
        queryInfo
      })

      this.$store.dispatch("orghistory/fetchOrgNotificationHistory", {
				id: org_id,
				queryInfo: queryInfo
			})

      this.$store.dispatch("organisations/fetchOrgPostcodes", org_id)

      this.$store.dispatch("organisations/editOrganisation", org_id)
    },

    closeOrganisationProfile(){
      console.log('organisation.index.closeOrganisationProfile')
      this.$store.dispatch("organisations/closeOrganisationOverview")
      this.$store.dispatch("leadhistory/closeLeadOverview")
      this.$store.dispatch("leadhistory/closeLeadOverviewNested")

    },

    editOrg(row) {
      this.$store.dispatch("organisations/editOrganisation", row.id).then(() => {
        this.showOrganisationFormModal = true
      });
    },

    deleteOrg(row) {
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
            .dispatch("organisations/deleteOrganisation", row.id)
            .then(({ success, message }) => {
              if (success) {
                this.$store.dispatch(
                  "organisations/fetchOrganisations",
                  this.payloadActive
                );
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

    import_organisations() {},

    queryModActive() {
      if ( this.isQuerySavedActive == true ){
        this.saveQueryActive()
      } else {
        this.removeSavedQueryActive()
      }
    },

    queryModInactive() {
      if ( this.isQuerySavedInactive == true ){
        this.saveQueryInactive()
      } else {
        this.removeSavedQueryInactive()
      }
    },

    saveQueryActive(){
      localStorage.setItem( "isOrgQuerySavedActive", this.isQuerySavedActive )
      localStorage.setItem( "savedOrgQueryActive", JSON.stringify( this.payloadActive ) )
    },

    saveQueryInactive(){
      localStorage.setItem( "isOrgQuerySavedInactive", this.isQuerySavedInactive )
      localStorage.setItem( "savedOrgQueryInactive", JSON.stringify( this.payloadInactive ) )
    },

    removeSavedQueryActive(){
      localStorage.setItem( "isOrgQuerySavedActive", this.isQuerySavedActive )
      localStorage.removeItem( "savedOrgQueryActive")

      this.filtersActive[0].value = "";
      this.filtersActive[1].value = "";
      this.filtersActive[2].value = "";
      this.filtersActive[3].value = "";
      this.filtersActive[4].value = "";
    },

    removeSavedQueryInactive(){
      localStorage.setItem( "isOrgQuerySavedInactive", this.isQuerySavedInactive )
      localStorage.removeItem( "savedOrgQueryInactive")

      this.filtersInactive[0].value = "";
      this.filtersInactive[1].value = "";
      this.filtersInactive[2].value = "";
      this.filtersInactive[3].value = "";
      this.filtersInactive[4].value = "";
    },
  },

  mounted() {
    /**
     * Active Saved Queries
     */
    var isQuerySavedActiveString = localStorage.getItem( "isOrgQuerySavedActive" )

    if ( isQuerySavedActiveString == "false" ) {
      this.isQuerySavedActive = false
      this.queryModActive()

    } else if ( isQuerySavedActiveString == "true" ) {
      this.isQuerySavedActive = true

      var savedQueryActiveString = localStorage.getItem( "savedOrgQueryActive" )
      var savedQueryActiveJSON = JSON.parse( savedQueryActiveString )

      if ( savedQueryActiveString != null ) {
        this.payloadActive = savedQueryActiveJSON

        this.filtersActive[0].value = savedQueryActiveJSON.filters[0].value
        this.filtersActive[1].value = savedQueryActiveJSON.filters[1].value
        this.filtersActive[2].value = savedQueryActiveJSON.filters[2].value
        this.filtersActive[3].value = savedQueryActiveJSON.filters[3].value
        this.filtersActive[4].value = savedQueryActiveJSON.filters[4].value
      }
    }

    /**
     * Inactive Saved Querires
     */
    var isQuerySavedInactiveString = localStorage.getItem( "isOrgQuerySavedInactive" )

    if ( isQuerySavedInactiveString == "false" ) {
      this.isQuerySavedInactive = false
      this.queryModInactive()

    } else if ( isQuerySavedInactiveString == "true" ) {
      this.isQuerySavedInactive = true

      var savedQueryInactiveString = localStorage.getItem( "savedOrgQueryInactive" )
      var savedQueryInactiveJSON = JSON.parse( savedQueryInactiveString )

      if ( savedQueryInactiveString != null ) {
        this.payloadInactive = savedQueryInactiveJSON

        this.filtersInactive[0].value = savedQueryInactiveJSON.filters[0].value
        this.filtersInactive[1].value = savedQueryInactiveJSON.filters[1].value
        this.filtersInactive[2].value = savedQueryInactiveJSON.filters[2].value
        this.filtersInactive[3].value = savedQueryInactiveJSON.filters[3].value
        this.filtersInactive[4].value = savedQueryInactiveJSON.filters[4].value
      }
    }

    if(this.$route.params.id){
      this.orgId = this.$route.params.id
      this.$store.dispatch('organisations/getOrganisation', { id: this.orgId, load: true }).then(({ success, message, errors }) => {
        if (success) {
          this.showOrganisationProfile = true
          this.updateAllDataInOrganisationProfile(this.orgId)
        }
      })
    }
  }
};
</script>

<style scoped>
  @media all and (max-width: 768px){
    .r-btn-reset{
      float: none;
    }
  }

  .organisation-status{
    border-radius: 25px;
    font-family: "SF UI Display Light";
    color: #303133;
  }

  .el-dialog__body{
  padding: 0px 20px !important;
}
.el-dialog__header{
  padding: 0px 20px 10px !important;
}
</style>
