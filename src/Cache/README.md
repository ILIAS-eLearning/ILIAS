ILIAS Caching Service
=====================

The ILIAS Caching Service replaces the old GlobalCache Service with ILIAS 9.
Internally, the GlobalCache service currently also uses the new cache service,
but will be removed in the medium term.

# Cache data

## When does it make sense to cache data?

Data caching should be used when simple data does not change between two
requests and is not user-independent. It must also be clear when the data in the
cache becomes invalid and needs to be updated. Caching must also always include
reading the data from a persistence layer (database, file system) as a fallback.

## Differentiation from artifacts

Artifacts are always suitable when the data not only does not change between two
requests, but is always the same depending on the exact version of the ILIAS
source code. Artifacts are generated when ILIAS is built, cache is filled at
runtime. This can be easily explained using the following example:

The translations in ILIAS (Language Service) would be a candidate for artifacts
under the following circumstances:

> The exact content of the translations in ILIAS is only available in the form
> of language files. In this case, this content could be built as an Artifact
> and
> thus be available with high performance.

But then why are translations not in Artifacts?

> ILIAS offers administration in the GUI for the translations, i.e. the exact
> translations do not necessarily have to come from the language files, but can
> change through customization.

Would the translations be a candidate for the cache?

> Yes, under the following circumstances: It is known exactly when the content
> of the translations changes (e.g. through changes to the language files
> through
> an ILIAS update or through manual adjustments in the administration. If these
> situations are known, the language service can invalidate the cache at the
> right
> moment, read the correct content and rebuild the cache.

## How to use the cache service

The data is stored in a cache container. To obtain such a container, a cache
container request is implemented. This is very simple and can also be
implemented on existing classes, for example, which is often useful for
repositories.

```php
use ILIAS\Cache\Container\Request;
 
class MyRepository implements Request {
    public function getContainerKey() : string{
        return 'my_repository';
    }
    public function isForced() : bool{
        return true;
    } 
}
```

The two methods to be implemented can be described as follows:

`getContainerKey`: This is the namespace for your container. ILIAS will 
check that two components are not using the same namespace and can therefore 
overwrite each other's cache.

`isForced`: Individual containers can be activated via the setup, see the README
for the setup. However, this can be overwritten with isForces if the cotnaienr
is to be active in any case. This should not be used in the core but only in 
plugins.

The container can then be obtained via the cache service:

```php
global DIC;

$container = $DIC->chache()->get(new MyRepository());
```
The following methods are then available in the container:

```php
use ILIAS\Cache\Container\Container;
/** @var Container $container */
$container = $DIC->chache()->get(new MyRepository());

$container->has('key'); // Check if a key exists
$container->get('key'); // Get a value
$container->set('key', 'value'); // Set a value
$container->delete('key'); // Delete a value
$container->flush(); // Delete all values
$container->lock(3.5); // Lock the container, see below
```

## Storable Values
The service only supports simple scalar values for storage, e.g. no objects 
can be stored. The values can be stored individually or as an array.

## Locking
The COntainer can be locked for a certain period of time, i.e. `has` always  
returns false during this time, `get` always returns null.
