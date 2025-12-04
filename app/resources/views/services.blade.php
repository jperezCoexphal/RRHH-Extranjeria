@extends('layouts.dashboard')

@section('title', 'services')

@section('content')
    <h1>Services</h1>
    
    @component('_components.card')
        @slot('title', 'Service 1')
        @slot('content')
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam hic cum fuga ex vero. Non dolores quas ducimus eaque, dolorem veniam illum ut repellat similique incidunt perferendis fuga quo accusantium.</p>
        @endslot
    @endcomponent
    @component('_components.card')
        @slot('title', 'Service 1')
        @slot('content')
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam hic cum fuga ex vero. Non dolores quas ducimus eaque, dolorem veniam illum ut repellat similique incidunt perferendis fuga quo accusantium.</p>
        @endslot
    @endcomponent
    @component('_components.card')
        @slot('title', 'Service 1')
        @slot('content')
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam hic cum fuga ex vero. Non dolores quas ducimus eaque, dolorem veniam illum ut repellat similique incidunt perferendis fuga quo accusantium.</p>
        @endslot
    @endcomponent
    @component('_components.card')
        @slot('title', 'Service 1')
        @slot('content')
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam hic cum fuga ex vero. Non dolores quas ducimus eaque, dolorem veniam illum ut repellat similique incidunt perferendis fuga quo accusantium.</p>
        @endslot
    @endcomponent

@endsection