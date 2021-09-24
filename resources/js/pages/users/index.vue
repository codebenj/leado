<template>
  <Section className="users" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button-group class="fl-right r-btn-reset">
        <el-button
          type="primary"
          @click="$router.push({ name: 'admin.users.create' })"
          >Add New User</el-button
        >
        <el-button type="primary" v-on:click="delete_users">Delete</el-button>
        <el-button type="primary" v-on:click="export_users">Export</el-button>
      </el-button-group>
    </template>
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg p-r-xl" :gutter="24">
          <el-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
            <label>&nbsp;</label>
            <el-select v-model="filters[1].value" placeholder="Select">
              <el-option
                v-for="role in roles"
                :key="role.value"
                :label="role.label"
                :value="role.value"
                placeholder="Filter by role"
                clearable
              />
            </el-select>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
            <label>&nbsp;</label>
            <el-input
              v-model="filters[0].value"
              placeholder="Search..."
              clearable
            />
          </el-col>
        </el-row>

        <data-tables-server
          :data="users"
          :total="total"
          :loading="loading"
          :filters="filters"
          :pagination-props="{ pageSizes: [10, 15, 20] }"
          @selection-change="handleSelectionChange"
          @query-change="loadMore"
          @row-dblclick="editUser"
        >
          <el-table-column type="selection" width="55"> </el-table-column>
          <el-table-column prop="name" label="Name"> </el-table-column>
          <el-table-column prop="email" label="Email"> </el-table-column>
          <el-table-column prop="user_role.name" label="Role" width="auto">
            <template slot-scope="{ row }">
              <el-tag
                :type="
                  row.user_role.name === 'administrator' ? 'primary' : (row.user_role.name === 'organisation' ? 'warning' : 'info')
                "
                disable-transitions
                >{{ capitalize(row.user_role.name) }}</el-tag
              >
            </template>
          </el-table-column>
          <el-table-column
            label=""
            width="55"
            align="center"
            class-name="action b-none"
          >
            <template slot-scope="{ row }">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                  <i class="el-icon-caret-bottom"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editUser(row)"
                    >Edit</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-delete"
                    @click.native="deleteUser(row)"
                    >Delete</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>
    </template>
  </Section>
</template>
<script>
import Section from "~/components/Section";
import { mapGetters } from "vuex";
import Swal from "sweetalert2";
import { DataTables, DataTablesServer } from "vue-data-tables";
import { uppercase, capitalize } from "~/helpers";

export default {
  name: "Users",
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
    DataTablesServer,
  },
  data() {
    return {
      pageTitle: "User Settings",
      filters: [
        {
          props: "search",
          value: "",
        },
        {
          props: "role",
          value: "",
        },
      ],
      roles: [
        {
          label: "All",
          value: "",
        },
        {
          label: "Administrator",
          value: "Administrator",
        },
        {
          label: "User",
          value: "User",
        },
      ],
      userIds: [],
      payload: {},
    };
  },
  computed: mapGetters({
    users: "users/users",
    total: "users/total",
    loading: "users/loading",
  }),

  methods: {
    uppercase: uppercase,
    capitalize: capitalize,

    import_users() {},

    async loadMore(queryInfo) {
      this.payload = queryInfo;
      this.$store.dispatch("users/fetchUsers", this.payload);
    },

    handleSelectionChange(val) {
      this.userIds = [];
      for (var i = 0; i < val.length; i++) {
        this.userIds.push(val[i].id);
      }
    },

    editUser(row) {
      this.$router.push({
        name: "admin.users.update",
        params: {
          id: row.id,
        },
      });
    },

    deleteUser(row) {
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
            .dispatch("users/deleteUser", row.id)
            .then(({ success, message }) => {
              if (success) {
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

    delete_users() {
      if (this.userIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select users to delete",
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
            .dispatch("users/deleteUsers", this.userIds)
            .then(({ success, message }) => {
              if (success) {
                this.$store.dispatch("users/fetchUsers", this.payload);
                this.userIds = [];
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

    export_users() {
      if (this.userIds.length === 0) {
        Swal.fire({
          title: "Error",
          text: "Please select users to export",
          type: "error",
        });
        return;
      }

      this.$store.dispatch("users/export", this.userIds).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "users.xlsx");
        document.body.appendChild(link);
        link.click();
      });
    },
  },
};
</script>

<style scoped>
  @media all and (max-width: 426px){
    .r-btn-reset{
      float: none;
    }
  }
</style>
