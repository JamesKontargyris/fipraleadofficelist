<section class="row no-margin">
	<div class="col-12 filter-container">
		<a class="filter-menu-icon-m" href="#">Filters</a>
		<div class="col-12 filters">
			<ul>
				@if(is_request('clients'))
					<li>@include('layouts.partials.filters.rows_hide_show_dormant')</li>
				@endif
				<li>@include('layouts.partials.filters.rows_to_view')</li>
				<li>@include('layouts.partials.filters.rows_sort_order')</li>
				@if(is_request('users'))
					<li>@include('layouts.partials.filters.rows_name_order')</li>
				@endif
				@if( Session::has($key . '.rowsToView') || Session::has($key . '.rowsSort'))
					<li><a href="?reset_sorting=yes" class="filter-but"><i class="fa fa-undo"></i> Reset Rows Per Page / Sorting</a></li>
				@endif
			</ul>
			@if(is_request('clients') || is_request('users'))
				<ul>
					<li class="letter-select-table-container">@include('layouts.partials.filters.letter-select-table')</li>
				</ul>
			@endif
		</div>
	</div>
</section>