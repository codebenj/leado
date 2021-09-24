<template>
  <h1>Redirect..</h1>
</template>
<script>
export default {
  middleware: ["auth"],
  created: function () {
    const leadId = this.$route.params.id ? this.$route.params.id : null;

    if (leadId) {
      this.$store.dispatch("leadhistory/getLeadHistory", leadId).then((data) => {
        if(data !== undefined) {
          this.$router.push({
            name: "organisation.lead",
            params: { id: leadId },
          });
        } else {
          this.$router.push({ name: "dashboard" });
        }
      });
    } else {
      this.$router.push({ name: "dashboard" });
    }
  },
};
</script>