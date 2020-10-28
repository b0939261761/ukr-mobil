@include('mails.header')

<div class="message">
  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <tbody>
      <tr>
        <td colspan="2">
          <h3 style="text-transform: uppercase; text-align: center;">
            Номер заказа {{ $data['orderId'] }}
          </h3>
        </td>
      </tr>

      <tr>
        <td style="width: 50%;">
          <p style="text-align: center;">
            {{ $data['customerName'] }}
          </p>
        </td>
        <td style="width: 50%;">
          <p style="text-align: center;">
            Время заказа: {{ date('d.m.Y H:i:s') }}
          </p>
        </td>
      </tr>
  
      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <b>Информация о доставке</b>
          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            {{ $data['aboutDelivery'] }}
          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            {{ $data['recipientName'] }}
          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            {{ $data['recipientPhone'] }}
          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            {{ $data['shippingAddress'] }}
          </p>
        </td>
      </tr>
    </tbody>
  </table>

  <br>
  <br>

  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <thead>
      <tr>
        <th colspan="4">
          <h3 style="text-align: center;">
            Заказ
          </h3>
        </th>
      </tr>
      <tr>
        <th>Название</th>
        <th>Цена</th>
        <th>Количество</th>
        <th>Итого</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data['productList'] as $item)
        <tr>
          <td style="text-align: center;">
            {{ $item['name'] }}
          </td>
          <td style="text-align: center;">
            {{ $item['priceFormat'] }}
          </td>
          <td style="text-align: center;">
            {{ $item['quantity'] }}
          </td>
          <td style="text-align: center;">
            {{ $item['totalFormat'] }}
          </td>
        </tr>
      @endforeach
      <tr>
        <td colspan="4" style="text-align: right;">
          <b>Итого:</b> {{ $data['total'] }}
        </td>
      </tr>
    </tbody>
  </table>
</div>
