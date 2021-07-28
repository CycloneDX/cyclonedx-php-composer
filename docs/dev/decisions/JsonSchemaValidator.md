# Choosing a JSON schema validator

There are 3 major candidates: 
* [justinrainbow/json-schema]
* [opis/json-schema]
* [swaggest/json-schema]

## Option: [justinrainbow/json-schema]

CONS:
* -> Does not claim fully to support _draft7_ - which is needed for CDX schema.
* Is shipped with composer v2.0 as `^5.2.10`.
  -> Which might lead to conflicts in the composer plugin, if we are using a newer version.

PROS:
* php: >=5.3.3
* No further dependencies

## Option: [opis/json-schema]

multiple versions split for different php compatibility:
* v2.0 -> php: ^7.4 || ^8.0
* v1.0 -> php: >=7.0

CONS:
* Claims to have full support of _draft7_.
* v1 has insuppiciecient support for schema draft7.  
  we need to support php7.3 at the moment. so a switch to v2 wi not entirely possible.  
  writing a switching translator that can handle v1 and v2 is ugly and undesired.

PROS:
  * v2 is pretty cool to code with, it is capable of every needed feature.

## Option: [swaggest/json-schema]

* Has some dependencies.

PROS:
* was used for unit/integration tests already.
* php: >=5.4

CONS:
* Claims to have full support of _draft7_.
* sill version 0.*


## Decision

Unless support of php7.3 is dropped, [swaggest/json-schema] will be used.  
After dropping support of php7.3 a switch to [opis/json-schema] v2 will be possible.



[justinrainbow/json-schema]: https://packagist.org/packages/justinrainbow/json-schema
[opis/json-schema]: https://packagist.org/packages/opis/json-schema
[swaggest/json-schema]: https://packagist.org/packages/swaggest/json-schema
