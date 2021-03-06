<?php

use Laracasts\Commander\CommanderTrait;
use Leadofficelist\Eventlogs\EventLog;
use Leadofficelist\Exceptions\ProfileNotFoundException;
use Leadofficelist\Forms\AddEditSurvey as AddEditSurveyForm;
use Leadofficelist\Forms\AddEditHOUSurvey as AddEditHOUSurveyForm;
use Leadofficelist\Knowledge_area_groups\KnowledgeAreaGroup;
use Leadofficelist\Knowledge_areas\KnowledgeArea;
use Leadofficelist\Knowledge_data\KnowledgeData;
use Leadofficelist\Knowledge_languages\KnowledgeLanguage;
use Leadofficelist\Knowledge_surveys\KnowledgeSurvey;
use Leadofficelist\Sector_categories\Sector_category;
use Leadofficelist\Units\Unit;
use Leadofficelist\Users\User;

class KnowledgeSurveysController extends \BaseController {

	use CommanderTrait;

	public $section = 'survey';
	protected $resource_key = 'survey';
	protected $resource_permission = 'view_knowledge';
	protected $search_term;
	protected $units;
	protected $areas;
	protected $languages;
	private $addEditSurvey;
	private $addEditHOUSurvey;

	function __construct( AddEditSurveyForm $addEditSurvey, AddEditHOUSurveyForm $addEditHOUSurvey ) {
		parent::__construct();

		View::share( 'page_title', 'Knowledge Survey' );
		View::share( 'key', 'survey' );
		$this->addEditSurvey    = $addEditSurvey;
		$this->addEditHOUSurvey = $addEditHOUSurvey;
	}

	/**
	 * Display the current user's knowledge profile
	 * GET /knowledgesurveys
	 *
	 * @return Response
	 */
	public function index() {
		$this->check_perm( 'view_knowledge' );

		$this->destroyCurrentPageNumber( true );

		if ( $this->searchCheck() ) {
			// Keep any flashed messages when redirecting
			Session::reflash();

			return Redirect::to( $this->resource_key . '/search' );
		}

		$items = User::where( 'date_of_birth', '!=', '0000-00-00' )->rowsSortOrder( $this->rows_sort_order )->paginate( $this->rows_to_view );
		$items->key = 'survey';
		$user_info  = $this->user;

		$this->getFormData();
		$units       = $this->units;
		$areas       = $this->areas;
		$area_groups = KnowledgeAreaGroup::orderBy( 'order', 'ASC' )->get();
		$languages   = $this->languages;

		return View::make( 'knowledge_surveys.index' )->with( compact( 'items', 'user_info', 'units', 'areas', 'languages', 'area_groups' ) );
	}

	/**
	 * Process a list search.
	 *
	 * @return $this
	 */
	public function search() {
		$this->check_perm( 'view_knowledge' );

		if ( $this->search_term = $this->findSearchTerm() ) {
			if ( Session::get( $this->resource_key . '.SearchType' ) == 'filter' ) {
				$items               = $this->getFiltered();
				$items->filter_value = $this->getFilteredValues();
			} else {

				$items = User::whereDate( 'date_of_birth', '>', '0000-00-00' )->where( function ( $query ) {
					$query->where( 'first_name', 'LIKE', $this->search_term )->orWhere( 'last_name', 'LIKE', $this->search_term );
				} )->orWhereHas( 'knowledge_areas', function ( $query ) {
					$query->where( 'knowledge_areas.name', 'LIKE', $this->search_term );
				} )->rowsSortOrder( $this->rows_sort_order )->paginate( $this->rows_to_view );
			}

			if ( ! $this->checkForSearchResults( $items ) ) {
				return Redirect::route( 'survey.index' );
			}
			$items->search_term = str_replace( '%', '', $this->search_term );
			$items->key         = 'survey';

			$this->getFormData();
			$units       = $this->units;
			$areas       = $this->areas;
			$area_groups = KnowledgeAreaGroup::orderBy( 'order', 'ASC' )->get();
			$languages   = $this->languages;

			return View::make( 'knowledge_surveys.index' )->with( compact( 'items', 'units', 'areas', 'languages', 'area_groups' ) );
		} else {
			return Redirect::route( 'survey.index' );
		}
	}

