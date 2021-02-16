window.addEventListener('load', function() {
  function AccountAccountComponent(context) {
    BaseComponent.call(this, context);

    var self = this;

    self.init = function () {
      initOrderHistory();
      return self;
    };


    function initOrderHistory() {
      //	Download file with order info
      $('#orderHistoryTable').on('click', '#orderReceiptDownload', function (e) {
        e.preventDefault();
        _request({
          url: 'index.php?route=account/account/downloadOrderInfo',
          data: {
            transferData: {
              orderId: e.currentTarget.dataset.orderId
            }
          },
          success: function (response) {
            if (response.code !== 200) {
              console.warn(response.message);
              return;
            }

            var url = response.data.downloadUrl,
              fileName = response.data.fileName;

            var eDownloadLink = $('\
                <a \
                  href="' + url + '"\
                  download="' + fileName + '"\
                  class="ego-download-order-info"\
                ></a>\
                ');

            $(e.target)
              .parent()
              .append(eDownloadLink);
            eDownloadLink
              .get(0)
              .click();
            eDownloadLink.remove();
          }
        });
      });
    }


    //endregion

    return self;
  }

  AccountAccountComponent.prototype = Object.create(BaseComponent.prototype);
  AccountAccountComponent.prototype.constructor = AccountAccountComponent;

  $('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

  var component = new AccountAccountComponent({elementRef: $('#content')});
  component.init();

  // -------------------------

  const pikadayOptions = {
    firstDay: 1,
    toString(date, format) {
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      if (format === 'YYYYMMDD') return `${year}${month}${day}`
      return `${day}.${month}.${year}`;
    },
    parse(dateString) {
      const parts = dateString.split('.');
      const day = parseInt(parts[0], 10);
      const month = parseInt(parts[1], 10) - 1;
      const year = parseInt(parts[2], 10);
      return new Date(year, month, day);
   },
    i18n: {
      previousMonth: 'Предыдущий Месяц',
      nextMonth: 'Следующий Месяц',
      months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
      weekdays: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
      weekdaysShort: ['Вск', 'Пнд', 'Втр', 'Срд', 'Чтв', 'Птн', 'Сбт']
    }
  }

  const balanceDateFrom = new Pikaday({
    ...pikadayOptions,
    onSelect: () => updateOrderBalanceTable(),
    field: document.getElementById('balanceDateFrom'),
  });

  const balanceDateTo = new Pikaday({
    ...pikadayOptions,
    onSelect: () => updateOrderBalanceTable(),
    field: document.getElementById('balanceDateTo'),
  });

  // -------------------------

  const dataTableOptions = {
    searching: false,
    ordering: false,
    autoWidth: false,
    info: false,
    lengthChange: false,
    language: {
      emptyTable: 'Записи отсутствуют',
      paginate: {
        first: 'Первая',
        previous: 'Предыдущая',
        next: 'Следующая',
        last: 'Последняя'
      },
    },
  }

  const orderBalanceTable = $('#order-balance-table').DataTable(dataTableOptions);

  const updateOrderBalanceTable = async () => {
    balanceDateFrom.setMaxDate(balanceDateTo.getDate());
    balanceDateTo.setMinDate(balanceDateFrom.getDate());
    const url = '/index.php?route=account/account/balance';
    const dateFrom = balanceDateFrom.toString('YYYYMMDD');
    const dateTo = balanceDateTo.toString('YYYYMMDD');
    const body = JSON.stringify({ dateFrom, dateTo });
    const response = await fetch(url, { method: 'POST', body });
    const data = await response.json();

    orderBalanceTable.clear();
    orderBalanceTable.draw();

    const insertRow = item => {
      const row = document.createElement('tr');
      if (item.url) row.onclick=`window.open('${item.url}')`;
      else row.classList.add('total-row');

      row.insertCell().textContent = item.name;
      row.insertCell().textContent = item.total;
      row.insertCell().textContent = item.balance;

      orderBalanceTable.row.add(row).draw();
    };

    data.forEach(insertRow);
  };

  updateOrderBalanceTable();

  // -------------------------

  const historyDateFrom = new Pikaday({
    ...pikadayOptions,
    onSelect: () => updateOrderHistoryTable(),
    field: document.getElementById('historyDateFrom'),
  });

  const historyDateTo = new Pikaday({
    ...pikadayOptions,
    onSelect: () => updateOrderHistoryTable(),
    field: document.getElementById('historyDateTo'),
  });

  const orderHistoryTable = $('#orderHistoryTable').DataTable({ ...dataTableOptions,  pageLength: 20 });

  const updateOrderHistoryTable = async () => {
    historyDateFrom.setMaxDate(historyDateTo.getDate());
    historyDateTo.setMinDate(historyDateFrom.getDate());
    const url = '/index.php?route=account/account/history';
    const dateFrom = historyDateFrom.toString('YYYYMMDD');
    const dateTo = historyDateTo.toString('YYYYMMDD');
    const body = JSON.stringify({ dateFrom, dateTo });
    const response = await fetch(url, { method: 'POST', body });
    const data = await response.json();

    orderHistoryTable.clear();
    orderHistoryTable.draw();

    const insertRow = item => {
      const row = document.createElement('tr');
      row.classList.add('order-history-table__row-desktop-show');
      row.dataset.url = item.link;
      row.dataset.orderId = item.orderId;

      row.insertCell().textContent = item.orderId;
      row.insertCell().textContent = item.date;
      row.insertCell().textContent = item.orderStatusName;
      row.insertCell().textContent = item.paymentMethod;
      row.insertCell().textContent = item.shippingMethod;
      row.insertCell().textContent = item.shippingFullName;
      row.insertCell().textContent = item.ttn;
      row.insertCell().textContent = item.storeName;
      row.insertCell().textContent = item.ttnStatus;
      row.insertCell().innerHTML = `${item.totalUAH}&nbsp;грн.<br>${item.totalUSD}`;

      const cellDowload = row.insertCell();
      const linkDownload = document.createElement('a');
      linkDownload.href = `/index.php?route=account/account/download?orderId=${item.orderId}`;
      // linkDownload.id = 'orderReceiptDownload';
      linkDownload.download = true;
      // linkDownload.classList.add('order-receipt-download');
      linkDownload.dataset.orderId = item.orderId;
      linkDownload.textContent = '1';
      cellDowload.appendChild(linkDownload);

      const cellOrder = row.insertCell();
      const linkOrder = document.createElement('a');
      linkOrder.href = item.link;
      linkOrder.classList.add('open');
      linkOrder.textContent = '1';
      cellOrder.appendChild(linkOrder);

      orderHistoryTable.row.add(row).draw();
    };

    data.forEach(insertRow);

  //     <td>
  //       <a href="#" class="order-receipt-download" id="orderReceiptDownload"
  //       data-order-id="{{ item.orderId }}">
  //         <img src="/ego/assets/img/download.png" />
  //       </a>
  //     </td>
  //     <td>
  //       <a href="{{ item.link }}" class="open">
  //         <img src="/ego/assets/img/icon-search.png" width="20" />
  //       </a>
  //     </td>
  //   </tr>
  //   <tr class="order-history-table__row-mobile-show">
  //     <td>
  //       <details class="order-history-table__mobile-accordion">
  //         <summary class="order-history-table__mobile-toggle">
  //           <ul class = "order-history-table__mobile-list-wrapper">
  //             <li class="order-history-table__mobile-list-item">
  //               <div class="order-history-table__mobile-list-item-title">Номер заказа</div>
  //               <div>{{ item.orderId }}</div>
  //             </li>
  //             <li class="order-history-table__mobile-list-item">
  //               <div class="order-history-table__mobile-list-item-title">Дата</div>
  //               <div>{{ item.date }}</div>
  //             </li>
  //             <li class="order-history-table__mobile-list-item">
  //               <div class="order-history-table__mobile-list-item-title">Статус</div>
  //               <div>{{ item.orderStatusName }}</div>
  //             </li>
  //             <li class="order-history-table__mobile-list-item">
  //               <div class="order-history-table__mobile-list-item-title">Тип доставки</div>
  //               <div>{{ item.shippingMethod }}</div>
  //             </li>
  //             <li class="order-history-table__mobile-list-item">
  //               <div class="order-history-table__mobile-list-item-title">Сумма</div>
  //               <div>{{ item.totalUAH }}&nbsp;грн. (${{ item.totalUSD }})</div>
  //             </li>
  //           </ul>
  //         </summary>

  //         <ul class = "order-history-table__mobile-list-wrapper order-history-table__mobile-list-wrapper--content">
  //           <li class="order-history-table__mobile-list-item">
  //             <div class="order-history-table__mobile-list-item-title">Способ оплаты</div>
  //             <div>{{ item.paymentMethod }}</div>
  //           </li>
  //           <li class="order-history-table__mobile-list-item">
  //             <div class="order-history-table__mobile-list-item-title">Контрагент</div>
  //             <div>{{ item.shippingFullName }}</div>
  //           </li>
  //           <li class="order-history-table__mobile-list-item">
  //             <div class="order-history-table__mobile-list-item-title">ТТН</div>
  //             <div>{{ item.ttn }}</div>
  //           </li>
  //           <li class="order-history-table__mobile-list-item">
  //             <div class="order-history-table__mobile-list-item-title">Склад отправки</div>
  //             <div>{{ item.storeName }}</div>
  //           </li>
  //           <li class="order-history-table__mobile-list-item">
  //             <div class="order-history-table__mobile-list-item-title">Отслеживание</div>
  //             <div>{{ item.ttnStatus }}</div>
  //           </li>
  //           <li class="order-history-table__mobile-list-item--button">
  //             <button
  //               id="orderReceiptDownload"
  //               class="btn-new btn-new--success"
  //               data-order-id="{{ item.orderId }}"
  //             >
  //               <span class="btn-new__wrapper-icon">
  //                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  //                   <path d="M216 0h80c13.3 0 24 10.7 24 24v168h87.7c17.8 0 26.7 21.5 14.1 34.1L269.7 378.3c-7.5 7.5-19.8 7.5-27.3 0L90.1 226.1c-12.6-12.6-3.7-34.1 14.1-34.1H192V24c0-13.3 10.7-24 24-24zm296 376v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h146.7l49 49c20.1 20.1 52.5 20.1 72.6 0l49-49H488c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"/>
  //                 </svg>
  //               </span>
  //               <span class="btn-new__wrapper-text">
  //                 Скачать
  //               </span>
  //             </button>
  //           </li>
  //           <li class="order-history-table__mobile-list-item">
  //             <a
  //               class="btn-new btn-new--success"
  //               href="{{ item.link }}"
  //             >
  //               <span class="btn-new__wrapper-icon">
  //                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
  //                   <path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"/>
  //                 </svg>
  //               </span>
  //               <span class="btn-new__wrapper-text">
  //                 Информация
  //               </span>
  //             </a>
  //           </li>
  //         </ul>
  //       </details>
  //     </td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //     <td></td>
  //   </tr>
  // {% endfor %}
  };

  updateOrderHistoryTable();

  // -------------------------


  const clickTab = () => {
    const activeTab = document.getElementById(`tab-${window.location.hash.slice(1)}`);
    if (activeTab) activeTab.click();
  }

  clickTab();

  window.onhashchange = clickTab;

  // const url = new URL(location.href);
  // url.searchParams.delete('success');
  // url.searchParams.delete('error');
  // window.history.pushState({}, null, url.toString());

  // -----------------------

  const phone = document.getElementById('phone');

  const onProfileSave = async evt => {
    evt.preventDefault();
    const body = JSON.stringify({
      firstName: document.getElementById('firstName').value,
      lastName: document.getElementById('lastName').value,
      phone: phone.value.replace(/\(|\)|-|\+|_/g, ''),
      email: document.getElementById('email').value,
      password: document.getElementById('password').value,
      region: document.getElementById('npRegion').value,
      city: document.getElementById('npCity').value,
      warehouse: document.getElementById('npWarehouse').value
    });

    const url = 'index.php?route=account/account/save';
    try {
      await fetch(url, { method: 'POST', body });
      window.uiService.popup
        .setHeader('Информация')
        .setBody('Профиль успешно обновлен')
        .hideFooter()
        .open();
    } catch (err) {
      console.error(err);
    }
  }

  const formProfileSave = document.getElementById('formProfileSave');
  formProfileSave.addEventListener('submit', onProfileSave);

  // -----------------------

  const onFocusPhone = ({ target }) => {
    const index = target.value.indexOf('_');
    if (index !== -1) target.setSelectionRange(index, index);
  }

  phone.addEventListener('focus', onFocusPhone);
  phone.addEventListener('click', onFocusPhone);

  phone.value = phone.value.replace(/^38/, '');
  const mask = ['3', '8','(', /\d/, /\d/, /\d/, ')', /\d/, /\d/, /\d/, '-', /\d/, /\d/, '-', /\d/, /\d/];
  vanillaTextMask.maskInput({ inputElement: phone, mask, showMask: true });
});
