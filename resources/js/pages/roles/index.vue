<template>
  <Section className="roles-permissions" :pageTitle="pageTitle">
    <template v-slot:button>
      <el-button class="fl-right" type="primary" @click="addNewRole()">
        Add New Role
      </el-button>
    </template>
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-row class="m-b-lg p-r-xl" :gutter="20">
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-input
              v-model="filters[0].value"
              placeholder="Search..."
              clearable
            />
          </el-col>
        </el-row>

        <data-tables-server
          :data="roles"
          :total="total"
          :filters="filters"
          :loading="loading"
          :pagination-props="{ pageSizes: [10, 15, 20] }"
          @query-change="loadMore"
        >
          <el-table-column prop="name" label="Role Name">
            <template slot-scope="{ row }">
              <span class="text-capitalize">{{ row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="permissions" label="Permissions">
            <template slot-scope="{ row }">
              <el-tag
                class="m-r-md m-t-md m-b-md"
                size="small"
                v-for="permission in row.permissions"
                :key="permission.id"
                >{{ permission.name }}</el-tag
              >
            </template>
          </el-table-column>

          <el-table-column
            label=""
            width="55"
            align="center"
            class-name="action b-none"
          >
            <template slot-scope="row">
              <el-dropdown trigger="click">
                <span class="el-dropdown-link">
                  <i class="el-icon-caret-bottom"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item
                    icon="el-icon-edit"
                    @click.native="editRole(row)"
                    >Edit</el-dropdown-item
                  >
                  <el-dropdown-item
                    icon="el-icon-delete"
                    @click.native="deleteRole(row)"
                    >Delete</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </template>
          </el-table-column>
        </data-tables-server>
      </el-card>

      <el-card class="box-card b-none" shadow="never">
        <div slot="header" class="clearfix">
          <h4>Permissions</h4>
        </div>
        <div>
          <el-tag
            class="permission-tag m-sm"
            :key="index"
            v-for="(permission, index) in permissions"
            :disable-transitions="false"
            @close="removePermission(permission)"
            closable
          >
            {{ permission }}
          </el-tag>

          <el-input
            class="input-new-tag"
            v-if="permissionInputVisible"
            v-model="permissionForm.name"
            ref="savePermissionInput"
            size="mini"
            @keyup.enter.native="addPermission"
            @blur="addPermission"
          >
          </el-input>
          <el-button
            v-else
            class="button-new-tag"
            size="small"
            @click="showPermissionInput"
            >+ New Permission</el-button
          >
        </div>
      </el-card>

      <el-dialog
        :title="dialogTitle"
        v-dialogDrag
        ref="dialog__wrapper"
        :visible="roleDialogVisible"
        :show-close="false"
        append-to-body
        @close="close()"
        @open="open()"
        width="30%"
      >
        <RolesForm />
      </el-dialog>
    </template>
  </Section>
</template>
<script>
import { mapGetters } from "vuex";
import { DataTablesServer } from "vue-data-tables";
import Swal from "sweetalert2";
import RolesForm from "~/pages/roles/form";
import Section from "~/components/Section";

export default {
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
    DataTablesServer,
    RolesForm,
  },
  data: () => ({
    pageTitle: "Roles & Permissions",
    dialogTitle: "Add New Role",
    filters: [
      {
        props: "search",
        value: "",
      },
    ],
  }),

  computed: mapGetters({
    roles: "roles/roles",
    total: "roles/total",
    loading: "roles/loading",
    roleDialogVisible: "roles/dialogVisible",
    permissions: "permissions/permissions",
    permissionForm: "permissions/permissionForm",
    permissionInputVisible: "permissions/permissionInputVisible",
  }),
  methods: {
    async loadMore(queryInfo) {
      this.$store.dispatch("roles/fetchRoles", queryInfo);
    },

    handleClose(done) {
      this.$store.dispatch("roles/setDialog", true);
    },

    open() {
      this.$store.dispatch("roles/setDialog", true);
    },

    close() {
      this.$store.dispatch("roles/setDialog", false);
    },

    addNewRole() {
      this.dialogTitle = "Add New Role";
      this.$store.dispatch("roles/editRole");
    },

    editRole({ row }) {
      this.dialogTitle = "Edit Role";
      this.$store.dispatch("roles/editRole", row);
    },

    deleteRole({ row }) {
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
            .dispatch("roles/deleteRole", row.id)
            .then(({ success, message }) => {
              if (success) {
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              } else {
                Swal.fire({
                  title: "Oops!",
                  text: message,
                  type: "error",
                });
              }
            });
        }
      });
    },

    showPermissionInput() {
      this.$store.dispatch("permissions/showPermission");

      this.$nextTick((_) => {
        this.$refs.savePermissionInput.$refs.input.focus();
      });
    },

    addPermission(permission) {
      this.$store
        .dispatch("permissions/addPermission", permission)
        .then((response) => {
          if (response && response.errors) {
            Swal.fire({
              title: "Oops!",
              text: this.permissionForm.errors.errors.name,
              type: "error",
            });
          }
        });
    },

    removePermission(permission) {
      this.$store
        .dispatch("permissions/removePermission", permission)
        .then(() => {
          this.loadMore({
            page: 1,
            pageSize: 20,
          });
        });
    },
  },

  beforeMount() {
    this.$store.dispatch("permissions/fetchPermissions");
  },
};
</script>
