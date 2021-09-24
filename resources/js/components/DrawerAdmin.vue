<template>
  <el-row
    :gutter="24"
    class="mx-0 mt-3 drawer-container"
    v-if="activeEscalation"
  >
    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
      <el-card class="box-card b-none pb0" shadow="never">
        <fa
          icon="times"
          fixed-width
          class="clickable drawerClose"
          @click="closeDrawer"
        />
        <!-- LO Head Content -->
        <el-row :gutter="24">
          <el-col :xs="24" :sm="24" :md="24" :lg="18" :xl="18">
            <el-row :gutter="24">
              <el-col
                :xs="24"
                :sm="24"
                :md="17"
                :lg="17"
                :xl="17"
                class="mt-4 mb-5"
              >
                <h1>
                  <span>{{ title }}</span>
                </h1>
              </el-col>
              <el-col
                :xs="24"
                :sm="24"
                :md="activeEscalation ? 24 : 24"
                :lg="activeEscalation ? 22 : 24"
                :xl="activeEscalation ? 22 : 24"
              >
                <el-row :gutter="24"  class="mb-3">
                  <el-col :span="24">
                    <!-- <el-row :gutter="24" v-if="activeEscalation" align="middle">
                      <el-col
                        :xs="24"
                        :sm="24"
                        :md="24"
                        :lg="24"
                        :xl="24"
                        class="el-tags-flex"
                      >
                        <el-tag
                          size="medium"
                          type="primary"
                          class="zoom-25"
                          :class="activeEscalation.color"
                          effect="dark"
                          disable-transitions
                          >{{ activeEscalation.escalation_level }}</el-tag
                        >

                        <el-tooltip
                          :content="getTooltip(activeEscalation)"
                          placement="top"
                          :disabled="!getTooltip(activeEscalation)"
                          popper-class="font-size-14"
                        >
                          <el-tag
                            size="medium"
                            type="primary"
                            class="escalation zoom-25 ml-1"
                            :class="activeEscalation.color"
                            effect="dark"
                            disable-transitions
                          >
                            {{ activeEscalation.escalation_status }}

                            <countdown
                              v-if="expirationDate(activeEscalation)"
                              :time="expirationDate(activeEscalation)"
                            >
                              <template slot-scope="props">
                                {{ timerFormat(props) }}
                              </template>
                            </countdown>
                          </el-tag>
                        </el-tooltip>
                      </el-col>
                    </el-row> -->


                    <el-row :gutter="24" v-if="activeEscalation" type="flex" class="escalation-row" align="middle">
                      <el-col
                        :xs="24"
                        :sm="24"
                        :md="8"
                        :lg="8"
                        :xl="8"
                        class="el-tags-flex mt-3"
                      >
                          <EscalationTag  :color="activeEscalation.color" :escalation_level="activeEscalation.escalation_level" />
                      </el-col>
                      <el-col
                        :xs="24"
                        :sm="24"
                        :md="13"
                        :lg="13"
                        :xl="13"
                        class="el-tags-flex mt-3"
                      >
                        <el-tooltip
                          :content="getTooltip(activeEscalation)"
                          placement="top"
                          :disabled="!getTooltip(activeEscalation)"
                          popper-class="font-size-14"
                        >

                          <EscalationTag :color="activeEscalation.color" :escalation_status="activeEscalation.escalation_status" :active_escalation="activeEscalation"/>
                        </el-tooltip>
                      </el-col>
                    </el-row>
                  </el-col>
                  <!-- Details & History Content -->

                  <!-- Enquirere Details -->
                  <el-col
                    :xs="24"
                    :sm="9"
                    :md="9"
                    :lg="9"
                    :xl="9"
                    v-if="lead"
                    class="detail-details mb-3"
                    :class="{ 'no-metters-col': !metersForm.id }"
                  >
                    <span class="drawer-tabs-title">Enquirer Details</span>
                    <p class="detail">
                      {{
                        checkData(lead.customer.first_name) +
                          " " +
                          checkData(lead.customer.last_name)
                      }}
                    </p>
                    <p class="detail">
                      {{ checkData(lead.customer.contact_number) }}
                    </p>
                    <p class="detail">{{ checkData(lead.customer.email) }}</p>
                    <p class="detail">
                      {{
                        checkData(lead.customer.address.suburb) +
                          " " +
                          checkData(lead.customer.address.address)
                      }}
                    </p>
                    <p class="detail">
                      {{
                        checkData(lead.customer.address.state) +
                          " " +
                          checkData(lead.customer.address.postcode)
                      }}
                    </p>
                  </el-col>

                  <!-- Org Details -->
                  <el-col
                    :xs="24"
                    :sm="9"
                    :md="9"
                    :lg="9"
                    :xl="9"
                    class="detail-details mb-3"
                    :class="{ 'no-metters-col': !metersForm.id }"
                    v-if="
                      activeEscalation &&
                        hideOrganisationEscalationStatus(
                          activeEscalation.escalation_status
                        )
                    "
                  >
                    <span class="drawer-tabs-title">Org Details</span>
                    <template v-if="activeEscalation">
                      <template v-if="activeEscalation.organisation">
                        <p @click="openOrg()" class="detail pointer">
                          {{ activeEscalation.organisation.name }}
                          <img
                            src="/app-assets/img/svg/group.svg"
                            alt="M"
                            v-if="isManualUpdateEnabled(activeEscalation)"
                          />

                          <MainPriorityIcon
                            :priority="activeEscalation.organisation.priority"
                            :tooltip="activeEscalation.organisation.priority"
                            :displayOnly="true"
                          />

                          <el-tag
                            v-show="
                              activeEscalation.organisation
                                .account_status_type_selection.length > 0
                            "
                            type="danger"
                            disable-transitions
                          >
                            {{
                              activeEscalation.organisation
                                .account_status_type_selection
                            }}
                          </el-tag>
                        </p>

                        <p @click="openOrg()" class="detail pointer">
                          {{
                            activeEscalation.organisation
                              ? activeEscalation.organisation
                                  .organisation_users[0].user.name
                              : ""
                          }}
                        </p>
                        <p @click="openOrg()" class="detail pointer">
                          {{
                            activeEscalation.organisation
                              ? activeEscalation.organisation.contact_number
                              : ""
                          }}
                        </p>
                        <p @click="openOrg()" class="detail pointer">
                          {{
                            activeEscalation.organisation
                              ? activeEscalation.organisation
                                  .organisation_users[0].user.email
                              : ""
                          }}
                        </p>
                        <p @click="openOrg()" class="detail pointer">
                          {{
                            activeEscalation.organisation
                              ? activeEscalation.organisation.address
                                  .full_address
                              : ""
                          }}
                        </p>
                      </template>
                      <template v-else>
                        <p class="detail">No Organisation Assigned</p>
                      </template>
                    </template>
                  </el-col>

                  <!-- User Popover -->
                  <el-col
                    :xs="24"
                    :sm="24"
                    :md="6"
                    :lg="6"
                    :xl="6"
                    align="end"
                    v-if="activeEscalation"
                    class="text-left"
                  >
                    <p class="detail mt-4 detail-line-height">Responsible:</p>
                    <AssignUsersPopover
                      :id="id"
                      :assigned_users="assigned_users"
                      :users="users"
                      :type="'drawer'"
                      v-if="checkProp()"
                    />
                    <p class="detail mt-1">
                      Lead ID #{{ lead.lead_id ? parseInt(lead.lead_id) : id }}
                    </p>
                    <p
                      class="detail"
                      v-if="
                        lead.metadata && lead.metadata.forminator_pro_form_id
                      "
                    >
                      Forminator Pro Form ID{{
                        " #" + lead.metadata.forminator_pro_form_id
                      }}
                    </p>
                    <p
                      class="detail"
                      v-if="
                        lead.metadata && !lead.metadata.forminator_pro_form_id
                      "
                    >
                      Forminator Pro Form ID: None
                    </p>
                    <p class="detail">
                      Created:
                      {{ getDatetimeByTimezone(lead.created_at) }}
                    </p>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
          </el-col>
          <el-col :xs="24" :sm="24" :md="24" :lg="6" :xl="6" class="mt-3">
            <el-row type="flex" justify="end" class="mb-1">
              <!-- <el-button
            class="w-100 lead-btn-top"
            type="primary"
            @click="
              $router.push({
                name: 'admin.leads.update',
                params: { id: activeEscalation.id }
              })
            ">Edit Lead</el-button
          > -->
              <el-button
                dusk="edit-lead"
                class="w-100 lead-btn-top"
                type="primary"
                @click="editLeadBtn()"
                v-if="!isLeadForm()"
                >Edit Lead</el-button
              >
            </el-row>
            <el-row
              type="flex"
              justify="end"
              class="mb-1"
              v-if="isManualUpdateEnabled()"
            >
              <el-button
                class="w-100 lead-btn-top"
                type="primary"
                @click.native="showEscalation()"
                plain
                >Update Escalation</el-button
              >
            </el-row>
            <el-row
              type="flex"
              justify="end"
              class="mb-1"
              v-if="lead && lead.customer_type == 'Supply & Install'"
            >
              <el-button
                dusk="reassinged-lead-button"
                class="w-100 lead-btn-top"
                :disabled="
                  lead.lead_escalations[0].escalation_level == 'Unassigned'
                "
                type="primary"
                @click="reassignLead"
                plain
                >Reassign Lead
              </el-button>
            </el-row>
            <!-- <el-row type="flex" justify="end" class="mb-1">
          <el-button
            class="w-100 lead-btn-top"
            type="primary"
            @click="openAssignUserToLeadModal"
            plain
            >Assign User</el-button>
        </el-row> -->
            <el-row type="flex" justify="end" class="mb-1" v-if="isManualUpdateEnabled()">
              <el-button
                class="w-100 lead-btn-top"
                type="primary"
                @click="sendEnquirerDetails"
                plain
                >Send Enquirer Details</el-button>
            </el-row>
          </el-col>
        </el-row>
      </el-card>
    </el-col>
    <el-col
      :xs="24"
      :sm="24"
      :md="24"
      :lg="24"
      :xl="24"
      class="tab-pane-content"
    >
      <el-card class="box-card b-none pt0" shadow="never">
        <el-tabs type="card" id="drawer-tabs">
          <el-tab-pane label="Lead History" class="py-3 tabs-pane">
            <!-- lead history -->
            <el-row :gutter="24">
              <!-- Meters Form -->
              <el-col
                :xs="24"
                :sm="24"
                :md="12"
                :lg="12"
                :xl="12"
                class="detail-details mb-3"
                v-if="metersForm.id"
              >
                <!-- actual meters -->
                <MetersForm :isDrawer="true" />
              </el-col>
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
                    label="Organisation"
                    width="250"
                    prop="organisation.name"
                  >
                    <template slot-scope="{ row }">
                      <label
                        v-if="
                          hideOrganisationEscalationStatus(
                            row.escalation_status
                          ) && row.organisation
                        "
                        >{{ row.organisation.name }}</label
                      >
                    </template>
                  </el-table-column>

                  <el-table-column
                    label="Escalation Level"
                    prop="escalation_level"
                    width="180"
                  >
                    <template slot-scope="{ row }">
                      <el-col style="padding-left: 0">
                        <EscalationTag  :color="row.color" :escalation_level="row.escalation_level" />
                      </el-col>
                    </template>
                  </el-table-column>

                  <el-table-column
                    label="Escalation Status"
                    prop="escalation_status"
                    width="300"
                    align="left"
                  >
                    <template slot-scope="{ row }">
                      <el-col style="padding-left: 0">
                        <EscalationTag  :color="row.color" :escalation_status="row.escalation_status" :active_escalation="row" />
                      </el-col>
                    </template>
                  </el-table-column>

                  <el-table-column label="Reason" prop="reason" width="250">
                    <template slot-scope="{ row }">
                      <label v-if="row.escalation_status == 'Won'">{{
                        row.metadata.response
                      }}</label>
                      <label v-else>{{ reasons(row) }}</label>
                      <br />
                      <label v-if="row.escalation_status == 'Won'">
                        <b v-if="row.gutter_edge_meters"
                          >Gutter Edge: {{ row.gutter_edge_meters }}m</b
                        >
                        <b v-if="row.valley_meters"
                          >Valley: {{ row.valley_meters }}m</b
                        >
                        <br />
                        <b v-if="row.installed_date"
                          >Installed Date:
                          {{ row.installed_date | moment("YYYY-MM") }}</b
                        >
                      </label>
                      <el-tooltip
                        v-if="row.comments"
                        :content="row.comments"
                        placement="top"
                      >
                        <i class="el-icon-s-comment"></i>
                      </el-tooltip>
                    </template>
                  </el-table-column>

                  <el-table-column label="Date" prop="created_at" width="250">
                    <template slot-scope="{ row }">
                      {{ getDatetimeByTimezone(row.created_at) }}
                    </template>
                  </el-table-column>
                </data-tables>
              </el-col>
            </el-row>
          </el-tab-pane>

          <el-tab-pane label="Lead Details" class="py-3 tabs-pane">
            <!-- lead history -->
            <el-row :gutter="24">
              <el-col
                :xs="24"
                :sm="24"
                :md="24"
                :lg="24"
                :xl="24"
                v-if="histories"
              >
                <div class="clearfix mb-3">
                  <span class="drawer-tabs-title">
                    Lead Details
                  </span>
                </div>
                <el-row :gutter="24">
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-col class="lead-details" v-if="leadForm.customer_type">
                      <p class="detail">
                        <span>Lead Type:</span>
                        {{ leadForm.customer_type }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.received_via">
                      <p class="detail">
                        <span>Enquiry Received:</span>
                        {{ formatText(leadForm.received_via) }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.house_type">
                      <p class="detail">
                        <span>Building Type:</span>
                        {{ leadForm.house_type }}
                      </p>
                    </el-col>
                    <el-col
                      class="lead-details"
                      v-if="leadForm.roof_preference"
                    >
                      <p class="detail">
                        <span>Roof Type:</span>
                        {{ formatText(leadForm.roof_preference) }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.use_for">
                      <p class="detail">
                        <span>Enquirer Position:</span>
                        {{ leadForm.use_for }}
                      </p>
                    </el-col>
                    <el-col
                      class="lead-details"
                      v-if="leadForm.gutter_edge_meters"
                    >
                      <p class="detail">
                        <span>Metres of Gutter Edge:</span>
                        {{ leadForm.gutter_edge_meters }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.valley_meters">
                      <p class="detail">
                        <span>Metres of Valley:</span>
                        {{ leadForm.valley_meters }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.source">
                      <p class="detail">
                        <span>Marketing Channel:</span>
                        {{ leadForm.source }}
                      </p>
                    </el-col>
                    <el-col class="lead-details" v-if="leadForm.comments">
                      <p class="detail">
                        <span>Enquirer Comments:</span>
                        {{ leadForm.comments }}
                      </p>
                    </el-col>
                    <el-col
                      class="lead-details"
                      v-if="leadForm.additional_information"
                    >
                      <p class="detail">
                        <span>Enquirer Additional Information:</span>
                        {{ leadForm.additional_information }}
                      </p>
                    </el-col>
                  </el-col>
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form
                      :model="leadForm"
                      status-icon
                      label-position="top"
                      ref="leadForm"
                      label-width="120px"
                      class="lead-form"
                    >
                      <el-row :gutter="24">
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                          <el-row :gutter="20">
                            <el-col
                              :xs="24"
                              :sm="24"
                              :md="12"
                              :lg="12"
                              :xl="12"
                            >
                              <el-form-item
                                label="Level"
                                prop="escalation_level"
                                :error="
                                  leadForm.errors.errors.escalation_level
                                    ? leadForm.errors.errors.escalation_level[0]
                                    : ''
                                "
                              >
                                <el-select
                                  popper-class="escalation_level_popper"
                                  v-model="leadForm.escalation_level"
                                  placeholder="Select Level"
                                  @change="
                                    autoSelectStatus();
                                    changeManualUpdate();
                                  "
                                >
                                  <el-option
                                    v-for="(level, index) in leadTypes[
                                      leadForm.customer_type
                                    ]"
                                    :key="index"
                                    :value="index"
                                    :label="index"
                                    >{{ index }}</el-option
                                  >
                                </el-select>
                              </el-form-item>
                            </el-col>
                            <el-col
                              :xs="24"
                              :sm="24"
                              :md="12"
                              :lg="12"
                              :xl="12"
                            >
                              <el-form-item
                                v-show="
                                  leadForm.customer_type == 'Supply & Install'
                                "
                                :label="leadForm.escalation_status ? 'Status' : ''"
                                prop="escalation_status"
                                :error="
                                  leadForm.errors.errors.escalation_status
                                    ? leadForm.errors.errors
                                        .escalation_status[0]
                                    : ''
                                "
                              >
                                <el-select
                                  popper-class="escalation_status_popper"
                                  v-if="leadForm.escalation_status"
                                  v-model="leadForm.escalation_status"
                                  placeholder="Select Status"
                                  :disabled="leadForm.lead_id == ''"
                                  @change="changeManualUpdate"
                                >
                                  <span v-if="leadForm.customer_type">
                                    <el-option
                                      v-for="(status, index) in leadTypes[
                                        leadForm.customer_type
                                      ][leadForm.escalation_level]"
                                      :key="index"
                                      :value="status"
                                      :label="status"
                                      >{{ status }}</el-option
                                    >
                                  </span>
                                </el-select>
                              </el-form-item>
                            </el-col>
                            <el-col :xs="24" :sm="24">
                              <el-form-item>
                                <el-button
                                  type="primary"
                                  :loading="loading"
                                  @click="saveLead('leadForm')"
                                  >Update Level/Status</el-button
                                >
                              </el-form-item>
                            </el-col>
                          </el-row>
                        </el-col>
                      </el-row>
                    </el-form>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
          </el-tab-pane>
          <el-tab-pane
            label="Comments & Notifications"
            class="py-3"
            v-if="user.user_role.name !== 'organisation'"
          >
            <!-- Comments Content -->
            <el-row :gutter="24" class="m-b-md-mt-3">
              <el-col
                v-if="lead && lead.staff_comments"
                :xs="24"
                :sm="24"
                :md="12"
                :lg="12"
                :xl="12"
              >
                <el-card class="box-card m-card-3" v-if="lead">
                  <el-tag type="primary" class="dark-blue-gray"
                    >Staff Comment</el-tag
                  >
                  <p class="detail">{{ lead.staff_comments }}</p>
                </el-card>
              </el-col>
            </el-row>
            <!-- lead comments -->
            <el-row :gutter="24" class="m-b-md mt-3">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <LeadComments :leadId="id" :newLayout="true" />
              </el-col>
            </el-row>

            <el-row :gutter="24" class="m-b-md" id="enquirer-row">
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="clearfix">
                  <span class="drawer-tabs-title">
                    Enquirer Notifications

                    <el-button
                      dusk="lead-overview-send-enquirer-notification"
                      class="fl-right r-btn-reset"
                      type="primary"
                      @click="sendNotification"
                      >Send Enquirer Notification</el-button
                    >
                  </span>
                </div>

                <data-tables
                  :data="notifications"
                  :total="notifications.length"
                  :table-props="tableProps"
                  :pagination-props="{ pageSizes: [10, 15, 20] }"
                >
                  <el-table-column label="Message" prop="description" />

                  <el-table-column
                    label="Date Sent"
                    prop="created_at"
                    width="300"
                  >
                    <template slot-scope="{ row }" v-if="row">
                      {{ getDatetimeByTimezone(row.created_at) }}
                    </template>
                  </el-table-column>
                  <!-- FOR SENT BY ICON - DON'T DELETE -->
                  <el-table-column label="Sent By" prop="sent_by">
                    <template
                      slot-scope="{ row }"
                      v-if="row.metadata['notification_type']"
                    >
                      <span v-if="row.metadata['email_and_sms'] == 'both'">
                        <i class="el-icon-message"></i>&nbsp;&nbsp;<i
                          class="el-icon-chat-dot-square"
                        ></i>
                      </span>
                      <span v-if="row.metadata['email_and_sms'] == 'email'">
                        <i class="el-icon-message"></i>
                      </span>
                      <span v-if="row.metadata['email_and_sms'] == 'sms'">
                        <i class="el-icon-chat-dot-square"></i>
                      </span>
                    </template>
                  </el-table-column>
                </data-tables>
              </el-col>
            </el-row>
            <el-row
              :gutter="24"
              class="m-b-md"
              id="enquirer-row"
              v-if="
                activeEscalation.escalation_status &&
                  hideOrganisationEscalationStatus(
                    activeEscalation.escalation_status
                  )
              "
            >
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="clearfix">
                  <span class="drawer-tabs-title">
                    Organisation Notifications

                    <el-button
                      dusk="lead-overview-send-enquirer-notification"
                      class="fl-right r-btn-reset"
                      type="primary"
                      @click="sendOrgNotification"
                      >Send Organisation Notification</el-button
                    >
                  </span>
                </div>

                <data-tables
                  :data="org_notifications"
                  :total="org_notifications.length"
                  :table-props="tableProps"
                  :pagination-props="{ pageSizes: [10, 15, 20] }"
                >
                  <el-table-column label="Message" prop="description" />

                  <el-table-column
                    label="Date Sent"
                    prop="created_at"
                    width="300"
                  >
                    <template slot-scope="{ row }" v-if="row">
                      {{ getDatetimeByTimezone(row.created_at) }}
                    </template>
                  </el-table-column>
                  <!-- FOR SENT BY ICON - DON'T DELETE -->
                  <el-table-column label="Sent By" prop="sent_by">
                    <template
                      slot-scope="{ row }"
                      v-if="row.metadata['notification_type']"
                    >
                      <span v-if="row.metadata['email_and_sms'] == 'both'">
                        <i class="el-icon-message"></i>&nbsp;&nbsp;<i
                          class="el-icon-chat-dot-square"
                        ></i>
                      </span>
                      <span v-if="row.metadata['email_and_sms'] == 'email'">
                        <i class="el-icon-message"></i>
                      </span>
                      <span v-if="row.metadata['email_and_sms'] == 'sms'">
                        <i class="el-icon-chat-dot-square"></i>
                      </span>
                    </template>
                  </el-table-column>
                </data-tables>
              </el-col>
            </el-row>

            <el-dialog
              title="Reassign Lead"
              :visible="reassignDialogVisible"
              :show-close="false"
              width="40%"
              v-dialogDrag
              ref="dialog__wrapper"
              append-to-body
            >
              <ReassignForm />
            </el-dialog>

            <el-dialog
              :title="messageTitle"
              :visible="sendNotificationDialogVisible"
              :show-close="false"
              v-dialogDrag
              ref="dialog__wrapper"
              append-to-body
              width="30%"
            >
              <SendNotificationForm
                v-bind:sendType="send_type"
                v-bind:activeEscalation="activeEscalation"
              />
            </el-dialog>

            <el-dialog
              :title="messageTitle"
              :visible="sendEnquirerDetailsDialogVisible"
              v-dialogDrag
              ref="dialog__wrapper"
              :show-close="false"
              append-to-body
              width="30%"
            >
              <SendEnquirerDetailsForm
                v-bind:activeEscalation="activeEscalation"
                v-bind:enquirerDetails="lead.customer"
              />
            </el-dialog>
          </el-tab-pane>
          <el-tab-pane
            label="Job"
            class="py-3"
            v-if="user.user_role.name == 'organisation'"
          >
            <template v-if="histories">
              <el-row :gutter="20" class="m-b-md">
                <span class="drawer-tabs-title" style="float:left">
                  Job Details
                </span>
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
                    {{ getDatetimeByTimezone(row.created_at) }}
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
        <JobDetailsForm v-bind:queryInfo="queryInfo" />
      </el-dialog>
    </template>

    <EscalationModal />

  </el-row>
</template>
<script>
import { DataTables } from "vue-data-tables";
import { mapGetters } from "vuex";
import Section from "~/components/Section";
import ReassignForm from "./../pages/admin/leads/reassign";
import SendNotificationForm from "./../pages/admin/leads/sendnotification";
import SendEnquirerDetailsForm from "./../pages/admin/leads/sendenquirerdetails.vue";
import LeadComments from "~/components/LeadComments.vue";
import MetersForm from "./../pages/admin/leads/meters.vue";
import AssignUsersPopover from "~/components/AssignUsersPopover.vue";
import EscalationModal from "~/components/escalations/main";
import Cookies from "js-cookie";
import { Bus } from "~/app";
import {
  hideOrganisationEscalationStatus,
  colorOrganisationStatus
} from "~/helpers";
import moment, { now } from "moment-timezone";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import Swal from "sweetalert2";
import EscalationTag from "~/components/EscalationTag";

export default {
  name: "DrawerAdmin",

  props: {
    id: { type: Number, default: null },
    nested: { type: Boolean, default: false },
    title: { type: String, default: "Lead Overview" }
  },

  components: {
    Section,
    DataTables,
    SendNotificationForm,
    SendEnquirerDetailsForm,
    EscalationTag,
    ReassignForm,
    LeadComments,
    MetersForm,
    // ASSIGN USER TO LEAD
    AssignUsersPopover,
    EscalationModal,
    MainPriorityIcon
  },

  data() {
    return {
      tableProps: {
        rowClassName: function(row, index) {
          if (row.row) {
            const { color } = row.row;

            return color;
          }
        }
      },
      dialogVisible: false,
      users: [],
      assigned_users: [],
      userLoading: true,
      send_type: "",
      messageTitle: "Send Enquirer a Message"
    };
  },

  computed: {
    ...mapGetters({
      leadForm: "leads/leadForm",
      leadFormRules: "leads/leadFormRules",
      loading: "leads/loading",
      leadTypes: "leads/leadTypes",
      user: "auth/user",
      lead: "leadhistory/lead",
      histories: "leadhistory/histories",
      metersForm: "leadhistory/metersForm",
      notifications: "leadhistory/notifications",
      org_notifications: "leadhistory/org_notifications",
      reassignDialogVisible: "leadhistory/reassignDialogVisible",
      sendNotificationDialogVisible: "leadhistory/sendNotificationDialogVisible",
      sendEnquirerDetailsDialogVisible: "leadhistory/sendEnquirerDetailsDialogVisible",
      activeEscalation: "leadhistory/active_escalation",
      escalationModals: "leadescalation/escalationModals"
    }),

    currentLead(){
      console.log('computed.currentLead')
    },
  },

  methods: {
    hideOrganisationEscalationStatus: hideOrganisationEscalationStatus,

    colorOrganisationStatus: colorOrganisationStatus,

    formUpdated: function(newV, oldV) {
      if (this.leadForm.lead_id != "") {
        if (this.isTouchedCounter > 0) {
          this.isTouched = true;
        }
        this.isTouchedCounter++;
      } else {
        if (this.isTouchedCounter > 0) {
          this.isTouched = true;
        }
        this.isTouchedCounter++;
      }
    },

    formatText(val) {
      return val.replace("-", " ").replace(/(?:^|\s)\S/g, a => a.toUpperCase());
    },

    reasons(row) {
      if (row.reason == "This lead has been LOST" || row.reason == "Other") {
        let reason = row.metadata["other_reason"] || row.reason;
        reason +=
          row.metadata.indicate_reason.length !== 0
            ? " - " + row.metadata.indicate_reason
            : "";
        return reason;
      } else if (row.reason == "This lead is currently Work in Progress") {
        if (row.metadata.other_reason == "Other") {
          return row.metadata.indicate_reason;
        } else {
          return row.metadata.other_reason;
        }
      } else {
        return row.reason;
      }
    },

    reassignLead() {
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "reassign"
      });
    },

    sendEnquirerDetails() {
      this.messageTitle = "Send Enquirer Details to Organisation";
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "send_enquirer_details"
      });
    },

    sendNotification() {
      this.send_type = "inquirer";
      this.messageTitle = "Send Enquirer a Message";
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "send_notification"
      });
    },

    sendOrgNotification() {
      this.messageTitle = "Send Organisation a Message";
      this.send_type = "organisation";
      this.$store.dispatch("leadhistory/setDialog", {
        close: true,
        form: "send_notification"
      });
    },

    getLeadHistory(id) {
      let lead_id = id ?? this.id

      this.$store.dispatch("leadhistory/getLeadHistory", lead_id);
    },

    isManualUpdateEnabled() {
      return (
        this.activeEscalation &&
        this.activeEscalation.organisation &&
        this.activeEscalation.organisation.metadata &&
        this.activeEscalation.organisation.metadata["manual_update"] === true
      );
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

    showEscalation() {
      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: this.activeEscalation,
        showEscalation: true,
        modal: this.modal(
          this.activeEscalation.escalation_level,
          this.activeEscalation.escalation_status
        )
      });
    },

    getEarliestHistory() {
      const escalationsMaxIIndexLength = this.lead.lead_escalations.length - 1;
      return this.getDatetimeByTimezone(
        this.lead.lead_escalations[escalationsMaxIIndexLength].created_at
      );
    },

    closeDrawer() {
      if (this.nested) {
        this.$store.dispatch("leadhistory/closeLeadOverview");
      }

      this.$store.dispatch("leadhistory/closeLeadOverviewNested")

      Bus.$emit("reload-assigned");

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

      return obj_datetime;
    },

    checkData(value) {
      return value ? value : "";
    },

    checkProp() {
      return this.userLoading ? false : true;
    },

    editLeadBtn() {
      let id = this.lead.lead_id ? parseInt(this.lead.lead_id) : this.id;
      Cookies.set("new_lead_id", id);


      this.$router.push({
        name: "admin.leads.update",
        params: { id: id }
      });

      this.$store
        .dispatch("leadhistory/closeLeadOverview")
        .then(_ => {})
        .catch(_ => {});

      this.$store.dispatch("leadhistory/closeLeadOverviewNested")
        .then(_ => {
          this.leadForm.reset();
        })
      this.$store.dispatch("organisations/closeOrganisationOverview")
    },
    saveLead(formName, next = null) {
      this.leadForm.update_type = "edit_lead";

      this.leadForm.organisation_id = this.organisation_id_temp
        ? this.organisation_id_temp
        : this.leadForm.organisation_id;

      this.leadForm.organisation_id = (this.leadForm.escalation_level == 'Unassigned') ? null : this.leadForm.organisation_id

      this.$refs[formName].validate(valid => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leads/saveLead")
            .then(({ success, message, errors }) => {
              let _errorMessage = "";
              if (errors) {
                _errorMessage = errors.error;
                if (!errors.error) {
                  _errorMessage =
                    message ||
                    "Cannot update the Level/Status. This lead form is missing some data. Go to ‘Edit Lead’ and fill out all required fields.";
                } else {
                  _errorMessage = errors.error;
                }
              }
              if (success) {
                this.isTouched = false;
                this.getLeadHistory();
                this.$store.dispatch("leads/fetchLeads", []);
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    if (next == null) {
                      Cookies.set("lead_id", this.leadForm.lead_id);
                    }
                  }
                });
              } else {
                Swal.fire({
                  title: "Oops!",
                  text: _errorMessage,
                  type: "error",
                  showCancelButton: true,
                  confirmButtonText: "Edit Lead",
                  cancelButtonText: "Cancel",
                  reverseButtons: true
                }).then(result => {
                  if (result.value) {
                    this.editLeadBtn();
                  }
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    openOrg() {
      if(this.isLeadForm()){
        this.closeDrawer()
      }else{
        let org_id = this.activeEscalation.organisation.id

        this.$store.dispatch('organisations/getOrganisation', { id: org_id, load: true }).then(({ success, message, errors }) => {
          if (success) {
            this.updateAllDataInOrganisationProfile(org_id)
            this.$emit('openOrg', org_id)
          }
        })
      }
    },

    updateAllDataInOrganisationProfile(org_id){
      let queryInfo = {'pageNo': 1, 'pageSize': 10, 'orgId' : org_id}

      this.$store.dispatch("organisations/fetchCurrentLeads", org_id)

      this.$store.dispatch("organisations/fetchReassignedLeads", org_id)

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

    changeManualUpdate() {
      this.leadForm.comments = "";
      if (
        this.leadForm.customer_type == "Supply & Install" &&
        this.leadForm.id
      ) {
        this.warningNotification ? this.warningNotification.close() : "";

        this.warningNotification = this.$notify.warning({
          customClass: "warning",
          title: "Warning",
          message:
            "Manually updating a leads Level / Status may confuse the assigned Organisation if they are not expecting the change. Notifications may be triggered based on the Level / Status change. Please proceed with care.",
          duration: 10000
        });
      }
    },

    autoSelectStatus() {
      this.leadForm.escalation_status = this.leadTypes[
        this.leadForm.customer_type
      ][this.leadForm.escalation_level][0];
    },

    getLeadDetails() {
        this.$store.dispatch("leads/editLead", this.id).then(data => {
          //use address search(new data), else full address for ussually old data from migration
          this.leadForm.address_search =
            this.leadForm.address_search.length > 0
              ? this.leadForm.address_search
              : data.data.lead.customer.address.full_address;

          let new_lead_id = Cookies.get("new_lead_id");

          if (new_lead_id) {
            this.new_lead_id = new_lead_id;
            Cookies.remove("new_lead_id");
          }
        });

        this.isTouched = false;
        this.isTouchedCounter = 0;
    },

    isLeadForm(){
      if(this.$route.name == 'admin.leads.update' || this.$route.name == 'admin.leads.create'){
        return true
      }
      return false
    }
  },

  created() {
    this.$watch("leadForm", this.formUpdated, {
      deep: true
    });
  },

  mounted(){
    if(this.id){
      this.$store.dispatch("leads/fetchSettings", {
        page: 1,
        pageSize: 100,
        filters: []
      });

      this.$store.dispatch("organisations/fetchStates")

      this.$store.dispatch("leads/fetchOrgAll")

      this.$store.dispatch("orglocators/fetchOrgLocators", {
        page: 1,
        pageTitle: 10,
        filters: []
      })

      this.$store.dispatch("organisations/fetchOrganisationPostcodes")

      // Default Country
      this.leadForm.country = "Australia";

      this.getLeadHistory();

      this.getLeadDetails();

      if (this.user.user_role.name !== "organisation") {
        this.$store.dispatch("leads/fetchOrgAll");
      }

      let ids = [];

      this.$store
        .dispatch("leadassign/fetchAssignedUsers", this.id)
        .then(res => {
          let data = res;

          if (data) {
            data.forEach(user => {
              ids.push(user.id);
            });

            this.assigned_users = ids;
          }
        }
      );

      this.$store.dispatch("leadassign/fetchUsers").then(res => {
        this.users = res;
        this.userLoading = false;
      });

      this.$store.dispatch("settings/getGetSettingCompanyName");

      this.$store.dispatch("leads/editLead", this.id)
    }

  },

  destroyed(){
    let lead_id = localStorage.getItem('lead_id')

    if(lead_id){
      this.$store.dispatch("leads/fetchSettings", {
        page: 1,
        pageSize: 100,
        filters: []
      });

      this.$store.dispatch("organisations/fetchStates")

      this.$store.dispatch("leads/fetchOrgAll")

      this.$store.dispatch("orglocators/fetchOrgLocators", {
        page: 1,
        pageTitle: 10,
        filters: []
      })

      this.$store.dispatch("organisations/fetchOrganisationPostcodes")

      // Default Country
      this.leadForm.country = "Australia";

      this.getLeadHistory(lead_id);

      if (this.user.user_role.name !== "organisation") {
        this.$store.dispatch("leads/fetchOrgAll");
      }

      let ids = [];

      this.$store
        .dispatch("leadassign/fetchAssignedUsers", lead_id)
        .then(res => {
          let data = res;

          if (data) {
            data.forEach(user => {
              ids.push(user.id);
            });

            this.assigned_users = ids;
          }
        }
      );

      this.$store.dispatch("leadassign/fetchUsers").then(res => {
        this.users = res;
        this.userLoading = false;
      });

      this.$store.dispatch("settings/getGetSettingCompanyName");
    }
  }

};
</script>

<style lang="scss" scoped>
.lead-details {
  padding-left: 0px !important;
  line-height: 1.8;
  span {
    color: #96959c;
    font-weight: 300;
  }
}
.detail-line-height {
  line-height: 2;
}

.pointer {
  cursor: pointer;
}

.el-tooltip__popper {
  font-size: 14px !important;
}

.mb {
  margin-bottom: 1rem;
}

::v-deep {
  .mb0,
  .mb0 .el-card__body {
    margin-bottom: 0 !important;
  }

  .mt0,
  .mt0 .el-card__body {
    margin-top: 0 !important;
  }

  .pb0,
  .pb0 .el-card__body {
    padding-bottom: 0px !important;
  }

  .pt0,
  .pt0 .el-card__body {
    padding-top: 0px !important;
  }
}

.m-card-1 {
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

.ml-1 {
  margin-left: 0.5em;
}

.el-tags-flex {
  display: flex;
  align-items: center;
  justify-content: center;
}

.no-padding {
  padding: unset !important;
}

#enquirer-row {
  margin-top: 3em;
}

@media (min-width: 481px) and (max-width: 1100px) {
  .no-metters-col {
    width: 50%;
  }

  .tab-pane-content {
    margin-top: 20px;
  }
}

@media all and (max-width: 991px) {
  .el-tags-col {
    margin-top: 1em;
  }

  .el-tags-col .zoom-25 {
    width: 100% !important;
  }
}

@media all and (max-width: 768px) {
  .m-card-1,
  .m-card-2,
  .m-card-3 {
    margin: 10px 20px;
  }

  .tab-pane-content {
    margin-top: 20px;
  }

  .escalation-row {
    display: block !important;
  }
}

@media (min-width: 581px) and (max-width: 767px) {
  .detail-details {
    width: 50% !important;
  }
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
</style>
