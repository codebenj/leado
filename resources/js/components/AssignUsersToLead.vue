<template>
	<div>
		<!-- MODAL -->
		<el-dialog title="Assign a User" v-dialogDrag ref="dialog__wrapper" :visible="assignUserToLeadDialogVisible" :show-close="false" width="30%" append-to-body>
			<el-form :model="assignUserToLeadForm" :rules="assignUserToLeadRules" ref="assignUserToLeadForm" status-icon label-position="top" label-width="120px">
				<el-row :gutter="24">
					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item label="Users" prop="users" :error="assignUserToLeadForm.errors.errors.reason ? assignUserToLeadForm.errors.errors.reason[0] : ''">
							<el-select v-model="assignUserToLeadForm.allUsers" filterable multiple collapse-tags clearable placeholder="Select a User">
								<el-option v-for="user in users" :key="user.id" :label="user.name" :value="user.id" :disabled="disableIto(user)" />
							</el-select>
						</el-form-item>
					</el-col>

					<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
						<el-form-item class="fl-right">
							<el-button type="primary" @click="assignUserToLead('assignUserToLeadForm')">Submit</el-button>
							<el-button type="danger" @click="closeAssignUserToLeadModal()">Cancel</el-button>
						</el-form-item>
					</el-col>
				</el-row>
			</el-form>
		</el-dialog>
	</div>
</template>

<script>
// IMPORTS
import { mapGetters } from "vuex";
import Swal from 'sweetalert2';

// EXPORTS
export default {
	name: "AssignUsersToLead",
	computed: {
		...mapGetters({
			lead: "leadhistory/lead",
			assignUserToLeadDialogVisible: "leadhistory/assignUserToLeadDialogVisible",
			assignUserToLeadForm: 'leadhistory/assignUserToLeadForm',
			assignUserToLeadRules: 'leadhistory/assignUserToLeadRules',
			users: 'leadhistory/users',
			assignedUsers: "leadhistory/assignedUsers",
		})
	},

	// STATE
	data(){
		return {
			allUsers: ""
		}
	},

	// ACTIONS
	methods: {
		disableIto(user){

			if(this.assignedUsers.filter(assignedUser => user.id == assignedUser.id).length){
				return true;
			}
			else{
				return false;
			}
		},
		closeAssignUserToLeadModal(){
			this.$store.dispatch("leadhistory/setDialog", { close: false, form: "assign_user_to_lead" });
		},
		assignUserToLead(formName){
			this.$refs[formName].validate((valid) => {
				if(valid){
					// DISPATCH: FORM
					this.$store.dispatch('leadhistory/assignUserToLead').then(({ msg, data, success }) => {
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
		}
	},
}
</script>
