const zeroStart = (num, digits = 2) => num.toString().padStart(digits, '0');

const getDateTime = (date = new Date()) => (date instanceof Date ? date : new Date(date));

const formatDate = (format, datePar) => {
  if (!format) return '';
  const date = getDateTime(datePar);
  let result = format.toString();

  const mask = {
    YYYY: date.getFullYear().toString(),
    YY: date.getFullYear().toString().slice(2),
    MM: zeroStart(date.getMonth() + 1),
    DD: zeroStart(date.getDate()),
    HH: zeroStart(date.getHours()),
    mm: zeroStart(date.getMinutes()),
    ss: zeroStart(date.getSeconds()),
    SSS: zeroStart(date.getMilliseconds(), 3)
  };

  Object.entries(mask).forEach(({ 0: key, 1: value }) => {
    result = result.replace(new RegExp(key, 'g'), value);
  });

  return result;
};

new Vue({
  el: '#app',
  data: () => ({
    states: [],
    error: null,
    orderId: ''
  }),
  computed: {
    name() {
      return this.states[0] && this.states[0].name;
    },
    price() {
      const price = this.states.reduce((acc, cur) => +cur.price > acc ? +cur.price : acc, 0);
      return `${price} грн.`;
    }
  },
  mounted() {
    const mask = ['+', '3', '8','(', '0', /\d/, /\d/, ')', /\d/, /\d/, /\d/, '-', /\d/, /\d/, '-', /\d/, /\d/];
    vanillaTextMask.maskInput({ inputElement: this.$refs.phoneInput, mask, showMask: true });
  },
  methods: {
    onFocusPhoneInput() {
      const index = this.$refs.phoneInput.value.indexOf('_');
      if (index !== -1) this.$refs.phoneInput.setSelectionRange(index, index);
    },
    async onSearch() {
      const phone = this.$refs.phoneInput.value.replace(/\(|\)|-|_/g, '');
      this.states = [];
      if (phone.length < 13) return this.error = 'Неправильный формат номера.\nВведите номер в формате +38(0xx)xxx-xx-xx';
      this.error = null;
      const url = 'index.php?route=information/sc_tracking/getStatus';
      const body = new FormData();
      body.append('orderId', this.orderId.padStart(9, '0'));
      body.append('phone', phone);

      try {
        const response = await fetch(url, { method: 'POST', body });
        if (response.ok) {
          const json = await response.json();
          const formatDateTime = formatDate.bind(null, 'HH:mm DD.MM.YYYY');
          this.states = json.map(el => Object.assign(el, { datetime: formatDateTime(el.datetime)}));
          if (!this.states.length) this.error = 'Заказ не найден';
        } else {
          this.error = `Ошибка HTTP: ${response.status}`;
        }
      } catch (err) {
        this.error = `Ошибка: ${err.message}`;
      };
    }
  }
});
