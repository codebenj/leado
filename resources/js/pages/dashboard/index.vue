<template>
  <Section
    dusk="dashboardPage"
    v-if="user"
    :className="
      user.user_role.name == 'organisation'
        ? 'org-lead-dashbard lead-dashboard'
        : 'lead-dashboard'
    "
    :pageTitle="pageTitle"
  >
    <template
      v-slot:button
      v-if="
        isAssignedRoles(user, [
          'super_admin',
          'administrator',
          'user',
          'organisation'
        ])
      "
    >
      <el-button
        dusk="btn-create-lead-page"
        class="fl-right r-btn-reset"
        type="primary"
        @click="addNewOrg()"
        v-if="user.user_role.name !== 'organisation'"
        >New Customer Enquiry</el-button
      >

      <div
        class="org-breakdown fl-right r-btn-reset"
        v-if="user.user_role.name == 'organisation'"
      >
        <div class="breakdown">
          <p class="title">WON</p>
          <p class="number green">
            {{ org_data.won_count ? org_data.won_count : 0 }}
          </p>
        </div>
        <div class="breakdown divider">
          <p class="title">LOST</p>
          <p class="number red">
            {{ org_data.lost_count ? org_data.lost_count : 0 }}
          </p>
        </div>
        <div class="breakdown">
          <p class="title">UNRESOLVED</p>
          <p class="number orange">
            {{ org_data.unallocated_count ? org_data.unallocated_count : 0 }}
          </p>
        </div>
      </div>

      <div
        v-if="user.user_role.name == 'organisation'"
        class="fl-right organisation-status-update org-breakdown"
      >
        <div
          v-if="!user.organisation_user.organisation.is_available"
          class="on-hold"
        >
          <label>New Lead Availability</label
          ><span @click="openModalUpdateStatus">UPDATE</span>
          <p class="status">On Hold</p>
          <p>
            You requested not to receive any New<br />Leads until
            {{
              user.organisation_user.organisation.available_when
                | moment("DD/MM/YYYY")
            }}. You may<br />update your availability at any time.
          </p>
        </div>
        <div v-else class="is-available">
          <label>New Lead Availability</label
          ><span @click="openModalUpdateStatus">UPDATE</span>
          <p class="status">Available</p>
          <p>New Leads can be assigned to you!</p>
        </div>
      </div>
    </template>

    <template v-slot:content>
      <!-- Removed - no Org Suspended Notification -->
      <!-- <el-dialog
        :before-close="handleClose"
        v-if="organisationIsSuspended() && !orgWarningAlreadyOpen"
        :visible="userOrganisationSuspended"
        width="30%"
        :show-close="false"
        append-to-body
        id="suspended-dialog-org"
      >
        <template v-if="false">
          <h4 class="red-status">
            <b>Suspended</b>
          </h4>
          <p class="text-center" style="margin-top: 60px; font-size:1rem">
            You have been suspended from receiving new leads. You can still
            update existing leads.
          </p>
          <p class="text-center" style="font-size:1rem">
            Please contact Admin to resolve this:
          </p>
          <p class="text-center" style="font-size:1rem"><b>1300 334 333</b></p>
          <p class="text-center" style="font-size:1rem">
            <b>leads@topidea.com.au</b>
          </p>
        </template>
        <OrgWarning :isNotice="true" v-on:handleClose="handleClose" />
      </el-dialog> -->

      <el-dialog
        :before-close="handleNoticeModalClose"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible="noticeModal"
        width="30%"
        :show-close="false"
        append-to-body
        id="suspended-dialog-org"
      >
        <OrgWarning
          :isNotice="true"
          v-on:handleClose="handleNoticeModalClose"
        />
      </el-dialog>

      <el-card class="box-card b-none" shadow="never">
        <el-row
          v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
          style="margin-bottom: 30px;"
          :gutter="24"
        >
          <el-col
            :xs="24"
            :sm="24"
            :md="24"
            :lg="24"
            :xl="24"
          >
            <!-- <el-radio v-model="isQuerySaved" @change="saveQuery" label="1">Filters Saved</el-radio>
            <el-radio v-model="isQuerySaved" @change="removeSavedQuery" label="0">Filters Cleared</el-radio> -->
            <el-switch
              v-model="isQuerySaved"
              @change="queryMod"
              active-text="Lock Filter"
              inactive-text="Unlock Filter"
            ></el-switch>
          </el-col>

          <!-- <el-col :xs="3" :sm="3" :md="3" :lg="3" :xl="3">
            <el-button @click="saveQuery">Save Query</el-button>
          </el-col>
          <el-col :xs="3" :sm="3" :md="3" :lg="3" :xl="3">
            <el-button @click="removeSavedQuery">Clear Saved Query</el-button>
          </el-col> -->
        </el-row>

        <el-tabs
          type="card"
          v-model="tabSelected"
          @tab-click="handleClick"
          v-if="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
        >
          <!-- <el-tab-pane
            v-for="(item, index) in tabs"
            :key="item.name"
            :label="item.title"
            :name="item.name"
          >
          </el-tab-pane> -->

          <el-tab-pane label="All Leads" name="all_leads">

          </el-tab-pane>

          <el-tab-pane label="Needs Attention" name="needs_attention">

          </el-tab-pane>

          <el-tab-pane label="My Leads" name="my_leads">

          </el-tab-pane>

        </el-tabs>

        <leads-filter
          :isAdmin="
            isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
          "
          :organisations="organisations"
          :filters.sync="filters"
          :filterLevel.sync="filterLevel"
          :filterLevel2.sync="filterLevel2"
          :leadTypes.sync="leadTypes"
          :leadTypes2.sync="leadTypes2"
          :statuses.sync="statuses"
          :hasCritical="has_critical"
          :sortedBy.sync="sortedBy"
          :isLock="isQuerySaved"
          :tabSelected="tabSelected"
        ></leads-filter>

        <SliderGesture
          v-if="user.user_role.name == 'organisation' && isMobile"
        />

        <data-tables-server
          :data="leads"
          :total="total"
          :loading="loading"
          :pageSize="100"
          :pagination-props="{ pageSizes: [100, 50, 20] }"
          :filters="filters"
          :table-props="tableProps"
          @selection-change="handleSelectionChange"
          @query-change="loadMore"
          @row-click="clickRow"
          :class="user.user_role.name == 'organisation' ? 'org-lead-table' : ''"
        >
          <template v-if="user.user_role.name == 'organisation'">
            <template v-if="!isMobile">
              <el-table-column
                label="Lead ID"
                width="150"
                prop="lead.lead_id"
                :className="
                  user.user_role.name == 'organisation'
                    ? 'pl-3 org-table-head'
                    : 'org-table-head'
                "
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead.lead_id ? row.lead.lead_id : row.lead_id }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label="Enquirer"
                width="150"
                prop="lead.customer.first_name"
                :className="
                  user.user_role.name == 'organisation'
                    ? 'min-width org-table-head'
                    : 'min-width'
                "
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
              label="Escalation Level"
              prop="escalation_level"
              :className="
                user.user_role.name == 'organisation' ? 'org-table-head' : ''
              "
              width="150"
              align="center"
            >
              <template slot-scope="{ row }">
                <!-- <el-tag
                  size="medium"
                  type="primary"
                  class="escalation"
                  :class="row.color"
                  effect="dark"
                  disable-transitions
                >
                  {{ row.escalation_level }}
                </el-tag>
                 -->
                  <el-col :class="user.user_role.name == 'organisation' ? 'p-xs' : ''"><EscalationTag  :color="row.color" :escalation_level="row.escalation_level" /></el-col>
              </template>
            </el-table-column>

              <el-table-column
                label="Escalation Status"
                prop="escalation_status"
                width="300"
                align="center"
                :className="
                  user.user_role.name == 'organisation' ? 'org-table-head' : ''
                "
              >
                <template slot-scope="{ row }">
                  <el-col :class="user.user_role.name == 'organisation' ? 'p-xs' : ''">
                  <el-tooltip
                    :content="getTooltip(row)"
                    placement="top"
                    :disabled="!getTooltip(row)"
                    popper-class="font-size-14"
                  >
                    <!-- <el-tag
                      size="medium"
                      type="primary"
                      class="escalation"
                      :class="row.color"
                      effect="dark"
                      disable-transitions
                    >
                      <span
                        v-html="escalationStatusContent(row.escalation_status)"
                      ></span>
                      <countdown
                        class="d-block mt-status"
                        :auto-start="pauseTimers()"
                        v-if="expirationDate(row)"
                        :time="expirationDate(row)"
                      >
                        <template v-if="hideIfPaused()" slot-scope="props">
                          {{ timerFormat(props) }}
                        </template>
                      </countdown>
                    </el-tag> -->
                  <EscalationTag  :color="row.color" :escalation_status="row.escalation_status" :active_escalation="row" />
                  </el-tooltip>
                  </el-col>
                </template>
              </el-table-column>

              <el-table-column
                label="Phone"
                prop="lead.customer.contact_number"
                width="150"
                :className="
                  user.user_role.name == 'organisation' ? 'org-table-head' : ''
                "
              >
                <template slot-scope="{ row }">
                  <span
                    v-if="user && row.lead"
                    v-show="
                      hideEnquirerInformation(row.escalation_status, user)
                    "
                  >
                    {{ row.lead.customer.contact_number }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                width="150"
                label="Post Code"
                prop="lead.customer.address.postcode"
                :className="
                  user.user_role.name == 'organisation' ? 'org-table-head' : ''
                "
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead.customer.address.postcode }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label="Date"
                prop="lead.created_at"
                width="150"
                :className="
                  user.user_role.name == 'organisation' ? 'org-table-head' : ''
                "
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead && row.lead.created_at | moment("k:mm") }}
                    <br />
                    {{ row.lead && row.lead.created_at | moment("DD/MM/YYYY") }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label="Sale"
                prop="lead.sale"
                v-if="isAssignedRoles(user, ['organisation'])"
                width="150"
                :className="
                  user.user_role.name == 'organisation' ? 'org-table-head' : ''
                "
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row && (row.lead.sale ? "$ " + row.lead.sale : "") }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label=""
                width="150"
                align="center"
                class-name="d-action not-drawer action-display"
              >
                <template slot-scope="{ row }">
                  <leads-action
                    :row="row"
                    :user="user"
                    :is_critical="row.is_critical"
                    :isManualUpdateEnabled="
                      isManualUpdateEnabled(row.organisation)
                    "
                    v-on:singleClickRow="singleClickRow"
                  ></leads-action>
                </template>
              </el-table-column>
            </template>
            <template v-else>
              <el-table-column
                label=""
                width="150"
                align="center"
                class-name="d-action not-drawer action-display"
              >
                <template slot-scope="{ row }">
                  <leads-action
                    :row="row"
                    :user="user"
                    :is_critical="row.is_critical"
                    :isManualUpdateEnabled="
                      isManualUpdateEnabled(row.organisation)
                    "
                    v-on:singleClickRow="singleClickRow"
                  ></leads-action>
                </template>
              </el-table-column>

              <el-table-column
                label="Enquirer"
                width="150"
                prop="lead.customer.first_name"
                class-name="min-width"
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
                label="Escalation Status"
                prop="escalation_status"
                width="300"
              >
                <template slot-scope="{ row }">
                  <el-col>
                  <el-tooltip
                    :content="getTooltip(row)"
                    placement="top"
                    :disabled="!getTooltip(row)"
                    popper-class="font-size-14"
                  >
                    <!-- <el-tag
                      size="medium"
                      type="primary"
                      class="escalation"
                      :class="row.color"
                      effect="dark"
                      disable-transitions
                    >
                      <span
                        v-html="escalationStatusContent(row.escalation_status)"
                      ></span>

                      <countdown
                        class="d-block mt-status"
                        :auto-start="pauseTimers()"
                        v-if="expirationDate(row)"
                        :time="expirationDate(row)"
                      >
                        <template v-if="hideIfPaused()" slot-scope="props">
                          {{ timerFormat(props) }}
                        </template>
                      </countdown>
                    </el-tag> -->
                    <EscalationTag  :color="row.color" :escalation_status="row.escalation_status" :active_escalation="row" />
                  </el-tooltip>
                  </el-col>
                </template>
              </el-table-column>

              <el-table-column
                label="Phone"
                prop="lead.customer.contact_number"
                width="150"
              >
                <template slot-scope="{ row }">
                  <span
                    v-if="user && row.lead"
                    v-show="
                      hideEnquirerInformation(row.escalation_status, user)
                    "
                  >
                    {{ row.lead.customer.contact_number }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                width="150"
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
                width="150"
                label="Post Code"
                prop="lead.customer.address.postcode"
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead.customer.address.postcode }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column label="Date" prop="lead.created_at" width="150">
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead && row.lead.created_at | moment("k:mm") }}
                    <br />
                    {{ row.lead && row.lead.created_at | moment("DD/MM/YYYY") }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label="Lead ID"
                width="150"
                prop="lead.lead_id"
                :className="user.user_role.name == 'organisation' ? 'pl-3' : ''"
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row.lead.lead_id ? row.lead.lead_id : row.lead_id }}
                  </span>
                </template>
              </el-table-column>

              <el-table-column
                label="Sale"
                prop="lead.sale"
                v-if="isAssignedRoles(user, ['organisation'])"
                width="150"
              >
                <template slot-scope="{ row }">
                  <span v-if="row.lead">
                    {{ row && (row.lead.sale ? "$ " + row.lead.sale : "") }}
                  </span>
                </template>
              </el-table-column>
            </template>
          </template>
          <template v-else>
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
                  :is_critical="row.is_critical"
                  :isManualUpdateEnabled="
                    isManualUpdateEnabled(row.organisation)
                  "
                  v-on:singleClickRow="singleClickRow"
                ></leads-action>
              </template>
            </el-table-column>
            <el-table-column
              label="Lead ID"
              width="120"
              prop="lead.lead_id"
              align="center"
            >
              <template slot-scope="{ row }">
                <span v-if="row.lead">
                  {{ row.lead.lead_id ? row.lead.lead_id : row.lead_id }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              width="auto"
              label="Enquirer"
              prop="lead.customer.first_name"
              class-name="min-width"
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
              width="130"
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
              width="auto"
              label="Organisation"
              prop="organisation.name"
              v-if="
                isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
              "
              class-name="min-width"
            >
              <template slot-scope="{ row }">
                <span
                  v-if="hideOrganisationEscalationStatus(row.escalation_status)"
                >
                  {{
                    row.organisation && row.organisation.name
                      ? row.organisation.name
                      : ""
                  }}

                  <ManualIcon :org="row.organisation" />
                </span>
              </template>
            </el-table-column>

            <el-table-column
              label="Phone"
              prop="lead.customer.contact_number"
              width="140"
            >
              <template slot-scope="{ row }">
                <span
                  v-if="user && row.lead"
                  v-show="hideEnquirerInformation(row.escalation_status, user)"
                >
                  {{ row.lead.customer.contact_number }}
                </span>
              </template>
            </el-table-column>

            <el-table-column
              label="Escalation Level"
              prop="escalation_level"
              v-if="
                isAssignedRoles(user, ['super_admin', 'administrator', 'user'])
              "
              width="auto"
              align="center"
            >
              <template slot-scope="{ row }">
                <!-- <el-tag
                  @click.native="showLeadHistory(row)"
                  size="medium"
                  type="primary"
                  class="escalation"
                  :class="row.color"
                  effect="dark"
                  disable-transitions
                >
                  {{ row.escalation_level }}
                </el-tag> -->

                  <el-col><EscalationTag  :color="row.color" :escalation_level="row.escalation_level" /></el-col>

              </template>
            </el-table-column>

            <el-table-column
              label="Escalation Status"
              prop="escalation_status"
              width="300"
              align="center"
            >
              <template slot-scope="{ row }">

                <el-col>
                <el-tooltip
                  :content="getTooltip(row)"
                  placement="top"
                  :disabled="!getTooltip(row)"
                  popper-class="font-size-14"
                >
                  <!-- <el-tag
                    size="medium"
                    type="primary"
                    class="escalation"
                    :class="row.color"
                    effect="dark"
                    disable-transitions
                  >
                    <span
                      v-html="escalationStatusContent(row.escalation_status)"
                    ></span>

                    <countdown
                      class="d-block mt-status"
                      :auto-start="pauseTimers()"
                      v-if="expirationDate(row)"
                      :time="expirationDate(row)"
                    >
                      <template v-if="hideIfPaused()" slot-scope="props">
                        {{ timerFormat(props) }}
                      </template>
                    </countdown>
                  </el-tag> --><EscalationTag  :color="row.color" :escalation_status="row.escalation_status" :active_escalation="row" />
                </el-tooltip>
                </el-col>
              </template>
            </el-table-column>

            <el-table-column
              label="Sale"
              prop="lead.sale"
              v-if="isAssignedRoles(user, ['organisation'])"
              width="100px"
            >
              <template slot-scope="{ row }">
                <span v-if="row.lead">
                  {{ row && (row.lead.sale ? "$ " + row.lead.sale : "") }}
                </span>
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
              <template slot-scope="{ row }" v-if="checkProp() && row.lead">
                <div class="assign-popover">
                  <AssignUsersPopover
                  :id="row.lead_id"
                  :assigned_users="checkAssignedUsers(row)"
                  :users="users"
                  :type="'index'"
                />
                </div>
              </template>
            </el-table-column>

            <el-table-column
              label=""
              width="55"
              align="center"
              class-name="action b-none not-drawer"
              v-if="!isMobile"
            >
              <template slot-scope="{ row }">
                <leads-action
                  :row="row"
                  :user="user"
                  :is_critical="row.is_critical"
                  :isManualUpdateEnabled="
                    isManualUpdateEnabled(row.organisation)
                  "
                  v-on:singleClickRow="singleClickRow"
                ></leads-action>
              </template>
            </el-table-column>
          </template>
        </data-tables-server>

      </el-card>

      <EscalationModal v-on:singleClickRow="singleClickRow" />

      <template v-if="user.user_role.name == 'administrator' && showOrganisationProfile">
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
          :before-close="beforeCloseNested"
        >
        <DrawerAdmin :id="historyDrawerNestedId" />
        </el-drawer>
      </template>

      <template
        v-if="historyDrawerId && user.user_role.name == 'administrator'"
      >
        <el-drawer
          ref="drawerAdmin"
          :visible.sync="historyDrawer"
          :with-header="false"
          :destroy-on-close="true"
          size="60%"
          append-to-body
          :before-close="beforeClose"
        >
          <DrawerAdmin :id.sync="historyDrawerId" v-on:closeDrawer="closeDrawer" v-on:openOrg="openOrg" />
        </el-drawer>
      </template>

      <template v-if="historyDrawerId && user.user_role.name == 'organisation'">
        <el-drawer
          ref="drawerAdmin"
          :visible.sync="historyDrawer"
          :with-header="false"
          :destroy-on-close="true"
          size="60%"
          append-to-body
          :before-close="beforeClose"
        >
          <DrawerOrg :id="historyDrawerId" v-on:closeDrawer="closeDrawer" />
        </el-drawer>
      </template>

      <template
        v-if="
          user.user_role.name == 'organisation' &&
            user.organisation_user.organisation
        "
      >
        <el-dialog
          v-dialogDrag
          ref="dialog__wrapper"
          :visible.sync="organisationStatusDialogVisible"
          width="28%"
          :show-close="true"
          append-to-body
          :before-close="organisationStatusDialogHandleClose"
          custom-class="organisation-status"
        >
          <OrganisationStatus
            :orgId="user.organisation_user.organisation.id"
            @closeMe="organisationStatusDialogHandleClose"
          />
        </el-dialog>
      </template>
    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import LeadsFilter from "~/components/dashboard/filters";
import LeadsAction from "~/components/dashboard/actions";
import EscalationModal from "~/components/escalations/main";
import ManualIcon from "~/components/ManualIcon";
import PostcodeIcon from "~/components/PostcodeIcon.vue";
import OrgWarning from "~/components/OrgWarning";

import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import {
  isAssignedRoles,
  isManualUpdateEnabled,
  hideEnquirerInformation,
  hideOrganisationEscalationStatus
} from "~/helpers";
import DrawerAdmin from "~/components/DrawerAdmin";
import DrawerOrg from "~/components/DrawerOrg";
import AssignUsersPopover from "~/components/AssignUsersPopover";

import Cookies from "js-cookie";
import { Bus } from "~/app";

import SliderGesture from "~/components/SliderGesture";
import OrganisationStatus from "~/components/OrganisationStatus";
import OrganisationProfile from "~/components/OrganisationProfile";
import EscalationTag from "~/components/EscalationTag";

export default {
  name: "LeadsDashboard",
  layout: "master",
  components: {
    Section,
    LeadsFilter,
    LeadsAction,
    EscalationModal,
    DataTablesServer,
    ManualIcon,
    DrawerAdmin,
    DrawerOrg,
    AssignUsersPopover,
    OrgWarning,
    SliderGesture,
    PostcodeIcon,
    OrganisationStatus,
    OrganisationProfile,
    EscalationTag
  },

  data() {
    return {
      orgId: 0,
      showOrganisationProfile: false,
      orgWarningAlreadyOpen: false,
      organisationStatusDialogVisible: false,
      pageTitle: "Leads Dashboard",
      tableProps: {
        rowClassName: function(row, index) {
          const { color } = row.row;
          const { is_critical } = row.row;
          let crit = is_critical ? " critical" : "";

          return color + crit;
        }
      },
      sortedBy: [
        {
          value: "Default",
          label: "Default"
        },
        {
          value: "Timer",
          label: "Timer"
        },
        {
          value: "Date",
          label: "Date"
        }
      ],
      statuses: [
        {
          value: "Special Opportunity",
          label: "Special Opportunity"
        },
        {
          value: "Declined",
          label: "Declined"
        },
        {
          value: "Waiting",
          label: "Waiting",
        },
        {
          value: "Critical",
          label: "Critical",
        },
        {
          value: "Prompt",
          label: "Prompt"
        },
        {
          value: "Discontinued",
          label: "Discontinued"
        },
        {
          value: "Lost",
          label: "Lost"
        },
        {
          value: "New",
          label: "New"
        },
        {
          value: "In Progress",
          label: "In Progress"
        },
        {
          value: "Finalized",
          label: "Finalized"
        },
      ],
      filterLevel: [
        {
          value: "",
          label: "Select"
        },
        {
          value: "Unassigned",
          label: "Unassigned"
        },
        {
          value: "New Lead",
          label: "New Lead"
        },
        {
          value: "Contact",
          label: "Contact"
        },
        {
          value: "In Progress",
          label: "In Progress"
        },
        {
          value: "General Inquiry",
          label: "General Inquiry"
        },
        {
          value: "Supply Only",
          label: "Supply Only"
        },
        {
          value: "Won",
          label: "Won"
        },
        {
          value: "Lost",
          label: "Lost"
        },
        {
          value: "Lost - Inconclusive",
          label: "Lost - Inconclusive"
        }
      ],
      filterLevelSO: [
        {
          value: "",
          label: "Select"
        },

        {
          value: "In Progress",
          label: "In Progress"
        },
        {
          value: "New",
          label: "New"
        },
        {
          value: "Finalized",
          label: "Finalized"
        }
      ],
      filterLevel2: {
          Unassigned: ["Special Opportunity", "Lost"],
          "New Lead": ["Waiting", "Critical" , "Declined"],
          "Contact": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
          "In Progress": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
          "Supply Only": ['New', 'In Progress', 'Finalized'],
          "General Enquiry": ['New', 'In Progress', 'Finalized']
      },
      leadTypes: {
        "Supply & Install": {
          Unassigned: ["Special Opportunity", "Lost"],
          "New Lead": ["Waiting", "Critical" , "Declined"],
          "Contact": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
          "In Progress": [
            "Waiting",
            "Prompt",
            "Critical" ,
            "Discontinued"
          ],
        },
        "Supply Only": {
          "Supply Only": ['New', 'In Progress', 'Finalized']
        },
        "General Enquiry": {
          "General Enquiry": ['New', 'In Progress', 'Finalized']
        }
      },
      leadTypes2: {
        "Supply & Install": [
          {
            value: "",
            label: "Select"
          },
          {
            value: "Unassigned",
            label: "Unassigned"
          },
          {
            value: "New Lead",
            label: "New Lead"
          },
          {
            value: "Contact",
            label: "Contact"
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
          },
          {
            value: "Lost - Inconclusive",
            label: "Lost - Inconclusive"
          }
        ],
        "Supply Only": [
          {
            value: "",
            label: "Select"
          },
          {
            value: "Supply Only",
            label: "Supply Only"
          },
          // {
          //   value: "New",
          //   label: "New"
          // },
          // {
          //   value: "In Progress",
          //   label: "In Progress"
          // },
          // {
          //   value: "Finalized",
          //   label: "Finalized"
          // }
        ],
        "General Enquiry": [
          {
            value: "",
            label: "Select"
          },
          {
            value: "General Enquiry",
            label: "General Enquiry"
          },
          // {
          //   value: "In Progress",
          //   label: "In Progress"
          // },
          // {
          //   value: "Finalized",
          //   label: "Finalized"
          // }
        ]
      },
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
        },
        {
          prop: "escalation_status",
          value: ""
        },
        {
          prop: "lead_type",
          value: ""
        },
        {
          prop: "sorted_by",
          value: "Default"
        },
        {
          prop: "lead_group",
          value: 0
        }
      ],
      tabs: [
        {
          title: 'All Leads',
          name: 'all_leads'
        },
        {
          title: 'Needs Attention',
          name: 'needs_attention'
        },
        {
          title: 'My Leads',
          name: 'my_leads'
        },
      ],
      query: {},
      isQueryReady: true,
      isQuerySaved: null,
      clicks: 0,
      timer: null,
      timerSettings: null,
      users: [],
      userLoading: true,
      isMobile: false,
      tabSelected: 'all_leads'
    };
  },

  computed: mapGetters({
    user: "auth/user",
    userOrganisationSuspended: "auth/userOrganisationSuspended",
    noticeModal: "leads/noticeModal",
    leads: "leads/leads",
    paused_dates: "leads/paused_dates",
    is_paused: "leads/is_paused",
    has_critical: "leads/has_critical",
    org_data: "leads/org_data",
    organisations: "leads/organisations",
    organisation: "organisations/organisations",
    loading: "leads/loading",
    total: "leads/total",
    escalationModals: "leadescalation/escalationModals",
    adminEmailSettings: "settings/setting",
    timesettings: "timesetting/timesettings",
    historyDrawer: "leadhistory/historyDrawer",
    historyDrawerId: "leadhistory/historyDrawerId",
    historyDrawerNested: 'leadhistory/historyDrawerNested',
    historyDrawerNestedId: 'leadhistory/historyDrawerNestedId',
    leadForm: "leads/leadForm",
  }),

  methods: {
    hideOrganisationEscalationStatus: hideOrganisationEscalationStatus,

    isAssignedRoles: isAssignedRoles,

    hideEnquirerInformation: hideEnquirerInformation,

    handleSelectionChange() {},

    openOrg(org_id){
      this.orgId = org_id
      this.showOrganisationProfile = true
    },

    closeOrganisationProfile(){
      this.showOrganisationProfile = false
    },

    handleClick(tab, event) {
      this.filters[7].value = tab.index
      this.tabSelected = this.tabs[tab.index].name
    },

    organisationStatusDialogHandleClose() {
      this.organisationStatusDialogVisible = false;
    },

    openModalUpdateStatus() {
      this.organisationStatusDialogVisible = true;
      this.orgWarningAlreadyOpen = true;
    },

    organisationIsSuspended() {
      if (
        this.user &&
        this.user.organisation_user &&
        this.user.organisation_user.organisation
      ) {
        if (this.user.organisation_user.organisation.is_suspended == "1") {
          return true;
        }
      }
      return false;
    },

    handleClose() {
      this.$store
        .dispatch("auth/closeOrganisationSuspendedModal")
        .then(_ => {})
        .catch(_ => {});
    },

    handleNoticeModalClose() {
      this.$store.dispatch("leads/closeNoticeModal");
    },

    queryMod() {
      if (this.isQuerySaved == true) {
        this.saveQuery();
      } else {
        this.removeSavedQuery();
      }
    },

    saveQuery() {
      localStorage.setItem("isQuerySaved", this.isQuerySaved);
      localStorage.setItem("savedQuery", JSON.stringify(this.query));
    },

    removeSavedQuery() {
      localStorage.setItem("isQuerySaved", this.isQuerySaved);
      localStorage.removeItem("savedQuery");

      this.filters[0].value = "";
      this.filters[1].value = "";
      this.filters[2].value = "";
      this.filters[3].value = "";
      this.filters[4].value = "";
      this.filters[5].value = "";
      this.filters[6].value = "";

      Bus.$emit("saved-query");
    },

    async loadMore(queryInfo) {
      this.userLoading = true;
      if (this.isQuerySaved == true) {
        this.queryMod();
      }
      var savedQueryString = localStorage.getItem("savedQuery");

      if (savedQueryString != null) {
        if (savedQueryString != queryInfo) {
          this.query = queryInfo;
        } else {
          this.query = JSON.parse(localStorage.getItem("savedQuery"));
        }
      } else {
        this.query = queryInfo;
      }

      clearTimeout(this.timeout);

      var self = this;

      this.timeout = setTimeout(function() {
        // enter this block of code after 1 second
        // handle stuff, call search API etc.
        self.$store.dispatch("leads/fetchLeads", self.query).then(() => {
          setTimeout(() => {
            Bus.$emit("user-loading", false);
          }, 500);
        });
      }, 1000);
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
      // const now = new Date();
      // const expirationDate = Date.parse(row.expiration_date);
      // const expirationValue1 = expirationDate > Date.parse(now) ? expirationDate - Date.parse(now) : 0;
      // const expirationValue2 = (row.paused_time != null && expirationDate > now) ? expirationValue1 + row.paused_time : 0;

      // // console.log(row.paused_time);

      // return expirationValue1;
      return row.time_left ? row.time_left : 0;
    },

    pauseTimers() {
      return this.is_paused == true ? false : true;
    },

    hideIfPaused() {
      // return ( this.is_paused == true ) ? false : true

      // IF TIMER SHOULD SHOW BUT PAUSED
      return true;
    },

    showLeadHistory(row) {
      this.singleClickRow(row);
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

    isManualUpdateEnabled: isManualUpdateEnabled,

    clickRow(row, column) {
      let nots = [
        "not-drawer action-display",
        "d-action not-drawer action-display",
        "action b-none not-drawer",
        "not-drawer",
        "not-drawer left-dropdown",
        "action b-none not-drawer action-display"
      ];
      let index = nots.indexOf(column.className);
      if (index == -1) {
        this.clicks++;
        if (this.clicks == 1) {
          this.timer = setTimeout(() => {
            this.singleClickRow(row);
            this.clicks = 0;
          }, 200);
        } else {
          clearTimeout(this.timer);
          if (this.user.user_role.name == "organisation") {
            this.singleClickRow(row);
          } else {
            this.doubleClickRow(row);
          }
          this.clicks = 0;
        }
      }
    },

    singleClickRow(row) {
      let lead_id = row.lead_id

      localStorage.setItem('lead_id', lead_id)

      this.$store.dispatch("leadhistory/openLeadOverview", lead_id)
    },

    doubleClickRow(row) {
      this.leadForm.reset();
      const isAdmin = isAssignedRoles(this.user, [
        "super_admin",
        "administrator",
        "user"
      ]);

      if (row.lead.lead_id) {
        Cookies.set("new_lead_id", row.lead.lead_id);
      } else {
        Cookies.remove("new_lead_id");
      }

      let id = row.lead.lead_id ? row.lead.lead_id : row.lead_id;
      // OLD CODE: let id = row.lead.lead_id ? row.lead.lead_id : ( isAdmin ) ? row.id : row.lead_id

      this.$router.push({
        name: isAdmin ? "admin.leads.update" : "organisation.lead",
        params: {
          id: id
        }
      });
    },

    closeDrawer(id) {
      Cookies.remove("lead_id")
      Bus.$emit("reload-assigned", id)
      this.$store.dispatch("leadhistory/closeLeadOverview")
      //this.$refs.drawerAdmin.closeDrawer()
      console.log('index.closeDrawer')
    },

    beforeClose(done){
      //Cookies.remove("lead_id")
      //Bus.$emit("reload-assigned", id)
      //this.$refs.drawerAdmin.closeDrawer()
      this.$store.dispatch("leadhistory/closeLeadOverview")
    },

    beforeCloseNested(){
      this.$store.dispatch("leadhistory/closeLeadOverviewNested")
    },

    showEmailRecievers() {
      try {
        let emails = String(this.adminEmailSettings.value);
        return emails.trim().split(",");
      } catch (e) {
        return [];
      }
    },

    checkProp() {
      return this.userLoading ? false : true;
    },

    checkAssignedUsers(row) {
      let index = this.leads.findIndex(lead => lead.id == row.id);

      if (index !== -1) {
        return row.lead.user_ids;
      }

      return "[]";
    },
    escalationStatusContent(val) {
      const _escalation_status = val.split(" - ");
      let data_escalation = "";
      _escalation_status.forEach((element, index) => {
        data_escalation += `<span class="d-block mt-status">
                            ${element}
                            </span>`;
      });
      return data_escalation;
    }
  },

  async beforeMount() {
    if (
      this.user &&
      this.isAssignedRoles(this.user, ["super_admin", "administrator", "user"])
    ) {
      await this.$store.dispatch("leads/fetchOrgAll");
    }

    this.$store.dispatch(
      "settings/getGetSetting",
      "admin-email-notification-receivers"
    );

    // FETCH TIME SETTINGS
    this.$store.dispatch("timesetting/fetchTimesettings").then(result => {
      this.timerSettings = result.data.data;
    });
    await this.$store.dispatch(
      "settings/getGetSetting",
      "admin-email-notification-receivers"
    );
    await this.$store.dispatch("leadassign/fetchUsers").then(res => {
      this.users = res;
      // this.userLoading = false
    });
  },

  mounted() {
    this.$echo
      .channel("traleado-global")
      .listen(".leadescalation.created", data => {
        // Prevent reloading dashboard when toaster is shown
        // this.$store.dispatch("leads/fetchLeads", this.query);
      });

    var isQuerySavedString = localStorage.getItem("isQuerySaved");

    if (isQuerySavedString == "false") {
      this.isQuerySaved = false;
      this.queryMod();
    } else if (isQuerySavedString == "true") {
      this.isQuerySaved = true;

      var savedQueryString = localStorage.getItem("savedQuery");
      var savedQueryJSON = JSON.parse(savedQueryString);

      if (savedQueryString != null) {
        this.query = savedQueryJSON;

        this.filters[0].value = savedQueryJSON.filters[0].value;
        this.filters[1].value = savedQueryJSON.filters[1].value;
        this.filters[2].value = savedQueryJSON.filters[2].value;
        this.filters[3].value = savedQueryJSON.filters[3].value;
        this.filters[4].value = savedQueryJSON.filters[4].value;
        this.filters[5].value = savedQueryJSON.filters[5].value;
        this.filters[6].value = savedQueryJSON.filters[6].value;
        this.filters[7].value = savedQueryJSON.filters[7].value;
        this.tabSelected = this.tabs[this.filters[7].value].name
      }

      Bus.$emit("saved-query");
    }

    const lead_id = parseInt(Cookies.get("lead_id"));
    if (lead_id) {
      Cookies.remove("lead_id");
      this.singleClickRow({ lead_id: lead_id });
    }

    Bus.$on("init_drawer", event => {
      this.singleClickRow({ lead_id: event });
    });

    Bus.$on("update-state-lead", event => {
      this.leads.forEach((lead, index) => {
        if (
          lead.lead.lead_id == event.lead_id ||
          lead.lead_id == event.lead_id
        ) {
          this.$store.dispatch("leads/updateStateLead", {
            index: index,
            ids: event.ids
          });
        }
      });
    });

    Bus.$on("user-loading", event => {
      this.userLoading = event;
    });

    setInterval(() => {
      let width = window.screen.width;
      if (width <= 768) {
        this.isMobile = true;
      } else {
        this.isMobile = false;
      }
    }, 500);

    this.$store.dispatch("leadhistory/closeLeadOverview")
    this.$store.dispatch("leadhistory/closeLeadOverviewNested")
  }
};
</script>

