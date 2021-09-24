<template>
  <Section dusk="organisationForm" className="organisation-form">
    <template v-slot:content>
      <el-card class="box-card b-none" shadow="never">
        <el-form
          style="margin-top: -30px"
          label-position="top"
          label-width="120px"
          :model="storeForm"
          :rules="storeFormRules"
          status-icon
          ref="storeForm"
        >
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Store Name"
                prop="name"
                :error="
                  storeForm.errors.errors.name
                    ? storeForm.errors.errors.name[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.name"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Store Code"
                prop="code"
                :error="
                  storeForm.errors.errors.code
                    ? storeForm.errors.errors.code[0]
                    : ''
                ">
                <el-input dusk="org_code" type="text" v-model="storeForm.code"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item
                label="Street Address"
                prop="street_address"
                :error="
                  storeForm.errors.errors.street_address
                    ? storeForm.errors.errors.street_address[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.street_address"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
              <el-form-item
                label="Suburb"
                prop="suburb"
                :error="
                  storeForm.errors.errors.suburb
                    ? storeForm.errors.errors.suburb[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.suburb"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
              <el-form-item
                label="Postcode"
                prop="postcode"
                :error="
                  storeForm.errors.errors.postcode
                    ? storeForm.errors.errors.postcode[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.postcode"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="24" :md="24" :lg="8" :xl="8">
              <el-form-item
                label="State"
                prop="state"
                :error="
                  storeForm.errors.errors.state
                    ? storeForm.errors.errors.state[0]
                    : ''
                ">
                <el-select popper-class="state_popper" v-model="storeForm.state" placeholder="Select State">
                <el-option
                  v-for="item in states"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Contact Number"
                prop="phone_number"
                :error="
                  storeForm.errors.errors.phone_number
                    ? storeForm.errors.errors.phone_number[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.phone_number"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Contact"
                prop="contact"
                :error="
                  storeForm.errors.errors.contact
                    ? storeForm.errors.errors.contact[0]
                    : ''
                ">
                <el-input dusk="org_code" type="text" v-model="storeForm.contact"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Year To Date Sales"
                prop="year_to_date_sales"
                :error="
                  storeForm.errors.errors.year_to_date_sales
                    ? storeForm.errors.errors.year_to_date_sales[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.year_to_date_sales"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Last Year Sales"
                prop="last_year_sales"
                :error="
                  storeForm.errors.errors.last_year_sales
                    ? storeForm.errors.errors.last_year_sales[0]
                    : ''
                ">
                <el-input dusk="org_code" type="text" v-model="storeForm.last_year_sales"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Keep Stock"
                prop="keep_stock"
                :error="
                  storeForm.errors.errors.keep_stock
                    ? storeForm.errors.errors.keep_stock[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.keep_stock"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Stock Kits"
                prop="stock_kits"
                :error="
                  storeForm.errors.errors.stock_kits
                    ? storeForm.errors.errors.stock_kits[0]
                    : ''
                ">
                <el-input dusk="org_code" type="text" v-model="storeForm.stock_kits"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Pricing"
                prop="pricing_book"
                :error="
                  storeForm.errors.errors.pricing_book
                    ? storeForm.errors.errors.keep_stock[0]
                    : ''
                ">
                <el-input type="text" v-model="storeForm.pricing_book"></el-input>
              </el-form-item>
            </el-col>

            <el-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
              <el-form-item
                label="Priority"
                prop="priority"
                :error="
                  storeForm.errors.errors.priority
                    ? storeForm.errors.errors.priority[0]
                    : ''
                ">
                <el-input dusk="org_code" type="text" v-model="storeForm.priority"></el-input>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row>
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <el-form-item>
                <el-button
                  dusk="organisation-save"
                  v-show="!storeForm.id"
                  type="primary"
                  @click="saveStore('storeForm')"
                  >Submit</el-button
                >

                <el-button
                  v-show="storeForm.id"
                  :disabled="!isTouched"
                  type="primary"
                  @click="saveStore('storeForm')"
                  >Update</el-button
                >

                <el-button
                  type="danger"
                  @click="closeStore()"
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
import Section from "~/components/Section"
import { mapGetters } from "vuex"
import Swal from "sweetalert2"

export default {
  name: "StoreForm",

  components: {
    Section,
  },

  computed: {
    ...mapGetters({
      storeForm: "store/storeForm",
      storeFormRules: "store/storeFormRules",
      states: "leads/statesList",
      showId: 'store/showId',
    }),
  },

  data() {
    return {
      isTouched: false,
      isTouchedCounter: 0
    }
  },

  methods: {
    saveStore(formName){
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.$store
            .dispatch("store/saveStore")
            .then(({ success, message, errors }) => {
              if (success) {
                this.isTouched = false
                Swal.fire({
                  title: "Success!",
                  text: message,
                  type: "success",
                  onClose: () => {
                    this.closeStore()
                  },
                })
              }
            })
        } else {
          return false;
        }
      })
    },

    closeStore(){
      this.$emit('closeStore')
    },

    formUpdated: function(newV, oldV) {
      if(this.storeForm.id){
        if(this.isTouchedCounter > 0){
          this.isTouched = true
        }
        this.isTouchedCounter++
      }
    }
  },

  created(){
    this.$watch('storeForm', this.formUpdated, {
      deep: true
    })
  },

  mounted(){
    console.log('stores.form.mounted')
    console.log('show id: '+this.showId)
  },
}
</script>

<style>

</style>
