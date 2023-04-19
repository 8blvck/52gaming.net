@extends('@app')
@section('content')
<main>
    <div class="page-container">
        <div class="container">
		    <div class="tracking-app-breadcrumbs">
		        <span><a href="{{ url('/'.$_locale->prefix) }}">{{ __('Главная') }}</a></span>
		        <span><a>{{ __('Заказы') }}</a></span>
		    </div>
			<?php print $data; ?>
        </div>
    </div>
</main>
@endsection
