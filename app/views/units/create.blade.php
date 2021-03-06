@extends('layouts.master')

@section('page-header')
Add a Network Member
@stop

@section('page-nav')
<li><a href="{{ route('units.index') }}" class="primary"><i class="fa fa-caret-left"></i> Overview</a></li>
@stop

@section('content')

@include('layouts.partials.messages')

{{ Form::open(['route' => 'units.store']) }}
<div class="row">
	<div class="col-4">
		<div class="formfield">
			{{ Form::label('name', 'Name:', ['class' => 'required']) }}
			{{ Form::text('name', Input::old('name')) }}
		</div>
        <div class="formfield">
            {{ Form::label('network_type', 'Type:', ['class' => 'required']) }}
            {{ Form::select('network_type', $network_types, Input::old('network_type'), ['class' => 'select2', 'style' => 'width:100%;']) }}
        </div>
		<div class="formfield">
			{{ Form::label('address', 'Address:') }}
			{{ Form::text('address1', Input::old('address1')) }}
			{{ Form::text('address2', Input::old('address2')) }}
			{{ Form::text('address3', Input::old('address3')) }}
			{{ Form::text('address4', Input::old('address4')) }}
		</div>
		<div class="formfield">
			{{ Form::label('postcode', 'Zip / Post Code:') }}
            {{ Form::text('postcode', Input::old('postcode')) }}
		</div>
	</div>
	<div class="col-4">
		<div class="formfield">
			{{ Form::label('short_name', 'Short Name:', ['class' => 'required']) }}
			{{ Form::text('short_name', Input::old('short_name')) }}
		</div>
		<div class="formfield">
			{{ Form::label('phone', 'Telephone:') }}
			{{ Form::text('phone', Input::old('phone')) }}
		</div>
		<div class="formfield">
			{{ Form::label('fax', 'Fax:') }}
			{{ Form::text('fax', Input::old('fax')) }}
		</div>
		<div class="formfield">
			{{ Form::label('email', 'Email:') }}
			{{ Form::email('email', Input::old('email')) }}
		</div>
        <div class="formfield">
            {{ Form::label('unit_group', 'Reporting Group:') }}
            {{ Form::select('unit_group', $unit_groups, Input::old('unit_group'), ['class' => 'select2', 'style' => 'width:100%;']) }}
        </div>
	</div>
	<div class="col-4 last">
		<div class="formfield">
			{{ Form::label('show_list', 'Available in:', ['class' => 'required']) }}
			{{ Form::hidden('show_list', 0) }}
			<p>{{ Form::checkbox('show_list', input::old('show_list'), true) }} Lead Office List</p>
			{{ Form::hidden('show_case', 0) }}
			<p>{{ Form::checkbox('show_case', input::old('show_case'), true) }} Case Studies</p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		{{ Form::submit('Add', ['class' => 'primary']) }}
		{{ Form::close() }}
	</div>
</div>
@stop