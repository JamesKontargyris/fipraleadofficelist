<?php namespace Leadofficelist\Unit_groups;

class EditUnitGroupCommand
{
	public $name;
    public $short_name;
    public $id;

    function __construct( $name, $short_name, $id )
	{
		$this->name = trim($name);
		$this->short_name = trim($short_name);
        $this->id   = $id;
    }

}