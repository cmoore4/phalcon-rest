<?php

/**
 * routeLoader loads a set of Phalcon Mvc\Micro\Collections from
 * the collections directory.
 *
 * php files in the collections directory must return Collection objects only.
 */

return call_user_func(function(){

	$collections = array();
	$collectionFiles = scandir(dirname(__FILE__) . '/collections');

	foreach($collectionFiles as $collectionFile){
		$pathinfo = pathinfo($collectionFile);

		//Only include php files
		if($pathinfo['extension'] === 'php'){

			// The collection files return their collection objects, so mount
			// them directly into the router.
			$collections[] = include(dirname(__FILE__) .'/collections/' . $collectionFile);
		}
	}

	return $collections;
});