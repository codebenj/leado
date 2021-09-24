<template>
  <Section dusk="leadForm" className="lead-form" :pageTitle="pageTitle">

    <template v-slot:button v-if="leadForm.id">
      <el-button
        type="primary"
        class="step-button fl-right r-btn-reset"
        @click="escalationPage"
        plain
        >Lead Overview</el-button
      >
    </template>

    <template v-slot:content>
      <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="18" :lg="18" :xl="18">
             <el-card class="box-card b-none" shadow="never">
              <el-button
                type="primary"
                class="step-button"
                @click="prev"
                :disabled="active == 0"
                plain
                >Previous</el-button
              >


              <el-button
                dusk="leadForm-save"
                v-show="leadForm.lead_id == ''"
                v-if="active == lastStep && !organisation_id_temp"
                class="step-button"
                type="primary"
                :loading="loading"
                @click="saveLead('leadForm')"
                >
                {{ submitText }}
              </el-button>

              <el-button
                dusk="leadForm-update"
                v-show="leadForm.lead_id != '' "
                :disabled="!isTouched"
                v-if="active == lastStep && !organisation_id_temp"
                class="step-button"
                type="primary"
                :loading="loading"
                @click="saveLead('leadForm')"
                >
                {{ submitText }}
              </el-button>

              <el-button
                v-else-if="active == lastStep"
                class="step-button"
                type="primary"
                :loading="loading"
                @click="outgoingNotification()"
                >
                Continue
              </el-button>

              <el-button
                dusk="leadForm-next"
                class="step-button"
                type="primary"
                @click="next('leadForm')"
                v-else
                >Next</el-button
              >
             </el-card>
          </el-col>
          <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" align="end" v-if="leadForm.id">
             <el-card class="box-card b-none" shadow="never">
               <p class="detail">Lead ID #{{ ( new_lead_id ) ? new_lead_id : leadForm.id }}</p>
               <p class="detail">Created: {{ leadForm.created_at | moment("DD/MM/YYYY") }}</p>
             </el-card>
          </el-col>
        </el-row>

      <el-card class="box-card b-none" shadow="never">
        <el-steps
          class="m-b-lg"
          :active="active"
          finish-status="success"
          align-center
          simple

        >
          <el-step
            v-for="(step, index) in steps"
            :key="index"
            :title="step.title"
            @click.native="checkStep(index)"
          ></el-step>
        </el-steps>

        <el-form
          :model="leadForm"
          status-icon
          :rules="leadFormRules"
          label-position="top"
          ref="leadForm"
          label-width="120px"
          class="lead-form"
        >
          <!-- lead details -->
          <div class="step-container lead-details" v-if="active == 0">
            <!-- form fields here  -->
            <el-row :gutter="20" class="m-b-lg">
              <h5 class="m-b-lg">Lead Details</h5>
              <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="customer_type">
                <el-form-item
                  label="Lead Type"
                  prop="customer_type"
                  :error="
                    leadForm.errors.errors.customer_type
                      ? leadForm.errors.errors.customer_type[0]
                      : ''
                  "
                >
                  <el-select
                    dusk="customer_type"
                    popper-class="customer_type_popper"
                    v-model="leadForm.customer_type"
                    placeholder="Select Lead Type"
                    @input="leadTypeChange"
                  >
                    <el-option
                      v-for="(leadType, index) in leadTypes"
                      :key="index"
                      :value="index"
                      :label="index"
                      >{{ index }}</el-option
                    >
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="escalation_level">
                <el-form-item
                  label="Level"
                  prop="escalation_level"
                  :error="
                    leadForm.errors.errors.escalation_level
                      ? leadForm.errors.errors.escalation_level[0]
                      : ''
                  "
                >
                  <el-select
                    popper-class="escalation_level_popper"
                    v-model="leadForm.escalation_level"
                    placeholder="Select Level"
                    @change="
                      autoSelectStatus();
                      changeManualUpdate();
                    "
                  >
                    <el-option
                      v-for="(level, index) in leadTypes[
                        leadForm.customer_type
                      ]"
                      :key="index"
                      :value="index"
                      :label="index"
                      >{{ index }}</el-option
                    >
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="escalation_status">
                <el-form-item
                  v-show="!hideStatus()"
                  label="Status"
                  prop="escalation_status"
                  :error="
                    leadForm.errors.errors.escalation_status
                      ? leadForm.errors.errors.escalation_status[0]
                      : ''
                  "
                >
                  <el-select
                    popper-class="escalation_status_popper"
                    v-model="leadForm.escalation_status"
                    placeholder="Select Status"
                    :disabled="leadForm.lead_id == ''"
                    @change="changeManualUpdate"
                  >
                    <span v-if="leadForm.customer_type">
                      <el-option
                        v-for="(status, index) in leadTypes[leadForm.customer_type][leadForm.escalation_level]"
                        :key="index"
                        :value="status"
                        :label="status"
                        >{{ status }}</el-option
                      >
                    </span>
                  </el-select>
                </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="received_via">
                <el-form-item
                  label="Enquiry received?"
                  prop="received_via"
                  :error="
                    leadForm.errors.errors.received_via
                      ? leadForm.errors.errors.received_via[0]
                      : ''
                  "
                >
                  <el-select
                    dusk="received_via"
                    popper-class="received_via_popper"
                    v-model="leadForm.received_via"
                    placeholder="Select"
                  >
                    <el-option
                      v-for="(receivedViaType, index) in receivedViaTypes"
                      :key="index"
                      :label="receivedViaType"
                      :value="receivedViaType"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>


            </el-row>
            <!-- end of form fields -->

            <!-- enquirer fields here  -->
            <el-row :gutter="20">
              <h5 class="m-b-lg">Enquirer Details</h5>

              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-show="leadForm.customer_type == 'General Enquiry'">
                <el-radio
                  v-model="leadForm.inquiry_type"
                  label="sales-enquiry"
                  >Sales Enquiry</el-radio>

                <el-radio
                  v-model="leadForm.inquiry_type"
                  label="general-enquiry"
                  >General Enquiry</el-radio>

                <el-radio
                  v-model="leadForm.inquiry_type"
                  label="feedback"
                  >Feedback</el-radio>
              </el-col>

              <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
                <el-form-item
                  label="First Name"
                  prop="first_name"
                  :error="
                    leadForm.errors.errors.first_name
                      ? leadForm.errors.errors.first_name[0]
                      : ''
                  "
                >
                  <el-input
                    dusk="first_name"
                    type="text"
                    v-debounce:300ms="validateName"
                    v-model="leadForm.first_name"
                  ></el-input>
                </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
                <el-form-item
                  label="Last Name"
                  prop="last_name"
                  :error="
                    leadForm.errors.errors.last_name
                      ? leadForm.errors.errors.last_name[0]
                      : ''
                  "
                >
                  <el-input type="text"
                    dusk="last_name"
                    v-debounce:300ms="validateName"
                    v-model="leadForm.last_name">
                  </el-input>
                </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
                <el-form-item
                  label="Email"
                  prop="email"
                  :error="
                    leadForm.errors.errors.email
                      ? leadForm.errors.errors.email[0]
                      : ''
                  "
                >
                  <el-input type="email" v-model="leadForm.email" dusk="email"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12" :lg="6" :xl="6">
                <el-form-item
                  label="Mobile Number"
                  prop="contact_number"
                  :error="leadForm.errors.errors.contact_number ? leadForm.errors.errors.contact_number[0] : ''"
                >
                  <el-input
                    type="text"
                    id="contact_number"
                    ref="phoneNumber"
                    placeholder="Mobile Number"
                    :value="leadForm.contact_number"
                    v-model="leadForm.contact_number"
                    v-bind:class="{ required: !isRequired }"
                    @input="contactNoChange"
                    @focus="contactNoFocus" />
                  <!-- <vue-tel-input
                    id="contact_number"
                    ref="phoneNumber"
                    placeholder="Mobile Number"
                    :value="leadForm.contact_number"
                    v-model="leadForm.contact_number"
                    style="line-height: 24px; border: 1px solid #dcdfe6"
                    v-bind:class="{ required: !isRequired }"
                    @input="contactNoChange"
                  /> -->
                </el-form-item>
              </el-col>
            </el-row>

            <!-- <el-row :gutter="20" class="d-none">
              <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                <el-form-item label="Address" prop="address_search" :class="{ 'is-error': addressSearchError }">
                  <div class="el-input">
                    <vue-google-autocomplete
                      id="search"
                      v-model="leadForm.address_search"
                      classname="el-input__inner"
                      placeholder="Start typing address..."
                      @change="clearAddressFields()"
                      v-on:placechanged="getAddressData"
                      :country="['au', 'nz']"
                    >
                    </vue-google-autocomplete>
                  </div>
                  <div class="el-form-item__error" v-if="addressSearchError">{{ leadFormRules.address_search[0].message }}</div>
                </el-form-item>
              </el-col>
            </el-row> -->

            <el-row :gutter="20" v-show="leadForm.customer_type != 'General Enquiry'">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item label="Address" prop="address">
                  <el-input dusk="address" type="text" v-model="leadForm.address" v-bind:class="{ required: leadForm.customer_type == 'Supply & Install' }"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <el-form-item label="City" prop="city">
                  <el-input dusk="city" type="text" v-model="leadForm.city" v-bind:class="{ required: leadForm.customer_type == 'Supply & Install' }"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <el-form-item label="Landline Number" prop="landline_number">
                  <el-input dusk="landline_number" type="tel" v-model="leadForm.landline_number" @input="landlineNumber"></el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <el-form-item label="Suburb" prop="suburb">
                  <el-input dusk="suburb" type="text" v-model="leadForm.suburb" v-bind:class="{ required: leadForm.customer_type == 'Supply & Install' }"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <el-form-item
                  label="Postcode"
                  prop="postcode"
                  @change="filterOrgs()"
                >
                  <el-input dusk="postcode" type="text" v-model="leadForm.postcode" v-on:blur="postcodeHandleBlur" v-bind:class="{ required: leadForm.customer_type == 'Supply & Install' }"></el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" class="state">
                <el-form-item
                  label="State"
                  prop="state"
                >
                  <el-select
                    popper-class="state_popper"
                      v-model="leadForm.state"
                      placeholder="Select State"
                      @change="filterOrgs()"
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

              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" v-show="leadForm.customer_type == 'General Enquiry'">
                <el-form-item label="Landline Number" prop="landline_number">
                  <el-input dusk="landline_number" type="tel" v-model="leadForm.landline_number" @input="landlineNumber"></el-input>
                </el-form-item>
              </el-col>

              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" class="d-none">
                <el-form-item label="Country" prop="country">
                  <el-input type="text" v-model="leadForm.country" v-bind:class="{ required: leadForm.customer_type == 'Supply & Install' }"></el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="20" v-if="leadForm.customer_type == 'General Enquiry'">
              <el-col :xs="24" :sm="24" :md="24" :lg="18" :xl="18">
                <el-form-item
                  label="Comments"
                  prop="comments"
                  :error="
                    leadForm.errors.errors.comments
                      ? leadForm.errors.errors.comments[0]
                      : ''
                  "
                >
                  <el-input
                    dusk="general-inquiry-comments"
                    type="textarea"
                    :autosize="{ minRows: 4 }"
                    v-model="leadForm.comments"
                    maxlength="1000"
                    show-word-limit
                  >
                  </el-input>
                </el-form-item>
              </el-col>
            </el-row>
            <!-- enquirer details  -->
          </div>

          <!-- job details -->
          <div class="step-container job-details" v-show="active == 1">
            <!-- form fields here  -->
            <el-row class="mt-5">
              <h5 class="m-b-lg">Job Details</h5>

              <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6" class="house_type">
                <label class="question m-b-md"> Building Type </label>

                <el-radio
                  dusk="house_type_single_storey_dwelling"
                  v-model="leadForm.house_type"
                  @change="changeBuildingType"
                  label="Single Storey dwelling"
                  >Single Storey dwelling</el-radio
                >
                <el-radio
                  dusk="house_type_double_storey_dwelling"
                  v-model="leadForm.house_type"
                  @change="changeBuildingType"
                  label="Double Storey dwelling"
                  >Double Storey dwelling</el-radio
                >
                <el-radio
                  dusk="house_type_townhouses_villas"
                  v-model="leadForm.house_type"
                  @change="changeBuildingType"
                  label="Townhouses/Villas"
                  >Townhouses/Villas</el-radio
                >
                <el-radio
                  dusk="house_type_commercial"
                  v-model="leadForm.house_type"
                  @change="changeBuildingType"
                  label="Commercial"
                  >Commercial</el-radio
                >

                  <el-row class="commercial-group p-md" v-show="leadForm.house_type == 'Commercial'">
                    <el-radio
                      v-model="leadForm.commercial"
                      label="School"
                      >School</el-radio
                    >
                    <el-radio
                      v-model="leadForm.commercial"
                      label="Hospital"
                      >Hospital</el-radio
                    >
                    <el-radio
                      v-model="leadForm.commercial"
                      label="Factory"
                      >Factory</el-radio
                    >
                    <el-radio
                      v-model="leadForm.commercial"
                      label="Office building"
                      >Office building</el-radio
                    >
                    <el-radio
                      v-model="leadForm.commercial"
                      label="Other"
                      >Other</el-radio
                    >

                    <el-input
                      placeholder="Other Commercial"
                      v-if="leadForm.commercial == 'Other'"
                      v-model="leadForm.commercial_other"
                    ></el-input>
                  </el-row>
                <el-radio
                  v-model="leadForm.house_type"
                  @change="changeBuildingType"
                  label="Carport/Pergola/Shed"
                  >Carport/Pergola/Shed</el-radio
                >
                <el-radio v-model="leadForm.house_type" label="Other"
                  >Other</el-radio
                >

                <el-input
                  placeholder="Other Building Type"
                  v-if="leadForm.house_type == 'Other'"
                  v-model="leadForm.house_type_other"
                ></el-input>
              </el-col>
              <el-col :xs="24" :sm="12" :md="4" :lg="4" :xl="4">
                <label class="question m-b-md"> Roof Type </label>

                <el-radio
                  dusk="roof_preference_tile"
                  v-model="leadForm.roof_preference"
                  label="Tile"
                  >Tile
                </el-radio>

                <el-radio
                  dusk="roof_preference_metal"
                  v-model="leadForm.roof_preference"
                  label="Metal"
                  v-show="leadForm.customer_type == 'Supply & Install'"
                  >Metal</el-radio
                >
                <el-radio
                  dusk="roof_preference_tile_metal"
                  v-model="leadForm.roof_preference"
                  label="Tile & Metal"
                  v-show="leadForm.customer_type == 'Supply & Install'"
                  >Tile & Metal</el-radio
                >
                <el-radio
                  dusk="roof_preference_metal_corrugated"
                  v-model="leadForm.roof_preference"
                  label="Metal Corrugated"
                  v-show="leadForm.customer_type == 'Supply Only'"
                  >Metal Corrugated</el-radio
                >
                <el-radio
                  dusk="roof_preference_metal_other"
                  v-model="leadForm.roof_preference"
                  label="Metal Other"
                  v-show="leadForm.customer_type == 'Supply Only'"
                  >Metal Other</el-radio
                >

                <el-radio v-model="leadForm.roof_preference" label="Other"
                  >Other</el-radio
                >

                <el-input
                  placeholder="Other Roof Type"
                  v-if="leadForm.roof_preference == 'Other'"
                  v-model="leadForm.roof_preference_other"
                ></el-input>
              </el-col>

              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                <label class="question m-b-md"> Enquirer Position </label>

                <div v-show="leadForm.customer_type == 'Supply & Install'" class="enquirer-position supply-install">
                  <el-radio
                    dusk="use_for_i_am_a_homeowner"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am a homeowner"
                    >I am a homeowner</el-radio
                  >
                  <el-radio
                    dusk="use_for_i_am_a_builder"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am a builder"
                    >I am a builder</el-radio
                  >
                  <el-radio
                    dusk="use_for_i_am_a_tradesperson"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am a tradesperson"
                    >I am a tradesperson</el-radio
                  >
                </div>

                <div v-show="leadForm.customer_type == 'Supply Only'" class="enquirer-position supply-only">
                  <el-radio
                    dusk="use_for_i_am_a_tradesperson_builder"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am a tradesperson/builder - this is a once off project."
                    >I am a tradesperson/builder - this is a once off project.</el-radio
                  >

                  <el-radio
                    dusk="use_for_i_am_a_tradesperson_builder_ongoing"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am a tradesperson/builder - this could be ongoing for me/us."
                    >I am a tradesperson/builder - this could be ongoing for me/us.</el-radio
                  >

                  <el-radio
                    dusk="use_for_i_am_a_trdae_own_project"
                    v-model="leadForm.use_for"
                    @change="changeEnquirerPosition"
                    label="I am not in the trade - this is my own project."
                    >I am not in the trade - this is my own project.</el-radio
                  >
                </div>

                <el-radio
                  v-model="leadForm.use_for"
                  @change="changeEnquirerPosition"
                  label="Other"
                  >Other</el-radio
                >

                <el-input
                  placeholder="Other Enquirer Position"
                  v-if="leadForm.use_for == 'Other'"
                  v-model="leadForm.use_for_other"
                ></el-input>
              </el-col>
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <label class="question m-b-md"> Estimations </label>

                <el-row :gutter="20">
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item
                      label="Metres of Gutter Edge"
                      prop="gutter_edge_meters"
                      :error="
                        leadForm.errors.errors.gutter_edge_meters
                          ? leadForm.errors.errors.gutter_edge_meters[0]
                          : ''
                      "
                    >
                      <el-input
                        dusk="gutter_edge_meters"
                        type="number"
                        v-model="leadForm.gutter_edge_meters"
                      ></el-input>
                    </el-form-item>
                  </el-col>

                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item
                      label="Metres of Valley"
                      prop="valley_meters"
                      :error="
                        leadForm.errors.errors.valley_meters
                          ? leadForm.errors.errors.valley_meters[0]
                          : ''
                      "
                    >
                      <el-input
                        dusk="valley_meters"
                        type="number"
                        v-model="leadForm.valley_meters"
                      ></el-input>
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
            <el-row :gutter="20" class="m-t-lg"
              ><el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                  <el-row>
                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                      <label class="question m-b-md"> Marketing Channel </label>
                    </el-col>

                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                      <el-radio
                        dusk="source_searched_on_the_internet"
                        v-model="leadForm.source"
                        label="Searched on the internet"
                      >
                        Searched on the internet</el-radio
                      >
                      <el-radio
                        dusk="source_flyer_from_a_store"
                        v-model="leadForm.source"
                        label="Flyer - From A Store"
                      >
                        Flyer - From A Store</el-radio
                      >
                      <el-radio
                        dusk="source_flyer_in_a_letter_box"
                        v-model="leadForm.source"
                        label="Flyer - In A Letter Box"
                      >
                        Flyer - In A Letter Box</el-radio
                      >
                      <el-radio
                        dusk="source_flyer_other"
                        v-model="leadForm.source"
                        label="Flyer - Other"
                      >
                        Flyer - Other
                      </el-radio>

                      <el-radio
                        dusk="source_newspaper"
                        v-model="leadForm.source"
                        label="Newspaper"
                      >
                        Newspaper
                      </el-radio>

                      <el-radio
                        dusk="source_radio"
                        v-model="leadForm.source"
                        label="Radio"
                      >
                        Radio
                      </el-radio>

                      <el-radio
                        dusk="source_referred_by_someone"
                        v-model="leadForm.source"
                        label="Referred By Someone"
                      >
                        Referred By Someone
                      </el-radio>

                      <el-radio
                        dusk="source_television"
                        v-model="leadForm.source"
                        label="Television"
                      >
                        Television
                      </el-radio>

                    </el-col>
                    <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                      <el-radio
                        dusk="source_vehicle_sign"
                        v-model="leadForm.source"
                        label="Vehicle Sign"
                      >
                        Vehicle Sign
                      </el-radio>

                      <el-radio
                        dusk="source_billboard"
                        v-model="leadForm.source"
                        label="Billboard"
                      >
                        Billboard
                      </el-radio>

                      <el-radio
                        dusk="source_facebook"
                        v-model="leadForm.source"
                        label="Facebook"
                      >
                        Facebook
                      </el-radio>

                      <el-radio
                        dusk="source_instagram"
                        v-model="leadForm.source"
                        label="Instagram"
                      >
                        Instagram
                      </el-radio>

                      <el-radio
                        dusk="source_sign_on_a_building"
                        v-model="leadForm.source"
                        label="Sign on a building"
                      >
                        Sign on a building
                      </el-radio>

                      <el-radio
                        dusk="source_other"
                        v-model="leadForm.source"
                        label="Other"
                      >
                        Other
                      </el-radio>
                    </el-col>

                    <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                      <el-input
                        v-if="
                          leadForm.source == 'Other' ||
                          leadForm.source == 'Flyer - Other'
                        "
                        class="comments"
                        type="text"
                        :autosize="{ minRows: 4 }"
                        placeholder="Other Marketing Channel"
                        v-model="leadForm.source_comments"
                      >
                      </el-input>
                    </el-col>
                  </el-row>
                </el-col>
                <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                  <label class="question m-b-md">
                    Enquirer Additional Information
                  </label>
                  <el-form-item prop="additional_information">
                    <el-input
                      dusk="additional_information"
                      type="textarea"
                      :autosize="{ minRows: 4 }"
                      placeholder="Enter additionial information"
                      v-model="leadForm.additional_information"
                      maxlength="1000"
                      show-word-limit
                    >
                    </el-input>
                  </el-form-item>
                </el-col>
              </el-row>
            </el-row>
          </div>

          <!-- internal details  remove -->
          <div class="step-container internal-details" v-show="active == 1111111">
            <!-- form fields here  -->

            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" class="received_via">
                <el-form-item
                  label="Enquiry received?"
                  prop="received_via"
                  :error="
                    leadForm.errors.errors.received_via
                      ? leadForm.errors.errors.received_via[0]
                      : ''
                  "
                >
                  <el-select
                    dusk="received_via"
                    popper-class="received_via_popper"
                    v-model="leadForm.received_via"
                    placeholder="Select"
                  >
                    <el-option
                      v-for="(receivedViaType, index) in receivedViaTypes"
                      :key="index"
                      :label="receivedViaType"
                      :value="receivedViaType"
                    >
                    </el-option>
                  </el-select>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item
                  label="Staff Comments"
                  prop="staff_comments"
                  :error="
                    leadForm.errors.errors.staff_comments
                      ? leadForm.errors.errors.staff_comments[0]
                      : ''
                  "
                >
                  <el-input
                    dusk="staff_comments"
                    type="textarea"
                    :autosize="{ minRows: 4 }"
                    v-model="leadForm.staff_comments"
                    maxlength="1000"
                    show-word-limit
                  >
                  </el-input>
                </el-form-item>
              </el-col>
            </el-row>

            <el-row :gutter="20" v-show="leadForm.customer_type == 'Supply & Install' && leadForm.lead_id == '' ">
              <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                <el-form-item
                  label="Message for Enquirer"
                  prop="enquirer_message"
                  :error="
                    leadForm.errors.errors.enquirer_message
                      ? leadForm.errors.errors.enquirer_message[0]
                      : ''
                  "
                >
                  <el-input
                    type="textarea"
                    :autosize="{ minRows: 4 }"
                    v-model="leadForm.enquirer_message"
                    maxlength="160"
                    show-word-limit
                  >
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="4" :lg="4" :xl="4">
                <label for="" class="d-block m-b-md">&nbsp;</label>
                <el-checkbox
                  v-model="leadForm.notify_enquirer"
                  :checked="leadForm.notify_enquirer ? 'checked' : ''"
                  >Send Message if Assigned</el-checkbox
                >
              </el-col>
            </el-row>
          </div>

          <!-- assign lead  -->
          <div class="step-container assign-lead" v-show="active == 2">
            <!-- form fields here  -->
            <el-row>
              <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <el-tabs
                  v-model="activeTab"
                  type="card"
                  @tab-click="handleTabClick"
                >
                  <el-tab-pane label="Organisation" name="organisation">
                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6"
                        v-if="( leadForm.originalData.organisation_id == '' && leadForm.organisation_id === null ) || ( leadForm.originalData.organisation_id == '' && leadForm.organisation_id == '' )"
                        class="organisation_filter">

                        <el-form-item
                          label="Filter Type"
                          prop="metres_gutter_edge"
                        >
                          <el-select
                            dusk="organisation_filter"
                            popper-class="organisation_filter_popper"
                            v-model="filterType"
                            placeholder="Select filter type"
                            @change="filterOrgs()"
                            :blur="postcodeHandleBlur()"
                          >
                            <el-option
                              v-for="(fType, index) in filterTypes"
                              :key="index"
                              :label="fType"
                              :value="fType"
                            >
                            </el-option>
                          </el-select>
                        </el-form-item>
                        <el-form-item
                          label="Organisation"
                          prop="organisation_id"
                          :error="
                            leadForm.errors.errors.organisation_id
                              ? leadForm.errors.errors.organisation_id[0]
                              : ''
                          "
                          :disabled="filterType !== ''"
                          class="organisation"
                        >

                          <el-select v-model="organisation_id_temp"
                            filterable
                            remote
                            clearable
                            placeholder="Select or Search"
                            popper-class="select-org-search"
                            id="org-select">
                              <el-option
                                v-for="organisation in filteredOrgs"
                                :key="organisation.id"
                                :label="organisation.name"
                                :value="organisation.id"
                                v-show="showOrganisation(organisation)"
                              >
                                <span>
                                  {{ organisation.name }}
                                  <ManualIcon :org="organisation" />
                                  <MainPriorityIcon :priority="organisation.priority" :tooltip="organisation.priority" :displayOnly="true" />
                                  <!-- <fa icon="balance-scale" @click="compare( organisation )" /> -->
                                </span>
                                <span
                                    v-show="organisation.account_status_type_selection.length > 0"
                                    class="on-hold"
                                    >{{ organisation.account_status_type_selection }}</span>
                              </el-option>
                            </el-select>
                        </el-form-item>
                      </el-col>
                      <template v-if="orgDetails.name">
                        <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6">
                          <div class="org-details p-l-lg">
                          <!-- <div class="org-details p-l-lg"> -->
                            <p class="m-b-sm">{{ orgDetails.name }}</p>
                            <p class="m-b-sm">{{ orgDetails.code }}</p>
                            <p class="m-b-sm">{{ orgDetails.email }}</p>
                            <p class="m-b-sm">{{ orgDetails.number }}</p>
                            <el-button
                              type="primary"
                              @click="organisationStat"
                              plain
                              >View Stats
                              <img src="/app-assets/img/ico/eye.svg" />
                            </el-button>
                          </div>
                        </el-col>
                      </template>
                      <template v-else>
                        <!-- <template v-if="compares.length > 0">
                          <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" v-for="id in compares" :key="'compaire-' + id">
                            <OrgStats :org_id="id" :key="'compaire-' + id" />
                          </el-col>
                        </template> -->
                        <el-col :xs="24" :sm="24" :md="18" :lg="18" :xl="18" v-if="isShowOrgStatsTable">
                          <OrgStatsTable @getOrgId="setOrgId" />
                        </el-col>
                      </template>
                    </el-row>
                  </el-tab-pane>
                  <!-- <el-tab-pane
                    label="Org. Locator"
                    name="orglocator"
                    v-if="!leadForm.id"
                  > -->
                  <!--
                  <el-tab-pane
                    label="Org. Locator"
                    name="orglocator"
                    v-if="leadForm.organisation_id == ''" >
                    -->
                  <el-tab-pane
                    label="Org. Locator"
                    name="orglocator"
                  >

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="Search"
                          prop="keyword"
                        >
                          <el-input
                            type="text"
                            v-model="orgLocatorForm.keyword"
                            clearable
                          ></el-input>
                        </el-form-item>
                      </el-col>

                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="Postcode or Suburb"
                          prop="org_postcode"
                          :error="
                            orgLocatorForm.errors.errors.org_postcode
                              ? orgLocatorForm.errors.errors.org_postcode[0]
                              : ''
                          "
                        >
                          <el-input
                            type="text"
                            v-model="orgLocatorForm.org_postcode"
                            clearable
                          ></el-input>
                        </el-form-item>
                      </el-col>

                    </el-row>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="YTD Sales greater than $"
                          prop="locator_ytd_sales_greater_than"
                          :error="
                            orgLocatorForm.errors.errors
                              .locator_ytd_sales_greater_than
                              ? orgLocatorForm.errors.errors
                                  .locator_ytd_sales_greater_than[0]
                              : ''
                          "
                        >
                          <el-input
                            type="text"
                            v-model="
                              orgLocatorForm.locator_ytd_sales_greater_than
                            "
                            clearable
                          ></el-input>
                        </el-form-item>
                      </el-col>
                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="LY Sales greater than $"
                          prop="locator_ly_sales_greater_than"
                          :error="
                            leadForm.errors.errors.locator_ly_sales_greater_than
                              ? leadForm.errors.errors
                                  .locator_ly_sales_greater_than[0]
                              : ''
                          "
                        >
                          <el-input
                            type="text"
                            v-model="
                              orgLocatorForm.locator_ly_sales_greater_than
                            "
                            clearable
                          ></el-input>
                        </el-form-item>
                      </el-col>
                    </el-row>
                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="Priority"
                          prop="locator_priority"
                          :error="
                            orgLocatorForm.errors.errors.locator_priority
                              ? orgLocatorForm.errors.errors.locator_priority[0]
                              : ''
                          "
                        >
                          <el-input
                            type="text"
                            v-model="orgLocatorForm.locator_priority"
                            clearable
                          ></el-input>
                        </el-form-item>
                      </el-col>

                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-form-item
                          label="State"
                          prop="locator_state"
                          :error="
                            leadForm.errors.errors.locator_state
                              ? leadForm.errors.errors.locator_state[0]
                              : ''
                          "
                        >
                          <el-select
                            popper-class="state_popper"
                              v-model="orgLocatorForm.locator_state"
                              placeholder="Select State"
                              clearable
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
                      <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                        <el-button type="primary" @click="filterOrgLocator"
                          >Filter</el-button
                        >
                      </el-col>
                    </el-row>

                    <el-row :gutter="20">
                      <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <data-tables-server
                          :data="orglocators"
                          :total="orglocator_total"
                          :loading="orglocator_loading"
                          :pagination-props="{ pageSizes: [5, 10, 20] }"
                          @query-change="loadMore"
                        >
                          <el-table-column
                            prop="org_id"
                            label="Org. ID"
                            width="80"
                          >
                          </el-table-column>

                          <el-table-column prop="name" label="Name">
                          </el-table-column>

                          <el-table-column
                            prop="street_address"
                            label="Street Address"
                          >
                          </el-table-column>

                          <el-table-column prop="suburb" label="Suburb">
                          </el-table-column>

                          <el-table-column
                            prop="postcode"
                            label="Postcode"
                            align="center"
                          >
                          </el-table-column>

                          <el-table-column
                            prop="state"
                            label="State"
                            align="center"
                          >
                          </el-table-column>

                          <el-table-column prop="phone" label="Phone" width="140">
                          </el-table-column>

                          <el-table-column
                            prop="last_year_sales"
                            label="Last Year Sales"
                            align="right"
                          >
                          </el-table-column>

                          <el-table-column
                            prop="year_to_date_sales"
                            label="Year to Date Sales"
                            align="right"
                          >
                          </el-table-column>

                          <el-table-column
                            prop="pricing_book"
                            label="Pricing Book"
                            align="center"
                          >
                          </el-table-column>

                          <el-table-column
                            prop="priority"
                            label="Priority"
                            align="center"
                          >
                          </el-table-column>
                        </data-tables-server>
                      </el-col>
                    </el-row>
                  </el-tab-pane>

                </el-tabs>
              </el-col>
            </el-row>
          </div>
        </el-form>
      </el-card>
      <el-dialog
        :visible="showOutgoingNotifications"
        v-dialogDrag
        ref="dialog__wrapper"
        :show-close="false"
        append-to-body
        width="30%"
      >
        <OutgoingNotificationModal
          :form="leadForm"
          :org_data="getOrgDetails('getData')"
          :loading="loading"
          @saveLead="saveLead('leadForm')"
          :key="JSON.stringify(getOrgDetails('getData'))"
        />
      </el-dialog>

      <template>
        <el-drawer
          :visible.sync="showOrganisationProfile"
          :with-header="false"
          size="80%"
          :destroy-on-close="true"
          :before-close="closeOrganisationProfile"
          :append-to-body="true"
        >
        <OrganisationProfile v-bind:org-id="orgId" v-on:closeOrganisationProfile="closeOrganisationProfile" />

        </el-drawer>
      </template>

      <template v-if="historyDrawer && user.user_role.name == 'administrator'">
        <el-drawer
        :visible.sync="historyDrawer"
        :with-header="false"
        :destroy-on-close="true"
        size="60%"
        :append-to-body="true"
        :before-close="handleAdminDrawerClose"
        >
          <DrawerAdmin :id="historyDrawerId" :nested="nestedDrawer" />
        </el-drawer>
      </template>

      <template>
        <el-dialog v-dialogDrag ref="dialog__wrapper" width="30%" title="Add Organisation Comment" :visible.sync="addOrgCommentDialogVisible"
        :show-close="false"
        append-to-body>
          <el-form :model="orgCommentForm" status-icon :rules="orgCommentRules" label-position="top"  ref="orgCommentForm" label-width="120px">
            <el-row :gutter="20">
              <el-col :span="24">
                <el-form-item label="Comment" prop="comment" :error="orgCommentForm.errors.errors.reason ? orgCommentForm.errors.errors.reason[0] : ''">
                  <el-input type="textarea" :autosize="{ minRows: 4 }" placeholder="Enter your comment..." v-model="orgCommentForm.comment" maxlength="500" show-word-limit></el-input>
                </el-form-item>
              </el-col>

              <el-col :span="24">
                <el-form-item class="fl-right">
                  <el-button type="primary" :loading="loading" @click="addComment('orgCommentForm')">Submit</el-button>
                  <el-button type="danger" @click="addCommentClose()">Cancel</el-button>
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
        </el-dialog>
      </template>

    </template>
  </Section>
