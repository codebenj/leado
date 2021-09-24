<template>
  <div>
    <!-- action dropdown for admin -->
    <el-dropdown
      trigger="click"
      v-show="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
    >
      <span class="el-dropdown-link">
        <i class="el-icon-caret-bottom"></i>
      </span>
      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item
          icon="el-icon-top-right"
          @click.native="showLeadHistory(row)"
          >Lead Overview</el-dropdown-item
        >

        <el-dropdown-item
          icon="el-icon-top-right"
          @click.native="showEscalation(row)"
          v-if="isManualUpdateEnabled"
          >Update Status</el-dropdown-item
        >

        <el-dropdown-item
          icon="el-icon-data-line"
          @click.native="showStats(row)"
          >Stats</el-dropdown-item
        >

        <el-dropdown-item icon="el-icon-edit" @click.native="editLead(row)"
          >Edit</el-dropdown-item
        >
        <el-dropdown-item icon="el-icon-delete" @click.native="deleteLead(row)"
          >Delete</el-dropdown-item
        >
      </el-dropdown-menu>
    </el-dropdown>

    <!-- action dropdown for org -->
    <el-dropdown
      class="drop-down-action-organization"
      trigger="click"
      v-show="isAssignedRoles(user, ['organisation'])"
      v-if="!isWonOrLost() || is_critical"
    >
      <span class="el-dropdown-link">
        {{ is_critical || checkForReminderSent() ? 'UPDATE REQUIRED' : 'UPDATE' }}
      </span>
      <el-dropdown-menu slot="dropdown" class="action-popper">
        <el-dropdown-item
          icon="el-icon-top-right"
          @click.native="showEscalation(row)"
          >Update Status</el-dropdown-item
        >
        <el-dropdown-item icon="el-icon-edit" @click.native="viewLead(row)"
          >View Lead</el-dropdown-item
        >
      </el-dropdown-menu>
    </el-dropdown>

    <el-dropdown
      class="drop-down-action-organization"
      trigger="click"
      v-show="isAssignedRoles(user, ['organisation'])"
      v-else
    >
      <span class="el-dropdown-link">
        VIEW LEAD
      </span>
      <el-dropdown-menu slot="dropdown" class="action-popper">
        <el-dropdown-item icon="el-icon-edit" @click.native="viewLead(row)"
          >View Lead</el-dropdown-item
        >
      </el-dropdown-menu>
    </el-dropdown>
  </div>
</template>
<script>
import Swal from "sweetalert2";
import { isAssignedRoles } from "~/helpers";
import { Bus } from '~/app'
import Cookies from 'js-cookie'
import { mapGetters } from 'vuex';

export default {
  name: "LeadsAction",
  props: ["row", "user", "isManualUpdateEnabled", "is_critical"],
  computed: mapGetters({
    userData: "auth/user",
  }),
  mounted() {
    //console.log(this.row);
  },
  methods: {
    isAssignedRoles: isAssignedRoles,

    showLeadHistory(row) {
      this.$emit( 'singleClickRow', row )
    },

    showStats(row) {
      const route = this.$router.resolve({
        path: `/admin/lead/${row.lead_id}/stats`,
      });
      window.open(route.href, "_blank");
    },

    editLead(row) {
      let id = ( row.lead.lead_id ) ? parseInt( row.lead.lead_id ) : row.lead_id
      // OLD CODE: let id = ( row.lead.lead_id ) ? parseInt( row.lead.lead_id ) : row.lead.id
      Cookies.set( 'new_lead_id', id )
      this.$router.push({
        name: "admin.leads.update",
        params: { id: id }
      });
    },

    deleteLead(row) {
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
            .dispatch("leads/deleteLead", row.lead_id)
            .then(({ success, message }) => {
              Bus.$emit( 'user-loading', true )
              if (success) {
                setTimeout( () => {
                  Bus.$emit( 'user-loading', false )
                }, 1000 )
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

    modal(escalationLevel, escalationStatus) {
      switch (escalationLevel) {
        case "Accept Or Decline":
          if (escalationStatus == "Declined-Lapsed"){
            return "declined-lapse";
          }if(escalationStatus == "Declined"){
            return "declined";
          }
          return "aod";
        case "Confirm Enquirer Contacted":
          if (escalationStatus == "Declined"){
            return "cec-declined";
          }
          else if(escalationStatus == "Discontinued"){
            return "discontinued";
          }

          return "cec";
        case "In Progress":
          return "inprogress";
        case "Won":
          return "won";
        case "Lost":
          return "lost";
        case "Abandoned":
          return "abandoned";
        case "Discontinued":
          return "discontinued";
        case "Declined":
          return "declined";
        default:
          return "finished";
      }
    },

    showEscalation(row) {
      this.$store.dispatch("leadescalation/showEscalationModal", {
        lead: row,
        showEscalation: true,
        modal: this.modal(row.escalation_level, row.escalation_status),
        user: this.userData
      });
    },

    viewLead(row) {
      this.$emit( 'singleClickRow', row )
    },

    isWonOrLost() {
      return this.row.escalation_status === 'Won' || this.row.escalation_status === 'Lost';
    },

    checkForReminderSent() {
      const _escalation_status = this.row.escalation_status.split(' - ');
      for (let index = 0; index < _escalation_status.length; index++) {
        const element = _escalation_status[index];
        if(element === 'Reminder Sent' || element === 'Email Sent') {
          return true
        }
      }
      return false;
    }
  },
};
</script>
