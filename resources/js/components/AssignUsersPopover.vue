<template>
    <el-popover
        placement="bottom"
        width="350"
        trigger="click">

        <el-row :gutter="24" class="mb-3 assigned-container" v-if="usersAssigned.length > 0">
            <template v-for="( assignedUser, index ) in usersAssigned">
                <el-col :span="12" class="mb-1" :key="index + '-' + assignedUser.id">
                    <div class="user-assigned">
                        <el-avatar :src="setAvatar( assignedUser )" :size="25" class="user-assigned-avatar"></el-avatar>
                        <span class="user-assigned-name">
                            <template v-if="assignedUser.name.length > 12">
                                {{ assignedUser.name.substring( 0, 12 ) }}...
                            </template>
                            <template v-else>
                                {{ assignedUser.name }}
                            </template>
                        </span>
                        <i class="el-icon-close user-assigned-close clickable" @click="removeAssignedUser( assignedUser.id )"></i>
                    </div>
                </el-col>
            </template>
        </el-row>

        <el-row :gutter="24">
            <el-col :span="24">
                <el-input v-model="queryUser" placeholder="Enter another name" @input="queryUserInput"></el-input>
            </el-col>
        </el-row>

        <el-divider>
            People
        </el-divider>

        <el-row :gutter="24" v-if="filterUsers">
            <template v-if="filterUsers.length < 1">
                <el-col :span="24" align="center">
                    <h6>No Users found.</h6>
                </el-col>
            </template>
            <template v-else>
                <el-col :span="24" class="user-container">
                    <template v-for="( user, index ) in filterUsers">
                        <p :key="index + '-' + user.id" class="user-select clickable" v-if="isAssigned( user.id )" @click="assignUser( user.id, user )">
                            <el-avatar :src="user.avatar" :size="30" class="user-avatar"></el-avatar>
                            {{ user.first_name + " " + user.last_name + " - ( " + roleCheck( user ) + " )" }}
                        </p>
                    </template>
                </el-col>
            </template>
        </el-row>

        <template slot="reference" class="assign-template">
        <div class="assign-template">
            <template v-if="usersAssigned.length > 0">
            <!-- Single Avatar -->
            <template v-if="usersAssigned.length == 1">
                <el-avatar :src="usersAssigned[0].avatar" class="assign-icon clickable"></el-avatar>
            </template>

            <!-- Two Avatar -->
            <template v-else-if="usersAssigned.length == 2">
                <el-avatar :src="usersAssigned[0].avatar" class="assign-icon clickable"></el-avatar>
                <el-avatar :src="usersAssigned[1].avatar" class="assign-icon assign-icon-two assign-icon-two-border clickable"></el-avatar>
            </template>

            <!-- Two Avatar with Count -->
            <template v-if="usersAssigned.length >= 3">
                <el-avatar :src="usersAssigned[0].avatar" class="assign-icon clickable"></el-avatar>
                <el-avatar class="assign-icon assign-icon-two assign-icon-bg clickable">
                {{ `+ ${usersAssigned.length - 1}` }}
                </el-avatar>
            </template>
            </template>
            <template v-else>
            <el-avatar icon="el-icon-user-solid" class="assign-icon clickable"></el-avatar>
            </template>

            <i class="el-icon-circle-plus assign-template-plus clickable" type="primary"></i>
        </div>
        </template>
    </el-popover>
</template>

<script>
// IMPORTS
import { mapGetters } from "vuex";
import { Bus } from '~/app'

