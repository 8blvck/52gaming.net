@extends('@empty')
@section('content')
<link rel="stylesheet" href="{{ url('/public/css/fonts.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ url('/public/css/app.css') }}" type="text/css" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="tracking-app-locale" content="{{ $locale }}">
<div class="tracking-app-order-statistics">
    <div class="tracking-app-title">
        <div class="tracking-app-title-text">{{ __('ПОСЛЕДНИЕ ЗАКАЗЫ') }}</div>
    </div>
    <div class="tracking-app-content">
        <div class="tracking-app-box-container">
            <div class="tracking-app-box tracking-app-box-semispaced">
                <div class="tracking-app-box-title">{{ __('Найти Ваш заказ') }}</div>
                <form method="get" data-action="{{ rtrim($order_page_url,'/').'/' }}" id="tracking-app-order-search-form" class="tracking-app-form tracking-app-chat-form tracking-app-flex align-center">
                    <div class="tracking-app-column">
                        <div class="tracking-app-chat-form-message">
                            <input type="text" placeholder="{{ __('Введите ID вашего заказа') }}" name="number" required>
                        </div>
                    </div>
                    <div class="tracking-app-column">
                        <div class="tracking-app-chat-form-message">
                            <input type="text" placeholder="{{ __('Введите ваш Steam логин') }}" name="steamLogin" required>
                        </div>
                    </div>
                    <div class="tracking-app-column tracking-app-column-shrink">
                        <button class="tracking-app-button tracking-app-button-red tracking-app-button-lg">{{ __('Проверить') }}</button>
                    </div>
                </form>
            </div>
            <div class="tracking-app-box tracking-app-box-flat tracking-app-box-transparent">
                <div class="tracking-app-orders-list" id="tracking-app-orders-list" data-url="{{ $order_page_url }}">
                </div>
            </div>
            <div class="tracking-app-box tracking-app-box-semispaced">
                <div class="tracking-app-pagination-container" id="tracking-app-orders-list-pagination">
                </div>
            </div>
            <div class="tracking-app-box tracking-app-box-semispaced">
                <div class="tracking-app-box-title">{{ __('Последние результаты по заказам') }}</div>
                <div class="tracking-app-logs tracking-app-orders-logs">
                    <table>
                        @foreach($reports as $item)
                        <tr>
                            <td>
                                <span class="tracking-app-logs-id"><img src="{{ $_cdn.'/games/'.$item->icon }}"><b>#{{ $item->id }}</b></span>
                            </td>
                            <td>
                                <span class="tracking-app-logs-name">
                                    <?php Carbon\Carbon::setLocale(config('app.locale')) ?> 
                                    {{ Carbon\Carbon::parse($item->created_at)->diffForHumans() }} 
                                    <span class="tracking-app-color-red">{{ __('Бустер') }} #{{ $item->user_id }}</span> 
                                    {{ __('добавил результат') }} 
                                    @if($item->order_type == 1 or $item->order_type == 10)
                                    {{ $item->mmr_diff > 0 ? '+' : '-' }}{{ abs($item->mmr_diff) }} {{ __('ММР') }} | {{ $item->mmr_start }} - {{ $item->mmr_finish }} {{ __('ММР') }}
                                    @endif
                                    @if($item->order_type == 8)
                                    {{ $item->mmr_diff > 0 ? '+' : '-' }}{{ abs($item->mmr_diff) }} {{ __('РЕЙТИНГА') }} {{ __($item->grade_current) }} | {{ $item->mmr_start }} - {{ $item->mmr_finish }} {{ __('РЕЙТИНГА') }}
                                    @endif
                                    @if($item->order_type == 2 or $item->order_type == 5)
                                    {{ __($item->result ? 'Победа' : 'Поражение') }} | {{ $item->cali_games_done }} {{ __('из') }} {{ $item->cali_games_total }} {{ __('ИГР') }}
                                    @endif
                                    @if($item->order_type == 7)
                                    {{ __($item->result ? 'Победа' : 'Поражение') }} | {{ $item->cali_games_done }} {{ __('из') }} {{ $item->cali_games_total }} {{ __('ПОБЕД') }}
                                    @endif
                                    @if($item->order_type == 4)
                                    +{{ $item->hours }}{{ __('ч.') }} {{ $item->training_hours_done }} {{ __('из') }} {{ $item->training_hours }} {{ __('ЧАСОВ') }}
                                    @endif
                                    @if($item->order_type == 3)
                                    {{ __('ПОЛУЧЕН РАНГ') }} {{ __($item->rank_current) }} 
                                    @endif
                                    @if($item->order_type == 9)
                                    {{ __('ПОЛУЧЕН РАНГ') }} {{ __($item->chess_current) }} 
                                    @endif
                                    @if($item->order_type == 6)
                                    {{ __('ПОЛУЧЕН КУБОК') }} {{ __($item->cup_current) }}
                                    @endif
                                    @if($item->order_type == 11)
                                    +1 {{ __('квест') }} {{ __('из') }} {{ sizeof(json_decode($item->training_services)) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <button class="tracking-app-button tracking-app-button-white tracking-app-button-sm" onclick="_tracking_app_.orderDetails('{{$item->id}}')">{{ strtoupper(__('Подробнее')) }}</button>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
_tracking_app_ = new Object;
_tracking_app_.token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
_tracking_app_.locale = document.querySelector('meta[name="tracking-app-locale"]').getAttribute('content');
_tracking_app_.domain = '{{ url('/') }}';
_tracking_app_.endpoint = _tracking_app_.domain + '/' + (_tracking_app_.locale.length ? _tracking_app_.locale + "/" : "") + 'statistics';
_tracking_app_.http = function(req, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', _tracking_app_.endpoint);
    xhr.responseType = 'json';
    xhr.onload = function () {
        if(xhr.response) {
            var response = xhr.response;
            if(typeof response == 'string') {
                response = JSON.parse(response);
            }
            if(response.status == 'ok') {
                cb(null, response);
            } else {
                cb(response, null);
            }
        } else {
            cb(null, null);
        }
    };
    xhr.setRequestHeader("X-CSRF-TOKEN", _tracking_app_.token);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(req));
};
_tracking_app_.lastOrders = function(page) {
    page = page || 1;
    let container = document.getElementById('tracking-app-orders-list');
    let pagination = document.getElementById('tracking-app-orders-list-pagination');
    let url = container.getAttribute('data-url');
    this.http({action:'lastOrders', page: page, onclick: '_tracking_app_.lastOrders', order_url: url}, function(err, res) {
        if(res) {
            container.innerHTML = res.html;
            pagination.innerHTML = res.pagination;
        } else {
            container.innerHTML = err.message;
        }
    });
};
_tracking_app_.orderDetails = function(number) {
    var form = document.getElementById('tracking-app-order-search-form');
    var inumber = form.querySelector('[name="number"]');
    var ilogin = form.querySelector('[name="steamLogin"]');
    // inumber.value = number;
    setTimeout(function() { inumber.focus() }, 500);
    window.scrollTo({top: inumber.scrollHeight, behavior: "smooth"});
}
if(document.getElementById('tracking-app-orders-list')) {     
    _tracking_app_.lastOrders(1);
} 
document.getElementById('tracking-app-order-search-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = event.currentTarget;
    var action = form.getAttribute('data-action');
    var number = form.querySelector('[name="number"]').value;
    form.setAttribute('action', action + number);
    form.submit();
});
</script>
@endsection