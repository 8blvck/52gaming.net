@extends('@app')
@section('content')
    <main>
        <div class="page-title flawless">
            <div class="container">
                <div class="row">
                    <div class="col-xs-0 col-sm-3 col-md-4 col-lg-4 transparent-3-xs">
                        <div class="image">
                            <img src="{{ url('/public/img/dragonknight.png') }}" alt="page-title">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-9 col-md-8 col-lg-8">
                        <div class="text">
                            <h3 class="title">{{ __('УМЕЕШЬ ХОРОШО ИГРАТЬ В ИГРЫ И ХОЧЕШЬ НА ЭТОМ ЗАРАБОТАТЬ?') }}</h3>
                            <p class="description">{{ __('Ознакомься со списком активных вакансий ниже и если считаешь, что ты нам подходишь - оставляй заявку. Тебе нужно будет пройти тест, чтобы доказать свой уровень, после которого ты сразу начнешь зарабатывать!') }}</p>                           
                        </div>
                    </div>                  
                </div>
            </div>
        </div>
        @if($advantages)
        <div class="page-container">
            <div class="container">
                <div class="caption">
                    <h4>{{ __('ПРЕИМУЩЕСТВА РАБОТЫ С НАМИ') }}</h4>
                </div>
                <div class="row">
                    @foreach($advantages as $item)
                    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                        <div class="advantage-item">
                            <div class="image">
                                <span class="icon">
                                    <img src="{{ url('/public/img/'.$item->image) }}" alt="{{ __($item->caption) }}">
                                </span>
                            </div>
                            <div class="text">
                                <h6>{{ __($item->caption) }}</h6>
                                <p>{{ __($item->description) }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <div class="page-container bg-fff">
            <div class="container">
                <div class="caption">
                    <h4>{{ __('ВСЕГО ВЫПЛАЧЕНО БУСТЕРАМ:') }} <span id="boosters-payout-amount">XXXXX</span> <span id="boosters-payout-currency">RUB</span></h4>
                </div>
                <div class="app-table" id="boosters-top-table">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Номер') }}</th>
                                <th colspan="2">{{ __('Бустер') }}</th>
                                <th>{{ __('Заработал за 30 дней') }}</th>
                                <th>{{ __('Заработал всего') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="position">1</span></td>
                                <td class="w40px"><span class="avatar"><img src="{{ url('/public/img/avatar.png') }}" alt="avatar.png"></span></td>
                                <td class="w30"><span class="username color-black">Nickname</span></td>
                                <td class="w30"><span class="money color-red">$556.22</span></td>
                                <td class="w20"><span class="money color-black">$2722.14</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="page-container">
            <div class="container">
                <div class="caption">
                    <h4>{{ __('НАШИ ВАКАНСИИ') }}</h4>
                    <p>{{ __('Подходишь по вакансии из списка ниже?') }} <br> {{ __('Заполни форму ниже и жди сообщения от нашего специалиста.') }}</p>
                </div>
                @if($games)
                <div class="vacancies">
                    <div class="content">
                        <div class="games-selector">
                            <button class="prev"><i class="xicons icon-left-open"></i></button>
                            <button class="next"><i class="xicons icon-right-open"></i></button>
                            <div class="slick" data-shown="6">
                                @foreach($games as $x => $game)
                                <a class="item {{ !$x?'active':''}} {{ sizeof($game->vacancies)?'':'soon'}}" href="#vacancies-{{ $game->id }}"  role="tab" data-toggle="tab">
                                    <div class="image">
                                        <img src="{{ url($_cdn.'games/'.$game->png) }}">
                                        @if(!sizeof($game->vacancies))
                                        <span class="ribbon">{{ __('СКОРО') }}</span>
                                        @endif
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>                          

                        <div class="tab-content">

                            @foreach($games as $x => $game)
                            <!-- tab -->
                            <div class="tab-pane {{ !$x?'active':''}}" id="vacancies-{{ $game->id }}">
                                <h6 class="panel-title">{{ __('Все вакансии и условия') }} {{ $game->pub }}</h6>
                                <div class="panel-group faqs-list" id="vacancies-parent-{{ $game->id }}">
                                    @if($game->vacancies)
                                    @foreach($game->vacancies as $i => $vacancy)
                                    <!-- accordeon -->
                                    <div class="panel">
                                        <a href="#vacancies-{{ $game->id }}-{{ $vacancy->id }}" class="{{ !$i?'':'collapsed'}}" data-toggle="collapse" data-parent="#vacancies-parent-{{ $game->id }}">
                                          <div class="panel-heading">
                                            <h6>{{ __($vacancy->name) }}</h6>
                                            <span class="toggler"></span>
                                          </div>
                                        </a>
                                        <div id="vacancies-{{ $game->id }}-{{ $vacancy->id }}" class="collapse {{ !$i?'in':''}}">
                                          <div class="panel-body">
                                            <h6>{{ __('Требования:') }}</h6>
                                            <dl>
                                                @if($vacancy->requirementsa)
                                                @foreach($vacancy->requirementsa as $y => $req)
                                                <dt>{{ __($req) }}</dt>
                                                @endforeach
                                                @else
                                                <li>{{ __('Уточняйте у администрации') }}</li>
                                                @endif
                                            </dl>
                                          </div>
                                        </div>
                                    </div>
                                    <!-- accordeon -->
                                    @endforeach
                                    @else
                                    <h6 class="text-center fs25">{{ __('ОЖИДАЮТСЯ') }}</h6>
                                    @endif
                                </div>
                            </div>
                            <!-- tab -->
                            @endforeach
                        </div>    
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="page-container bg-fff">
            <div class="container">
                <div class="caption">
                    <h4>{{ __('ФОРМА ОБРАТНОЙ СВЯЗИ') }}</h4>
                </div>
                <div class="form transparent">
                    <form class="row" id="booster-request-form">
                        <div class="response" data-default="{{ __('Обработка') }}">{{ __('Обработка') }}</div>
                        <fieldset>
                            <div class="col-xs-12">
                                <label>
                                    <p>{{ __('Выберите интересующие Вас игры') }}</p>
                                </label>
                                <label class="select">
                                    <select name="game">
                                        <option value="0">- {{ __('Выбрать') }} -</option>
                                        @foreach($games as $game)
                                            <option value="{{ $game->id }}">{{ $game->pub }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Имя') }}</p></label>
                                <label class="">
                                    <input type="text" name="username" placeholder="{{ __('Введите ваше имя') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Ваша почта') }}</p></label>
                                <label class="">
                                    <input type="text" name="email" placeholder="{{ __('Введите ваш E-mail') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Ваш никнейм в игре') }}</p></label>
                                <label class="">
                                    <input type="text" name="nickname" placeholder="{{ __('Введите ваш никнейм') }}">
                                </label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Ссылка на Ваши социальные сети') }}</p></label>
                                <label class="socials ">
                                    <i class="icon xicons icon-vk"></i>
                                    <input type="text" name="vkontakte" placeholder="{{ __('Укажите ссылку на вашу страницу Вконтакте') }}">
                                </label>
                                <label class="socials ">
                                    <i class="icon xicons icon-facebook"></i>
                                    <input type="text" name="facebook" placeholder="{{ __('Укажите ссылку на вашу страницу Facebook') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Ваши дополнительные контакты') }}</p></label>
                                <label class="socials ">
                                    <i class="icon xicons icon-discord"></i>
                                    <input type="text" name="discord" placeholder="{{ __('Укажите ID вашего discord аккаунта') }}">
                                </label>
                                <label class="socials ">
                                    <i class="icon xicons icon-skype"></i>
                                    <input type="text" name="skype" placeholder="{{ __('Укажите ID вашего skype аккаунта') }}">
                                </label>
                                <label class="socials ">
                                    <i class="icon xicons icon-telegram"></i>
                                    <input type="text" name="telegram" placeholder="{{ __('Укажите ID вашего telegram аккаунта') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('У Вас есть опыт выполнения заказов в Dota 2?') }}</p></label>
                                <label class="radio"><input type="radio" name="exp" value="1"><i class="checkmark"></i><b>{{ __('Да') }}</b></label>
                                <label class="radio"><input type="radio" name="exp" value="0" checked><i class="checkmark"></i><b>{{ __('Нет') }}</b></label>
                                <label class="">
                                    <input type="text" name="exp_source" placeholder="{{ __('Укажите где вы работали ранее') }}">
                                    <small class="note">{{ __('Ссылка или название сервиса, ссылка на ваш профиль') }}</small>
                                </label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Cколько часов в день вы играете?') }}</p></label>
                                <label class="">
                                    <input type="number" min="1" max="24" name="play_hours">
                                    <small class="note">{{ __('Максимально допустимое число часов - 24') }}</small>
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label><p>{{ __('Сколько дней в неделю вы играете?') }}</p></label>
                                <label class="">
                                    <input type="number" min="1" max="7" name="play_week">
                                    <small class="note">{{ __('Максимально допустимое число дней - 7') }}</small>
                                </label>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="col-xs-12">
                                <label><p>{{ __('Комментарий') }}</p></label>
                                <label>
                                    <textarea placeholder="{{ __('Введите сообщение') }}" rows="5" name="comment"></textarea>
                                    <small class="note">{{ __('Здесь вы можете указать информацию, которая может быть нам полезна при рассмотрении вашей заявки') }}</small>
                                </label>
                            </div>
                        </fieldset>
                        <div class="col-xs-12">
                            <button type="button" class="btn btn-code-red btn-xl filled submit" onclick="_.boosterRequest(event)">{{ __('Отправить') }}</button>
                        </div>                  
                    </form> 
                </div>                  
            </div>          
        </div>
    </main>
@endsection
