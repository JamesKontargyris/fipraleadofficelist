@if(Session::get($items->key . '.rowsHideShowDormant') == 'hide')
	<a href="?dormant=show" class="filter-but highlight"><i class="fa fa-eye"></i> Show Dormant</a>
@elseif( Session::get($items->key . '.rowsHideShowActive') == 'show' && Session::get($items->key . '.rowsHideShowDormant') == 'show')
	<a href="?dormant=hide" class="filter-but highlight"><i class="fa fa-eye-slash"></i> Hide Dormant</a>
@endif