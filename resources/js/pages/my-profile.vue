<template>
  <Section className="my-profile" :pageTitle="pageTitle">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          :model="profileForm"
          status-icon
          :rules="profileFormRules"
          label-position="top"
          ref="profileForm"
          label-width="120px"
          class="demo-ruleForm"
        >
          <el-row :gutter="20">
            <div class="text-center">
              <img v-if="userAvatar" :src="userAvatar" class="avatar m-b-md" />

              <el-button type="primary" id="pick-avatar">Select an image</el-button>
              <avatar-cropper
                @uploaded="handleUploaded"
                trigger="#pick-avatar"
                upload-url="/api/v1/users/upload_avatar"
                upload-form-name="image"
                :labels="{ submit: 'OK', cancel: 'Cancel' }"
              />
            </div>
          </el-row>

          <el-row :gutter="20" class="m-t-lg" v-if="user && user.user_role.name == 'user-role-to-show-hide-temporary'">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Company Name"
                prop="company_name"
                :error="
                  profileForm.errors.errors.company_name
                    ? profileForm.errors.errors.company_name[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="profileForm.company_name"
                  @change="inputChange"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="First Name"
                prop="first_name"
                :error="
                  profileForm.errors.errors.first_name
                    ? profileForm.errors.errors.first_name[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="profileForm.first_name"
                  @change="inputChange"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Last Name"
                prop="last_name"
                :error="
                  profileForm.errors.errors.last_name
                    ? profileForm.errors.errors.last_name[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="profileForm.last_name"
                  @change="inputChange"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Email"
                prop="email"
                :error="
                  profileForm.errors.errors.email
                    ? profileForm.errors.errors.email[0]
                    : ''
                "
              >
                <el-input type="email" v-model="profileForm.email" @change="inputChange"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Contact Number"
                prop="phone"
                :error="
                  profileForm.errors.errors.phone
                    ? profileForm.errors.errors.phone[0]
                    : ''
                "
              >
                <!-- <el-input type="text" v-model="profileForm.phone"></el-input> -->
                <vue-tel-input
                  v-model="profileForm.phone"
                  :value="profileForm.phone"
                  v-bind:class="{ required: !isRequired }"
                  @blur="blur"
                  @input="input"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Password"
                prop="password"
                :error="
                  profileForm.errors.errors.password
                    ? profileForm.errors.errors.password[0]
                    : ''
                "
              >
                <el-input
                  @change="inputChange"
                  type="password"
                  v-model="profileForm.password"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Confirm Password"
                prop="password"
                :error="
                  profileForm.errors.errors.password_confirmation
                    ? profileForm.errors.errors.password_confirmation[0]
                    : ''
                "
              >
                <el-input
                  @change="inputChange"
                  type="password"
                  v-model="profileForm.password_confirmation"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="State"
                prop="state"
                :error="
                  profileForm.errors.errors.state
                    ? profileForm.errors.errors.state[0]
                    : ''
                "
              >
                <!-- <el-input type="text" v-model="orgForm.state"></el-input> -->
                <el-select v-model="profileForm.state" placeholder="Select State" @change="inputChange">
                <el-option
                  v-for="item in states"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  profileForm.errors.errors.postcode
                    ? profileForm.errors.errors.postcode[0]
                    : ''
                "
              >
                <el-input type="text" v-model="profileForm.postcode" @change="inputChange"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <!-- <el-row :gutter="24" class="d-none">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item
                label="Address"
                prop="search_address"
                :error="
                  profileForm.errors.errors.address
                    ? profileForm.errors.errors.address[0]
                    : ''
                "
              >
                <div class="el-input">
                  <vue-google-autocomplete
                    id="search"
                    v-model="profileForm.address_search"
                    classname="el-input__inner"
                    placeholder="Start typing address..."
                    v-on:placechanged="getAddressData"
                  >
                  </vue-google-autocomplete>
                </div>
                <div class="el-form-item__error" v-if="addressSearchError">{{ profileFormRules.address_search[0].message }}</div>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" class="d-none">
              <el-form-item
                label="Address"
                prop="address"
                :error="
                  profileForm.errors.errors.address
                    ? profileForm.errors.errors.address[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="profileForm.address"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8" class="d-none">
              <el-form-item
                label="City"
                prop="city"
                :error="
                  profileForm.errors.errors.city
                    ? profileForm.errors.errors.city[0]
                    : ''
                "
              >
                <el-input
                  type="text"
                  v-model="profileForm.city"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
            </el-col>
          </el-row> -->

          <!-- <el-row :gutter="24" class="d-none">
            <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
              <el-form-item
                label="Suburb"
                prop="suburb"
                :error="
                  profileForm.errors.errors.suburb
                    ? profileForm.errors.errors.suburb[0]
                    : ''
                "
              >
                <el-input type="text" v-model="profileForm.suburb"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  profileForm.errors.errors.postcode
                    ? profileForm.errors.errors.postcode[0]
                    : ''
                "
              >
                <el-input type="text" v-model="profileForm.postcode"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
              <el-form-item
                label="State"
                prop="state"
                :error="
                  profileForm.errors.errors.state
                    ? profileForm.errors.errors.state[0]
                    : ''
                "
              >
                <el-input type="text" v-model="profileForm.state"></el-input>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
              <el-form-item
                label="Country"
                prop="country_id"
                :error="
                  profileForm.errors.errors.country_id
                    ? profileForm.errors.errors.country_id[0]
                    : ''
                "
              >
                <el-select
                  v-model="profileForm.country_id"
                  placeholder="Select Country"
                >
                  <el-option
                    v-for="country in countries"
                    :key="country.code"
                    :label="country.name"
                    :value="country.id"
                    >{{ country.name }}</el-option
                  >
                </el-select>
              </el-form-item>
            </el-col>
          </el-row> -->

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-button type="primary" @click="save('profileForm')" :disabled="!formUpdated"
                  >Submit</el-button
                >
                <el-button
                  type="danger"
                  @click="$router.push({ name: 'admin.organisations' })"
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
import Swal from "sweetalert2";
import AvatarCropper from "vue-avatar-cropper";
import Section from "~/components/Section";
import { VueTelInput } from "vue-tel-input";
import VueGoogleAutocomplete from "vue-google-autocomplete";

export default {
  title: "MyProfile",
  layout: "master",
  middleware: "auth",
  components: {
    Section,
    AvatarCropper,
    VueTelInput,
    VueGoogleAutocomplete
  },

  data: () => ({
    pageTitle: "My Profile",
    address: "Address",
    userAvatar: undefined,
    isPhoneNumberValidate: true,
    isRequired: true,
    formatNumber: "",
    addressSearchError: false,
    formUpdated: false
  }),

  computed: mapGetters({
    countries: "organisations/countries",
    profileForm: "profile/profileForm",
    profileFormRules: "profile/profileFormRules",
    profile: "profile/profile",
    user: "auth/user",
    states: "leads/statesList",
  }),

  methods: {
    getAddressData(addressData, placeResultData, id) {
      const country = addressData.country ? addressData.country : ""

      this.profileForm.address_search = placeResultData.formatted_address ? placeResultData.formatted_address : "";
      this.profileForm.address = `${addressData.street_number ? addressData.street_number : "" } ${addressData.route ? addressData.route : ""}`;

      this.profileForm.suburb = addressData.locality ? addressData.locality : "";
      this.profileForm.city = addressData.administrative_area_level_2 ? addressData.administrative_area_level_2 : "";

      this.profileForm.city = addressData.locality ? addressData.locality : "";
      this.profileForm.state = this.getState(addressData, placeResultData);
      this.profileForm.postcode = addressData.postal_code ? addressData.postal_code : "";
      this.profileForm.country = country;
      this.profileForm.country_id = this.getCountryID(country) ?? 0

      this.addressChange()
    },

    addressChange() {
      let hasError = false
      let errorMessage = ''
      const addressParts = [
        {
          'part': 'state',
          'message': 'Please select more accurate address, address is missing state.'
        },
        {
          'part': 'postcode',
          'message': 'Please select more accurate address, address is missing postcode.'
        },
        {
          'part': 'country',
          'message': 'Please select more accurate address, address is missing country.'
        }
      ];

      if (!this.profileForm[addressParts[0].part] && !this.profileForm[addressParts[1].part]) {
        hasError = true;
        errorMessage = 'Please select more accurate address, address is missing postcode and state.'
      } else {
         for (let addressPart of addressParts) {
          if ( ['state', 'postcode'].includes(addressPart.part) ) continue;

          hasError = !this.profileForm[addressPart.part]
          errorMessage = hasError ? addressPart.message : ''

          if (hasError) {
            break;
          }
        }
      }

      this.addressSearchError = hasError
      this.profileFormRules.address_search[0].message = errorMessage
    },

    getCountryID(country){
      const country_ = this.countries.filter(c => { return c.name === country})
      return country_[0].id;
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

    input(number, isValid, country) {
      if (isValid.isValid) {
        this.formatNumber = isValid.number.e164;
        this.isRequired = true;
        this.isPhoneNumberValidate = true;
        this.$refs["profileForm"].validate();
      } else {
        this.formatNumber = "";
        this.isRequired = false;
        this.isPhoneNumberValidate = false;
      }

      this.inputChange()
    },

    save(formName) {
      if (!this.isPhoneNumberValidate) {
        this.profileForm.phone = "";
      } else {
        this.profileForm.phone = this.formatNumber
          ? this.formatNumber
          : this.profileForm.phone;
      }

      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("profile/save")
            .then(({ success, message, errors }) => {
              if (success) {
                this.formUpdated = false

                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                });
              }
            });
        } else {
          return false;
        }
      });
    },

    fetchStates() {
      this.$store.dispatch("organisations/fetchStates");
    },

    editOrg() {
      const orgId = this.$route.params.id ? this.$route.params.id : "";

      if (orgId) {
        this.$store.dispatch("organisations/editOrganisation", orgId);
        this.cardTitle = "Edit Organisation";
      }
    },

    getOptionText(index, state) {
      return index == 0 ? "Select State" : state;
    },

    handleUploaded(res) {
      this.userAvatar = res.data;
      this.$store.dispatch("auth/fetchUser")
    },

    beforeAvatarUpload() {},

    inputChange() {
      this.formUpdated = true;
    }
  },

  mounted() {
    this.userAvatar = this.user.avatar.length ? this.user.avatar : undefined;
  },

  beforeMount() {
    this.editOrg();
    this.fetchStates();
    this.$store.dispatch("profile/get");
    this.$store.dispatch("auth/fetchUser");
  },
};
</script>
