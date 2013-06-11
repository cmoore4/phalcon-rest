<?php
namespace PhalconRest\Responses;

class Response extends \Phalcon\DI\Injectable{

	protected $head = false;

	public function __construct(){
		//parent::__construct();
		$di = \Phalcon\DI::getDefault();
		$this->setDI($di);
		if(strtolower($this->di->get('request')->getMethod()) === 'head'){
			$this->head = true;
		}
	}

	/**
	 * In-Place, recursive conversion of array keys in snake_Case to camelCase
	 * @param  array $snakeArray Array with snake_keys
	 * @return  no return value, array is edited in place
	 */
	protected function arrayKeysToSnake($snakeArray){
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
	protected function snakeToCamel($val) {
		return str_replace(' ', '', lcfirst(ucwords(str_replace('_', ' ', $val))));
	}

}