	/**
	 * Show the current user's profile
	 * @return $this
	 * @throws ProfileNotFoundException
	 */
	public function getShowProfile() {
		$this->check_perm( 'view_knowledge' );

		$user_info = $this->user;

		if ( isset( $this->user ) && $this->user->date_of_birth != '0000-00-00' ) {
			$language_info  = $this->getUserLanguageInfo();
			$expertise_info = $this->getExpertise();
			$score_info     = $this->getUserExpertiseInfoIDKeys();
			$knowledge_data = $this->getKnowledgeData();

			return View::make( 'knowledge_surveys.profile' )->with( compact( 'user_info', 'language_info', 'expertise_info', 'score_info', 'knowledge_data' ) );
		} elseif ( isset( $this->user ) && $this->user->date_of_birth == '0000-00-00' ) {
			return View::make( 'knowledge_surveys.profile' )->with( compact( 'user_info', 'language_info', 'expertise_info', 'score_info', 'knowledge_data' ) );
		}

		throw new ProfileNotFoundException();
	}

	/**
	 * Display another user's profile
	 * GET /knowledgesurveys/{id}
	 *
	 * @param  int $id
	 *
	 * @return Response
	 * @throws ProfileNotFoundException
	 */
	public function show( $id ) {
		$this->check_perm( 'view_knowledge' );

		$user = User::find( $id );

		if ( isset( $user ) && $user->survey_updated && $user->date_of_birth != '0000-00-00' ) {
			$user_info      = $user;
			$language_info  = $this->getUserLanguageInfo( $id );
			$expertise_info = $this->getExpertise( $id );
			$score_info     = $this->getUserExpertiseInfoIDKeys( $id );
			$knowledge_data = $this->getKnowledgeData( $id );
			$fipriot_info   = json_decode( file_get_contents( 'http://fipra.com/wp-json/wp/v2/fipriot?email=' . $user->email ) );

			return View::make( 'knowledge_surveys.show' )->with( compact( 'user_info', 'language_info', 'expertise_info', 'score_info', 'fipriot_info', 'knowledge_data' ) );
		}

		throw new ProfileNotFoundException();
	}

	public function getUpdateProfile() {
		$this->check_perm( 'edit_knowledge' );

		$dob_data        = $this->getDateSelect( 'dob' );
		$languages       = $this->getLanguages();
		$expertise       = $this->getExpertise();
		$expertise_areas = $this->getExpertiseAreas();
		$survey_name     = 'knowledge_survey';

		$other_data     = KnowledgeData::where( 'user_id', '=', $this->user->id )->where('survey_name', '=', $survey_name)->get()->toArray();
		$knowledge_data = [];
		foreach ( $other_data as $data ) {
			$knowledge_data[ $data['slug'] ] = $data['serialized'] ? unserialize( $data['data_value'] ) : $data['data_value'];
		}

		$user_info = $this->user;
		if ( isset( $user_info ) ) {
			$language_info  = $this->getUserLanguages();
			$expertise_info = $this->getUserExpertiseInfoIDKeys();

			return View::make( 'knowledge_surveys.edit' )->with( compact( 'dob_data', 'languages', 'expertise', 'user_info', 'language_info', 'expertise_info', 'knowledge_data', 'expertise_areas', 'survey_name' ) );
		}

		return Redirect::to( 'survey/profile' );

	}

	public function postUpdateProfile() {
		$this->check_perm( 'edit_knowledge' );

		$input = Input::all();
		// Add the knowledge areas into the validation rules and update feedback messages
		foreach ( KnowledgeArea::all() as $area ) {
			$this->addEditSurvey->rules[ 'areas.' . $area->id ]                  = 'required|min:1|max:5';
			$this->addEditSurvey->messages[ 'areas.' . $area->id . '.required' ] = 'Please select an expertise score for ' . $area->name . '.';
		}
		// Add each public office row into the validations rules and update feedback messages
		foreach ( Input::get( 'public_office' ) as $id => $row ) {
			if ( $id > 0 ) {
				$this->addEditSurvey->rules["public_office.$id.position"] = 'required';
				$this->addEditSurvey->rules["public_office.$id.from"]     = 'required';
				$this->addEditSurvey->rules["public_office.$id.to"]       = 'required';
			}
			$this->addEditSurvey->messages["public_office.$id.position.required"] = 'The public office "position" field is required.';
			$this->addEditSurvey->messages["public_office.$id.from.required"]     = 'The public office "from" field is required.';
			$this->addEditSurvey->messages["public_office.$id.to.required"]       = 'The public office "to" field is required.';
		}
		// Add each political party row into the validations rules and update feedback messages
		foreach ( Input::get( 'political_party' ) as $id => $row ) {
			if ( $id > 0 ) {
				$this->addEditSurvey->rules["political_party.$id.position"] = 'required';
				$this->addEditSurvey->rules["political_party.$id.party"]    = 'required';
				$this->addEditSurvey->rules["political_party.$id.from"]     = 'required';
				$this->addEditSurvey->rules["political_party.$id.to"]       = 'required';
			}
			$this->addEditSurvey->messages["political_party.$id.position.required"] = 'The political party "position" field is required.';
			$this->addEditSurvey->messages["political_party.$id.party.required"]    = 'The political party "party" field is required.';
			$this->addEditSurvey->messages["political_party.$id.from.required"]     = 'The political party "from" field is required.';
			$this->addEditSurvey->messages["political_party.$id.to.required"]       = 'The political party "to" field is required.';
		}

		// Validate input
		$this->addEditSurvey->validate( $input );

		$this->execute( 'Leadofficelist\Knowledge_surveys\UpdateUserInfoCommand' ); // Takes care of user-specific data
		$this->execute( 'Leadofficelist\Knowledge_surveys\UpdateLanguageInfoCommand' ); // Takes case of pre-defined languages
		$this->execute( 'Leadofficelist\Knowledge_surveys\UpdateKnowledgeInfoCommand' ); // Takes care of knowledge area scores
		$this->execute( 'Leadofficelist\Knowledge_data\UpdateKnowledgeDataCommand' ); // Takes care of everything else

		Flash::overlay( 'Knowledge profile updated.', 'success' );
		EventLog::add( 'Knowledge survey updated', $this->user->getFullName(), Unit::find( $this->user->unit_id )->name, 'edit' );

		return Redirect::to( 'survey/profile' );
	}

