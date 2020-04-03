require('./bootstrap');
window.Vue = require('vue');

Vue.component('TodoList', require('./components/TodoList.vue').default);

const app = new Vue({
    el: '#app',
});
