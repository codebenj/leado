<template>
  <Section className="org-locator-page" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          :model="orglocatorForm"
          status-icon
          :rules="orgLocatorFormRules"
          label-position="top"
          ref="orglocatorForm"
          label-width="120px"
        >
          <el-row :gutter="24">
            <el-col :xs="24" :sm="10" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Org. ID"
                prop="org_id"
                :error="
                  orglocatorForm.errors.errors.org_id
                    ? orglocatorForm.errors.errors.org_id[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.org_id"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="14" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Name"
                prop="name"
                :error="
                  orglocatorForm.errors.errors.name
                    ? orglocatorForm.errors.errors.name[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orglocatorForm.name"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Phone Number"
                prop="phone"
                :error="
                  orglocatorForm.errors.errors.phone
                    ? orglocatorForm.errors.errors.phone[0]
                    : ''
                "
              >
                <!-- <el-input type="text" v-model="orglocatorForm.phone"></el-input> -->
                <vue-tel-input
                  ref="phoneNumber"
                  :value="orglocatorForm.phone"
                  v-model="orglocatorForm.phone"
                  v-bind:class="{ required: !isRequired }"
                  @blur="blur"
                  @input="validate"
                />
              </el-form-item>
            </el-col>

            <!-- <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item label="Address" prop="address">
                <vue-google-autocomplete
                  id="search"
                  v-model="orglocatorForm.address_search"
                  classname="el-input__inner"
                  placeholder=""
                  v-on:placechanged="getAddressData"
                >
                </vue-google-autocomplete>
              </el-form-item>
            </el-col> -->
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Address"
                prop="street_address"
                :error="
                  orglocatorForm.errors.errors.street_address
                    ? orglocatorForm.errors.errors.street_address[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.street_address"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="Suburb"
                prop="suburb"
                :error="
                  orglocatorForm.errors.errors.suburb
                    ? orglocatorForm.errors.errors.suburb[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.suburb"
                ></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  orglocatorForm.errors.errors.postcode
                    ? orglocatorForm.errors.errors.postcode[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.postcode"
                ></el-input>
              </el-form-item>
            </el-col>

            <el-col :span="4">
              <!-- <el-form-item
                label="State"
                prop="state"
                :error="
                  orglocatorForm.errors.errors.state
                    ? orglocatorForm.errors.errors.state[0]
                    : ''
                "
              >
                <el-input type="text" v-model="orglocatorForm.state"></el-input>
              </el-form-item> -->

              <el-form-item
                  label="State"
                  prop="state"
                >
                  <el-select
                    popper-class="state_popper"
                      v-model="orglocatorForm.state"
                      placeholder="Select State"
                    >
                    <el-option
                      v-for="(state, index) in statesList"
                      :key="index"
                      :value="state.value"
                      :label="state.label"
                      >{{ state.label }}</el-option
                    >
                  </el-select>
                </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="10" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Last Year Sales"
                prop="last_year_sales"
                :error="
                  orglocatorForm.errors.errors.last_year_sales
                    ? orglocatorForm.errors.errors.last_year_sales[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.last_year_sales"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="10" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Year To Date Sales"
                prop="year_to_date_sales"
                :error="
                  orglocatorForm.errors.errors.year_to_date_sales
                    ? orglocatorForm.errors.errors.year_to_date_sales[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.year_to_date_sales"
                ></el-input>
              </el-form-item>
            </el-col>
            <!-- <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
              <el-form-item
                label="Stock Kits"
                prop="keeps_stock"
                :error="
                  orglocatorForm.errors.errors.keeps_stock
                    ? orglocatorForm.errors.errors.keeps_stock[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.keeps_stock"
                ></el-input>
              </el-form-item>
            </el-col> -->
          </el-row>

          <el-row :gutter="24">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Pricing Book"
                prop="pricing_book"
                :error="
                  orglocatorForm.errors.errors.pricing_book
                    ? orglocatorForm.errors.errors.pricing_book[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.pricing_book"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Priority"
                prop="priority"
                :error="
                  orglocatorForm.errors.errors.priority
                    ? orglocatorForm.errors.errors.priority[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="orglocatorForm.priority"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-button
                  v-show="orglocatorForm.id == ''"
                  type="primary"
                  @click="saveOrgLocator('orglocatorForm')"
                  >Submit</el-button
                >

                <el-button
                  v-show="orglocatorForm.id != ''"
                  :disabled="!isTouched"
                  type="primary"
                  @click="saveOrgLocator('orglocatorForm')"
                  >Update</el-button
                >

                <el-button
                  type="danger"
                  @click="$router.push({ name: 'orglocator' })"
                >
                  Cancel
                </el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </el-form>
      </el-card>
    </template>
  </Section>
</template>

<script>
import { mapGetters } from "vuex";
import { VueTelInput } from "vue-tel-input";
import Swal from "sweetalert2";
import VueGoogleAutocomplete from "vue-google-autocomplete";
import Section from "~/components/Section";

export default {
  name: "OrgLocator",
  layout: "master",
  middleware: "auth",

  components: {
    VueTelInput,
    VueGoogleAutocomplete,
    Section,
  },

  data: () => ({
    pageTitle: "Add New Organisation",
    isRequired: true,
    isPhoneNumberValidate: true,
    formatNumber: "",
    isTouched: false,
    isTouchedCounter: 0
  }),

  computed: {
    ...mapGetters({
      states: "orglocators/states",
      orglocatorForm: "orglocators/orglocatorForm",
      orgLocatorFormRules: "orglocators/orgLocatorFormRules",
      statesList: "leads/statesList",
    }),
  },

  methods: {
    getAddressData(addressData, placeResultData, id) {
      this.orglocatorForm.address_search = placeResultData.formatted_address
        ? placeResultData.formatted_address
        : "";
      this.orglocatorForm.street_address = `${
        addressData.street_number ? addressData.street_number : ""
      } ${addressData.route ? addressData.route : ""}`;

      this.orglocatorForm.suburb = addressData.locality ? addressData.locality : "";
      this.orglocatorForm.city = addressData.administrative_area_level_2 ? addressData.administrative_area_level_2 : "";
      this.orglocatorForm.state = this.getState(addressData, placeResultData);
      this.orglocatorForm.postcode = addressData.postal_code
        ? addressData.postal_code
        : "";
      this.orglocatorForm.country = addressData.country
        ? addressData.country
        : "";
    },

    getState(addressData, placeResultData) {
      const state = placeResultData.address_components.find(
        (address) =>
          address.short_name == addressData.administrative_area_level_1
      );

      return state ? state.long_name : "";
    },

    blur() {
      this.isRequired = true;
      if (!this.isPhoneNumberValidate) {
        this.isRequired = false;
      }
    },

    validate(number, isValid, country) {
      if (isValid.isValid) {
        this.formatNumber = isValid.number.e164;
        this.isRequired = true;
        this.isPhoneNumberValidate = true;
        this.$refs["orglocatorForm"].validate();
      } else {
        this.formatNumber = "";
        this.isRequired = false;
        this.isPhoneNumberValidate = false;
      }
    },

    saveOrgLocator(formName, next = null) {
      if (!this.isPhoneNumberValidate) {
        this.orglocatorForm.phone = "";
      } else {
        this.orglocatorForm.phone = this.formatNumber
          ? this.formatNumber
          : this.orglocatorForm.phone;//validate
      }

      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("orglocators/saveOrgLocator")
            .then(({ success, message, errors }) => {
              if (success) {
                this.isTouched = false
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    if(next == null){
                      this.$router.push({ name: "orglocator" });
                    }
                  },
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    editOrgLocator() {
      const id = this.$route.params.id ? this.$route.params.id : "";

      if (id) {
        this.$store.dispatch("orglocators/editOrgLocator", id);
        this.pageTitle = "Edit Org. Locator";
      } else {
        this.orglocatorForm.reset();
      }
    },

    getOptionText(index, state) {
      return index == 0 ? "Select State" : state;
    },

    formUpdated: function(newV, oldV) {
      console.log(this.orglocatorForm)
      if(this.orglocatorForm.id != ''){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    }
  },

  beforeRouteLeave (to, from, next) {
    if(this.isTouched){

      Swal.fire({
        title: 'Just Checking!',
        text: 'Your changes have not been updated. Would you like to update now?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.value) {
          this.saveOrgLocator('orglocatorForm', next)
          next()
        }else{
          next()
        }
      })

    }else{
      next()
    }
  },

  created(){
    this.$watch('orglocatorForm', this.formUpdated, {
      deep: true
    })
  },

  beforeMount() {
    this.editOrgLocator();
  },
};
</script>
