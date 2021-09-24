<template>
	<div>
		<el-card class="box-card b-none" shadow="never">
      <div class="clearfix" slot="header">
				<h4>Org. Notifications</h4>
				<el-button class="fl-right" type="primary" @click="initDialogMessage">Send Message</el-button>
			</div>
			<data-tables-server
			:data="orgNotificationHistory"
			:total="orgNotificationHistoryTotal"
			:loading="orgNotificationHistoryLoading"
			:pagination-props="{ pageSizes: [10, 15, 20] }"
			@query-change="loadMore">

        <el-table-column label="Message" prop="description" />

				<el-table-column label="Date Sent" prop="created_at" width="300">
					<template slot-scope="{ row }" v-if="row">
					{{ row.created_at | moment("k:mm DD/MM/YYYY") }}
					</template>
				</el-table-column>

				<!-- FOR SENT BY ICON - DON'T DELETE -->
				<el-table-column label="Sent By" prop="sent_by">
					<template slot-scope="{ row }" v-if="row.metadata['notification_type']">
					<span v-if="row.metadata['email_and_sms'] == 'both'">
						<i class="el-icon-message"></i>&nbsp;&nbsp;<i class="el-icon-chat-dot-square"></i>
					</span>
					<span v-if="row.metadata['email_and_sms'] == 'email'">
						<i class="el-icon-message"></i>
					</span>
					<span v-if="row.metadata['email_and_sms'] == 'sms'">
						<i class="el-icon-chat-dot-square"></i>
					</span>
					</template>
				</el-table-column>

			</data-tables-server>

      <el-dialog
        title="Send a Notification to this Org."
        :visible="sendOrgNotificationDialogVisible"
        :show-close="false"
        v-dialogDrag
        ref="dialog__wrapper"
        width="30%"
        append-to-body
      >
        <SendOrgNotificationForm :organisation="organisation" v-on:destroyDialog="destroyDialog" />
      </el-dialog>

		</el-card>
	</div>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import { Bus } from '~/app'
import SendOrgNotificationForm from "../pages/admin/leads/sendorgnotification";

export default {
  props: {
    orgId: Number
  },
	name: "OrganizationNotificationHistory",

	components: {
		DataTablesServer,
    SendOrgNotificationForm,
	},

	computed: mapGetters({
		// <ANY_LABEL_TO_BE_USED_IN_LAYOUT>: "<MODULE_FILE_NAME>/<GETTER>"
		orgNotificationHistory: "orghistory/orgNotificationHistory",
		orgNotificationHistoryTotal: "orghistory/orgNotificationHistoryTotal",
		orgNotificationHistoryLoading: "orghistory/orgNotificationHistoryLoading",
    organisation: 'organisations/organisation',
	}),

	data: () => ({
    sendOrgNotificationDialogVisible: false
  }),

	beforeMount() {
		Bus.$on( 're-fetch-org-notif-history', _ => {
    		this.loadData()
		} )
    	this.loadData()
	},

	methods: {
    addComment(){},

    initDialogMessage() {
      this.sendOrgNotificationDialogVisible = true
    },

    destroyDialog() {
      this.sendOrgNotificationDialogVisible = false
    },

		async loadMore(queryInfo) {
			//const orgId = this.$route.params.id ?? this.orgId
      let orgId = this.orgId

			this.$store.dispatch("orghistory/fetchOrgNotificationHistory", {
				id: orgId,
				queryInfo: queryInfo
			});
		},

		loadData() {
      //const orgId = this.$route.params.id ?? this.orgId
      let orgId = this.orgId
			this.$store.dispatch( 'orghistory/fetchOrgNotificationHistory', { id: orgId, queryInfo: '' } )
		}
	}
}
</script>

<style scoped>
	/* SCOPED STYLE HERE */
</style>