// EXPORTS
export default {
	name: "AssignUsersPopover",

    props: [ 'id', 'assigned_users', 'users', 'type' ],

	computed: {
		...mapGetters({
			lead: "leadassign/lead",
		})
	},

    data: () => ({
        queryUser: '',
        filterUsers: [],
        usersAssigned: []
    }),

    beforeMount() {
        this.initData()
    },

    mounted() {
        Bus.$on( 'reload-assigned', id => {
            if ( id && id == this.id ) this.fetchAssignedUsers( id )
        } )

        Bus.$on( 'update-state-lead', event => {
            if ( event.lead_id && event.lead_id == this.id ) this.fetchAssignedUsers( event.lead_id )
        } )
    },

	// ACTIONS
	methods: {
        initData() {
            let users = []
            this.filterUsers = this.users

            if ( this.type == 'drawer' ) {
                users = this.assigned_users
            } else {
                if ( this.assigned_users ) {
                    if ( Array.isArray( this.assigned_users ) ) {
                        users = this.assigned_users

                    } else {
                        users = this.assigned_users.substring( 1, this.assigned_users.length - 1 ).split( ',' )
                    }

                    let temp_user = []
                    users.forEach( el => {
                        temp_user.push( parseInt( el ) )
                    } )
                    users = temp_user
                }
            }

            this.setUsers( users )
        },

        setUsers( ids ) {
            if ( ids.length > 0 ) {
                let temp_user = []
                let temp_assigned = []
                ids.forEach( id => {
                    this.users.forEach( user => {
                        // filtered users
                        if ( user.id !== id ) {
                            let exist = false

                            temp_user.forEach( temp => {
                                if ( temp.id == id ) exist = true
                            } )

                            if ( ! exist ) temp_user.push( user )
                        }

                        // assigned users
                        if ( user.id == id ) temp_assigned.push( user )
                    } )
                } )
                this.filterUsers = temp_user
                this.usersAssigned = temp_assigned

            }
        },

        queryUserInput() {
            if ( this.queryUser == '' ) {
                this.filterUsers = this.users

            } else {
                let users = this.filterUsers.filter( x => {
                    let name = x.name.toLowerCase()
                    let exist = name.includes( this.queryUser.toLowerCase() )
                    return exist
                } )

                this.filterUsers = users
            }
        },

        isAssigned( id ) {
            let bol = true

            this.usersAssigned.forEach( el => {
                if ( el.id == id ) bol = false
            } )

            return bol
        },

        async fetchAssignedUsers( id ) {
            let ids = []
            await this.$store.dispatch( 'leadassign/fetchAssignedUsers', id ).then( res => {
                let data = res

                if ( data && data.length > 0 ) {
                    data.forEach( user => ids.push( user.id ) )
                }
            } )
            this.setUsers( ids )
        },

        async assignUser( id, userData ) {
            let ids = []
            await this.$store.dispatch( 'leadassign/fetchAssignedUsers', this.id ).then( res => {
                let data = res

                if ( data.length > 0 ) {
                    data.forEach( user => ids.push( user.id ))
                }
            } )
            ids.push( id )
            await this.$store.dispatch('leadassign/assignUserToLead', { lead_id: this.id, ids: ids }).then( res => {
                if ( res ) {
                    this.usersAssigned.push( userData )
                    Bus.$emit( 'update-state-lead', { ids: ids, lead_id: this.id } )
                }
            } )
        },

        removeAssignedUser( user_id ) {
            let ids = []
            let index = null
            let removed_user = null

            this.usersAssigned.forEach( ( el, i ) => {
                if ( el.id !== user_id ) {
                    ids.push( el.id )
                } else {
                    removed_user = el
                    index = i
                }
            } )

            this.$store.dispatch('leadassign/removeAssignedUser', { lead_id: this.id, ids: ids }).then( res => {
                if ( res ) {
                    this.usersAssigned.splice( index, 1 )

                    let exist = this.filterUsers.find( find => find.id == removed_user.id )
                    if ( ! exist ) this.filterUsers.push( removed_user )

                    Bus.$emit( 'update-state-lead', { ids: ids, lead_id: this.id } )
                }
            } )
        },

        setAvatar( userData ) {
            return userData.avatar
        },

        roleCheck( user ) {
            let role = ( user.user_role.name == 'administrator' ) ? 'Admin' : 'User'
            return role
        },
	}
}
</script>

<style lang="scss" scoped>
    .assign-template {
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    .assign-template i {
        margin-left: 5px;
    }

    .assign-template:hover .assign-template-plus {
        opacity: 1;
    }

    .assign-template-plus {
        opacity: 0;
        color: #409eff;
    }

    .assign-icon-two {
        margin-left: -15px;

        .assign-icon-two-border {
            border: 3px solid #fff;
        }
    }

    .assign-icon-bg {
        background: #333333 !important;
    }

    .user-container {
        max-height: 300px;
        overflow-y: auto;

        .user-select {
            display: flex;
            align-items: center;
            margin-bottom: 10px !important;

            .user-avatar {
                margin-right: .5em;
            }
        }

        .user-select:hover {
            background: #409EFF;
            border-radius: 5px;
            color: #ffffff;
        }
    }

    .assigned-container {
        max-height: 150px;
        overflow-y: auto;

        .assigned-tag {
            .assigned-avatar {
                width: 25px;
                height: 25px;
                position: absolute;
                margin-top: 5px;
            }

            .el-tag {
                width: 100%;
                text-align: right;
            }
        }
    }

    .mb-1 {
        margin-bottom: .5em;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .user-assigned {
        display: flex;
        align-items: center;
        background: #E5F4FF;
        border-radius: 50px;
        width: 100% !important;
        padding-right: 5px;

        .user-assigned-name {
            margin-left: 3px;
        }

        .user-assigned-close {
            margin-left: auto !important;
            margin-top: 1px;
            border-radius: 50%;
            padding: 1px 1px 0px;

            &:hover {
                background: #fff;
            }
        }
    }
</style>
