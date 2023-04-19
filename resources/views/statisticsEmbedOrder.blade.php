@extends('@empty')
@section('content')
<link rel="stylesheet" href="{{ url('/public/css/fonts.css') }}" type="text/css" />
<link rel="stylesheet" href="{{ url('/public/css/app.css') }}" type="text/css" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="tracking-app-locale" content="{{ $locale }}">
@if(!$order)
<div class="tracking-app-order-statistics">
    <div class="tracking-app-title">
        <div class="tracking-app-title-text">{{ __('ПОИСК') }}</div>
    </div>
    <div class="tracking-app-content">
        <div class="tracking-app-box-container">
            <div class="tracking-app-box tracking-app-box-semispaced">
                <div class="tracking-app-box-title">{{ __('Найти Ваш заказ') }}</div>
                <form method="get" data-action="{{ rtrim($order_page_url,'/').'/' }}" id="order-search-form" class="tracking-app-form tracking-app-chat-form tracking-app-flex align-center">
                    <div class="tracking-app-column">
                        <div class="tracking-app-chat-form-message">
                            <input type="text" placeholder="{{ __('Введите ID вашего заказа') }}" name="number" value="{{ $number }}" required>
                        </div>
                    </div>
                    <div class="tracking-app-column">
                        <div class="tracking-app-chat-form-message">
                            <input type="text" placeholder="{{ __('Введите ваш Steam логин') }}" name="steamLogin" value="{{ $steamLogin }}" required>
                        </div>
                    </div>
                    <div class="tracking-app-column tracking-app-column-shrink">
                        <button class="tracking-app-button tracking-app-button-red tracking-app-button-lg">{{ __('Проверить') }}</button>
                    </div>
                </form>
            </div>
            <div class="tracking-app-box tracking-app-box-spaced">
                <div class="tracking-app-box-title tracking-app-text-center tracking-app-color-red" style="padding: 10vh 0;font-size: 20px;">{{ __('Мы не нашли заказ по вашему запросу') }}</div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('order-search-form').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = event.currentTarget;
    var action = form.getAttribute('data-action');
    var number = form.querySelector('[name="number"]').value;
    form.setAttribute('action', action + number);
    form.submit();
});    
</script>
@elseif($order)
<div class="tracking-app-order-statistics">
    <div class="tracking-app-title">
        <?php if($order->status == 1): ?>
        <div class="tracking-app-badge tracking-app-title-badge tracking-app-badge-light">{{ __($order->status_name) }}</div>
        <?php elseif($order->status == 7 || $order->status == 8): ?>
        <div class="tracking-app-badge tracking-app-title-badge tracking-app-badge-dark">{{ __($order->status_name) }}</div>
        <?php else: ?>
        <div class="tracking-app-badge tracking-app-title-badge tracking-app-badge-default">{{ __($order->status_name) }}</div>
        <?php endif; ?>
        <div class="tracking-app-title-text">
            {{ $order->system_number }}: {{ __($order->type_name) }} 
            <?php if($order->type == 1 or $order->type == 8 or $order->type == 10): ?>
            <?= $order->mmr_start ?>><?= $order->mmr_finish ?>
            <?php endif; ?>
            <?php if($order->type == 2 or $order->type == 5): ?>
            <?= $order->cali_games_total ?> {{ __('игр') }}
            <?php endif; ?>
            <?php if($order->type == 4): ?>
            <?= $order->training_hours ?> {{ __('ч.') }}
            <?php endif; ?>
            <?php if($order->type == 7): ?>
            <?= $order->cali_games_total ?> {{ __('побед') }}
            <?php endif; ?>
        </div>
    </div>
    <div class="tracking-app-content">
        @if($_user)
        <div class="tracking-app-box-container tracking-app-flex">
            <div class="tracking-app-column">
                <div class="tracking-app-box tracking-app-box-spaced">
                    @if($order->type == 9 or $order->type == 3)
                    <div class="tracking-app-flex tracking-app-chart-graph-buttons">
                        <div class="tracking-app-column">
                            <div class="tracking-app-box-title">{{ __('Текущее состояние заказа') }}</div>
                        </div>
                    </div>
                    <div class="tracking-app-order-chart-medals tracking-app-flex tracking-app-flex-justify">
                        <div class="tracking-app-column item box tracking-app-text-center">
                            <div class="tracking-app-order-step-main-subtitle tracking-app-text-center">{{ __('Стартовый ранг') }}</div>
                            <?php foreach($order->medals as $medal): ?>
                            <?php if($order->medal_start == $medal->id): ?>
                                <div class="tracking-app-medal-container tracking-app-text-center">
                                    @if($order->type == 9)
                                    <img src="{{ $_cdn.'dota/chess/'.$medal->image }}" style="height: 65px;">
                                    @else
                                    <img src="{{ $_cdn.'dota/ranks/'.$medal->image }}" style="height: 65px;">
                                    @endif                                    
                                </div>
                                <span class="value">{{ $medal->title }} {{ $medal->rank }}</span>
                            <?php break; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="tracking-app-column-shrink tracking-app-text-center">
                            <img class="slider-arrows" src="{{ url('/public/img/order-arrows-double.svg') }}" alt>
                        </div>
                        <div class="tracking-app-column item box tracking-app-text-center">
                            <div class="tracking-app-order-step-main-subtitle tracking-app-text-center">{{ __('Текущий ранг') }}</div>
                            <?php foreach($order->medals as $medal): ?>
                            <?php if($order->medal_current == $medal->id): ?>
                                <div class="tracking-app-medal-container tracking-app-text-center">
                                    @if($order->type == 9)
                                    <img src="{{ $_cdn.'dota/chess/'.$medal->image }}" style="height: 65px;">
                                    @else
                                    <img src="{{ $_cdn.'dota/ranks/'.$medal->image }}" style="height: 65px;">
                                    @endif                                    
                                </div>
                                <span class="value">{{ $medal->title }} {{ $medal->rank }}</span>
                            <?php break; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="tracking-app-column-shrink tracking-app-text-center">
                            <img class="slider-arrows" src="{{ url('/public/img/order-arrows-double.svg') }}" alt>
                        </div>
                        <div class="tracking-app-column item box tracking-app-text-center">
                            <div class="tracking-app-order-step-main-subtitle tracking-app-text-center">{{ __('Желаемый ранг') }}</div>
                            <?php foreach($order->medals as $medal): ?>
                            <?php if($order->medal_finish == $medal->id): ?>
                                <div class="tracking-app-medal-container tracking-app-text-center">
                                    @if($order->type == 9)
                                    <img src="{{ $_cdn.'dota/chess/'.$medal->image }}" style="height: 65px;">
                                    @else
                                    <img src="{{ $_cdn.'dota/ranks/'.$medal->image }}" style="height: 65px;">
                                    @endif                                    
                                </div>
                                <span class="value">{{ $medal->title }} {{ $medal->rank }}</span>
                            <?php break; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    @elseif($order->type == 6)
                    <div class="tracking-app-flex tracking-app-chart-graph-buttons">
                        <div class="tracking-app-column">
                            <div class="tracking-app-box-title">{{ __('Текущее состояние заказа') }}</div>
                        </div>
                    </div>
                    <div class="tracking-app-order-chart-medals tracking-app-flex tracking-app-flex-justify">
                        <div class="tracking-app-column item box tracking-app-text-center">
                            <div class="tracking-app-order-step-main-subtitle tracking-app-text-center">{{ __('Желаемый кубок') }}</div>
                            <?php foreach($order->medals as $medal): ?>
                            <?php if($order->medal_finish == $medal->id): ?>
                                <div class="tracking-app-medal-container tracking-app-text-center">
                                    <img src="{{ $_cdn.'dota/cups/'.$medal->image }}" style="height: 65px;">                                   
                                </div>
                                <span class="value">{{ $medal->title }}</span>
                            <?php break; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="tracking-app-column-shrink tracking-app-text-center">
                            <img class="slider-arrows" src="{{ url('/public/img/order-arrows-double.svg') }}" alt>
                        </div>
                        <div class="tracking-app-column item box tracking-app-text-center">
                            <div class="tracking-app-order-step-main-subtitle tracking-app-text-center">{{ __('Полученный кубок') }}</div>
                            <?php foreach($order->medals as $medal): ?>
                            <?php if($order->medal_current == $medal->id): ?>
                                <div class="tracking-app-medal-container tracking-app-text-center">
                                    <img src="{{ $_cdn.'dota/cups/'.$medal->image }}" style="height: 65px;">                                   
                                </div>
                                <span class="value">{{ $medal->title }}</span>
                            <?php break; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    @elseif($order->type == 11)
                    <div class="tracking-app-flex tracking-app-chart-graph-buttons">
                        <div class="tracking-app-column">
                            <div class="tracking-app-box-title">{{ __('Текущее состояние заказа') }}</div>
                        </div>
                    </div>
                    <div class="tracking-app-order-chart-medals tracking-app-flex tracking-app-flex-justify">
                        <div class="tracking-app-quest-map" quests-ordered="{{ json_encode($order->training_services) }}" quests-done="{{ json_encode($order->services_done) }}">
                            {!! $order->svg !!}                                             
                        </div>
                    </div>
                    @else
                    <div class="tracking-app-flex tracking-app-chart-graph-buttons">
                        <div class="tracking-app-column">
                            <div class="tracking-app-box-title">{{ __('Динамика изменения заказа') }}</div>
                        </div>
                        <div class="tracking-app-column tracking-app-column-shrink">
                            <div class="tracking-app-tab-buttons tracking-app-tab-buttons-sm">
                                <button type="button" class="tracking-app-chart-graph-select active" onclick="_tracking_app_.chartGraphRebuild(event)" data-start-time="<?= strtotime(date('Y-m-d')) ?>">{{ __('За день') }}</button>
                                <button type="button" class="tracking-app-chart-graph-select" onclick="_tracking_app_.chartGraphRebuild(event)" data-start-time="<?= strtotime(date('Y-m-d', strtotime('-'.date('w').' days'))) ?>">{{ __('За неделю') }}</button>
                                <button type="button" class="tracking-app-chart-graph-select" onclick="_tracking_app_.chartGraphRebuild(event)" data-start-time="<?= strtotime($order->created_at) ?>">{{ __('За все время') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="tracking-app-chart-graph">
                        <div id="order-charts"></div>
                    </div>
                    @endif
                </div>
                <div class="tracking-app-box tracking-app-box-spaced">
                    <div class="tracking-app-box-title">{{ __('Лог заказа') }}</div>
                    <div class="tracking-app-logs">
                        <table>
                            @foreach($order->logs as $item)
                            <tr>
                                <td><span class="tracking-app-logs-time">{{ date('d.m.y H:i', strtotime($item->created_at)) }}</span></td>
                                <td><span class="tracking-app-logs-name">{{ __($item->details) }}</span></td>
                                <td><span class="tracking-app-logs-name">{{ $item->action_id == 11 ? __('Отчет').' #'.filter_var($item->message, FILTER_SANITIZE_NUMBER_INT) : '' }}</span></td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="tracking-app-box tracking-app-box-spaced">
                    <div class="tracking-app-box-title">{{ __('История игр') }}</div>
                    <div class="tracking-app-games-history">
                        <div class="tracking-app-games-history-empty" style="display: none;">
                            <p>{!! __('Мы учитываем игры только в том случае, если у вас активирована опция "<b>Expose Public Match Data</b>" в настройках клиента дота 2. Вы можете проверить это, открыв клиент игры и перейдя в дополнительные настройки') !!}</p>    
                            <a class="tracking-app-button tracking-app-button-red" href="https://store.steampowered.com/account/preferences/" target="_blank">{{ __('ПЕРЕЙТИ В ПРОФИЛЬ') }}</a>                                        
                        </div>
                        <table class="tracking-app-games-history-table" data-url="{{ $order->dotabuff }}">
                            <tbody>  
                            <tr>
                                <td><h3 class="tracking-app-color-red tracking-app-text-center">{{ __('Поиск игр') }} ...</h3></td>
                            </tr>                            
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($_user)
                <div class="tracking-app-box tracking-app-box-spaced">
                    <div class="tracking-app-tab-buttons">
                        <button type="button" class="active">{{ __('Чат с бустером') }}</button>
                    </div>
                    <div class="tracking-app-chat">
                        <div class="tracking-app-chat-messages"></div>
                        <div class="tracking-app-chat-form tracking-app-flex align-center">
                            <div class="tracking-app-column">
                                <div class="tracking-app-chat-form-message">
                                    <label class="attachments">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" xml:space="preserve">
                                        <g><path d="M283.8,990c-63.7,0-123.1-27.8-166.1-72c-83.3-85.8-106.5-235.5,10.3-355.7c68.4-70.5,342.5-352.6,479.2-493.5c48.6-50,110.4-69.3,169.6-53c58.1,16,105.7,65.1,121.3,124.9c15.9,61-2.9,124.6-51.5,174.6l-458.3,472c-26.1,26.9-55.7,42.9-85.4,46.1c-29.4,3.2-57.5-6.6-77.2-26.8c-35.6-36.7-40.7-105.8,18.6-166.8l321.9-331.5c13.3-13.6,34.6-13.6,47.9,0c13.2,13.6,13.2,35.7,0,49.3L292.1,689c-27.9,28.7-30.4,56-18.6,68.2c5.2,5.3,13.2,7.8,22.2,6.8c14-1.5,29.9-10.8,44.7-26.1l458.3-472c31.4-32.4,43.5-70.5,34-107.3c-9.5-36.2-38.3-65.9-73.4-75.6c-35.7-9.9-72.7,2.6-104.2,34.9C518.3,258.9,244.2,541.1,175.8,611.5c-89.3,92-67.9,197.8-10.3,257.1c57.7,59.4,160.4,81.4,249.7-10.5l479.2-493.6c13.3-13.6,34.6-13.6,47.9,0c13.3,13.6,13.3,35.7,0,49.3L463.1,907.4C406.6,965.6,343.4,990,283.8,990"/></g>
                                        </svg>
                                        <input type="file" class="tracking-app-chat-files">
                                    </label>
                                    <input type="text" class="tracking-app-form-input-with-icon tracking-app-chat-area" placeholder="{{ __('Ввести сообщение') }}">
                                </div>
                            </div>
                            <div class="tracking-app-column tracking-app-column-shrink">
                                <button class="tracking-app-button tracking-app-button-red tracking-app-chat-button">{{ __('Отправить') }}</button>
                            </div>
                        </div>
                    </div>

                </div>
                @endif
            </div>
            <div class="tracking-app-column tracking-app-column-shrink tracking-app-aside-box-container">
                @if(in_array($order->type, [1,2,5,7,8,10]))
                <div class="tracking-app-box tracking-app-aside-box">
                    <div class="tracking-app-winrate-chart"> 
                        <svg width="100%" height="100%" viewBox="0 0 42 42">
                          <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="#fff"></circle>
                          <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#303030" stroke-width="4"></circle>
                          <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#e74d3d" stroke-width="4" stroke-dasharray="<?= $order->winrate ?> <?= 100 - $order->winrate ?>" stroke-dashoffset="0" class="tracking-app-animate-stroke"></circle>
                        </svg>                                  
                    </div>
                    <div class="tracking-app-winrate-info tracking-app-flex">
                        <div class="tracking-app-column">
                            <div>{{ __('Побед') }}</div>
                            <div class="tracking-app-winrate-value tracking-app-color-red"><?= $order->winrate ?>%</div>
                        </div>
                        <div class="tracking-app-column">
                            <div>{{ __('Поражений') }}</div>
                            <div class="tracking-app-winrate-value tracking-app-color-black"><?= 100 - $order->winrate ?>%</div>
                        </div>
                    </div>
                </div>                
                @endif
                <div class="tracking-app-box tracking-app-aside-box">
                    <div class="tracking-app-box-title">{{ __('Информация') }}</div>
                    <div class="tracking-app-info-table">
                        <table>
                            @if($order->type == 1 or $order->type == 10)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('СЕЙЧАС ММР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100 / ($order->mmr_finish - $order->mmr_start) * $order->mmr_boosted }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->mmr_start + $order->mmr_boosted }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('КОНЕЧНЫЙ ММР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->mmr_finish }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ОСТАЛОСЬ ММР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->mmr_finish - ($order->mmr_start + $order->mmr_boosted) }}</span></td></tr>
                            @endif
                            @if($order->type == 8)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('РЕЙТИНГ СЕЙЧАС') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100 / ($order->mmr_finish - $order->mmr_start) * $order->mmr_boosted }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->grade_current) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('РЕЙТИНГ СТАРТОВЫЙ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->grade_start) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('РЕЙТИНГ ФИНАЛЬНЫЙ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->grade_finish) }}</span></td></tr>
                            @endif
                            @if($order->type == 2 || $order->type == 5)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('СЫГРАНО ИГР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100 / $order->cali_games_total * $order->cali_games_done }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_done }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ВСЕГО ИГР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_total }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ОСТАЛОСЬ ИГР') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_total - $order->cali_games_done }}</span></td></tr>
                            @endif
                            @if($order->type == 7)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ПОЛУЧЕНО ПОБЕД') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100 / $order->cali_games_total * $order->cali_games_done }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_done }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ВСЕГО ПОБЕД') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_total }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ОСТАЛОСЬ ПОБЕД') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->cali_games_total - $order->cali_games_done }}</span></td></tr>
                            @endif
                            @if($order->type == 6)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ТЕКУЩИЙ КУБОК') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100/$order->medal_finish*$order->medal_current }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->cup_current ?? 'Не получен') }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ЖЕЛАЕМЫЙ КУБОК') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->cup_finish) }}</span></td></tr>
                            @endif
                            @if($order->type == 3)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ТЕКУЩИЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100/$order->medal_finish-$order->medal_start*$order->medal_current-$order->medal_start }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->rank_current) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('СТАРТОВЫЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->rank_start) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ЖЕЛАЕМЫЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->rank_finish) }}</span></td></tr>
                            @endif
                            @if($order->type == 9)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ТЕКУЩИЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100/$order->medal_finish-$order->medal_start*$order->medal_current-$order->medal_start }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->chess_current) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('СТАРТОВЫЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->chess_start) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ЖЕЛАЕМЫЙ РАНГ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ __($order->chess_finish) }}</span></td></tr>
                            @endif
                            @if($order->type == 4)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('СЫГРАНО ЧАСОВ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100 / $order->training_hours * $order->training_hours_done }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->training_hours_done }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ВСЕГО ЧАСОВ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->training_hours }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('ОСТАЛОСЬ ЧАСОВ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ $order->training_hours - $order->training_hours_done }}</span></td></tr>
                            @endif
                            @if($order->type == 11)
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('КВЕСТОВ ВЫПОЛНЕНО') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: {{ 100/sizeof($order->training_services)*sizeof($order->services_done) }}%"></span></div></td><td><span class="tracking-app-info-table-value">{{ sizeof($order->services_done) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('КВЕСТОВ ВСЕГО') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ sizeof($order->training_services) }}</span></td></tr>
                                <tr><td colspan="99"><span class="tracking-app-info-table-name">{{ __('КВЕСТОВ ОСТАЛОСЬ') }}</span></td></tr>
                                <tr><td><div class="tracking-app-progress-bar"><span style="width: 0%"></span></div></td><td><span class="tracking-app-info-table-value">{{ sizeof($order->training_services) - sizeof($order->services_done) }}</span></td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="tracking-app-box-title">{{ __('Дополнительно') }}</div>
                    <div class="tracking-app-info-table">
                        <table>
                            @if($order->give_account)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Предоставить аккаунт') }}</span></td></tr>
                            @endif
                            @if($order->do_faster)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Выполнить быстрее') }}</span></td></tr>
                            @endif
                            @if($order->play_with_booster)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Играть с бустером') }}</span></td></tr>
                            @endif
                            @if($order->cali_warranty)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Гарантия винрейта') }}</span></td></tr>
                            @endif
                            @if($order->heroes)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Играть на моих героях') }}</span></td></tr>
                            @endif
                            @if($order->heroes_ban)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Не играть на выбранных героях') }}</span></td></tr>
                            @endif
                            @if($order->lanes)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Играть на выбранных линиях') }}</span></td></tr>
                            @endif
                            @if($order->servers)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-2.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Играть на указанных серверах') }}</span></td></tr>
                            @endif
                            @if($order->security_variant != 1)
                            <tr><td><span class="tracking-app-info-table-icon"><img src="{{ url('/public/img/service-icon-1.png') }}"></span><span class="tracking-app-info-table-additional">{{ __('Не отключать Steam Guard') }}</span></td></tr>
                            @endif
                            @if($order->security_variant == 1&&!$order->heroes&&!$order->heroes_ban&&!$order->lanes&&!$order->servers&&!$order->give_account&&!$order->do_faster&&!$order->play_with_booster&&!$order->cali_warranty)
                            <tr><td><span class="tracking-app-info-table-additional">{{ __('Без опций') }}</span></td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="tracking-app-box-container">
            <div class="tracking-app-box tracking-app-box-spaced">
                <div class="tracking-app-games-history">
                    <div class="tracking-app-games-history-empty">
                        <p>{!! __('К сожалению вы ввели неправильный Steam Login') !!}</p>    
                        <button class="tracking-app-button tracking-app-button-red" onclick="window.location.href='{{ $order_page_url }}'">{{ __('ВЕРНУТЬСЯ') }}</button>                                        
                    </div>
                </div>
            </div>            
        </div>
        @endif
    </div>
