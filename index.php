<?php

use Phalcon\DI\FactoryDefault as DefaultDI,
	Phalcon\Mvc\Micro\Collection,
	Phalcon\Config\Adapter\Ini as IniConfig,
	Phalcon\Loader;
setlocale(LC_MONETARY, 'en_US');

/** 
 * By default, namespaces are assumed to be the same as the path.
 * This function allows us to assign namespaces to alternative folders.
 * It also puts the classes into the PSR-0 autoLoader.
 */
$loader = new Loader();
$loader->registerNamespaces(array(
	'PhalconRest\Models' => __DIR__ . '/models/',
	'PhalconRest\Controllers' => __DIR__ . '/controllers/',
	'PhalconRest\Exceptions' => __DIR__ . '/exceptions/'
))->register();

/**
 * The DI is our direct injector.  It will store pointers to all of our services
 * and we will insert it into all of our controllers.
 * @var DefaultDI
 */
$di = new DefaultDI();

/**
 * $di's setShared method provides a singleton instance.
 * If the second parameter is a function, then the service is lazy-loaded
 * on its first instantiation.
 */
$di->setShared('config', function() {
	return new IniConfig("config/config.ini");
});

$di->set('modelsCache', function() {

        //Cache data for one day by default
        $frontCache = new \Phalcon\Cache\Frontend\Data(array(
                'lifetime' => 3600
        ));

        //Memcached connection settings
        $cache = new \Phalcon\Cache\Backend\File($frontCache, array(
                'cacheDir' => __DIR__ . '/cache/'
        ));

        return $cache;
});

/**
 * If our request contains a body, it has to be valid JSON.  This parses the 
 * body into a standard Object and makes that vailable from the DI.  If this service
 * is called from a function, and the request body is nto valid JSON or is empty,
 * the program will throw an Exception.
 */
$di->setShared('requestBody', function() {
	$in = file_get_contents('php://input');
	$in = json_decode($in, FALSE);

	// JSON body could not be parsed, throw exception
	if($in === null){
		throw new HTTPException(
			'There was a problem understanding the data sent to the server by the application.',
			409,
			array(
				'dev' => 'The JSON body sent to the server was unable to be parsed.',
				'internalCode' => 'REQ1000',
				'more' => ''
			)
		);
	}

	return $in;
});

/**
 * Out application is a Micro application, so we mush explicitly define all the routes.
 * For APIs, this is ideal.  This is as opposed to the more robust MVC Application
 * @var $app
 */
$app = new Phalcon\Mvc\Micro();
$app->setDI($di);

/**
 * Before every request, make sure user is authenticated.
 * Returning true in this function resumes normal routing.
 * Returning false stops any route from executing.
 */

/*
This will require changes to fit your application structure.  
It supports Basic Auth, Session auth, and Exempted routes.

It also allows all Options requests, as those tend to not come with
cookies or basic auth credentials and Preflight is not implemented the
same in every browser.
*/

/*
$app->before(function() use ($app, $di) {

	// Browser requests, user was stored in session on login, replace into DI
	if ($di->getShared('session')->get('user') != false) {
		$di->setShared('user', function() use ($di){
			return $di->getShared('session')->get('user');
		});
		return true;
	}

	// Basic auth, for programmatic responses
	if($app->request->getServer('PHP_AUTH_USER')){
		$user = new \PhalconRest\Controllers\UsersController();
		$user->login(
			$app->request->getServer('PHP_AUTH_USER'),
			$app->request->getServer('PHP_AUTH_PW')
		);
		return true;
	}

	
	// All options requests get a 200, then die
	if($app->__get('request')->getMethod() == 'OPTIONS'){
		$app->response->setStatusCode(200, 'OK')->sendHeaders();
		exit;
	}


	// Exempted routes, such as login, or public info.  Let the route handler
	// pick it up.
	switch($app->getRouter()->getRewriteUri()){
		case '/users/login':
			return true;
			break;
		case '/example/route':
			return true;
			break;
	}

	// If we made it this far, we have no valid auth method, throw a 401.
	throw new POSys\Exceptions\HTTPException(
		'Must login or provide credentials.',
		401,
		array(
			'dev' => 'Please provide credentials by either passing in a session token via cookie, or providing password and username via BASIC authentication.',
			'internalCode' => 'Unauth:1'
		)
	);

	return false;
});*/

/**
 * Collections let us define groups of routes that will all use the same controller.
 * We can also set the handler to be lazy loaded.  Collections can share a common prefix.
 * @var $exampleCollection
 */
$exampleCollection = new Phalcon\Mvc\Micro\Collection();
	$exampleCollection->setLazy(true)
		->setPrefix('/example')
		->setHandler(new PhalconRest\Controllers\ExampleController($di));

	// First paramter is the route, which with the collection prefix here would be GET /example/
	// Second paramter is the function name of the Controller.
	$exampleCollection->get('/', 'get');
	$exampleCollection->get('/{id:[0-9]+}', 'getOne');
	$exampleCollection->post('/', 'post');
	// $id will be passed as a parameter to the Controller's delete function
	$exampleCollection->delete('/{id:[0-9]+}', 'delete');
$app->mount($exampleCollection);

/**
 * After a route is run, usually when its Controller returns a final value,
 * the application runs the following function which actually sends the response to the client.
 *
 * The default behavior is to send the Controller's returned value to the client as JSON.
 * However, by parsing the request querystring's 'type' paramter, it is easy to install
 * different response type handlers.  Below is an alternate csv handler.
 */
$app->after(function() use ($app) {
	//Respond by default as JSON
	if(!$app->request->get('type') || $app->request->get('type') == 'json'){
		$app->response->setJsonContent($app->getReturnedValue());
		$app->response->send();
		return;
	}
	else if($app->request->get('type') == 'csv'){
		$return = $app->getReturnedValue();

		header('Content-type: application/csv');
		header('Content-Disposition: attachment; filename="'.time().'.csv"');
		header('Pragma: no-cache');
    	header('Expires: 0');
		
		$handle = fopen('php://output', 'w');
		fputcsv($handle, array_keys($return[0]));
		foreach($return as $line){
			fputcsv($handle, $line);
		}
		fclose($handle);
		return;
	}
});

/**
 * If the application throws an HTTPException, send it on to the client as json.
 * Elsewise, just log it.  
 * TODO:  Improve this.
 */
set_exception_handler(function($exception) use ($app){
	//HTTPException's send method provides the correct response headers and body
	if(is_a($exception, 'PhalconRest\\Exceptions\\HTTPException')){
		$exception->send();
	}
	error_log($exception);
	error_log($exception->getTraceAsString());
});

$app->handle();
