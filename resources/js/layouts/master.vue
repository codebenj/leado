<template>
	<el-container v-if="user">
		<!-- sidebar menu -->
		<Sidebar />

		<!-- content -->
		<el-container class="main-container">
			<!-- top navigation -->
			<Navbar />

			<el-row class="w-100">
				<el-col :span="24">
					<!-- main container -->
					<Child />
					<Footer />
				</el-col>
			</el-row>
		</el-container>
	</el-container>
</template>

<script>
import PageLoader from '~/components/PageLoader'
import Sidebar from '~/partials/Sidebar'
import Navbar from '~/partials/Navbar'
import Footer from '~/partials/Footer'
import { mapGetters } from 'vuex'

export default {
	name: 'MasterLayout',

	computed: mapGetters({
		user: 'auth/user',
	}),

	components: {
		Sidebar,
		Footer,
		PageLoader,
		Navbar
  },

}
</script>
