@extends('@app')
@section('content')
<main>
    <div class="page-container">

        <div class="container">
            <div class="caption">
                <h4>{{ __('Черный список') }}</h4>
                <p class="description">{{ __('Информация о недобросовестных пользователях сервиса') }}</p>
            </div>
        </div>

        <div class="container soarer bg-fff">
            <div class="form transparent padding2030">
                <label><p>{{ __('Поле поиска для совпадений') }}</p></label>
                <div class="flex form-group">
                    <div class="column"><label><input type="text" id="black-list-search" placeholder="{{ __('Введите Имя, Facebook/vk, Skype, Discord подозреваемого бустера') }}"></label></div>
                    <div class="column shrink"><label><button class="btn btn-code-red rounded filled submit" onclick="_.blackListTable()">{{ __('ПРОВЕРИТЬ') }}</button></label></div>
                </div>
            </div>
        </div>

        <div class="container soarer bg-fff">
            <div class="app-table black-list-table" id="black-list-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Имя') }}</th>
                            <th>{{ __('Facebook/VK') }}</th>
                            <th>{{ __('Skype') }}</th>
                            <th>{{ __('Discord') }}</th>
                            <th class="hidden-451">{{ __('Другие контакты') }}</th>
                            <th>{{ __('Причина') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="99"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="table-pagination"></div>
            </div>
        </div>

        <div class="container soarer bg-fff">
            <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 col-md-offset-1 col-lg-offset-1">
                <div class="form transparent black-list">
                    <p class="alert">{{ __('Если вы стали жертвой недобросовестного бустера и у вас есть доказательства - пожалуйста заполните форму ниже. Мы рассмотрим ваше обращение и добавим бустера в наш список.') }}</p>
                    <form>
                        <div class="response" data-default="{{ __('Обработка') }}">{{ __('Обработка') }}</div>
                        <div class="col-xs-12 col-sm-6">
                            <label>
                                <p>{{ __('Имя') }}</p>
                                <input type="text" name="username" placeholder="{{ __('Введите ваше имя') }}">
                            </label>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <label>
                                <p>{{ __('Ваша почта') }}</p>
                                <input type="text" name="email" placeholder="{{ __('Введите ваш E-mail') }}">
                            </label>
                        </div>
                        <div class="col-xs-12">
                            <label>
                                <textarea name="comment" placeholder="Введите сообщение" rows="5"></textarea>
                            </label>
                        </div>
                        <div class="col-xs-12">
                            <label><p>{{ __('Прикрепить файлы с доказательством') }}</p></label>
                            <div class="dropzone">
                                <span class="cloud"><i class="xicons icon-upload-cloud"></i></span>
                                <span class="cover"></span>
                                <br>
                                <span>{{ __('Перетащите файлы в окно .jpg .png') }}</span>
                                <br>
                                <small>{{ __('или') }}</small>
                                <br>
                                <label class="btn btn-default" for="drop-catcher"><i class="xicons icon-attach"></i> {{ __('Выберите файл') }}</label>
                                <input id="drop-catcher" type="file" accept="image/png, image/jpeg" name="files[]" multiple />
                            </div>                              
                        </div>
                        <div class="col-xs-12">
                            <button type="button" class="btn btn-code-red submit filled" onclick="_.blackListRequest(event)">{{ __('Отправить') }}</button>
                        </div>                  
                    </form> 
                </div>                  
            </div>
        </div>

    </div>
</main>
@endsection
