<template>
  <div class="card">
    <div v-if="title" class="card-header">
      <div class="card-title-wrap bar-primary" :class="marginClass">
        <h4 class="card-title">
          {{ title }}
        </h4>
        <slot name="card_header_buttons" />
      </div>
      <slot name="card_header_after_title" />
    </div>

    <div class="card-body" :class="cardBodyClass">
      <slot name="card_body" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'Card',

  props: {
    title: { type: String, default: null },
    marginClass: { type: String, default: '' },
    cardBodyClass: { type: String, default: '' }
  }
}
</script>
