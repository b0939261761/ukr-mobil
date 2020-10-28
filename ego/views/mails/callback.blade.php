@include('mails.header')

<div class="message">
    <p style="{{$email->fontSize}}">
        {!! $data['text'] !!}
    </p>
</div>
