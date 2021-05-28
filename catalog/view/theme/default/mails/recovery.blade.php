@include('_header')

<div style="width:520px;margin:0 auto 60px;text-align:center;font-size: 15px;line-height:23px;color:#333333">
  <h2 style="margin:0 0 20px;font-size:24px;font-weight:normal;line-height:29px;color:#333333">Запит на скидання паролю</h2>
  <div style="background-color:#c85449;height:3px;margin-bottom:20px"></div>
  <img src="https://ukr-mobil.com/image/mail/recovery.png" alt="Recovery" style="display:block;margin: 0 auto 20px" width="64" height="64">
  <p style="margin: 0 0 20px">
    Ви виконали запит на скидання паролю для вашого акаунту.<br>
    Для завершення скидання паролю перейдіть за посиланням:<br>
    <a href="{{ $linkReset }}" style="color:#ee6c6d;font-size:14px" target="_blank">{{ $linkReset }}</a>
  </p>
  <div style="background-color:#cccccc;height:1px;margin-bottom:35px"></div>
  <p style="margin: 0">
    У разі, якщо даний запит робили не ви, будь ласка, проігноруйте лист, або напишіть нам на пошту для уточнення інформації.
  </p>
</div>

@include('_footer')
