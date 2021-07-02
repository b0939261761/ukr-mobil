<h2>Замовлення</h2>
<table style="font-size: 14px;">
  <tr>
    <td style="font-weight: bold; padding-right: 15px;">Телефон</td>
    <td>
      <a href="tel:{{ $phone }}">{{ $phone }}</a>
    </td>
  </tr>

  <tr>
    <td style="font-weight: bold; padding-right: 15px;">Код</td>
    <td>{{ $product['id'] }}</td>
  </tr>

  <tr>
    <td style="font-weight: bold; padding-right: 15px;">Назва</td>
    <td>{{ $product['name'] }}</td>
  </tr>

  <tr>
    <td style="font-weight: bold; padding-right: 15px;">Ціна</td>
    <td>$ {{ $product['price'] }}</td>
  </tr>
</table>

@if($customer)
  <h2>Користувач</h2>
  <table style="font-size: 14px;">
    <tr>
      <td style="font-weight: bold; padding-right: 15px;">Ім'я та Призвіще</td>
      <td>{{ $customer['fullName'] }}</td>
    </tr>

    <tr>
      <td style="font-weight: bold; padding-right: 15px;">Телефон</td>
      <td>
        <a href="tel:{{ $customer['phone'] }}">{{ $customer['phone'] }}</a>
      </td>
    </tr>

    <tr>
      <td style="font-weight: bold; padding-right: 15px;">Email</td>
      <td>
        <a href="mailto:{{ $customer['email'] }}">{{ $customer['email'] }}</a>
      </td>
    </tr>
  </table>
@endif
