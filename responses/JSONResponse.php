<?php
namespace PhalconRest\Responses;

class JSONResponse extends Response{

	protected $snake = true;
	protected $envelope = true;

	public function __construct($di){
		parent::__construct($di);
	}

	public function send($records){

		// If the query string 'envelope' is set to false, do not use the envelope.
		// Instead, return headers.
		$request = $this->di->get('request');
		if($request->get('envelope', null, null) === 'false'){
			$this->envelope = false;
		}

		// Most devs prefer camelCase to snake_Case in JSON, but this can be overriden here
		if($this->snake){
			$records = $this->arrayKeysToSnake($records);
		}

		if($this->envelope){
			// Provide an envelope for JSON responses.  '_meta' and 'records' are the objects. 
			$message = array();
			$message['_meta'] = array(
				'status' => 'SUCCESS',
				'count' => count($records)
			); 
			$message['records'] = $records;
		} else {
			//TODO: HTTP headers in palce of envelope
			$message = $records;
		}
		
		$this->di->get('response')->setJsonContent($message);
		$this->di->get('response')->send();

		return $this;
	}

	public function convertSnakeCase($snake){
		$this->snake = (bool) $snake;
		return $this;
	}

	public function useEnvelope($envelope){
		$this->envelope = (bool) $envelope;
		return $this;
	}

	/**
	 * In-Place, recursive conversion of array keys in snake_Case to camelCase
	 * @param  array $snakeArray Array with snake_keys
	 * @return  no return value, array is edited in place
	 */
	private function arrayKeysToSnake($snakeArray){
		foreach($snakeArray as $k=>$v){
			if (is_array($v)){
				$v = $this->arrayKeysToSnake($v);
			}
			$snakeArray[$this->snakeToCamel($k)] = $v;
			if($this->snakeToCamel($k) != $k){
				unset($snakeArray[$k]);
			}
		}
		return $snakeArray;
	}

	/**
	 * Replaces underscores with spaces, uppercases the first letters of each word, 
	 * lowercases the very first letter, then strips the spaces
	 * @param string $val String to be converted
	 * @return string     Converted string
	 */
	private function snakeToCamel($val) {
		return str_replace(' ', '', lcfirst(ucwords(str_replace('_', ' ', $val))));
	}


}