<style>
.organisation-status-update {
  border: 1px solid #dcdfe6;
  box-sizing: border-box;
  border-radius: 4px;

  padding: 15px;
  font-size: 16px;
  font-family: "SF UI Display Light";

  color: #303133;
}

.organisation-status-update label {
  color: #303133;
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 14px;
}

.organisation-status-update span {
  font-style: normal;
  font-weight: 500;
  font-size: 14px;
  line-height: 14px;
  color: #409eff;
  cursor: pointer;
  margin-left: 10px;
}

.organisation-status-update .is-available p {
  color: #4caf50;
  font-style: normal;
  font-weight: normal;
  font-size: 12px;
  line-height: 18px;
  margin-bottom: 0px;
}
.organisation-status-update .is-available .status {
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 14px;
  margin-bottom: 5px;
}

.organisation-status-update .on-hold .status {
  font-style: normal;
  font-weight: bold;
  font-size: 14px;
  line-height: 14px;
  margin-bottom: 5px;
}

.organisation-status-update .on-hold {
  font-style: normal;
  font-weight: normal;
  font-size: 10px;
  line-height: 16px;
  color: #f44336;
}

.organisation-status-update .on-hold p {
  font-style: normal;
  font-weight: normal;
  font-size: 12px;
  line-height: 18px;
  margin-bottom: 0px;
}

