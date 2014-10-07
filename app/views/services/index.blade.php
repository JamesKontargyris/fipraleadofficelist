@extends('layouts.master')

@section('page-header')
@if(is_search()) Searching for: {{ $items->search_term }} @else Services Overview @endif
@stop

@section('page-nav')
<li><a href="{{ route('services.create') }}" class="secondary"><i class="fa fa-plus-circle"></i> Add a Service</a></li>
@stop

@section('content')

@include('layouts.partials.messages')

@if(count($items) > 0)
	@include('layouts.partials.rows_nav')

	@include('layouts.partials.filters')

	<section class="index-table-container">
		<div class="row">
			<div class="col-12">
				<table width="100%" class="index-table">
					<thead>
						<tr>
							<td width="55%">Service Name</td>
							<td width="10%" class="content-center hide-s">Clients</td>
							<td colspan="2">Actions</td>
						</tr>
					</thead>
					<tbody>
						@foreach($items as $service)
							<tr>
								<td><strong><a href="{{ route('services.show', ['id' => $service->id]) }}">{{ $service->name }}</a></strong></td>
								<td class="content-center hide-s">{{ number_format(0,0,'.',',') }}</td>
								<td class="actions content-right">
									{{ Form::open(['route' => array('services.edit', $service->id), 'method' => 'get']) }}
										<button type="submit" class="primary" ><i class="fa fa-pencil"></i></button>
									{{ Form::close() }}
								</td>
								<td class="actions content-right">
									{{ Form::open(['route' => array('services.destroy', $service->id), 'method' => 'delete']) }}
										<button type="submit" class="red-but delete-row" data-resource-type="sector"><i class="fa fa-times"></i></button>
									{{ Form::close() }}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</section>

	@include('layouts.partials.pagination_container')
@else
	@include('layouts.partials.index_no_records_found')
@endif
@stop