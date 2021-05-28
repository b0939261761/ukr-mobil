@include('_header')

<div style="width:600px;margin:0 auto 10px;text-align:center">
  <img src="https://ukr-mobil.com/image/mail/wishlist.png" alt="Availability" style="display:block;margin: 0 auto 25px" width="64" height="64">
  <h1 style="margin:0 0 45px;font-size:30px;font-family:'trebuchet ms',helvetica,sans-serif;line-height:36px;font-weight:normal;color: #303031">
    Товар уже в наявності!
  </h1>

  <table style="margin-bottom:20px;text-align:left;padding:0 20px" cellspacing="0" cellpadding="0">
    <thead>
      <tr>
        <th style="padding: 0 10px 10px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Товар
        </th>
        <th style="padding: 0 10px 10px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Код
        </th>
        <th style="padding: 0 10px 10px 0;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333">
          Назва товару
        </th>
        <th style="padding-bottom: 10px;border-bottom: 1px solid #efefef;font-weight:normal;font-family:arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center">
          Ціна
        </th>
      </tr>
    </thead>
    <tbody>
      @foreach($products as $product)
        @php ($tdStyle = 'padding-bottom:10px;border-bottom:1px solid #efefef;')
        @if($loop->last)
          @php ($tdStyle = '')
        @endif

        <tr>
          <td style="{{ $tdStyle }}padding-top:10px;padding-right:10px" valign="top">
            <img
              style="display:block"
              src="{{ $product['image'] }}"
              title="{{ $product['name'] }}"
              alt="{{ $product['name'] }} - ukr-mobil.com"
              width="60"
              height="60"
            />
          </td>
          <td style="{{ $tdStyle }}padding-top:10px;padding-right:10px;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333" valign="top">
            {{ $product['id'] }}
          </td>
          <td style="{{ $tdStyle }}padding-top:10px;padding-right:10px;line-height:17px;font-family:arial,'helvetica neue',helvetica,sans-serif" valign="top">
            <a style="color:#bc191d"href="#">{{ $product['name'] }}</a>
          </td>
          <td style="{{ $tdStyle }}padding-top:10px;font-family: arial,'helvetica neue',helvetica,sans-serif;color:#333333;text-align:center" valign="top">
            {{ $product['priceUAH'] }}₴<br>({{ $product['priceUSD'] }}$)
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div style="background-color:#cccccc;height:1px;margin:50px 20px 20px"></div>
  <p style="margin:0;font-family:arial,'helvetica neue',helvetica,sans-serif;line-height:23px;color:#333333;font-size:15px">
    Ви отримали даний лист, адже використали функцію "Сповістити про надходження".
    Якщо у вас викинли будь-які запитаня - дайте нам знати зручним для Вас способом!
  </p>
</div>

@include('_footer')
