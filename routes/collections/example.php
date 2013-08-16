<?php

/**
 * Collections let us define groups of routes that will all use the same controller.
 * We can also set the handler to be lazy loaded.  Collections can share a common prefix.
 * @var $exampleCollection
 */

// This is an Immeidately Invoked Function in php.  The return value of the
// anonymous function will be returned to any file that "includes" it.
// e.g. $collection = include('example.php');
return call_user_func(function(){

	$exampleCollection = new \Phalcon\Mvc\Micro\Collection();

	$exampleCollection
		// VERSION NUMBER SHOULD BE FIRST URL PARAMETER, ALWAYS
		->setPrefix('/v1/example')
		// Must be a string in order to support lazy loading
		->setHandler('\PhalconRest\Controllers\ExampleController')
		->setLazy(true);

	// Set Access-Control-Allow headers.
	$exampleCollection->options('/', 'optionsBase');
	$exampleCollection->options('/{id}', 'optionsOne');

	// First paramter is the route, which with the collection prefix here would be GET /example/
	// Second paramter is the function name of the Controller.
	$exampleCollection->get('/', 'get');
	// This is exactly the same execution as GET, but the Response has no body.
	$exampleCollection->head('/', 'get');

	// $id will be passed as a parameter to the Controller's specified function
	$exampleCollection->get('/{id:[0-9]+}', 'getOne');
	$exampleCollection->head('/{id:[0-9]+}', 'getOne');
	$exampleCollection->post('/', 'post');
	$exampleCollection->delete('/{id:[0-9]+}', 'delete');
	$exampleCollection->put('/{id:[0-9]+}', 'put');
	$exampleCollection->patch('/{id:[0-9]+}', 'patch');

	return $exampleCollection;
});