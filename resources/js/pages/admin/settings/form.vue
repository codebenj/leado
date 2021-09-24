<template>
    <el-form :model="settingsForm" status-icon :rules="settingsFormRules" label-position="top"  ref="settingsForm" label-width="120px">
        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Escalation Level" prop="metadata.level" :error="settingsForm.errors.errors.level ? settingsForm.errors.errors.level[0] : ''">
                    <el-select v-model="settingsForm.metadata.level" placeholder="Select Level" @change="updateStatus">
                        <el-option v-for="(escalation, index) in escalations" :key="index" :value="index" :label="index">{{ index }}</el-option>
                    </el-select>
                </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Escalation Status" prop="metadata.status" :error="settingsForm.errors.errors.status ? settingsForm.errors.errors.status[0] : ''" @change="inputChange">
                    <el-select v-model="settingsForm.metadata.status" placeholder="Select Status">
                        <el-option v-for="(status, index) in escalations[settingsForm.metadata.level]" :key="index" :value="status" :label="status">{{ status }}</el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Key" prop="key" :error="settingsForm.errors.errors.key ? settingsForm.errors.errors.key[0] : ''">
                    <el-input type="text" v-model="settingsForm.key" @change="inputChange"></el-input>
                </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Value" prop="value" :error="settingsForm.errors.errors.value  ? settingsForm.errors.errors.value[0] : ''">
                    <el-input type="text" v-model="settingsForm.value" @change="inputChange"></el-input>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Name" prop="name" :error="settingsForm.errors.errors.name ? settingsForm.errors.errors.name[0] : ''">
                    <el-input type="text" v-model="settingsForm.name" @change="inputChange"></el-input>
                </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Type" prop="metadata.type" :error="settingsForm.errors.errors.type ? settingsForm.errors.errors.type[0] : ''">
                    <el-select v-model="settingsForm.metadata.type" placeholder="Select Type" @change="inputChange">
                        <el-option v-for="type in types" :key="type" :value="type">{{ type }}</el-option>
                    </el-select>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item label="Admin Tooltip" prop="metadata.admin_tooltip" :error="settingsForm.errors.errors.admin_tooltip ? settingsForm.errors.errors.admin_tooltip[0] : ''">
                    <el-input
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 4}"
                        placeholder="Please enter admin tooltip"
                        v-model="settingsForm.metadata.admin_tooltip"
                        @change="inputChange">
                    </el-input>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item label="Org Tooltip" prop="metadata.org_tooltip" :error="settingsForm.errors.errors.org_tooltip ? settingsForm.errors.errors.org_tooltip[0] : ''">
                    <el-input
                        type="textarea"
                        :autosize="{ minRows: 2, maxRows: 4}"
                        placeholder="Please enter org tooltip"
                        v-model="settingsForm.metadata.org_tooltip">
                    </el-input>
                </el-form-item>
            </el-col>
        </el-row>

        <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-form-item class="fl-right">
                    <el-button type="primary" :loading="loading" @click="saveSettings('settingsForm')" :disabled="!formUpdated">Submit</el-button>
                    <el-button type="danger" @click="closeDialog()">
                            Cancel
                    </el-button>
                </el-form-item>
            </el-col>
        </el-row>
    </el-form>
</template>
<script>
import Swal from 'sweetalert2'
import { mapGetters } from 'vuex';

export default {
	name: 'SettingsForm',
	data: () => ({
        formUpdated: false
	}),
	computed: mapGetters({
		escalations: 'settings/escalations',
        types: 'settings/types',
        loading: 'settings/loading',
        settingsForm: 'settings/settingsForm',
		settingsFormRules: 'settings/settingsFormRules',
        dialogVisible: 'settings/dialogVisible',
	}),
	methods: {
        updateStatus() {
          this.settingsForm.metadata.status = this.escalations[this.settingsForm.metadata.level][0]
          this.inputChange()
        },

        saveSettings(formName) {
            this.$refs[formName].validate((valid) => {
				if (valid) {
					// dispatch form submit
					this.$store.dispatch('settings/saveSettings').then(({ success, message, errors }) => {
                        if ( success ) {
                            this.formUpdated = false
                            this.$store.dispatch('settings/setDialog', false)

                            Swal.fire({
                                title: 'Success!',
                                text: message,
                                type: 'success',
                            })
                        }
                    })

				} else {
					return false;
				}
            });
        },

        closeDialog() {
            this.$store.dispatch('settings/setDialog', false)
        },

        inputChange() {
            this.formUpdated = true
        }
	},
	mounted() {
	}
}
</script>
