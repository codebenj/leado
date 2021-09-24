<template>
    <el-dialog
      class="escalation-modal"
      title=""
      :visible="escalationVisible"
      :show-close="true"
      @open="open"
      @close="close"
      width="35%"
      v-dialogDrag
      ref="dialog__wrapper"
      append-to-body>

      <AcceptOrDecline v-if="escalationModals[0] && escalationModals[0].value" />
      <ConfirmEnquirerContacted v-if="escalationModals[1] && escalationModals[1].value" />
      <InProgress v-if="escalationModals[2] && escalationModals[2].value" />
      <Finalized v-if="escalationModals[3] && escalationModals[3].value" />
      <Declined v-if="escalationModals[4] && escalationModals[4].value" />
      <Lost v-if="escalationModals[5] && escalationModals[5].value" />
      <Won v-if="escalationModals[6] && escalationModals[6].value" />
      <EscalationConfirmation v-if="escalationModals[7] && escalationModals[7].value" />
      <DeclinedLapse v-if="escalationModals[8] && escalationModals[8].value" />
      <CECDeclined v-if="escalationModals[9] && escalationModals[9].value" />
      <Discontinued v-if="escalationModals[10] && escalationModals[10].value" />
      <Abandoned v-if="escalationModals[11] && escalationModals[11].value" />

      <el-divider></el-divider>
      <p class="text-center">
        Click <a href="#" @click="viewLead()"><strong>here</strong></a> to view lead history.
      </p>
    </el-dialog>
</template>


<script>
import Swal from 'sweetalert2'
import { mapGetters } from 'vuex';
import AcceptOrDecline from "./aod";
import ConfirmEnquirerContacted from './cec'
import InProgress from './inprogress'
import Finalized from './finalized'
import Lost from './lost'
import Won from './won'
import Declined from './declined'
import DeclinedLapse from './declined-lapse'
import EscalationConfirmation from "./confirmation"
import CECDeclined from "./cec-declined"
import Discontinued from "./discontinued"
import Abandoned from "./abandoned"

export default {
    name: 'EscalationModal',
    components: {
        AcceptOrDecline,
        ConfirmEnquirerContacted,
        InProgress,
        Finalized,
        Declined,
        Lost,
        Won,
        EscalationConfirmation,
        DeclinedLapse,
        CECDeclined,
        Discontinued,
        Abandoned
    },
	computed: mapGetters({
        leads: 'leads/leads',
        escalationVisible: 'leadescalation/escalationVisible',
        escalationModals: 'leadescalation/escalationModals',
        selectedLead: 'leadescalation/selectedLead',
    }),
	methods: {
        open() {

        },

        close() {
            this.$store.dispatch('leadescalation/showEscalationModal', {
                showEscalation: false,
            })
        },

        viewLead(){
            this.$store.dispatch('leadescalation/showEscalationModal', {
                showEscalation: false,
            }).then(() => {
                this.$emit( 'singleClickRow', this.selectedLead )
            })
        }
	},
}
</script>
