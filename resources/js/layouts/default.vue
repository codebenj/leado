<template>
  <div class="main-layout">
    <Navbar />

    <div class="container mt-4">
      <child />
    </div>
  </div>
</template>

<script>
import Sidebar from '~/partials/Sidebar'
import Navbar from '~/partials/Navbar'

export default {
  name: 'MainLayout',

  components: {
	Sidebar,
	Navbar
  }
}
</script>
