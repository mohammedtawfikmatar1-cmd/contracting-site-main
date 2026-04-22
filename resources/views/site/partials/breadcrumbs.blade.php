@php
    /*
        مكوّن مسار التنقل:
        - يستقبل مصفوفة $items بالشكل: [['label' => 'الرئيسية', 'url' => route('home')], ...]
        - آخر عنصر يمكن أن يكون بدون رابط لتمثيل الصفحة الحالية.
    */
    $items = $items ?? [];
@endphp

@if(!empty($items))
    <nav class="site-breadcrumbs" aria-label="مسار التنقل">
        <ol class="site-breadcrumbs__list">
            @foreach($items as $item)
                <li class="site-breadcrumbs__item">
                    @if(!empty($item['url']))
                        <a href="{{ $item['url'] }}" class="site-breadcrumbs__link">{{ $item['label'] }}</a>
                    @else
                        <span class="site-breadcrumbs__current" aria-current="page">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