	public function getHOUSurvey() {

	}

	public function postHOUSurvey() {


	}

	protected function getDateSelect( $purpose = 'dob' ) {
		$data   = [];
		$months = array(
			"January",
			"February",
			"March",
			"April",
			"May",
			"June",
			"July",
			"August",
			"September",
			"October",
			"November",
			"December"
		);


		// Days
		for ( $i = 1; $i <= 31; $i ++ ) {
			$data['days'][ $i ] = strval( $i );
		}

		// Months
		for ( $i = 1; $i <= 12; $i ++ ) {
			$data['months'][ $i ] = $months[ $i - 1 ];
		}

		// Years
		if ( $purpose == 'joined_fipra' ) {
			// Only go back to 2000, when Fipra was formed
			$data['years'] = array_combine( range( date( "Y" ), 2000 ), array_map( 'strval', range( date( "Y" ), 2000 ) ) );

		} else {
			// Go back to 1910 for date of birth entry
			$data['years'] = array_combine( range( date( "Y" ), 1910 ), array_map( 'strval', range( date( "Y" ), 1910 ) ) );
		}

		return $data;
	}

	protected function getLanguages() {
		$languages_processed = [];
		$languages           = KnowledgeLanguage::orderBy( 'name', 'ASC' )->get();

		foreach ( $languages as $language ) {
			$languages_processed[ $language->id ] = $language->name;
		}

		return $languages_processed;
	}

	protected function getExpertise() {
		$expertise             = [];
		$knowledge_area_groups = KnowledgeAreaGroup::orderBy( 'order' )->get()->toArray();
		foreach ( $knowledge_area_groups as $group ) {
			$expertise['descriptions'][ $group['name'] ] = $group['description'];
			$knowledge_areas                             = KnowledgeArea::where( 'knowledge_area_group_id', '=', $group['id'] )->orderBy( 'name' )->get()->toArray();
			foreach ( $knowledge_areas as $area ) {
				$expertise['areas'][ $group['name'] ][ $area['id'] ] = $area['name'];
			}
		}

		return $expertise;
	}

	protected function getExpertiseAreas() {
		$sector_categories = Sector_category::orderBy( 'name' )->where( 'name', '<>', 'Other' )->get()->toArray();
		$expertise_areas   = [];
		foreach ( $sector_categories as $cat ) {
			$expertise_areas[ $cat['id'] ] = $cat['name'];
		}

		return $expertise_areas;
	}

	protected function getUserLanguageInfo( $id = null ) {
		return $id ? User::find( $id )->knowledge_languages()->get()->lists( 'name' ) : $this->user->knowledge_languages()->get()->lists( 'name' );
	}

	protected function getKnowledgeData( $id = null ) {
		$results   = $id ? User::find( $id )->knowledge_data()->get()->toArray() : $this->user->knowledge_data()->get()->toArray();
		$user_data = [];
		foreach ( $results as $data ) {
			$user_data[ $data['slug'] ] = $data['serialized'] ? unserialize( $data['data_value'] ) : $data['data_value'];
		}

		return $user_data;
	}

	protected function getUserLanguages( $id = null ) {
		$languages_processed = [];
		// Get language data via the pivot table
		$languages = $id ? User::find( $id )->knowledge_languages()->get()->toArray() : $this->user->knowledge_languages()->get()->toArray();

		foreach ( $languages as $language ) {
			$languages_processed[] = $language['id'];
		}

		return $languages_processed;

	}

