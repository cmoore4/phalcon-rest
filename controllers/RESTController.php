<?php
namespace PhalconRest\Controllers;
use \PhalconRest\Exceptions\HTTPException;

/**
 * Base RESTful Controller.
 * Supports queries with the following paramters:
 *   Searching:
 *     q=(searchField1:value1,searchField2:value2)
 *   Partial Responses:
 *     fields=(field1,field2,field3)
 *   Limits:
 *     limit=10
 *   Partials:
 *     offset=20
 *   
 */
class RESTController extends \PhalconRest\Controllers\BaseController{

	/**
	 * If query string contains 'q' parameter.
	 * This indicates the request is searching an entity
	 * @var boolean
	 */
	protected $isSearch = false;

	/**
	 * If query contains 'fields' parameter.
	 * This indicates the request wants back only certain fields from a record
	 * @var boolean
	 */
	protected $isPartial = false;

	/**
	 * Set when there is a 'limit' query parameter
	 * @var integer
	 */
	protected $limit = null;

	/**
	 * Set when there is an 'offset' query parameter
	 * @var integer
	 */
	protected $offset = null;

	/**
	 * Array of fields requested to be searched against
	 * @var array
	 */
	protected $searchFields = null;

	/**
	 * Array of fields requested to be returned
	 * @var array
	 */
	protected $partialFields = null;

	/**
	 * Sets which fields may be searched against, and which fields are allowed to be returned in
	 * partial responses.  This will be overridden in child Controllers that support searching
	 * and partial responses.
	 * @var array
	 */
	protected $allowedFields = array(
		'search' => array(),
		'partials' => array()
	);


	/**
	 * Constructor calls the parse method.
	 */
	public function __construct($di){
		parent::__construct($di);
		$this->parseRequest($this->allowedFields);
	}

	/**
	 * Parses out the search parameters from a request.  
	 * Unparsed, they will look like this:
	 *    (name:Benjamin Framklin,location:Philadelphia)
	 * Parsed:
	 *     array('name'=>'Benjamin Franklin', 'location'=>'Philadelphia') 
	 * @param  string $unparsed Unparsed search string
	 * @return array            An array of fieldname=>value search parameters
	 */
	protected function parseSearchParameters($unparsed){
		
		// Strip parens that come with the request string
		$unparsed = trim($unparsed, '()');
		
		// Now we have an array of "key:value" strings.  
		$splitFields = explode(',', $unparsed);
		$mapped = array();

		// Split the strings at their colon, set left to key, and right to value.
		foreach ($splitFields as $field) {
			$splitField = explode(':', $field);
			$mapped[$splitField[0]] = $splitField[1];
		}

		return $mapped;
	}

	/**
	 * Parses out partial fields to return in the response.
	 * Unparsed:
	 *     (id,name,location)
	 * Parsed:
	 *     array('id', 'name', 'location')
	 * @param  string $unparsed Unparsed string of fields to return in partial response
	 * @return array            Array of fields to return in partial response
	 */
	protected function parsePartialFields($unparsed){
		return explode(',', trim($unparsed, '()'));
	}

	/**
	 * Main method for parsing a query string.
	 * Finds search paramters, partial response fields, limits, and offsets.
	 * Sets Controller fields for these variables.
	 * 
	 * @param  array $allowedFields Allowed fields array for search and partials
	 * @return boolean              Always true if no exception is thrown
	 */
	protected function parseRequest($allowedFields){
		$request = $this->di->get('request');
		$searchParams = $request->get('q', null, null);
		$fields = $request->get('fields', null, null);
		
		// Set limits and offset, elsewise allow them to have defaults set in the Controller
		$this->limit = ($request->get('limit', null, null)) ?: $this->limit;
		$this->offset = ($request->get('offset', null, null)) ?: $this->offset;

		// If there's a 'q' parameter, parse the fields, then determine that all the fields in the search
		// are allowed to be searched from $allowedFields['search'] 
		if($searchParams){
			$this->isSearch = true;
			$this->searchFields = $this->parseSearchParameters($searchParams);

			// This handly snippet determines if searchFields is a strict subset of allowedFields['search']
			if(!array_unique($allowedFields['search'] + $this->searchFields) === $allowedFields['search']){
				throw new HTTPException(
					"The fields you specified cannot be searched.", 
					401,
					array(
						'dev' => 'You requested to search fields that are not available to be searched.',
						'internalCode' => 'S1000',
						'more' => '' // Could have link to documentation here.
				));
			}
		}

		// If there's a 'fields' paramter, this is a partial request.  Ensures all the requested fields
		// are allowed in partial responses.
		if($fields){
			$this->isPartial = true;
			$this->partialFields = $this->parsePartialFields($fields);

			// Determines if fields is a strict subset of allowed fields
			if(!array_unique($allowedFields['partials'] + $this->partialFields) === $allowedFields['partials']){
				throw new HTTPException(
					"The fields you asked for cannot be returned.", 
					401,
					array(
						'dev' => 'You requested to return fields that are not available to be returned in partial responses.',
						'internalCode' => 'P1000',
						'more' => '' // Could have link to documentation here.
				));
			}
		}

		return true;
	}


}