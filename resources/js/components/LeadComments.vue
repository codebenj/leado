<template>
	<div>
		<template v-if="!newLayout">
			<el-card class="box-card b-none" shadow="never">
				<div class="clearfix" slot="header">
					<h4>
						{{ settingCompanyName.value }} Lead Comments
						<el-button class="fl-right r-btn-reset" type="primary" @click="addComment">Add New</el-button>
					</h4>
				</div>
				<data-tables-server
				:data="comments"
				:total="commentsTotal"
				:loading="loading"
				:pagination-props="{ pageSizes: [10, 15, 20] }"
				@query-change="loadMore">
					<el-table-column label="Comment" prop="comment" align="left" header-align="center" />
					<el-table-column label="Name" prop="user.name" />
					<el-table-column label="Date Added" prop="created_at">
						<template slot-scope="{ row }">
							{{ row.created_at | moment("k:mm DD/MM/YYYY") }}
						</template>
					</el-table-column>

					<el-table-column width="55" align="center" class-name="action b-none">
						<template slot-scope="{ row }">
							<el-dropdown trigger="click">
								<span class="el-dropdown-link"><i class="el-icon-caret-bottom"></i></span>

								<el-dropdown-menu slot="dropdown">
									<el-dropdown-item icon="el-icon-edit" @click.native="editComment(row)">Edit</el-dropdown-item>
									<el-dropdown-item icon="el-icon-delete" @click.native="deleteComment(row)">Delete</el-dropdown-item>
								</el-dropdown-menu>
							</el-dropdown>
						</template>
					</el-table-column>
				</data-tables-server>
			</el-card>
		</template>

		<template v-else>
			<div class="clearfix">
				<span class="drawer-tabs-title">
          {{ settingCompanyName.value }} Comments
					<el-button  dusk="add-comment" class="fl-right r-btn-reset" type="primary" @click="addComment">Add Comment</el-button>
				</span>
			</div>
			<data-tables-server
			:data="comments"
			:total="commentsTotal"
			:loading="loading"
			:pagination-props="{ pageSizes: [10, 15, 20] }"
			@query-change="loadMore"
      @row-click="clickRow"
			id="lead-comments">
				<el-table-column label="User" prop="user.name" align="left" header-align="left" class-name="p-reset">
          <template slot-scope="{ row }">
						{{ row.user.name }}
					</template>
        </el-table-column>

				<el-table-column width="380" label="Comments" prop="comment" align="left" header-align="left" class-name="p-reset comment_section" />

				<el-table-column label="Date & Time" prop="created_at" align="left" header-align="left" class-name="p-reset" >
					<template slot-scope="{ row }">
						{{ row.created_at | moment("k:mm DD/MM/YYYY") }}
					</template>
				</el-table-column>

				<el-table-column width="55" align="center" class-name="action b-none">
					<template slot-scope="{ row }">
						<el-dropdown trigger="click">
							<span class="el-dropdown-link"><i class="el-icon-caret-bottom"></i></span>

							<el-dropdown-menu slot="dropdown">
								<el-dropdown-item icon="el-icon-edit" @click.native="editComment(row)">Edit</el-dropdown-item>
								<el-dropdown-item icon="el-icon-delete" @click.native="deleteComment(row)">Delete</el-dropdown-item>
							</el-dropdown-menu>
						</el-dropdown>
					</template>
				</el-table-column>
			</data-tables-server>
		</template>

		<el-dialog v-dialogDrag ref="dialog__wrapper" width="30%" :title="titleDialog" :visible="addLeadCommentDialogVisible" :show-close="false" append-to-body>
			<el-form :model="leadCommentForm" status-icon :rules="leadCommentRules" label-position="top"  ref="leadCommentForm" label-width="120px">
				<el-row :gutter="20">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item label="Comment" prop="comment" :error="leadCommentForm.errors.errors.reason ? leadCommentForm.errors.errors.reason[0] : ''">
							<el-input dusk="lead-comments-textarea" type="textarea" :autosize="{ minRows: 4 }" placeholder="Enter your comment..." v-model="leadCommentForm.comment" maxlength="1000" show-word-limit></el-input>
						</el-form-item>
					</el-col>

					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item class="fl-right">
							<el-button dusk="lead-button-save" type="primary" :loading="loading" @click="add('leadCommentForm')">Submit</el-button>
							<el-button type="danger" @click="addCommentClose()">Cancel</el-button>
						</el-form-item>
					</el-col>
				</el-row>
			</el-form>
		</el-dialog>

    <el-dialog v-dialogDrag ref="dialog__wrapper" width="30%" title="View Lead Comment" :visible="viewLeadCommentDialogVisible" :show-close="false" append-to-body>
			<el-form :model="leadCommentForm" status-icon label-position="top"  ref="leadCommentForm" label-width="120px">
				<el-row :gutter="20">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item label="Comment" prop="comment">
							<el-input :readonly="true" resize="none" dusk="lead-comments-textarea" type="textarea" :autosize="{ minRows: 4 }" placeholder="Enter your comment..." v-model="leadCommentForm.comment" maxlength="1000" show-word-limit></el-input>
						</el-form-item>
					</el-col>

					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item class="fl-right">
							<el-button dusk="lead-button-save" type="primary" :loading="loading" @click="openEdit()">Edit Comment</el-button>
							<el-button type="danger" @click="viewCommentClose()">Close</el-button>
						</el-form-item>
					</el-col>
				</el-row>
			</el-form>
		</el-dialog>
	</div>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import Swal from 'sweetalert2';

