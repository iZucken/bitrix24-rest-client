
## Bitrix24 REST API Client Classes

#### Decisions

It was decided for this project to forfeit server-side API batching functionality.

#### Supported functionality

All of the functionality is supported through bitrix\rest\client\Bitrix24 interface.
Existing implementations include web hook client and oauth self-authorizing client, as well as some utility decorators.

These are generic wrappers, so you still need to know all of the methods and their parameters from the original documentation at this point.

Then there are bitrix\endpoint\* classes which wrap the clients to provide additional convenience.

###### Supported endpoints

* Lead CRUD

#### Known caveats

* Documented methods crm.\*.details.\* are not accessible through web hooks or rest application with full rights.
* In some cases API converts an array value in unexpected place to TRUE.

#### Hints

* Frequently 'Id not defined' error is a signal of a malformed request.
* To delete particular multi-field value object, pass id and an empty string or null as its value.
