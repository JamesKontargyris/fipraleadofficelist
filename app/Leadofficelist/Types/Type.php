<?php namespace Leadofficelist\Types;

class Type extends \BaseModel
{
	protected $fillable = [ 'name' ];
	public $timestamps = false;

	public function clients()
	{
		return $this->belongsTo( '\Leadofficelist\Clients\Client', 'id', 'type_id' );
	}

    public function type_category()
    {
        return $this->belongsTo( '\Leadofficelist\Type_categories\Type_category', 'category_id', 'id' );
    }

	public function add( $type )
	{
		$this->name       = $type->name;
		$this->short_name = $type->short_name;
		$this->category_id = $type->type_category;
		$this->save();

		return $this;
	}

	public function edit( $type )
	{
		$update_type             = $this->find( $type->id );
		$update_type->name       = $type->name;
		$update_type->short_name = $type->short_name;
		$update_type->category_id = $type->type_category;
		$update_type->save();

		return $update_type;
	}

	public static function getTypesForFormSelect( $blank_entry = false, $blank_message = 'Please select...', $prefix = "" )
	{
		$types = [ ];
		//Remove whitespace from $prefix and add a space on the end, so there is a space
		//between the prefix and the unit name
		$prefix = trim( $prefix ) . ' ';
		//If $blank_entry == true, add a blank "Please select..." option
		if ( $blank_entry )
		{
			$types[''] = $blank_message;
		}

		foreach ( Type::orderBy( 'name', 'ASC' )->get( [ 'id', 'name' ] ) as $type )
		{
			$types[ $type->id ] = $prefix . $type->name;
		}

		if ( $blank_entry && count( $types ) == 1 )
		{
			return false;
		} elseif ( ! $blank_entry && count( $types ) == 0 )
		{
			return false;
		} else
		{
			return $types;
		}
	}
}