</div>
<script src="{{ url('/public/js/apexcharts.min.js') }}"></script>
<script>
_tracking_app_ = new Object;
_tracking_app_.datasets = <?= json_encode($order->datasets) ?>;
_tracking_app_.today = <?= strtotime(date('Y-m-d')) ?>;
_tracking_app_.otype = <?= $order->type ?>;
_tracking_app_.chart = null;
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
_tracking_app_.chartGraphRebuild = function(event) {
    if(event.target.classList.contains('active')) return;
    document.querySelector('.tracking-app-chart-graph-select.active').classList.remove('active');
    event.target.classList.add('active');
    _tracking_app_.renderLineChart(document.querySelector("#order-charts"));
};
_tracking_app_.renderLineChart = function(container) {
    if(!container) return;
    var start_time = document.querySelector('.tracking-app-chart-graph-select.active').getAttribute('data-start-time');
    var series = [];
    for(var x in this.datasets) {
        if(this.datasets[x].date >= start_time) {
            series.push({y: this.datasets[x].value, x:_tracking_app_.unixToDate(this.datasets[x].date * 1000, this.today == start_time)});
        }
    }
    if(_tracking_app_.chart) {
        _tracking_app_.chart.updateSeries([{ data: series }]);
        return;
    }
    _tracking_app_.chart = new ApexCharts(
        container,
        {
            series: [{ data: series }],
            xaxis: { 
                axisBorder: {show: false},
                axisTicks: {show: false},
                crosshairs: {
                    show: true,
                    width: 50,
                    position: 'front',
                    opacity: 0.2,   
                    fill: {
                        type: 'solid',
                        color: '#CA4335',
                    },
                },
                tooltip: {
                    enabled: false
                }
            },
            chart: {
                height: 350,
                type: 'line',
                zoom: { enabled: false },
                toolbar: { show: false },
                fontFamily: 'Lato, Helvetica, Arial, sans-serif',
            },
            dataLabels: { 
                enabled: false,
                offsetY: 30,
                offsetx: 15,
            },
            grid: {
                row: { opacity: 0.7 },
                borderColor: '#f1f1f1',
                borderWidth: 1,
            },
            colors: ['#F23441'],
            legend: { show: false, floating: false },
            stroke: {
                show: true,
                curve: 'straight',
                lineCap: 'square',
                colors: ['#F23441'],
                width: 2,
                dashArray: 0,      
            },
            tooltip: {    
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    var current = series[seriesIndex][dataPointIndex];
                    var previous = (series[seriesIndex][dataPointIndex - 1] || series[seriesIndex][0]);
                    var win =  current == previous ? null : (current > previous ? true : false);
                    var diff = current - previous;
                    return diff == 0 ? '' : '<div class="tracking-app-chart-graph-tooltip">' + 
                        '<span>' + (win == null ? ' ' : win == true ? '{{ __('Победа') }}' : '{{ __('Поражение') }}') + '</span>' + 
                        '<strong>' + (diff > 0 ? '+' : '-') + Math.abs(diff) + 
                        (_tracking_app_.otype == 1 || _tracking_app_.otype == 10 ? ' {{__('ММР')}}' : 
                        (_tracking_app_.otype == 2 || _tracking_app_.otype == 5 ? ' {{__('игр.')}}' : 
                        (_tracking_app_.otype == 8 ? ' {{__('рейтинга')}}' :
                        (_tracking_app_.otype == 4 ? ' {{__('час.')}}' : '')))) 
                        + '</strong>' + 
                    '</div>';
                },
                enabled: true,
                shared: false,
                theme: false,
                followCursor: false,
                marker: { show: false },
            },
            markers: {
                size: 0,
                hover: {
                    sizeOffset: 3
                }
            },
        }
    );
    _tracking_app_.chart.render();     
};
_tracking_app_.renderProgressCharts = function(container) {
    if(!container) return;
    for(var x=0,max=container.length;x<max;x++) {
        var chart = container[x], values = chart.getAttribute('data-values') || '';
        var values = values.split('/');
        var sum = values.reduce(function(a, b) { return parseFloat(a) + parseFloat(b); });
        for(var i in values) {
            chart.childNodes[i].style.width = Math.round(100 / sum * values[i]) + "%";
        }
    }    
};
_tracking_app_.renderQuestMap = function(container) {
    if(!container) return;
    let ordered = (JSON.parse(container.getAttribute('quests-ordered')) || []).map(function(e) { return ''+e; });
    let done = (JSON.parse(container.getAttribute('quests-done')) || []).map(function(e) { return ''+e; });
    Array.prototype.forEach.call(container.querySelectorAll('circle[data-quest]'), function(el) {
        var id = el.getAttribute('data-quest');
        if(ordered.indexOf(id) >= 0) el.classList.add("selected"); 
        if(done.indexOf(id) >= 0) el.classList.add("done"); 
    });   
};
_tracking_app_.loadMatchHistory = function() {
    var container = document.querySelector(".tracking-app-games-history-table");
    var empty = document.querySelector('.tracking-app-games-history-empty');
    var url = container.getAttribute('data-url') || null;
    _tracking_app_.http({action:'match_history', url: url}, function(err, res) {
        if(res) {
            container.style.display = 'table';
            empty.style.display = 'none';
            container.innerHTML = res.html;
            _tracking_app_.renderProgressCharts(document.getElementsByClassName('tracking-app-chart-bar-stacked'));
        } else {
            container.style.display = 'none';
            empty.style.display = 'block';
        }
    });
}
_tracking_app_.unixToDate = function(timestamp, timeonly) {
    const months = ["{{__('янв')}}","{{__('фев')}}","{{__('мар')}}","{{__('апр')}}","{{__('май')}}","{{__('июн')}}","{{__('июл')}}","{{__('авг')}}","{{__('сен')}}","{{__('окт')}}","{{__('ноя')}}","{{__('дек')}}"];
    var dt = new Date(timestamp);
    var date = dt.getDate() + " " + months[dt.getMonth()];
    var time = dt.getHours()+":"+('0' + dt.getMinutes()).slice(-2);
    return timeonly ? time : date + " " + time;
};
_tracking_app_.renderQuestMap(document.querySelector('.tracking-app-quest-map'));
_tracking_app_.renderLineChart(document.querySelector("#order-charts"));
_tracking_app_.renderProgressCharts(document.querySelector('.tracking-app-chart-bar-stacked'));
_tracking_app_.loadMatchHistory();
</script>