</template>

<script>
import { mapGetters } from "vuex";
import { DataTables, DataTablesServer } from "vue-data-tables";
import { VueTelInput } from "vue-tel-input";
import ReassignForm from "./reassign";
import Swal from "sweetalert2";
import Section from "~/components/Section";
import ManualIcon from "~/components/ManualIcon.vue";
import MainPriorityIcon from "~/components/priorities/Main.vue";
import VueGoogleAutocomplete from "vue-google-autocomplete";
import Cookies from 'js-cookie'
import { isManualUpdateEnabled } from '~/helpers'
// import OrgStats from '~/components/OrgStats'
import OrgStatsTable from '~/components/OrgStatsTable'
import OrganisationProfile from "~/components/OrganisationProfile"
import { Bus } from '~/app'
import OutgoingNotificationModal from '~/components/notifications/OutgoingNotificationModal'
import DrawerAdmin from "~/components/DrawerAdmin"

export default {
  name: "LeadEnquiryForm",
  layout: "master",
  middleware: ["auth"],
  components: {
    Section,
    DataTablesServer,
    VueTelInput,
    ReassignForm,
    VueGoogleAutocomplete,
    ManualIcon,
    OrgStatsTable,
    MainPriorityIcon,
    OutgoingNotificationModal,
    OrganisationProfile,
    DrawerAdmin
    // OrgStats
  },
  data: () => ({
    pageTitle: "New Enquiry",
    active: 0,
    activeTab: "organisation",
    filterTypes: ["None", "Postcode", "State"],
    filterType: "State",
    filteredOrgs: [],
    orgDetails: {
      name: "",
      email: "",
      code: "",
      number: "",
    },
    query: {},
    isRequired: true,
    isPhoneNumberValidate: true,
    formatNumber: "",
    contactNoIsValid: true,
    contactNoLengthValid: true,
    submitText: "Create Lead",
    warningNotification: null,
    infoNotification: null,
    steps: [
      { title: "Step 1" },
      // { title: "Step 2" },
      // { title: "Step 3" },
      // { title: "Step 4" },
    ],
    lastStep: 2,
    addressSearchError: false,
    getAddressDataStatus: false,
    organisation_id_temp: "",
    rawAddressData: {},
    new_lead_id: null,
    isTouched: false,
    isTouchedCounter: 0,

    compares: [],
    org_ids_compares: [],
    isShowOrgStatsTable: false,
    showOrganisationProfile: false,
    orgId: 0,
  }),

  computed: mapGetters({
    user: "auth/user",
    leadForm: "leads/leadForm",
    organisations: "leads/organisations",
    postcodes: "organisations/postcodes",
    leadFormRules: "leads/leadFormRules",
    leadTypes: "leads/leadTypes",
    receivedViaTypes: "leads/receivedViaTypes",
    orgLocatorForm: "leads/orgLocatorForm",
    countries: "organisations/countries",
    loading: "leads/loading",
    orglocators: "orglocators/orglocators",
    orglocator_total: "orglocators/total",
    orglocator_loading: "orglocators/loading",
    reasons: "leadhistory/reasons",
    reassignForm: "leadhistory/reassignForm",
    reassignFormRules: "leadhistory/reassignFormRules",
    reassignLoading: "leadhistory/loading",
    stepFields: "leads/stepFields",
    filter_organizations: "organisations/filter_organizations",
    statesList: "leads/statesList",
    showOutgoingNotifications: "notifications/showOutgoingNotifications",
    historyDrawer: 'leadhistory/historyDrawer',
    historyDrawerId: 'leadhistory/historyDrawerId',
    nestedDrawer: 'leadhistory/nestedDrawer',
    addOrgCommentDialogVisible: "organisations/addOrgCommentDialogVisible",
    orgCommentForm: 'organisations/orgCommentForm',
    orgCommentRules: 'organisations/orgCommentRules',
  }),

  methods: {
    addComment(formName) {
			this.$refs[formName].validate((valid) => {
				if(valid){
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

    addCommentClose() {
			this.$store.dispatch("organisations/setDialog", { close: false, form: "add_organisation_comment" });
		},

    handleAdminDrawerClose() {
      this.$store.dispatch("leadhistory/closeLeadOverview").then(_ => {}).catch(_ => {})
    },



    setOrgId(org_id){
      this.orgId = org_id

      this.$store.dispatch('organisations/getOrganisation', { id: org_id, load: true }).then(({ success, message, errors }) => {
        if (success) {
          this.updateAllDataInOrganisationProfile(org_id)

          this.showOrganisationProfile = true
        }
      })
    },

    updateAllDataInOrganisationProfile(org_id){
      let queryInfo = {'pageNo': 1, 'pageSize': 10, 'orgId' : org_id}

      this.$store.dispatch("organisations/fetchCurrentLeads", org_id)

      this.$store.dispatch("organisations/fetchReassignedLeads", org_id)

      this.$store.dispatch("organisations/fetchOrgComments", {
        org_id,
        queryInfo
      })

      this.$store.dispatch("orghistory/fetchOrgNotificationHistory", {
				id: org_id,
				queryInfo: queryInfo
			})
      this.$store.dispatch("organisations/fetchOrgPostcodes", org_id)

      this.$store.dispatch("organisations/editOrganisation", org_id)
    },

    closeOrganisationProfile(){
      this.showOrganisationProfile = false
      this.$store.dispatch("leadhistory/closeLeadOverview")
    },

    checkStep(index){
      if(index < this.active){
        this.active = index
      }
      else{
        this.addressChange();
        var step = this.stepFields.find((step) => step.step_no === this.active);
        var messages = "";

        if(step != undefined){
          for (let field of step.fieldsRequired) {
          this.$refs["leadForm"].validateField(field, (v) => {
            messages += v ? v : "";
          });
        }

        this.active = messages || this.addressSearchError || !this.contactNoIsValid
          ? this.active
          : this.active = index;
        }
      }

      if ( this.active == 3 ) {
        Bus.$emit( 'reload-org-stat', this.filter_organizations )
      }
    },
    escalationPage(){
      // this.$router.push({ name: "admin.leads.history", params: {id: this.leadForm.lead_id}})
      Cookies.set( 'lead_id', this.leadForm.lead_id )
      this.$router.push({ name: 'dashboard' })
    },

    changeBuildingType(){
      const opportunities = ["Commercial", "Townhouses/Villas"];

      this.infoNotification ? this.infoNotification.close() : "";

      if (opportunities.includes(this.leadForm.house_type)) {
        this.infoNotification = this.$notify.info({
          customClass: "info",
          title: "Info",
          message:
            "The choice you made could be a special opportunity. See Sales Admin before assigning this lead.",
          duration: 10000,
        });
      }
    },

    changeEnquirerPosition() {
      const opportunities = ["I am a builder", "I am a tradesperson"];

      this.infoNotification ? this.infoNotification.close() : "";

      if (opportunities.includes(this.leadForm.use_for)) {
        this.infoNotification = this.$notify.info({
          customClass: "info",
          title: "Info",
          message:
            "The choice you made could be a special opportunity. See Sales Admin before assigning this lead.",
          duration: 10000,
        });
      }
    },

    changeManualUpdate() {
      this.leadForm.comments ="";
      if ( this.leadForm.customer_type == 'Supply & Install' && this.leadForm.id ) {
        this.warningNotification ? this.warningNotification.close() : "";

        this.warningNotification = this.$notify.warning({
          customClass: "warning",
          title: "Warning",
          message:
            "Manually updating a leads Level / Status may confuse the assigned Organisation if they are not expecting the change. Notifications may be triggered based on the Level / Status change. Please proceed with care.",
          duration: 10000,
        });
      }
    },

    validateName() {
      if ( this.leadForm.first_name && this.leadForm.last_name ) {
          // dispatch validate name
          this.$store
            .dispatch("leads/validateName")
            .then(({ success, data, errors }) => {
              if (success) {
                this.warningNotification ? this.warningNotification.close() : "";
                if (data.is_existing) {
                  this.warningNotification = this.$notify.warning({
                    customClass: "warning",
                    title: "Warning",
                    message:
                      "A lead already exists with the same Enquirer First/Last name. Please check that this is not a duplicate entry.",
                    duration: 10000,
                  });
                }
              }else {
                Swal.fire({
                  title: "Oops!",
                  html: errors.error,
                  type: "error",
                });
              }
            });

      }
    },

    getAddressData(addressData, placeResultData, id) {
      this.getAddressDataStatus = true

      this.leadForm.address_search = placeResultData.formatted_address
        ? placeResultData.formatted_address
        : "";

      this.leadForm.address = `${
        addressData.street_number ? addressData.street_number : ""
      } ${addressData.route ? addressData.route : ""}`;

      this.leadForm.streetNo = addressData.street_number ? addressData.street_number : "";
      this.leadForm.streetName = addressData.route ? addressData.route : "";
      this.leadForm.suburb = addressData.locality ? addressData.locality : "";
      this.leadForm.city = addressData.administrative_area_level_2 ? addressData.administrative_area_level_2 : "";
      this.leadForm.state = this.getState(addressData, placeResultData);
      this.leadForm.postcode = addressData.postal_code
        ? addressData.postal_code
        : "";
      this.leadForm.country = addressData.country ? addressData.country : "";
      this.rawAddressData = placeResultData;

      this.addressChange()
    },

    clearAddressFields() {
      if ( !this.getAddressDataStatus ) {
        this.leadForm.address = ''
        this.leadForm.suburb = ''
        this.leadForm.city = ''
        this.leadForm.state = ''
        this.leadForm.postcode = ''
        this.leadForm.country = ''

      }

      this.getAddressDataStatus = false
    },

    getState(addressData, placeResultData) {
      const state = placeResultData.address_components.find(
        (address) =>
          address.short_name == addressData.administrative_area_level_1
      );

      return state ? state.long_name : "";
    },

    leadTypeChange(value) {
      let count = 3;
      this.steps = [];
      this.lastStep = 2;
      this.leadForm.comments = "";
      this.leadForm.house_type = "";
      this.leadForm.commercial = "";
      this.leadForm.house_type_other = "";
      this.leadForm.roof_preference = "";
      this.leadForm.roof_preference_other = "";
      this.leadForm.use_for = "";
      this.leadForm.source = "";
      this.leadForm.source_comments = "";
      this.leadForm.additional_information = "";
      this.leadForm.gutter_edge_meters = "";
      this.leadForm.valley_meters = "";

      if (value == "Supply Only") {
        count = 2;
        this.lastStep = 1;
        this.leadForm.escalation_level = "Supply Only";
        this.leadForm.escalation_status = "New";

      }
      else if(value == "General Enquiry"){
        count = 1;
        this.lastStep = 0;
        this.leadForm.escalation_level = "General Enquiry";
        this.leadForm.escalation_status = "New";
        this.leadForm.received_via = 'Web Form'
      }
      else {
        this.leadForm.escalation_level = "Unassigned";
        this.leadForm.escalation_status = "";
      }

      this.updateRequired()

      for (var i = 1; i <= count; i++) {
        this.steps.push({ title: "Step " + i });
      }
    },

    updateRequired(){
      const step = this.stepFields.find((step) => step.step_no === this.active)

      this.leadFormRules.last_name[0].required = true
      this.leadFormRules.suburb[0].required = true
      this.leadFormRules.state[0].required = true
      this.leadFormRules.postcode[0].required = true
      this.leadFormRules.comments[0].required = false

      let addrParts = ['suburb' ,'state', 'postcode']

      // Initialize required address parts
      for(let addrPart of addrParts) {
        if(step.fieldsRequired.indexOf(addrPart) !== -1) {
          index = step.fieldsRequired.indexOf(addrPart);

          step.fieldsRequired.push(addrPart);
        }
      }

      if(this.leadForm.customer_type == 'Supply & Install'){
        var index = step.fieldsRequired.indexOf('email');

        if (index > -1) {
          step.fieldsRequired.splice(index, 1);
          this.leadFormRules.email[0].required = false
        }

        if(step.fieldsRequired.indexOf("contact_number") === -1){
          step.fieldsRequired.push("contact_number");
          this.leadFormRules.contact_number[0].required = true
        }
      }
      else if(this.leadForm.customer_type == 'Supply Only'){
        var index = step.fieldsRequired.indexOf('contact_number');

        this.leadFormRules.suburb[0].required = false
        this.leadFormRules.state[0].required = false
        this.leadFormRules.postcode[0].required = false
        if (index > -1) {
          step.fieldsRequired.splice(index, 1);
          this.leadFormRules.contact_number[0].required = false
        }

        // remove required address parts if lead type is supply only

        for(let part of addrParts) {
          if(step.fieldsRequired.indexOf(part) !== -1) {
            index = step.fieldsRequired.indexOf(part);

            step.fieldsRequired.splice(index, 1);
          }
        }

        if(step.fieldsRequired.indexOf("email") === -1){
          step.fieldsRequired.push("email");
          this.leadFormRules.email[0].required = true
        }
      }
      else if(this.leadForm.customer_type == 'General Enquiry'){
        var index = step.fieldsRequired.indexOf('contact_number');

        this.leadFormRules.last_name[0].required = false
        this.leadFormRules.suburb[0].required = true
        this.leadFormRules.state[0].required = false
        this.leadFormRules.postcode[0].required = true
        this.leadFormRules.comments[0].required = false

        if (index > -1) {
          step.fieldsRequired.splice(index, 1);
          this.leadFormRules.contact_number[0].required = false
        }

        // remove required address parts if lead type is supply only

        for(let part of addrParts) {
          if(step.fieldsRequired.indexOf(part) !== -1) {
            index = step.fieldsRequired.indexOf(part);

            step.fieldsRequired.splice(index, 1);
          }
        }

        if(step.fieldsRequired.indexOf("email") === -1){
          step.fieldsRequired.push("email");
          this.leadFormRules.email[0].required = true
        }
      }
    },

    setUpSteps(value){
      let count = 3;
      this.steps = [];
      this.lastStep = 2;

      if (value == "Supply Only") {
        count = 2;
        this.lastStep = 1;
      }else if(value == "General Enquiry"){
        count = 1;
        this.lastStep = 0;
      }

      for (var i = 1; i <= count; i++) {
        this.steps.push({ title: "Step " + i });
      }
    },

    contactNoFocus() {
      if(this.leadForm.contact_number == ''){
        this.leadForm.contact_number = '+61'
      }
    },

    removeCharAt( str, char_pos ) {
      let part1 = str.substring( 0, char_pos )
      let part2 = str.substring( char_pos + 1, str.length )
      return ( part1 + part2 )
    },

    contactNoChange() {
      let number = this.leadForm.contact_number
      let fourth = number.charAt( 3 )

      if ( Number.isInteger( parseInt( fourth ) ) && parseInt( fourth ) == 0 ) {
        number = this.removeCharAt( number, 3 )
        this.leadForm.contact_number = number
      }

      /* if ( ! number.includes( prefix ) ){
        this.leadForm.contact_number =  prefix + number
      }

      if ( number.includes( prefix_with_zero ) ) {
        this.leadForm.contact_number = number.replace( prefix_with_zero, '+61' )
      }

      /* if ( this.leadForm.contact_number == '+61+6' ) {
        this.leadForm.contact_number = prefix
      } */

      if ( ! this.leadFormRules.contact_number[0].required && number.length == 0 ) {
        this.contactNoIsValid = true;
        this.formatNumber = "";
        this.leadForm.contact_number = "";
        this.isRequired = false;
        this.isPhoneNumberValidate = false;
        this.$refs['leadForm'].fields.find((f) => f.prop == "contact_number").resetField()
        return
      }

      /* if (isValid.isValid) {
        this.contactNoIsValid = isValid.isValid
        this.formatNumber = isValid.number.e164;
        this.isRequired = true;
        this.isPhoneNumberValidate = true;
        this.$refs["leadForm"].validateField('contact_number');
      } else {
        this.contactNoIsValid = false
        this.formatNumber = "";
        this.isRequired = false;
        this.isPhoneNumberValidate = false;
      } */
    },

    filterOrgLocator() {
      this.query.postcode = this.orgLocatorForm.org_postcode;
      this.query.kilometer = this.orgLocatorForm.locator_kilometer_from_postcode;
      this.query.ytd_sale = this.orgLocatorForm.locator_ytd_sales_greater_than;
      this.query.ly_sale = this.orgLocatorForm.locator_ly_sales_greater_than;
      this.query.priority = this.orgLocatorForm.locator_priority;
      this.query.state = this.orgLocatorForm.locator_state;
      this.query.keyword = this.orgLocatorForm.keyword;

      this.$store.dispatch("orglocators/fetchSearchOrgLocators", this.query);
    },

    organisationStat() {
      const org_id = this.leadForm.organisation_id
        ? this.leadForm.organisation_id
        : this.organisation_id_temp;
      if (org_id != 0) {
        let route = this.$router.resolve({
          path: "/admin/organization/" + org_id + "/stats",
        });
        window.open(route.href, "_blank");
      }
    },

    next(formName) {
      this.addressChange()
      const step = this.stepFields.find((step) => step.step_no === this.active);
      var messages = "";

      for (let field of step.fieldsRequired) {
        this.$refs[formName].validateField(field, (v) => {
          messages += v ? v : "";
        });
      }

      this.active = messages || this.addressSearchError || !this.contactNoIsValid
        ? this.active
        : this.active + (this.active < 4 ? 1 : 0);

    },

    prev() {
      this.active = this.active - (this.active < 1 ? 0 : 1);
    },

    addressChange() {
      const leadId = this.$route.params.id ? this.$route.params.id : "";
      let rawAddressData = this.rawAddressData;
      let hasError = false
      let errorMessage = ''
      let addressParts = {
        'supply_install': [
        {
          'part': 'streetNo',
          'message': 'Please select more accurate address, address is missing street no.'
        },
        {
          'part': 'streetName',
          'message': 'Please select more accurate address, address is missing street name.'
        },
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
      ],
      'supply_only': [
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
      ]};

      let cusTypeReqAddressParts = addressParts.supply_install;
      if(this.leadForm.customer_type == 'Supply Only') {
        cusTypeReqAddressParts = addressParts.supply_only;
      }

      if (!this.leadForm[cusTypeReqAddressParts[0].part] && !this.leadForm[cusTypeReqAddressParts[1].part]) {
        hasError = true;
        errorMessage = 'Please select more accurate address, address is missing postcode and state.'
      } else {
        for (let addressPart of cusTypeReqAddressParts) {
          if ( ['state', 'postcode'].includes(addressPart.part) ) continue;

          hasError = !this.leadForm[addressPart.part]
          errorMessage = hasError ? addressPart.message : ''

          if (hasError) {
            break;
          }
        }

        this.generatePostcodeOrganization()
      }

      if (!leadId || !this.orgDetails.name) {
        this.filterOrgs()
      }
    },

    handleTabClick(tab, event) {},

    postcodeHandleBlur(e){
      this.generatePostcodeOrganization()
    },

    generatePostcodeOrganization(){
      let postcodes = this.postcodes.filter(
        (postcodes_) => postcodes_.postcode === this.leadForm['postcode']
      )
      let organisation_ids = this.extractValue(postcodes, 'organisation_id')
      this.org_ids_compares = organisation_ids
      this.$store.dispatch("organisations/fetchFilterOrganizations", organisation_ids)
    },

    showOrganisation(organisation){
      if(organisation.org_status == 0){
        return false
      }
      return true
    },

    showOption(organisation) {
      if(this.leadForm.organisation_id !== organisation.id && organisation.is_suspended == 0){
        return false;
      }
      else{
        return true;
      }
    },

    reassignLead(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leadhistory/reassignLead", this.leadForm)
            .then(({ data, success, message, errors }) => {
              if (success) {
                this.$router.push({
                  name: "admin.leads.update",
                  params: {
                    id: data.id,
                  },
                });

                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                }).then(() => {
                  window.location.reload();
                });
              }
            })
            .catch(error => {
              Swal.fire({
                title: "Oops!",
                text: "Something went wrong!",
                type: "error",
              });
            });
        } else {
          return false;
        }
      });
    },
    outgoingNotification() {
      // Bus.$emit( 'reload-outgoing-notification')
      this.$store.dispatch("notifications/setOutgoingModalState", {
          outgoingModalState: true
        });
    },

    closeOutgoingNotifications() {
      this.$store.dispatch("notifications/setOutgoingModalState", {
          outgoingModalState: false
        });
    },

    saveLead(formName, next = null) {
      this.leadForm.update_type = 'edit_lead';
      if (!this.isPhoneNumberValidate) {
        this.leadForm.contact_number = "";
      } else {
        this.leadForm.contact_number = this.formatNumber
          ? this.formatNumber
          : this.leadForm.contact_number;
      }

      this.leadForm.organisation_id = this.organisation_id_temp ? this.organisation_id_temp : this.leadForm.organisation_id
      this.$refs[formName].validate((valid) => {
        if (valid) {
          // dispatch form submit
          this.$store
            .dispatch("leads/saveLead")
            .then(({ success, message, errors }) => {
              if (success) {
                this.isTouched = false
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    if(next == null){
                      this.closeOutgoingNotifications();
                      Cookies.set( 'lead_id', this.leadForm.lead_id )
                      this.$router.push({ name: 'dashboard' })
                    }
                  },
                });
              }else{
                this.closeOutgoingNotifications();
                Swal.fire({
                  title: "Oops!",
                  html: errors.error,
                  type: "error",
                });
              }
            });
        } else {
          return false;
        }
      });
    },



    extractValue(arr, prop) {
      let extractedValue = arr.map(item => item[prop]);
      return extractedValue;
    },

    filterOrgs() {
      const field = this.filterType.toLowerCase()
      this.organisation_id_temp = ""

      if ( field == 'state' ) {
        this.filteredOrgs = this.organisations.filter(
          (org) => org.address[field] === this.leadForm[field]
        )
        this.isShowOrgStatsTable = true
      } else if ( field == 'postcode' ) {
        this.generatePostcodeOrganization()
        this.filteredOrgs = this.filter_organizations
        this.isShowOrgStatsTable = true

      } else if ( field == 'none' ) {
        this.filteredOrgs = this.organisations
        this.isShowOrgStatsTable = false
      }

      Bus.$emit( 'reload-org-stat', this.filteredOrgs )

      this.orgDetails = {
          name: '',
          email: '',
          code: '',
          number: '',
      };
    },

    getOrgDetails(returnType = null) {
      let org_id = (this.organisation_id_temp != "") ? this.organisation_id_temp : this.leadForm.organisation_id

      const selectedOrg = this.organisations.find(
        (org) => org.id === org_id
      );

      if (selectedOrg) {
        if (returnType) {
          return {
            name: selectedOrg.name,
            email: selectedOrg.user.email,
            code: selectedOrg.org_code,
            number: selectedOrg.contact_number,
            manual_update: selectedOrg.metadata.manual_update,
          }
        } else {
          this.orgDetails = {
            name: selectedOrg.name,
            email: selectedOrg.user.email,
            code: selectedOrg.org_code,
            number: selectedOrg.contact_number,
          };
        }
      }
    },

    autoSelectStatus() {
      if (!(this.hideStatus())) {
        this.leadForm.escalation_status = this.leadTypes[this.leadForm.customer_type][this.leadForm.escalation_level][0]
      } else {
        this.leadForm.escalation_status = ''
      }
    },

    hideStatus() {
      const statusInfo = this.leadForm.escalation_level === 'Unassigned' ||
          this.leadForm.escalation_level === 'Won' ||
          this.leadForm.escalation_level === 'Lost' ||
          this.leadForm.escalation_level === 'Lost - Inconclusive';
      return statusInfo;
    },

    editLead() {
      const leadId = this.$route.params.id ? this.$route.params.id : "";
      if (leadId) {
        this.$store.dispatch("leads/editLead", leadId).then((data) => {
          this.getOrgDetails();
          this.setUpSteps(this.leadForm.customer_type);
          this.updateRequired();

          //use address search(new data), else full address for ussually old data from migration
          this.leadForm.address_search = (this.leadForm.address_search.length > 0) ? this.leadForm.address_search: data.data.lead.customer.address.full_address;
          //this.leadForm.address_search = data.data.lead.customer.address.full_address

          // Append Extension if IP - Extended N > 3
          // if(data.data.escalation_status.indexOf('Extended') !== -1 && this.leadTypes[data.data.lead.customer_type][data.data.escalation_level].indexOf(data.data.escalation_status) === -1) {
          //   this.leadTypes[data.data.lead.customer_type][data.data.escalation_level].splice(
          //     this.leadTypes[data.data.lead.customer_type][data.data.escalation_level].indexOf('Awaiting Response - Admin Notified') + 1,
          //     0,
          //     data.data.escalation_status
          //   );
          // }

          this.pageTitle = 'Edit Lead Information'
          let new_lead_id = Cookies.get( 'new_lead_id' )
          if ( new_lead_id ) {
            this.new_lead_id = new_lead_id
            Cookies.remove( 'new_lead_id' )
          }
        });
        this.submitText = "Update Lead"

        this.isTouched = false
        this.isTouchedCounter = 0
      }
    },

    async loadMore(queryInfo) {
      this.query.page = queryInfo.page;
      this.query.pageSize = queryInfo.pageSize;

      this.$store.dispatch("orglocators/fetchSearchOrgLocators", this.query);
    },

    isManualUpdateEnabled: isManualUpdateEnabled,

    formUpdated: function(newV, oldV) {
      if(this.leadForm.lead_id != ''){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }else{
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    },

    checkLeadFormId() {
      return true
      // return leadForm.originalData.organisation_id == '' && leadForm.organisation_id === null) || (leadForm.originalData.organisation_id == '' && leadForm.organisation_id == ''
    },

    landlineNumber() {
      let val = this.leadForm.landline_number
      let nots = [ 'General Enquiry', 'Supply Only' ]
      let withs = [ 'Supply & Install' ]

      if ( withs.includes( this.leadForm.customer_type ) && val !== '' ) {
        this.leadFormRules.contact_number[0].required = false
      }

      if ( withs.includes( this.leadForm.customer_type ) && val == '' ) {
        this.leadFormRules.contact_number[0].required = true
      }

      if ( nots.includes( this.leadForm.customer_type ) && val == '' ) {
        this.leadFormRules.contact_number[0].required = false
      }
    }
  },

  beforeRouteLeave (to, from, next) {
    if(this.isTouched){
      if(this.leadForm.lead_id != ''){ //if edit lead
        Swal.fire({
          title: 'Just Checking!',
          text: 'Your changes have not been updated. Would you like to update now?',
          type: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes',
          cancelButtonText: 'No'
        }).then((result) => {
          if (result.value) {
            this.saveLead('leadForm', next)
            next()
          }else{
            next()
          }
        })
      }else{ //new lead
        Swal.fire({
          title: 'Just Checking!',
          text: 'You have not completed this form, if you continue you will lose this data.',
          type: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Continue',
          cancelButtonText: 'Back to form'
        }).then((result) => {
          if (result.value) {
            next()
          }
        })
      }

    }else{
      next()
    }
  },

  beforeMount() {
    this.$store.dispatch("leads/fetchSettings", {
      page: 1,
      pageSize: 100,
      filters: [],
    });

    this.$store.dispatch("organisations/fetchStates");

    this.$store.dispatch("leads/fetchOrgAll").then(() => {
      this.editLead();
    });

    this.$store.dispatch("orglocators/fetchOrgLocators", {
      page: 1,
      pageTitle: 10,
      filters: [],
    });

    this.$store.dispatch("organisations/fetchOrganisationPostcodes");

    // Default Country
    this.leadForm.country = 'Australia'
  },

  created(){
    this.$watch('leadForm', this.formUpdated, {
      deep: true
    })
  },

  mounted(){
    this.isTouched = false
    this.isTouchedCounter = 0

    Bus.$on( 'form-filter-org', _ => {
      this.filterOrgs()
    } )
  },
};
</script>

<style scoped>
  .on-hold{
    float: right; color: #DE1F21; font-size: 11px; padding: 5px; background-color: #FDEEEE; border-radius: 4px; line-height: 15px; margin-top:5px
  }
  .supply-only label{
    word-break: keep-all;
    white-space: normal;
  }

  .r-btn-reset{
    padding: 10px;
    text-align: center;
  }

  @media all and (max-width: 426px){
    .r-btn-reset{
      float: none;
    }
  }
</style>
