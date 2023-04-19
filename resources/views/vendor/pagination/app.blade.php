<ul class="tracking-app-pagination-info tracking-app-float-left">
    <li>{{ __('Показано') }} {{ $from }} - {{ $to }}</li>
    <li>{{ __('из') }} {{ $paginator->total() }}</li>
</ul>
@if ($paginator->hasPages())
    <ul class="tracking-app-pagination tracking-app-float-right">
        @if ($paginator->onFirstPage())
            <li class="arrow disabled"><a><i class="xicons icon-left-open"></i>&nbsp;  {{ __('Назад') }}</a></li>
        @else
            <li class="arrow"><a  onclick="{{ $onclick }}({{$paginator->currentPage() - 1}})"><i class="xicons icon-left-open"></i>&nbsp;  {{ __('Назад') }}</a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled"><a>{{ $element }}</a></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><a>{{ $page }}</a></li>
                    @else
                        <li><a onclick="{{ $onclick }}({{$page}})">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="arrow"><a onclick="{{ $onclick }}({{$paginator->currentPage() + 1}})">{{ __('Вперед') }} &nbsp; <i class="xicons icon-right-open"></i></a></li>
        @else
            <li class="arrow disabled"><a>{{ __('Вперед') }} &nbsp; <i class="xicons icon-right-open"></i></a></li>
        @endif
    </ul>
@endif
