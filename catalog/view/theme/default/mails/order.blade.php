@include('_header')

<div style="width:600px;margin:0 auto 30px;text-align:center">
  <h1 style="margin:0 0 20px;font-size:30px;font-family:'trebuchet ms',helvetica,sans-serif;line-height:36px;font-weight:normal;color: #303031">
    Дякуємо за ваше замовлення!
  </h1>
  <p style="margin:0 40px 20px;font-family:arial,'helvetica neue',helvetica,sans-serif;line-height:21px;color:#333333;font-size:14px">
    Ваше замовлення прийняте на опрацювання! При потребі ми зв'яжемося з вами для підтвердження, або уточннення даних!
  </p>

  <a
    style="background-color:#bc191d;display:inline-block;border-radius:20px;padding:10px 25px;color:#ffffff;font-size:16px;line-height:19px;margin-bottom:45px"
    href="https://ukr-mobil.com/account#orders"
  >
    Перевірити статус замовленя
  </a>

  <table style="margin-bottom:25px;text-align:left;padding:20px 20px 5px" cellspacing="0" cellpadding="0" bgcolor="#efefef">
    <tr>
      <td style="width:240px;padding-right:40px" valign="top">

        <table cellspacing="0" cellpadding="0" style="margin-bottom:20px">
          <tr>
            <td style="font-size:13px;font-family:'trebuchet ms',helvetica,sans-serif;font-weight:bold;color:#222222">
              Підсумок:
            </td>
          </tr>
          <tr>
            <td style="width:115px;font-size:14px;line-height:21px;color:#222222" valign="top">
              № замовлення:
            </td>
            <td style="font-size:14px;line-height:21px;color:#222222">
              {{ $order['orderIds'] }}
            </td>
          </tr>
          <tr>
            <td style="font-size:14px;line-height:21px;color:#222222">
              Дата:
            </td>
            <td style="font-size:14px;line-height:21px;color:#222222">
              {{ $order['date'] }}
            </td>
          </tr>
          <tr>
            <td style="font-size:14px;line-height:21px;color:#222222">
              Сума:
            </td>
            <td style="font-size:14px;line-height:21px;color:#222222">
              {{ $order['totalUSD'] }}$
            </td>
          </tr>
        </table>

        <p style="margin:0;font-size:13px;font-family:'trebuchet ms',helvetica,sans-serif;font-weight:bold;color:#222222">Спосіб оплати:</p>
        <p style="margin:0;font-size:14px;line-height:21px;color:#222222">{{ $order['paymentMethod'] }}</p>
      </td>
      <td valign="top">
        <p style="margin:0;font-size:13px;font-family:'trebuchet ms',helvetica,sans-serif;font-weight:bold;color:#222222">
          Інформація про доставку:
        </p>
        <p style="margin:0;font-size:14px;line-height:21px;color:#222222" valign="top">
          Отримувач: {{ $order['shippingFullname'] }}
        </p>
        <p style="margin:0 0 5px;font-size:14px;line-height:21px;color:#222222" valign="top">
          Спосіб доставки: {{ $order['shippingMethod'] }}
        </p>
        <p style="margin:0;font-size:13px;font-family:'trebuchet ms',helvetica,sans-serif;font-weight:bold;color:#222222">
          Адреса доставки:
        </p>
        <p style="margin:0;font-size:14px;line-height:21px;color:#222222">{{ $order['shippingAddress'] }}$</p>
      </td>
    </tr>
  </table>

  <table style="margin-bottom:20px;text-align:left;padding:0 20px" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
        <th style="padding: 0 10px 5px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Товар
        </th>
        <th style="padding: 0 10px 5px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Код
        </th>
        <th style="padding: 0 10px 5px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Назва товару
        </th>
        <th style="padding-bottom: 5px;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center">
          Ціна
        </th>
        <th style="padding: 0 10px 5px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center">
          К&#8209;сть
        </th>
        <th style="padding-bottom: 5px;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center">
          Сума
        </th>
      </tr>
    </thead>
    <tbody>
      @foreach($products as $product)
        <tr>
          <td style="border-bottom:1px solid #efefef;padding:10px 10px 10px 0;" valign="top">
            <img
              style="display:block"
              src="{{ $product['image'] }}"
              title="{{ $product['name'] }}"
              alt="{{ $product['name'] }} - ukr-mobil.com"
              width="60"
              height="60"
            />
          </td>
          <td style="border-bottom:1px solid #efefef;padding:10px 10px 10px 0;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333" valign="top">
            {{ $product['id'] }}
          </td>
          <td style="border-bottom:1px solid #efefef;padding:10px 10px 10px 0;line-height:17px;font-family:arial,'helvetica neue',helvetica,sans-serif" valign="top">
            <a style="color:#bc191d"href="#">{{ $product['name'] }}</a>
          </td>
          <td style="border-bottom:1px solid #efefef;padding:10px 10px 10px 0;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center" valign="top">
            {{ $product['priceUAH'] }}₴<br>({{ $product['priceUSD'] }}$)
          </td>
          <td style="border-bottom:1px solid #efefef;padding:10px 10px 10px 0;padding-right:10px;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center" valign="top">
            {{ $product['quantity'] }}
          </td>
          <td style="border-bottom:1px solid #efefef;padding:10px 0 10px 0;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center" valign="top">
            {{ $product['totalUAH'] }}₴<br>({{ $product['totalUSD'] }}$)
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table cellspacing="0" cellpadding="0" style="text-align:right;padding:0 20px;margin-left:auto">
    @if ($order['commissionUAH'])
      <tr>
        <td style="padding-right:60px;font-family:arial,'helvetica neue',helvetica,sans-serif;line-height:24px;color:#333333;font-size:16px;font-weight:bold">
          Комісія за переказ:
        </td>
        <td style="font-family: arial,'helvetica neue',helvetica,sans-serif;line-height:21px;color:#333333;font-size:14px;font-weight:bold">
          {{ $order['commissionUAH'] }}₴
        </td>
      </tr>
    @endif

    <tr>
      <td style="padding-right:60px;font-family:arial,'helvetica neue',helvetica,sans-serif;line-height:24px;color:#333333;font-size:16px;font-weight:bold">
        Сума замовлення:
      </td>
      <td style="width:120px;font-family: arial,'helvetica neue',helvetica,sans-serif;line-height:21px;color:#333333;font-size:14px;font-weight:bold">
        {{ $order['totalUAH'] }}₴ ({{ $order['totalUSD'] }}$)
      </td>
    </tr>
  </table>

</div>

@include('_footer')
