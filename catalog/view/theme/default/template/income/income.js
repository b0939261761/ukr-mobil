// import { createApp } from '/catalog/view/theme/default/template/income/vue.runtime.esm-browser.prod.js';
import { createApp, ref } from '/catalog/view/theme/default/template/income/vue.esm-browser.js';

const Example = {
  setup() {
    const title = ref('Hello');

    const onClick = () => title.value = '1112';

    return {
      title,
      onClick
    };
  },
  template: `
      <div class="tabs">
    </div>
  `
};

const App = {
  components: {
    Example
  },

  template: `
    <h3>Vue 3</h3>
    <Example />
  `
};

createApp(App).mount('#app');

// console.log(1111);
