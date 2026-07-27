import { createApp } from 'vue'
import App from './App.vue'
Vue.mixin({ methods: { t, n } })

const app = createApp(App)
app.mount('#files-gcs')
