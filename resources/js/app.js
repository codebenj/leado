import Vue from 'vue'
import store from '~/store'
import router from '~/router'
import i18n from '~/plugins/i18n'
import App from '~/components/App'
import DataTable from 'laravel-vue-datatable'

import LaravelPermissions from 'laravel-permissions'
import VueCountdown from '@chenfengyuan/vue-countdown';
import VueTelInput from 'vue-tel-input' // vue-tel-input
import vueDebounce from 'vue-debounce'


// Element UI
import ElementUI from 'element-ui'
import lang from 'element-ui/lib/locale/lang/en'
import locale from 'element-ui/lib/locale'

// Echo
import Echo from 'laravel-echo';
import VueEcho from 'vue-echo';
import Pusher from 'pusher-js'

import * as VueGoogleMaps from 'vue2-google-maps'

import '~/plugins'
import '~/components'
import '~/directives/directives'

// Config Websocket Connection
const environment = process.env.APP_ENV
let echo_config = {}

if(window.config.appEnv == 'develop') {
  echo_config = {
    broadcaster: 'socket.io',
    host: 'https://' + window.location.hostname
  }
} else {
  echo_config = {
    broadcaster: 'pusher',
    key: window.config.PUSHER_APP_KEY,
    cluster: window.config.PUSHER_APP_CLUSTER,
    encrypted: false,
  };
}

const EchoInstance = new Echo(echo_config)
Vue.use(VueEcho, EchoInstance)

Vue.use(DataTable)
Vue.use(ElementUI)
Vue.use(require('vue-moment')); // Moment JS
Vue.use(LaravelPermissions) // Laravel Permissions
Vue.component(VueCountdown.name, VueCountdown); // Countdown Timer
Vue.use(VueTelInput) // vue-tel-input
locale.use(lang)

global.$ = global.jQuery = require('jquery')
Vue.config.productionTip = false
Vue.filter('capitalize', (value) => {
  if (!value) return ''
  value = value.toString()
  return value.charAt(0).toUpperCase() + value.slice(1)
})

Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyCTuOVB720DTWz74jYjK1lAUKZZFdZMpGI',
    libraries: 'places',
  },
})

Vue.use(vueDebounce)


export const Bus = new Vue()

/* eslint-disable no-new */
new Vue({
  i18n,
  store,
  router,
  ...App
})

require('./bootstrap')
