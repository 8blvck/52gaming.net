@extends('@app')
@section('content')
<main>
    <div class="container">
        <div class="home-content text-center">
            <div class="home-text">
                <h1 class="title">{{ __('Сайт, предоставляющий услуги игрокам в прокачке рейтинга (буста, фарм золота и т.д.) в различных играх.') }}</h1>
            </div>
            <div class="home-image">
                <img src="{{ url('/public/img/pudget.png') }}" alt="home">
            </div>
            <div class="home-buttons">
                <div class="flex align-center">
                    @foreach($pages as $item)
                    <div class="column shrink">
                        <a href="{{ url($_locale->prefix.'/'.ltrim($item->slug,'/')) }}" class="btn-code-red btn-xl margin05" type="button">{{ __($item->name) }}</a>
                    </div>
                    @endforeach
                </div>
            </div>                  
        </div>      
    </div>
</main>
@endsection
