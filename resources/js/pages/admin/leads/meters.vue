<template>
  <el-row :gutter="24" class="m-b-md metersForm">
    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="!isDrawer">
      <el-card class="box-card b-none" v-if="histories" shadow="never">
        <div slot="header" class="clearfix">
          <h4>Metres Installed</h4>
        </div>

        <el-form
          :model="metersForm"
          status-icon
          :rules="metersFormRules"
          label-position="top"
          ref="metersForm"
          label-width="120px"
        >
          <el-row :gutter="24">
            <el-col :xs="24" :sm="24" :md="16" :lg="12" :xl="8">
              <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <el-form-item
                    label="Metres of Gutter Edge"
                    prop="gutter_edge_meters"
                    :error="
                      metersForm.errors.errors.gutter_edge_meters
                        ? metersForm.errors.errors.gutter_edge_meters[0]
                        : ''
                    "
                  >
                    <el-input-number
                      v-model="metersForm.gutter_edge_meters"
                      controls-position="right"
                      :min="0"
                      :disabled="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
                    ></el-input-number>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                  <el-form-item
                    label="Metres of Valley"
                    prop="valley_meters"
                    :error="
                      metersForm.errors.errors.valley_meters
                        ? metersForm.errors.errors.valley_meters[0]
                        : ''
                    "
                  >
                    <el-input-number
                      v-model="metersForm.valley_meters"
                      controls-position="right"
                      :min="0"
                      :disabled="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
                    ></el-input-number>
                  </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8"  v-show="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])">
                  <el-form-item>
                    <el-button
                      type="primary"
                      :loading="loading"
                      @click="saveMeters()"
                      >Submit</el-button
                    >
                  </el-form-item>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
        </el-form>
      </el-card>
    </el-col>

    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-else>
      <span class="drawer-tabs-title">Metres Installed</span>

      <el-form
        :model="metersForm"
        status-icon
        :rules="metersFormRules"
        label-position="top"
        ref="metersForm"
        label-width="120px"
      >
        <el-row :gutter="24">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-row :gutter="24">
              <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <el-form-item
                  label="Metres of Gutter Edge"
                  prop="gutter_edge_meters"
                  :error="
                    metersForm.errors.errors.gutter_edge_meters
                      ? metersForm.errors.errors.gutter_edge_meters[0]
                      : ''
                  "
                >
                  <el-input-number
                    v-model="metersForm.gutter_edge_meters"
                    controls-position="right"
                    :min="0"
                    :disabled="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
                  ></el-input-number>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <el-form-item
                  label="Metres of Valley"
                  prop="valley_meters"
                  :error="
                    metersForm.errors.errors.valley_meters
                      ? metersForm.errors.errors.valley_meters[0]
                      : ''
                  "
                >
                  <el-input-number
                    v-model="metersForm.valley_meters"
                    controls-position="right"
                    :min="0"
                    :disabled="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
                  ></el-input-number>
                </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
                <el-form-item
                  label="Date of Installation:"
                  prop="installation_date"
                  :error="
                    metersForm.errors.errors.installation_date
                      ? metersForm.errors.errors.installation_date[0]
                      : ''
                  "
                >
                  <el-date-picker
                    :picker-options="datePickerOptions"
                    class="w-100"
                    type="month"
                    v-model="metersForm.installation_date"
                    placeholder="Pick a month"
                    :disabled="!isAssignedRoles(user, ['super_admin', 'administrator', 'user'])"
                  >
                  </el-date-picker>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24"  v-show="isAssignedRoles(user, ['super_admin', 'administrator', 'user'])">
                <el-form-item>
                  <el-button
                    dusk="meters-form-save"
                    type="primary"
                    :loading="loading"
                    @click="saveMeters()"
                    class="w-100"
                    >Submit</el-button
                  >
                </el-form-item>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </el-form>
    </el-col>
  </el-row>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";
import { isAssignedRoles } from "~/helpers";
import moment, { now } from "moment";

export default {
  name: "MetersForm",
  props: {
    isDrawer: { type: Boolean, default: false }
  },
  data: () => ({}),
  computed: {
    ...mapGetters({
    user: "auth/user",
    organisations: "leadhistory/organizations",
    histories: "leadhistory/histories",
    loading: "leadhistory/loading",
    metersForm: "leadhistory/metersForm",
    metersFormRules: "leadhistory/metersFormRules",
  }),
    //allow dates - today and previous dates
    datePickerOptions() {
      var today = new Date();

      return {
        disabledDate(date) {
          return !(date < today);
        }
      };
    },
  },

  methods: {
    isAssignedRoles: isAssignedRoles,
    saveMeters() {
      // dispatch save lead escalation data
      this.$store
        .dispatch("leadhistory/saveMeters")
        .then(({ success, message, errors }) => {
          if (success) {
            Swal.fire({
              title: "Success!",
              text: message,
              type: "success",
            });
          } else {
            Swal.fire({
              title: "Oops!",
              text: errors.error,
              type: "error",
            });
          }
        });
    },
  },

  beforeMount() {},
};
</script>
