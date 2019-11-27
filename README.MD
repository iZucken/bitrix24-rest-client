
## Bitrix24 REST API Client Classes

#### Decisions

It was decided for this project to forfeit server-side API batching functionality.

#### Supported functionality

All of the functionality is supported through bitrix\rest\client\Bitrix24 interface.
Existing implementations include web hook client and oauth self-authorizing client, as well as some utility decorators.

These are generic wrappers, so you still need to know all of the methods and their parameters from the original documentation at this point.

Then there are bitrix\\endpoint\\* classes which wrap the clients to provide additional convenience.

#### Usage

```
$bitrixClient = new \bitrix\rest\client\OauthAutoLogin(
    $config['bitrix']['uri'],
    new \bitrix\rest\OauthFullCredentials(
        $config['bitrix']['id'],
        $config['bitrix']['secret'],
        $config['bitrix']['user'],
        $config['bitrix']['pass']
    ),
    new \bitrix\storage\File(__DIR__.'/bitrix.json'),
    new \Psr\Log\NullLogger()
);
$this->bitrixClient->call('app.info');
```

#### Known caveats

* Documented methods crm.\*.details.\* are documented but not accessible.
* In some cases API converts an array to TRUE if it expects a boolean.
* Some field types, like multiple-choice integer field, in some cases may be equal to `false` when nothing is chosen, but this library won't allow you to provide boolean value.
In these cases, such values also cannot be unset by just providing an empty array, false, null, or any combination of them.
* Even if it is not specified, fields having regex rule are considered 'required' implicitly
* USERFIELD REGEX RULES ARE NOT CHECKED FOR VALIDITY, INVALID REGEXES ALWAYS PASS VALIDATION

#### Hints

* To delete particular multi-field value object, pass id and an empty string or null as its value.

#### Documentation bits

##### Update methods

###### Multi-fields ( crm_multifield )

If ID is not specified in a multi-field value, a new value will be created.
If non-existent ID is specified, the value will be ignored.

##### Generic list methods

Usable in list parameters 'fieldName's depend on particular entity.
There can be some undocumented derivative fields available for filtering like `HAS_*FIELD*`.
Generic lists are always paginated by 50 items.

* Based on docs, it is possible for particular list to only accept part of list parameters
* It is possible for fields in list parameters to be ignored arbitrarily
* It is possible for particular list methods to have required filter fields

###### List method parameters:

These parameters are wrapped inside GenericListFilter class

` 'ORDER' => [ ... [ fieldName => 'ASC' | 'DESC' ] ] `

Pretty straight forward.

` 'SELECT' => [ ... fieldName ] `

Defines what to include in list results. 'ID' or any other identity is always selected.

Some special options:
 * `'*'` - all normal non-multiple fields
 * `'UF_*'` - all user fields

` 'start' => int `

Offset given in a previous list response to continue from. Must be a multiple of 50.

Some endpoints also offer 'navigation' parameter which seemingly does the same???

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
