Types
-----------

Types is a simple extension to Reflection classes, allowing to Define lists and tuples. Currently 
only subtypes, ancestors and equals is given by the interface. 

ATTENTION
---------

This package is currently only used in the BackgroundTasks! You are invited to use it and find other
use cases. Any suggestions, improvements and pull requests are highly appreciated.

Usage
-----

```
$objectType = new SingleType(ilObject2::class);
$userType = new SingleType(ilUser::class);

$this->assertTrue($userType->isExtensionOf($objectType));

$objectTypes = new ListType(ilObject2::class);
//or $objectTypes = new ListType($objectType);
$userTypes = new ListType(ilUser::class);

$this->assertTrue($userTypes->isExtensionOf($objectTypes));
$this->assertFalse($userTypes->isExtensionOf($objectType));

$objectTuple = new TupleType([ilObject2::class, new ListType(ilObject2::class));
$userTuple = new TupleType([ilUser::class, new ListType(ilUser::class)]);
$this->assertTrue($userTuple->isExtensionOf($objectTuple))

```

UnitTests
---------
Unit tests are currently coupled with BackgroundTasks, they will be added later.

(Types need to be coupled to something -> Describing their own type lacks inheritance checking.)