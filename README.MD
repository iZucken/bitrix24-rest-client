
## Bitrix24 REST API Client Classes

#### Decisions

It was decided for this project to forfeit server-side API batching functionality.

#### Supported functionality

All of the functionality is supported through bitrix\rest\client\Bitrix24 interface.
Existing implementations include web hook client and oauth self-authorizing client, as well as some utility decorators.

These are generic wrappers, so you still need to know all of the methods and their parameters from the original documentation at this point.

Then there are bitrix\endpoint\\* classes which wrap the clients to provide additional convenience.

###### Supported endpoints

* Lead CRUD

#### Known caveats

* Documented methods crm.\*.details.\* are not accessible through web hooks or rest application with full rights.
* In some cases API converts an array value in unexpected place to TRUE.

#### Hints

* Frequently 'Id not defined' error is a signal of a malformed request.
* To delete particular multi-field value object, pass id and an empty string or null as its value.

#### Documentation bits

##### Update methods

###### Multi-fields

If ID is not specified in a multi-field value, a new value will be created.
If non-existent ID is specified, the value will be ignored.

##### List methods

As pretty much all CRUD methods list is almost generic in structure among many entities.
Available to the method 'fieldName' are based on particular entity. 

* Not every list has pages or parameters
* Based on docs, it is possible for particular list to only accept part of list parameters
* It is possible for fields in list parameters to be ignored arbitrarily
* Few lists appear to support LIMIT but this is yet uncertain
* There can be some undocumented derivative fields available for filtering like `HAS_PHONE`, `HAS_*`
* Particular list method can have it's own required filter fields

###### List method parameters:

` 'ORDER' => [ ... [ fieldName => 'ASC' | 'DESC' ] ] `

Pretty straight forward.

` 'SELECT' => [ ... fieldName ] `

Defines what to include in list results. 'ID' or any other identity is always selected.

Some special options:
 * `'*'` - all normal non-multiple fields
 * `'UF_*'` - all user fields

` 'START' => int `

Offset given in a previous list response to continue from. Some endpoints also offer 'navigation' parameter which seemingly does the same???

` 'FILTER' => [ ... [ filterType.fieldName => filterValue ] ] `

Possible values for 'filterType':

`'='` - equals (appears to be default)

`'!'` - not equals

`'<'` `'>'` `'<='` `'>='` - comparison, works on strings

`'%'` - makes value a wildcard like `'%value%'`

Use array in 'filterValue' to simulate 'IN' condition.

Some lists appear to support '%' in 'fieldValue', thus enabling custom wildcards, this shall be clearly stated by a particular entity or its list method.

###### List response:

Generally consists of fields:

`'result'` - actual filter result set, generally up to 50 results for paged lists

`'total'` - total amount of possible filtered results

`'next'` - optional field for paged lists, to be used in 'START' filter parameter

