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


API Assumptions
---------------

URL Structure:

```
/index.php/path1/path2?q=(search1:value1,search2:value2)&fields=field1,field2,field3&limit=10&offest=20&type=csv&suppress_error_codes=true
```

The Fields:
-----------

*Search*
Searches are determined by the 'q' parameter.  Following that is a parenthesis enclosed list of key:value pairs, separated by commas.
ex: q=(name:Jonhson,city:Oklahoma)

*Partial Responses*
Partial responses are used to only return certain explicit fields from a record. They are determined by the 'fields' paramter, which is a list of field names separated by commas, enclosed in parenthesis.
ex: fields=(id,name,location)

*Limit and Offset*
Often used to paginate large result sets.  Offset is the record to start from, and limit is the number of records to return.
ex: limit=20&offset=20   will return results 21 to 40

*Return Type*
Overrides any accept headers.  JSON is assumed otherwise.  Return type handler must be implemented.
ex: type=xml

*Suppressed Error Codes*
Some clients require all responses to be a 200 (Flash, for example), even if there was an application error.
With this paramter included, the application will always return a 200 response code, and clients will be
responsible for checking the response body to ensure a valid response.
ex: suppress_error_codes=true

[phalcon]: http://phalconphp.com/index
[phalconDocs]: http://docs.phalconphp.com/en/latest/
[apigeeBook]: https://blog.apigee.com/detail/announcement_new_ebook_on_web_api_design