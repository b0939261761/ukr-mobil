@include('mails.header')

<div class="message">
  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <tbody>
      <tr>
        <td colspan="2">
          <h3 style="text-transform: uppercase; text-align: center;">
            Номер заказа {{ $data['orderIds'] }}
          </h3>
        </td>
      </tr>

      <tr>
        <td style="width: 50%;">
          <p style="text-align: center;">{{ $data['customerFullname'] }}</p>
        </td>
        <td style="width: 50%;">
          <p style="text-align: center;">Время заказа: {{ date('d.m.Y H:i:s') }}</p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;"><b>Информация о доставке</b></p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">{{ $data['shippingMethod'] }}</p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">{{ $data['shippingFullname'] }}</p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">{{ $data['shippingPhone'] }}</p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">{{ $data['shippingAddress'] }}</p>
        </td>
      </tr>
    </tbody>
  </table>

  <br>
  <br>

  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <thead>
      <tr>
        <th colspan="5">
          <h3 style="text-align: center;">
            Заказ
          </h3>
        </th>
      </tr>
      <tr>
        <th>Код</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Кол-во</th>
        <th>Итого</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data['products'] as $product)
        <tr>
          <td style="text-align: center;">
            {{ $product['code'] }}
          </td>
          <td>
            {{ $product['name'] }}
          </td>
          <td style="text-align: center;">
            {{ $product['priceUAH'] }}&nbsp;грн. (${{ $product['priceUSD'] }})
          </td>
          <td style="text-align: center;">
            {{ $product['quantity'] }}
          </td>
          <td style="text-align: center;">
            {{ $product['totalUAH'] }}&nbsp;грн. (${{ $product['totalUSD'] }})
          </td>
        </tr>
      @endforeach
    </tbody>

    <tfoot>
      @if ($data['commissionUAH'])
        <tr>
          <td colspan="3" style="text-align: right;">
            <b>Всего:</b>
          </td>
          <td colspan="2" style="text-align: right;">
            {{ $data['totalUAH'] }}&nbsp;грн. (${{ $data['totalUSD'] }})
          </td>
        </tr>
        <tr>
          <td colspan="3" style="text-align: right;">
            <b>Коммисия за перевод:</b>
          </td>
          <td colspan="2" style="text-align: right;">
            {{ $data['commissionUAH'] }}&nbsp;грн.
          </td>
        </tr>
      @endif
      <tr>
        <td colspan="3" style="text-align: right;">
          <b>К оплате:</b>
        </td>
        <td colspan="2" style="text-align: right;">
        {{ $data['toPayUAH'] }}&nbsp;грн. (${{ $data['toPayUSD'] }})
        </td>
      </tr>
    </tfoot>
  </table>
</div>