.el-table__row td {
  padding-left: 0px;
  padding-right: 0px;
}

.el-tooltip__popper {
  font-size: 14px !important;
}

.el-select-dropdown__wrap {
  max-height: 600px;
}

.el-drawer__body {
  height: 100%;
  box-sizing: border-box;
  overflow-y: auto;
}

.not-drawer.left-dropdown .el-dropdown {
  background-color: #f5f7fa;
  border-radius: 3px;
  color: #117ee7;
  padding: 0 5px;
}

.org-lead-dashbard .page-header {
  margin-bottom: 0px !important;
}

.org-lead-dashbard .el-card__body {
  padding-bottom: 0px !important;
}

.d-action .el-dropdown {
  background-color: #f5f7fa;
  border-radius: 3px;
  color: #117ee7;
  padding: 0 5px;
}

.el-table__header-wrapper .org-table-head .cell {
  font-size: 15px;
}

.pl-3 {
  padding-left: 1.5em !important;
}

.critical {
  background: #fff48a !important;
}

.critical .action-display {
  background: #fff48a !important;
}

.critical:hover .action-display {
  background: #f5f7fa !important;
}

.action-display {
  opacity: 1 !important;
}

.assign-popover {
  display: flex;
  justify-content: center;
}

#suspended-dialog-org .el-dialog {
  border-radius: 25px;
}

