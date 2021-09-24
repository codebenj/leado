<template>
  <div class="text-center">
    <h5 class="text-center">Confirmation</h5>
    <p
      class="m-b-lg"
      v-if="confirmationMessage !== ''"
      v-html="confirmationMessage"
    >
      {{}}
    </p>

    <el-row :gutter="20">
      <el-col :span="24" class="m-b-lg" v-if="reachedExtendedLimit">
        <el-alert
          :title="
            `Lead #${selectedLead.lead_id} has reached the extended limit.`
          "
          type="error"
          center
          description="An email/sms notification was sent to admin to further review."
        >
        </el-alert>
      </el-col>
      <el-col
        :span="24"
        class="text-left m-b-lg"
        v-if="selectedLead && !hasDeclined"
      >
        <el-button
          v-if="showCallEnquirer"
          type="success"
          class="w-100 m-b-lg"
          @click="callEnquirer(selectedLead.lead.customer.contact_number)"
          >Call Enquirer</el-button
        >

        <p>
          Name:
          <strong>
            {{
              `${selectedLead.lead.customer.first_name} ${selectedLead.lead.customer.last_name}`
            }}
          </strong>
        </p>
        <p>
          Street:
          <strong>
            {{ selectedLead.lead.customer.address.address }}
          </strong>
        </p>
        <p>
          State:
          <strong>
            {{ selectedLead.lead.customer.address.state }}
          </strong>
        </p>
        <p>
          Suburb:
          <strong>
            {{ selectedLead.lead.customer.address.suburb }}
          </strong>
        </p>
        <p>
          Postcode:
          <strong>
            {{ selectedLead.lead.customer.address.postcode }}
          </strong>
        </p>
        <p>
          Phone:
          <strong>
            {{ selectedLead.lead.customer.contact_number }}
          </strong>
        </p>
      </el-col>
      <el-col :span="24" class="confirmation-buttons">
        <el-button
          dusk="confirmation-done"
          type="primary"
          class="w-100"
          @click="close()"
          >Done</el-button
        >
      </el-col>
    </el-row>
  </div>
</template>
<script>
import Swal from "sweetalert2";
import { mapGetters } from "vuex";

export default {
  name: "EscalationConfirmation",
  computed: mapGetters({
    reachedExtendedLimit: "leadescalation/reachedExtendedLimit",
    selectedLead: "leadescalation/selectedLead",
    confirmationMessage: "leadescalation/confirmationMessage",
    hasDeclined: "leadescalation/hasDeclined",
    confirmationLeadDetails: "leadescalation/confirmationLeadDetails",
    showCallEnquirer: "leadescalation/showCallEnquirer"
  }),
  methods: {
    close() {
      this.$store.dispatch("leadescalation/showEscalationModal", {
        showEscalation: false
      });
    },

    callEnquirer(number) {
      window.location.href = `tel:${number}`;
    }
  }
};
</script>