	protected function getUserExpertiseInfo( $id = null ) {
		// Get language data via the pivot table

		$expertise     = $id ? User::find( $id )->knowledge_areas()->get() : $this->user->knowledge_areas()->get();
		$expertiseData = [];

		foreach ( $expertise as $expert ) {
			// Create an array with expertise names as keys and scores as values
			$expertiseData[ $expert->name ] = $expert->pivot->score;
		}

		// Put into alphabetical order
		asort( $expertiseData, SORT_STRING );

		return $expertiseData;
	}

	protected function getUserExpertiseInfoIDKeys( $id = null ) {
		// Get language data via the pivot table
		$expertise     = $id ? User::find( $id )->knowledge_areas()->get() : $this->user->knowledge_areas()->get();
		$expertiseData = [];

		foreach ( $expertise as $expert ) {
			// Create an array with expertise names as keys and scores as values
			$expertiseData[ $expert->id ] = $expert->pivot->score;
		}

		// Put into alphabetical order
		asort( $expertiseData, SORT_STRING );

		return $expertiseData;
	}

	/**
	 * Get all data required to populate the filters.
	 *
	 * @return bool
	 */
	protected function getFormData() {
		$this->units     = $this->getUnitsFormData( true, 'All' );
		$this->areas     = $this->getKnowledgeAreasFormData( true, 'All' );
		$this->languages = $this->getKnowledgeLanguagesFormData( true, 'All' );

		return true;
	}

	protected function getAll() {
		return User::whereDate( 'date_of_birth', '>', '0000-00-00' )->orderBy( 'id', 'DESC' )->get();
	}

	protected function getSelection() {
		if ( $this->searchCheck() ) {
			$search_term             = $this->findSearchTerm();
			$this->search_term_clean = str_replace( '%', '', $search_term );

			$items = User::whereDate( 'date_of_birth', '>', '0000-00-00' )->where( function ( $query ) use($search_term) {
				$query->where( 'first_name', 'LIKE', $search_term )->orWhere( 'last_name', 'LIKE', $search_term );
			} )->orWhereHas( 'knowledge_areas', function ( $query ) use ($search_term) {
				$query->where( 'knowledge_areas.name', 'LIKE', $search_term );
			} )->rowsSortOrder( $this->rows_sort_order )->paginate( $this->rows_to_view );
		} else {
			$items = User::whereDate( 'date_of_birth', '>', '0000-00-00' )->rowsSortOrder( $this->rows_sort_order )->paginate( $this->rows_to_view );
		}

		return $items;
	}

	protected function getFiltered( $for = 'screen' ) {
		// Get a replica of the filters so we can play with them, without affecting the original
		$filters = Session::get( $this->resource_key . '.Filters' );
		// Get a blank Eloquent model
		$items = User::whereNotNull( 'id' )->whereDate( 'date_of_birth', '>', '0000-00-00' );
		// Where functions are split across all columns to ensure filters work correctly
		$items->where( function ( $query ) use ( $filters ) {
			if ( isset( $filters['unit_id'] ) ) {
				// Get the product_id of the selected sector and prepare it for a LIKE SQL query
				foreach ( $filters['unit_id'] as $id ) {
					$query->orWhere( 'unit_id', '=', $id );
				}
			}
		} )->where( function ( $query ) use ( $filters ) {
			if ( isset( $filters['knowledge_area_id'] ) ) {
				foreach ( $filters['knowledge_area_id'] as $id ) {
					// Get all users that have this knowledge area assigned to them
					$validUsers = KnowledgeArea::find( $id )->users()->get();
					foreach ( $validUsers as $user ) {
						// Is the user's score 4 or 5?
						if ( $user->pivot->score >= 4 ) // If so, include them in the query
						{
							$query->orWhere( 'id', '=', $user->id );
						}
						{
						}
					}
				}
			}
		} )->rowsSortOrder( $this->rows_sort_order );

		if ( $for == 'export' ) {
			// Get all results for PDF export
			return $items->get();
		} else {
			// Paginate results for screen display
			return $items->paginate( $this->rows_to_view );
		}

	}

	/**
	 * Override of BaseController version
	 *
	 * @return string
	 */
	protected function getFilteredValues() {
		// Get names of filtered values
		$values = '';
		foreach ( Session::get( $this->resource_key . '.Filters' ) as $filter_name => $filter_array ) {

			foreach ( $filter_array as $filter_value ) {
				if ( $filter_name != 'unit_id' ) {
					$values .= KnowledgeArea::find( $filter_value )->name . ', ';
				} else {
					$values .= Unit::find( $filter_value )->name . ', ';
				}
			}
		}

		return rtrim( $values, ', ' );
	}
}