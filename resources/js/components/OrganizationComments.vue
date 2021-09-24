<template>
	<div>
		<el-card class="box-card b-none" shadow="never" v-if="isEdit()">
			<div class="clearfix" slot="header">
				<h4>Organisation Comments</h4>
				<el-button class="fl-right" type="primary" @click="addComment">Add New</el-button>
			</div>
			<data-tables-server
			:data="comments"
			:total="commentsTotal"
			:loading="loading"
			:pagination-props="{ pageSizes: [10, 15, 20] }"
			@query-change="loadMore">
				<el-table-column label="Comment" prop="comment" />
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
	</div>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import Swal from 'sweetalert2';

export default {
  props: {
    orgId: Number
  },
	name: "OrganizationComments",
	components: {
		DataTablesServer,
	},
	computed: mapGetters({
		// <ANY_LABEL_TO_BE_USED_IN_LAYOUT>: "<MODULE_FILE_NAME>/<GETTER>"
		comments: "organisations/orgComments",
		commentsTotal: "organisations/orgCommentsTotal",
		loading: "organisations/orgCommentLoading",
		addOrgCommentDialogVisible: "organisations/addOrgCommentDialogVisible",
		orgCommentForm: 'organisations/orgCommentForm',
		orgCommentRules: 'organisations/orgCommentRules',
	}),
	data: () => ({}),
	methods: {
		async loadMore(queryInfo) {
			//const orgId = this.$route.params.id ? this.$route.params.id : this.orgId;
      const orgId = this.orgId ? this.orgId : this.$route.params.id;
			this.$store.dispatch("organisations/fetchOrgComments", {
				orgId,
				queryInfo
			});
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
					// DISPATCH FORM SUBMIT
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
		editComment(row) {
			this.$store.dispatch('organisations/editOrgComment', {
				comment: row,
				close: true,
				form: "add_organisation_comment"
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
					.dispatch("organisations/deleteOrgComments", row.id)
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
		isEdit(){
			return this.$route.params.id ? this.$route.params.id : this.orgId;
		}
	}
}
</script>

<style scoped>
	/* SCOPED STYLE HERE */
</style>
