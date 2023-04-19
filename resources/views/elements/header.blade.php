<header>
	<div class="container">
		<div class="flex align-middle">
			<div class="column">
				<div class="logo">
					<a href="{{ url('/'.$_locale->prefix) }}"><img src="{{ url('/public/img/logo-b.png') }}" alt="logo"></a>
				</div>
			</div>
			<div class="column shrink">
				<ul class="locale-dropdown large">
					<li>
						<a><img src="{{ url('/public/img/locals/'.$_locale->icon) }}"/>{{ strtoupper($_locale->name) }}</a>
					</li>
				    <li>
						@foreach($_locales as $locale)
							@if($locale->name != $_locale->name)
							<a href="{{ url($locale->prefix . '/' . $_path . $_query) }}"><img src="{{ url('/public/img/locals/'.$locale->icon) }}"/>{{ strtoupper($locale->name) }}</a>
							@endif
						@endforeach
				    </li>
				</ul>
			</div>
			<div class="column shrink">
				<div class="login">
					<a href="https://booster.52gaming.net/" class="btn-code-red btn-lg" type="button">{{ __('Войти') }}</a>
				</div>
			</div>
		</div>
	</div>
</header>