@if($_user)
<script src="{{ url('/public/js/socket.io.js') }}"></script>
<script> 
    window.ws_remote = '{{ $_api_ws }}'; 
    window.ws_storage = '{{ $_cdn.'shared/' }}'; 
    window.ws_avatars = '{{ $_cdn.'avatars/' }}'; 
    window.ws_user = {avatar: '<?= $_cdn.'avatars/'.$_user->avatar ?>', name: '<?= $_user->nick_name ?>', id: '<?= $_user->id ?>'};
    window.ws_room = '{{ $order->system_number }}';                        
    _tracking_app_.ws_remote = window.ws_remote || null;
    _tracking_app_.ws_user = window.ws_user || null;
    _tracking_app_.ws_storage = window.ws_storage || null;
    _tracking_app_.ws_room = window.ws_room || null;
    _tracking_app_.SocketEvent = {
      CONNECT: 'user connected',
      DISCONNECT: 'disconnect',
      MESSAGE: 'message',
      JOINCHAT: 'join chat room',
      JOIN: 'join room',
      LEAVE: 'leave room',
      ROOMJOINED: 'room joined',
      ROOMMSG: 'room message',
      ROOMMSGS: 'room messages',
    };   

    _tracking_app_.joinChat = function() {
        var overlay = document.querySelector('.tracking-app-chat-messages');
        var area = document.querySelector('.tracking-app-chat-area');
        var button = document.querySelector('.tracking-app-chat-button');
        var files = document.querySelector('.tracking-app-chat-files');
        files.addEventListener('change', function(e) {
            _tracking_app_.fileToBuffer(e.currentTarget, function(file) {
                _tracking_app_.sendMessage(area, file);
                files.value = '';
            });
        });
        button.addEventListener('click', function(e) {
            _tracking_app_.sendMessage(area);
        });
        area.addEventListener('keyup', function(e) {
            if(e.keyCode == 13) _tracking_app_.sendMessage(area);
        });
        if(!_tracking_app_.socket) _tracking_app_.socket = io(_tracking_app_.ws_remote);
        _tracking_app_.socket.emit(_tracking_app_.SocketEvent.JOINCHAT, {room: _tracking_app_.ws_room, nick_name: _tracking_app_.ws_user.name});
        _tracking_app_.socket.off(_tracking_app_.SocketEvent.ROOMMSGS).on(_tracking_app_.SocketEvent.ROOMMSGS, function(data) {
            var html = '';
            var date = '';
            for(var x in data) {
                var mdate = new Date(data[x].date).getDate(); 
                if(mdate != date) html += _tracking_app_.buildSeparator(data[x].date);
                date = mdate;
                html += _tracking_app_.buildMessage(data[x]);
            }
            overlay.innerHTML = html;
            overlay.scrollTop = overlay.scrollHeight;
        });
        _tracking_app_.socket.off(_tracking_app_.SocketEvent.ROOMMSG).on(_tracking_app_.SocketEvent.ROOMMSG, function(data) {
            var html = _tracking_app_.buildMessage(data);
            overlay.innerHTML = overlay.innerHTML + html;
            overlay.scrollTop = overlay.scrollHeight;
        });
    };

    _tracking_app_.buildSeparator = function(date) {
        var months = ["{{__('Января')}}","{{__('Февраля')}}","{{__('Марта')}}","{{__('Апреля')}}","{{__('Мая')}}","{{__('Июня')}}","{{__('Июля')}}","{{__('Августа')}}","{{__('Сентября')}}","{{__('Октября')}}","{{__('Ноября')}}","{{__('Декабря')}}"];
        var dt = new Date(date);
        var date = dt.getDate() + " " + months[dt.getMonth()];
        return '<div class="tracking-app-chat-date-separator"><span>'+date+'</span></div>';
    }

    _tracking_app_.buildMessage = function(message) {
        return '<div class="tracking-app-chat-message '+(_tracking_app_.ws_user.id == message.user.id ? 'self' : '')+'">'+
            '<div class="cell image">'+
                '<div class="avatar"><img src="'+(message.user.avatar)+'"></div>'+
            '</div>'+
            '<div class="cell content">'+
                '<p class="person">'+(_tracking_app_.ws_user.id == message.user.id ? '{{ __('Вы') }}' : '{{ __('Бустер') }}' + ' №'+message.user.id)+', '+
                '<span class="created-at">'+_tracking_app_.unixToDate(message.date, true)+'</span></p>'+
                '<div class="message-text">'+message.text+
                '<div class="files">'+(message.file ? '<a target="_blank" href="'+_tracking_app_.ws_storage+message.file+'">'+message.file+'</a>' : '')+'</div>'+
                '</div>'+
            '</div>'+
        '</div>'; 
    };

    _tracking_app_.sendMessage = function(area, file) {
        var imessage = area;
        var message = imessage.value;
        if(!file && (!message || !message.trim().length)) return imessage.classList.add('required').focus();
        imessage.classList.remove('required');
        var data = {
            user: { id: _tracking_app_.ws_user.id, avatar: _tracking_app_.ws_user.avatar, name: _tracking_app_.ws_user.name },
            text: message,
            date: new Date,
            room: _tracking_app_.ws_room,
        };
        if(file) data.file = file;
        _tracking_app_.socket.emit(_tracking_app_.SocketEvent.ROOMMSG, data); 
        imessage.value = '';
    };

    _tracking_app_.fileToBuffer = function(e, cb) {
        var file = e.files[0];
        if(!file) return cb(null);
        var reader = new FileReader();
        reader.readAsArrayBuffer(file); 
        reader.onload = function(e) { 
            cb({ name: file.name, size: file.size, type: file.type, data: reader.result });
        };
    };
    _tracking_app_.joinChat();
</script>
@endif
@endif
@endsection
