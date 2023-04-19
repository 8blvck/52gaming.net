@extends('@app')
@section('content')
<main>
    <div class="container">
        <div class="home-content text-center">
            <div class="home-text">
                <h1 class="title fs30"><strong>404</strong> {{ __('Простите, но эта страница не найдена') }}</h1>
            </div>
            <div class="home-image">
                <img src="{{ url('/public/img/pudget.png') }}" alt="home">
            </div>                  
        </div>      
    </div>
</main>
@endsection
