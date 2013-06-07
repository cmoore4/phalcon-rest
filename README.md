PhalconRest
===========

A Base project for APIs using the [Phalcon][phalcon] framework
---------------------------------------------------

The Phalcon framework is an awesome PHP framework that exists as a C-extension to the language.
This allows it to be incredibly fast.  But aside from its quickness, it is an amazingly
powerful fraemwork with excellent [documentation][phalconDocs] that follows many best practises of
modern software development.  This includes using the Direct Injection pattern to handle service
resolution across classes, a PSR-0 compliant autoloader, MVC architecture (or not), caching
handlers for database, flatfile, redis, etc.. and a ton of additional features.

The purpose of this project is to establish a base project with Phalcon that uses the best practices
of both the Phalcon Framework and [API Design][apigeeBook], as discussed in the seminal work from Apigee.

This project establishes much of the boilerplate needed to create a well-designed API.  It doesn't seek
to implement any of your program logic, merely facilitate the parsing of requests, show how an auth
system could be incorporated, show simple cache strategies, and stub out basic response handling.

It is highly recommended to read through the index.php, HTTPException.php and RESTController.php files, as
I've tried to comment them extensively.

Also included is an .htaccess file for Apache servers that will help with CORS requests-- though it is
not compliant with REST, it does help for development purposes.


API Assumptions
---------------

**URL Structure**

```
/path1/path2?q=(search1:value1,search2:value2)&fields=field1,field2,field3&limit=10&offest=20&type=csv&suppress_error_codes=true
```

**Request Bodies**

Request bodies will be submitted as valid JSON.

The Fields
-----------

**Search**

Searches are determined by the 'q' parameter.  Following that is a parenthesis enclosed list of key:value pairs, separated by commas.

> ex: q=(name:Jonhson,city:Oklahoma)

**Partial Responses**

Partial responses are used to only return certain explicit fields from a record. They are determined by the 'fields' paramter, which is a list of field names separated by commas, enclosed in parenthesis.

> ex: fields=(id,name,location)

**Limit and Offset**

Often used to paginate large result sets.  Offset is the record to start from, and limit is the number of records to return.

> ex: limit=20&offset=20   will return results 21 to 40

**Return Type**

Overrides any accept headers.  JSON is assumed otherwise.  Return type handler must be implemented.

> ex: type=xml

**Suppressed Error Codes**

Some clients require all responses to be a 200 (Flash, for example), even if there was an application error.
With this paramter included, the application will always return a 200 response code, and clients will be
responsible for checking the response body to ensure a valid response.

> ex: suppress_error_codes=true

Responses
---------

All route controllers must return an array.  This array is used to create the response object.

**JSON**

JSON is the default response type.  It comes with an envelope wrapper, so responses will look like this:

```
GET /example?q=(popular:true)&offset=1&limit=2&fields=(name,location,prince)

{
    "_meta": {
        "count": 2,
        "status": "SUCCESS"
    },
    "records": [
        {
            "location": "Pride Rock",
            "name": "Nala",
            "prince": "Simba"
        },
        {
            "location": "Castle",
            "name": "Sleeping Beauty",
            "prince": "Charming"
        }
    ]
}
```

**CSV**

CSV is the other implemented handler.  It uses the first record's keys as the header row, and then creates a csv from each row in the array.

```
name,location,prince
Nala,"Pride Rock",Simba
"Sleeping Beauty",Castle,Charming
```

Errors
-------

PhalconRest\Exception\HTTPException extends PHP's native exceptions.  Throwing this type of exception 
returns a nicely formatted JSON response to the client.

```
throw new \PhalconRest\Exceptions\HTTPException(
	'Could not return results in specified format',
	403,
	array(
		'dev' => 'Could not understand type specified by type paramter in query string.',
		'internalCode' => 'NF1000',
		'more' => 'Type may not be implemented. Choose either "csv" or "json"'	
	)
);
```

Returns this:

```
{
    "_meta": {
        "status": "ERROR"
    },
    "error": {
        "devMessage": "Could not understand type specified by type paramter in query string.",
        "error": 403,
        "errorCode": "NF1000",
        "more": "Type may not be implemented. Choose either \"csv\" or \"json\"",
        "userMessage": "Could not return results in specified format"
    }
}
```


Example Controller
-------------------

The Example Controller sets up a route at /example and implements all of the above query parameters.
You can mix and match any of these queries:

>  api.example.local/example?q=(name:Belle)

>  api.example.local/example?fields=(name,location)

>  api.example.local/example/5?fields=(name)

>  api.example.local/example?type=csv

>  api.example.local/example?q=(popular:true)&offset=1&limit=2&type=csv&fields=(name,location,prince)

[phalcon]: http://phalconphp.com/index
[phalconDocs]: http://docs.phalconphp.com/en/latest/
[apigeeBook]: https://blog.apigee.com/detail/announcement_new_ebook_on_web_api_design