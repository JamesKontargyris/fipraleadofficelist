@extends('layouts.master')

@section('page-header')
    {{ $widget->name }} ({{ $widget->slug }})
@stop

@section('page-nav')
    <li><a href="{{ URL::previous() }}" class="primary"><i class="fa fa-caret-left"></i> Go back</a></li>
    <li><a href="/widgets/{{ $widget->id }}/edit" class="primary"><i class="fa fa-pencil"></i> Edit this widget</a></li>
@stop

@section('content')


    @include('layouts.partials.messages')

    <div class="row">
        <div class="col-12">
            {{ $widget->content }}
        </div>
    </div>
@stop