export default {
	name: "LeadComments",
	components: {
		DataTablesServer,
	},
	props: {
		leadId: { type: Number, default: null },
		newLayout: { type: Boolean, default: true },
	},
	computed: mapGetters({
		// <ANY_LABEL_TO_BE_USED_IN_LAYOUT>: "<MODULE_FILE_NAME>/<GETTER>"
		comments: "leadhistory/leadComments",
		commentsTotal: "leadhistory/leadCommentsTotal",
		loading: "leadhistory/leadCommentLoading",
		addLeadCommentDialogVisible: "leadhistory/addLeadCommentDialogVisible",
		viewLeadCommentDialogVisible: "leadhistory/viewLeadCommentDialogVisible",
		leadCommentForm: 'leadhistory/leadCommentForm',
		leadCommentRules: 'leadhistory/leadCommentRules',
    settingCompanyName: 'settings/settingCompanyName'
	}),
	data: () => ({
    tableRowClassName({row}) {
        console.log(row)
        return 'comment_section'
    },

    titleDialog: '',
  }),
	methods: {
		async loadMore(queryInfo) {
			let leadId = this.$route.params.id ? this.$route.params.id : "";

			if ( this.leadId ) leadId = this.leadId
			this.$store.dispatch("leadhistory/fetchLeadComments", {
				leadId,
				queryInfo
			});
		},
    viewComment(row) {
			this.$store.dispatch('leadhistory/editLeadComment', {
				comment: row,
				close: true,
				form: "view_lead_comment"
			})
    },

    clickRow(row, column) {
      let nots = [
        "action b-none"
      ];
      let index = nots.indexOf(column.className);
      if (index == -1) {
        this.viewComment(row);
      }
    },

		viewCommentClose() {
			this.$store.dispatch("leadhistory/setDialog", { close: false, form: "view_lead_comment" });
		},

		addComment() {
      this.titleDialog = "Add Lead Comment";
      this.$store.dispatch("leadhistory/resetLeadComment");
			this.$store.dispatch("leadhistory/setDialog", { close: true, form: "add_lead_comment" });
		},
		addCommentClose() {
			this.$store.dispatch("leadhistory/setDialog", { close: false, form: "add_lead_comment" });
		},

		add(formName) {
			this.$refs[formName].validate((valid) => {
				if(valid){
					// DISPATCH FORM SUBMIT
					this.$store.dispatch('leadhistory/addLeadComment').then(({ msg, data, success }) => {
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

    openEdit(row) {
      this.viewCommentClose();
      this.editComment(row);
    },

		editComment(row) {
      this.titleDialog = "Edit Lead Comment";
			this.$store.dispatch('leadhistory/editLeadComment', {
				comment: row,
				close: true,
				form: "add_lead_comment"
			})
		},
		deleteComment(row) {
			Swal.fire({
				title: "Delete this comment?",
				text: "You won't be able to revert this!",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Yes, delete it"
			}).then((result) => {
				if(result.value){
					return this.$store
					.dispatch("leadhistory/deleteLeadComments", row.id)
					.then(({ success, msg }) => {
						if(success){
							Swal.fire({
								title: "Success",
								text: msg,
								type: "success"
							});
						}
					});
				}
			});
		},
	}
}
</script>

<style scoped>
	@media all and (max-width: 426px){
		.r-btn-reset{
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

<style>
  .comment_section .cell {
    padding: 0px 15px !important;
  }
	.is-left .cell{
    	text-align: left !important;
  	}

	.p-reset {
		padding: 12px 0 !important;
	}
</style>
