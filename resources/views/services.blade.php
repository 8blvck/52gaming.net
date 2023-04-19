@extends('@app')
@section('content')
    <main>
        <div class="page-title flawless">
            <div class="container">
                <div class="row">
                    <div class="col-xs-0 col-sm-3 col-md-4 col-lg-4 transparent-3-xs">
                        <div class="image">
                            <img src="{{ url('/public/img/sven.png') }}" alt="{{ $_page->seo_title }}">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-9 col-md-8 col-lg-8">
                        <div class="text">
                            <h3 class="title">{{ __('ВЫ ВЛАДЕЛЕЦ СЕРВИСА ПО БУСТУ, ПРОДАЖЕ АККАУНТОВ ИЛИ ОБУЧЕНИЮ?') }}</h3>
                            <p class="description">{{ __('Мы предоставляем услуги по аутсорсу выполнения ваших заказов. Мы берем на себя все сложности по набору игроков и контролю за ними. Вы занимаетесь только привлечением клиентов и развитием списка услуг.') }}</p> 
                            <div class="games-selector margint40">
                                <button class="prev"><i class="xicons icon-left-open"></i></button>
                                <button class="next"><i class="xicons icon-right-open"></i></button>
                                <div class="dots"></div>
                                <div class="slick" data-shown="4" data-autoplay="true">
                                    @foreach($games as $game)
                                    <a class="item {{ $game->publish?'active':'soon'}}">
                                        <div class="image">
                                            <img src="{{ url($_cdn.'games/'.$game->png) }}">
                                            @if(!$game->publish)
                                            <span class="ribbon">{{ __('СКОРО') }}</span>
                                            @endif
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>                      
                        </div>
                    </div>                          
                </div>
            </div>
        </div>
        @if($advantages)
        <div class="page-container">
            <div class="container">
                <div class="caption">
                    <h4>{{ __('ПОЧЕМУ НУЖНО РАБОТАТЬ С НАМИ') }}</h4>
                </div>
                <div class="row">
                    @foreach($advantages as $item)
                    <div class="col-sm-12">
                        <div class="advantage-item fluid">
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
                    <h4>{{ __('ХОТИТЕ УЗНАТЬ ПОДРОБНОСТИ?') }}</h4>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12 col-md-offset-1 col-lg-offset-0">
                    <div class="form transparent">
                        <form>
                            <div class="response" data-default="{{ __('Обработка') }}">{{ __('Обработка') }}</div>
                            <div class="col-xs-12 col-sm-4">
                                <label class="">
                                    <p>{{ __('Имя') }}</p>
                                    <input type="text" name="username" placeholder="{{ __('Введите ваше имя') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label class="">
                                    <p>{{ __('Сервис(ссылка)') }}</p>
                                    <input type="text" name="service_link" placeholder="{{ __('Ссылка на Ваш сервис') }}">
                                </label>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <label class="">
                                    <p>{{ __('Ваша почта') }}</p>
                                    <input type="text" name="email" placeholder="{{ __('Введите ваш E-mail') }}">
                                </label>
                            </div>
                            <div class="col-xs-12">
                                <label><p>{{ __('Выберите интересующие Вас игры для сотрудничества') }}</p></label>
                                @foreach($games as $x => $game)
                                <label class="checkbox {{$game->publish?'':'disabled'}}"><input type="checkbox" name="games[]" value="{{ $game->id }}" {{$game->publish?'':'disabled'}} {{!$x?'checked':''}}><i class="checkmark"></i><b>{{ $game->pub }}</b></label>
                                @endforeach
                            </div>
                            <div class="col-xs-12">
                                <label><p>{{ __('Дополнительная информация') }}</p></label>
                                <label><textarea placeholder="{{ __('Введите дополнительную информацию о сервисе') }}" rows="5" name="comment"></textarea></label>
                            </div>
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-code-red btn-xl filled submit" onclick="_.partnerRequest(event)">{{ __('Отправить') }}</button>
                            </div>                  
                        </form> 
                    </div>                  
                </div>
            </div>          
        </div>
    </main>
@endsection
