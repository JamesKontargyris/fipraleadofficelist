<?php

use Laracasts\Commander\CommanderTrait;
use Laracasts\Flash\Flash;
use Leadofficelist\Clients\Client;
use Leadofficelist\Clients\ClientLink;
use Leadofficelist\Forms\AddEditClientLink as AddEditClientLinkForm;
use Leadofficelist\Units\Unit;

class ClientLinksController extends \BaseController
{
	use CommanderTrait;

	public $resource_key = 'client_links';

	protected $units;
	private $addEditClientLinkForm;

	function __construct(AddEditClientLinkForm $addEditClientLinkForm)
	{
		parent::__construct();
		$this->check_perm( 'manage_clients' );
		View::share( 'page_title', 'Client Link' );
		$this->addEditClientLinkForm = $addEditClientLinkForm;
	}


	/**
	 * Display a listing of the resource.
	 * GET /clientlinks
	 *
	 * @return Response
	 */
	public function index()
	{
		if(Input::has('client_id') && $client = Client::find(Input::get('client_id')))
		{
			$id = Input::get('client_id');
			$items = ClientLink::where('client_id', '=', $id)->get();
			return View::make('client_links.index')->with(compact('id', 'client', 'items'));
		}

		return Redirect::route('clients.index');
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /clientlinks/create
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->getFormData();

		$unit_1 = Input::has('unit_1') ? Unit::find(Input::get('unit_1')) : null;
		$client_1 = Input::has('client_1') ? Client::find(Input::get('client_1')) : null;

		return View::make( 'client_links.create' )->with( [
			'units' => $this->units,
			'unit_1' => $unit_1,
			'client_1' => $client_1
		] );
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /clientlinks
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();
		$this->addEditClientLinkForm->rules['unit_1'] = 'required|numeric|different:unit_2|unique:client_links,unit_id,NULL,id,client_id,' . $input['client_2'];
		$this->addEditClientLinkForm->rules['unit_2'] = 'required|numeric|unique:client_links,unit_id,NULL,id,client_id,' . $input['client_1'];
		$this->addEditClientLinkForm->validate( $input );

		$this->execute( 'Leadofficelist\Clients\CreateClientLinkCommand' );

		Flash::overlay( 'Client link created.', 'success' );

		return Redirect::route( 'clients.index' );
	}

	/**
	 * Display the specified resource.
	 * GET /clientlinks/{id}
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function show( $id )
	{
		return Redirect::route('clients.index');
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /clientlinks/{id}/edit
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function edit( $id )
	{
		return Redirect::route('clients.index');
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /clientlinks/{id}
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function update( $id )
	{
		return Redirect::route('clients.index');
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /clientlinks/{id}
	 *
	 * @param  int $id
	 *
	 * @return Response
	 */
	public function destroy( $id )
	{
		if ( $client_link = $this->getClientLink( $id ) )
		{
			ClientLink::destroy( $id );
			$also_delete = $client_link->link_to_id;
			ClientLink::destroy( $also_delete );

			Flash::message( 'Client/Unit link deleted.' );

		}

		return Redirect::route( 'clients.index' );
	}

	protected function getClientLink( $id )
	{
		return ClientLink::find( $id );
	}

	protected function getFormData()
	{
		$this->units = $this->getUnitsFormData();

		return true;
	}

	/**
	 * Get all the units in a select element-friendly collection.
	 *
	 * @return array
	 */
	protected function getUnitsFormData()
	{
		if ( ! Unit::getUnitsForFormSelect( true ) )
		{
			return [ '' => 'No units available to select' ];
		}

		return Unit::getUnitsForFormSelect( true );
	}

	/**
	 * Respond to Ajax requests when creating a client link
	 * User selects a unit, this function returns all clients added by that unit
	 *
	 * @return array
	 */
	public function getClientsByUnit()
	{
		if(Input::has('unit_id'))
		{
			$results = '';
			$unit_id = Input::get('unit_id');
			$clients = Client::where('unit_id', '=', $unit_id)->get(['id', 'name']);

			foreach($clients as $client)
			{
				$results[$client->id] = $client->name;
			}

			return $results;
		}

		return false;
	}

}