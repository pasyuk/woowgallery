import Vue from 'vue';
import App from './App.vue';

Vue.config.productionTip = false;

document.querySelectorAll('.woowgallery-block').forEach((el) => {
  new Vue({
    render: h => h(App, {
      props: {
        appid: el.parentElement.id
      }
    })
  }).$mount(`#${el.id}`);
});