#suspended-dialog-org .el-dialog .el-dialog__body {
  padding: 40px 20px;
  padding-bottom: 45px;
}

#suspended-dialog-org .el-dialog__header {
  display: none !important;
}

.org-breakdown {
  display: flex;
  gap: 10px;
}

.org-breakdown .breakdown {
  font-size: 17px;
  color: #7d8087;
}

.org-breakdown .breakdown .title {
  font-family: "SF UI Display Light";
  color: #7d8087 !important;
  font-size: 15px;
}

.org-breakdown .breakdown p {
  margin-bottom: 0px;
}

.org-breakdown .breakdown.divider {
  padding: 0px 10px;
  border-left: 2px solid rgba(128, 128, 128, 0.1);
  border-right: 2px solid rgba(128, 128, 128, 0.1);
}

.org-breakdown .breakdown .number {
  font-size: 23px;
}

.org-breakdown .breakdown .number.green {
  color: #67c23a;
}

.org-breakdown .breakdown .number.red {
  color: #f56c6c;
}

.org-breakdown .breakdown .number.orange {
  color: #fbad37;
}

.mt-status {
  margin-top: 0px !important;
}

.organisation-status {
  border-radius: 25px;
}

@media all and (min-width: 1600px) {
  #suspended-dialog-org .el-dialog {
    width: 25% !important;
  }

  .org-lead-table table {
    width: 100% !important;
  }

  .organisation-status-update {
    margin-right: 20px;
    margin-top: -10px !important;
  }
}

@media all and (max-width: 980px) {
  .org-breakdown.fl-right {
    float: unset !important;
    padding-top: 25px;
  }

  .organisation-status-update {
    padding-right: 20px;
    margin-top: 10px !important;
  }
}

@media all and (max-width: 768px) {
  .el-drawer.rtl {
    width: 100% !important;
  }
}

@media all and (max-width: 426px) {
  .r-btn-reset {
    float: none;
  }
}
</style>
