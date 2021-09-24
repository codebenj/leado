<template>
  <div
    class="basic-layout d-flex ai-center jc-center m-none"
  >
    <slot>
      <router-view />
    </slot>
  </div>
</template>

<script>
export default {
  name: "BasicLayout",
};
</script>
