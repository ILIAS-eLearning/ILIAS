# Repository Pattern

## What, why and how?

### Architectural Perspective

From the perspective of an architect of the application, a repository can be
defined as such:

**A repository is the single source of truth for a domain of an application.**

`Repository` here is a name that classifies the entities we want to create and
that fullfil a special purpose in our application design. The role is, to be a
`single source of truth`. `Single` here is crucial. It means, that only the
`Repository` and no other entity may tell or acknowledge facts. A `truth` or
`fact` here can be understood as some information or a state of the application.
The `Repository` does not need to know every truth about the application, but
rather specializes on one `domain` in the application. A `domain` can be understood
as a field of expertise or knowledge the application (or a component of the
application) cares about. So in fact, an application most likely won't have one
repository but rather many of them, that all take care of one domain in the
application.

### Technical Perspectice

This rather abstract perspective can be complemented with a more technical one.
Here, we can see, that a `Repository` in fact abstract the underlying database.
Via its interface it captures common patterns of access, may it be read or write,
to the knowledge in the domain it is responsible for. Since it is a single entity
that mediates all access to the database, we can control where, if and when
persistence to the database actually happens.

Although a `Repository` can be understood as a database from a technical perspective
we should not confuse it with a database (as we will see later on) in general.
It is thus not the goal of a `Repository` to be a SQL-abstraction. An implementation
of a `Repository` over a relational database might rather use an SQL-abstraction
to implement the required access patterns.

### Alternatives

There are other patterns and concepts to implement persistency that could be
considered alternatives to the repository pattern. Two of those are used in ILIAS
and thus deserve a closer look here.

#### Active Record

An `Active Record` is an object that carries around its own persistence logic,
e.g. in the form of `read`- and `update`-methods that serialize the object to
a database. Most of the ilObjects are active records in that regard and ILIAS
also brings a service (`Service/ActiveRecord`) that makes it easy to implement
them.

Active records can be considered to be easier to understand and handle then
repositories. They have some well known [downsides](https://www.mehdi-khalili.com/orm-anti-patterns-part-1-active-record),
most prominently the SRP-violation which results in decreased testability and
modularization when using active records.


#### Just Query

By "Just Query" we mean the habit to just build a SQL-query according to ones needs
and ask the database to process it. This pattern is also found in ILIAS in numerous
places, often involving complex queries over multiple tables.

This approach has the advantage that it allows to precisely access the required data
(and not more), while the database may take care of executing the query efficiently.
One downside is that this approach is brittle regarding schema- or name changes in
the database, which is especially problematic when data from different components is
included. Many advantages of a repository can also be understood as disadvantages
or problems with "just queries".


### Advantages

* **The explicit interface makes writing tests easy.** - The repository introduces
an interface that captures all access patterns to the underlying data. This interface
can be written down as PHP `interface` and then used to create mocks during testing.
This of course means, that components using the repository do no create instances
of it themselves but instead get that dependency injected.
* **The explicit interface makes writing alternative implementations (import/export,
caching, ...) easy.** - The explicit interface has even more advantages. Although we
will most probably implement repositories over a relational database mostly, we are
not limited to that. We could well implement a repository that adds caching over
another repository or reads its data from other sources like files.
* **The database schema is completely contained in the implementation of the
repository.** - If we implement the repository over a relational database, no other
component than the repository is allowed to access the data contained in the
repositories tables (by definition). We thus are free to change the schema whenever
and however we want, because nobody besides the repository should ever need access
to that tables.
* **Database integrity can be handled in one place.** - Since all access to the
database is mediated through the repository we have an obvious place to handle
integrity constraints. We could use `ilAtomQuery` to coordinate writes to multiple
tables, while we can design the interface of the repository in a way that only
allows to make writes that respect integrity constraints.
* **Query and write patterns get meaningful names.** - Since all access patterns
are encoded as methods on the interface of the repository, these patterns automatically
acquire names that can help to understand code that is using the repository. This is
especially helpful compared to bare queries that only reveal their intent grudgingly.
* **The repository forces you think about access to the persistence layer.** - Since
components that want to use persistence need the repository we can see clearer where
persistency actually happens. Objects read from the repository may be passed to
other components for further processing while we can be sure that these may only
change these objects on the persistence layer if they also have the repository. Compared
to active records that could be saved by every component seeing the active record
objects this will clarify the architecture of a component regarding persistence.


### Disadvantages

* **You write an additional interface.** - No pain, no gain. Regarding an active
record or the "just query"-approach we should write an additional interface if
we really want to take advantage of the repository pattern.
* **You cannot just query or change some data on the database.** - We will most
probably come into situations where we currently just call `update()` on an
active record or `$ilDB->query(..)` to retrieve some data but do not have the
appropriate repository handy. This is the downside of having a clearer architecture.
* **You have to establish clear boundaries between different domains.** - Since
the repositories need to be authoritative for one domain, we in fact need to know
what that domain is (and what it is not). We cannot simply use the same piece of
data in two domains but instead need to duplicate if required.
* **Integrity over domains is hard.** - Repositories allow to keep integrity
constraints in one domain but do not offer strategies to coordinate writes over
multiple domains. We will either need to introduce further measures to allow
for transactions over multiple domains or our components will need to deal
with possible inconsistencies between different domains. This in fact is already
the case: Do we really know that the user we reference in our records exists in
the user database?
* **The repository pattern does not fit "reporting queries" well.** - Repositories
are good for working with object graphs, but there are situations where we need
an overview over data that spans multiple domains. This is often the case when we
have tables that are filled via queries containing multiple joins. We could use
multiple repositories but we will make more roundtrips to the database and possibly
load data we do not require then. If we need more performance we may build a
specialized repository (or tables only) that fit the needs of our report and gets
updated frequently or event-driven.
* **The repository pattern is not about performance.** - The repository is a way
to gain some advantages, but performance is not a target. Inside a repository
implementation we might use known measures, like introducing indizes on the
database or limiting the data we actually query. Caching is easy to implement
for a repository. Choosing good access patterns (i.e. methods on the repository
interface) may help a lot for both efforts. Getting good performance when
multiple repositories are involved may require to duplicate data in a new and
specialized repositories. Anyway, solving performance issues will require some
work and does not come for free when using the repository pattern.

### Guidelines for Implementation

* **Write the interface and data-objects first while implementing consumers of the
repository.** - This allows us to work on the interesting stuff first, i.e. the
business logic we are implementing. While doing that we can discover which data
and access pattern the problem at hand actually requires and the interface of the
repository can evolve accordingly. 
* **Inject the repository as dependency.** - This allows us to use different
implementations of the repository, e.g. for testing and decouples the business-
logic from the persistence layer.
* **Make sure to understand that this is not about SQL. Do not try to build an
SQL-abstraction.** - A repository could (and very often will) be implemented on
top of an SQL-database. However, it is not an abstraction of the underlying SQL-
database but is a conceptual entity on its own. If we cannot name our access
patterns to the repository or write queries with a lot of parameters that modify
the behaviours, the repository might not be a good fit to the problem at hand.
* **Repositories work well with immutable objects. Try to think about how data flows
through the program.** - A repository conceptually is a source and/or sink in some
data flow. A typical request might pull some entity from a repository, modify it
according to some user input and then put the modified version back to the
repository. If this request is modeled using immutable objects, the actual flow
of data can become more obvious then in typical stateful OOP implementations
since changes need to be propagated explicitly. 
* **Reference other domains by ID only, only tell and acknowledge facts you know
about.** - A core idea of the repository pattern is, that conceptually there only
is one source of truth for a given bunch of facts (i.e. domain). If we need to
reference facts from other domains this should be done by ids, not by the facts
themselves to maintain that single source of truth principle. However, in some
situations we might need to copy facts from one domain into another, e.g. when
building reports. Conceptually this makes them different facts, even if they seem
to contain the same information. Updating one repository by using another will
then become a problem on its own.
* **You may write facilities that aggregate information from different repositories or
write to different repositories, but these may only know about the interfaces,
not the implementation.** - In some situations it might become handy to have
facilities that update multiple repositories at the same time. If such a facility
is implemented, it should only use interfaces to the required repositories, not
implementation details. Some key advantages of repositories would vanish otherwise.
Also keep in mind that such a facility can not give guarantees about consistency
of the involved repositories since the repositories also might be used alone. If
we need consistency guarantees we do not have two different domains.


## Use in ILIAS

The repository pattern may be applied under various circumstances in ILIAS:

* **Organize and partition storage of data inside ilObjects** - The repository
pattern may be used to add internal structure to the, sometimes big, ilObjects.
We might, e.g. introduce one repository for settings and another one for business
logic related data. This could be used as a strategy to clean up these objects
or to make the objects testable by automated tests.
* **Expose data of a service to consumers** - The repository pattern might be
used as an API for consumers of some service. Other services could hint on some
abstract interface to the repository and use it as a convenient entry point to
add data to or query data from the service.
* **Organize and partition storage of data in plugins** - Plugins deal with
similar problems then ilObjects. The requirement to store and retrieve data, often
times for different domains, fits repository well. Repositories can structure
these accesses and make a plugin more testable.

## Conventions 

If you implement the repository in your component, you SHOULD follow these
conventions:

* The interface to the repository SHOULD be called `XYZRepository`, where XYZ
is the domain of your component which the repository controls.
* Implementations of the repository SHOULD repeat the `XYZRepository` name and
also tell how they implement the repository. E.g. `XYZDatabaseRepository` or
`XYZAPCCacheRepository`.
* The methods to request data from the repository SHOULD be prefixed with `get`
or `is`, `has` or `does` if they return booleans. They SHOULD be named according to the pattern
in which they request information.
* The methods to change data in the repos SHOULD be prefixed with `update`.
They SHOULD be named according to the pattern in which they update information. 
* The methods to remove data from the repo SHOULD be prefixed with `delete`.
They SHOULD be named according to the pattern in which they delete information. 
* Repositories SHOULD NOT be dealing with primitive data if they could deal
with structured and typed data instead. This means that objects should be preferred
over arrays when requesting or inserting data.

## Examples

The following code-snippets are examples to get a better understanding of the
repository pattern. Furthermore, these examples can be used as templates for
the development of new features and refactoring of already existing ones.

In the start of this chapter, we discuss and describe at first how and why the
examples are implemented as they are. If you are just really interested in an 
example implementation of the pattern with the usage of SQL, skip to the chapter 
*Simple example* or just look at the code examples folder.

Note that this examples are not perfect for every situation. Like always in
software development, the developer has to decide which is the best way of usage
for his or hers applications.

For the moment, we found 3 types of examples to use the repository pattern for. We
even found some more possible usages, which after further analysis turned out to be
anti-patterns. They are documented under the chapter of *Bad Examples*

For simplicity reasons, the class of the objects to persist is in all following
example the same. The class is called `ilGeoLocation` and is immutable:

```php
    class ilGeoLocation {
        protected $id;
        protected $title;
        protected $latitude;
        protected $longitude;
        protected $expiration_timestamp;

        public __construct(int $a_id, string $a_title, float $a_latitude, 
                           float $a_longitude, \DateTimeImmutable $a_expiration_timestamp)
        {
            $this->id = $a_id;
            $this->title = $a_title;
            $this->latitude = $latitude;
            $this->longitude = $longitude;
            $this->expiration_timestamp = $a_expiration_timestamp;
        }

        public function getId() : int { return $this->id; }
        public function getTitle() : string { return $this->title; }
        public function getLatitude() : float { return $this->latitude; }
        public function getLongitude() : float { return $this->longitude; }
        public function getExpirationAsTimestamp() : int { return $this->ExpirationTimestamp->getTimestamp(); }
        public function getExpirationAsImmutablDateTime() : \DateTimeImmutable { return $this->expirationTimestamp; }
    }
```

For these examples, we define an interface called `ilGeoLocationRepository`, which is
the contract for all the other example Repository-Classes. The implementing classes
of this interface do interact with the database or whatever medium is used. In most
cases, those interactions are a set of different **CRUD**-Operations (**Cr**eate,
**R**ead, **U**pdate and **D**elete). E.g. the **read**-Operation contains simple
methods like returning a single object, identified by an ID. On the other hand,
others return an array of objects or just a boolean, if a requested object exists.
The same rules apply for updating or even deleting multiple objects at once.

Always keep in mind, that those CRUD-Operations are not only limited to an
SQL-Statement for a database, but also are possible for a filesystem. But for most of this
examples, we use a database. The database is injected in the constructor. The
benefit of injecting the database to the repository class is to make mocking for
unit tests easier.

The following few lines of code represent an interface for a repository, called 
`ilGeoLocationRepository`:

```php
    interface ilGeoLocationRepository {
    
        // Create operations
        public function createGeoLocation(
			string $a_title,
			float $a_latitude,
			float $a_longitude,
			\DateTimeImmutable $a_expiration_timestamp
		);

        // Read operations
        public function getGeoLocationById(int $a_id);
        public function getGeoLocationsByCoordinates(float $a_latitude, float $a_longitude);
        public function ifLocationExistsById(int $a_id) : bool;
        public function ifAnyLocationExistsByGeoLocation(float $a_latitude, float $a_longitude) : bool;
        
        // Update operations
        public function updateGeoLocationObject(ilGeoLocation $a_obj);
        public function updateGeoLocationTimestampByCoordinates(float $a_searched_latitude, float $a_searched_longitude, \DateTimeImmutable $a_update_timestamp);
        
        // Delete operations
        public function deleteGeoLocationById(int $a_id);
        public function deleteGeoLocationsByCoordinates(float $a_latitude, float $a_longitude);
        public function deleteExpiredGeoLocations();
    }
```

For the full code of each example, be sure to visit the "code-examples"-folder.

### Simple example

A simple example for the Repository-Pattern would be a class, that *writes* objects
to a database and *reads* them from the database if needed.

In the following example, we want to *write* and *read* objects that contains
GeoLocations. We call the class for this objects `ilGeoLocation` and the class
that is used to persist them `ilGeoLocationRepository`.

In this simple example, these operations are written in *SQL* and executed with
*ilDB*. Depending on the operation, the data for the SQL instruction is read from
the object/array or an object will be create from the response of the database. Following
the different methods for different CRUD-Operations

#### CRUD-Operations

**Create operations**

* Create an object out of the given data: create_____(...$obj_data)
    * For example: createGeoLocation(string $a_title, float $a_latitude, float $a_longitude, \DateTimeImmutable $a_expiration_timestamp)
    * *Note:* There is a good reason, why the arguments are given as primitives
    instead of an actual object. The actual object needs to have an id.
    Since the only entity which knows about facts of the domain, and ids are also
	facts of the domain, is the repository, the repository object is the only
	entity that can create new ids for new objects.

```php
    // Get next free id for object
    $id = $this->db->nextId($this->db->quoteIdentifier(self::TABLE_NAME));

    // Insert in database
    $this->db->insert($this->db->quoteIdentifier(self::TABLE_NAME), array(
        'id' => array('integer', $id),
        'title' => array('text', $a_title),
        'latitude' => array('float', $a_latitude)
        'longitude' => array('float', $a_longitude),
        'expiration_timestamp' => array('timestamp', $a_expiration_timestamp->getTimestamp())
    ));


	// Return the new created object or just the id
	return new ilGeoLocation(
		$id,
		$a_title
		$a_latitude,
		$a_longitude,
		$a_expiration_timestamp
	);
```

**Read operations**

* Get specific object by unique identifier. Returns object: get____ById($id)
    * For example: get*GeoLocation*ById(int $id) : ilGeoLocation

```php
    // Set up SQL-Statement
    $query = 'Select * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
             ' WHERE id = ' . $this->db->quote($a_id, 'integer');

    // Execute query
    $result = $this->db->query($query);

    // Fetch row for returning
    if($row = $this->db->fetchAssoc($result))
    {
		// Create object out of fetched data and return it
		return new ilGeoLocation(
			(int)$row['id'],
			$row['title'],
			(float)$row['latitude'],
			(float)$row['longitude'],
			new DateTimeImmutable($row['expiration_timestamp'])
		);
	}

	throw new \InvalidArgumentException("Unknown id for geolocation: $a_id");
```

* Get all objects with specified attributes. Returns Array: get____By____($attribute)
    * get*GeoLocations*By*Coordinates*($a_latitude, $a_longitude) : array

```php
	// Set up SQL-Statement
	$query = 'Select * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
			 ' WHERE latitude = ' . $this->db->quote($a_latitude, 'float') .
			 ' AND longitude = ' . $this->db->quote($a_longitude, 'float');

	// Execute query
	$result = $this->db->query($query);

	// Fill array with all matching objects
	$locations = array();
	while($row = $this->db->fetchAssoc($result))
	{
		// Create object and add it to list
		$locations[] = new ilGeoLocation(
			(int)$row['id'],
			$row['title'],
			(float)$row['latitude'],
			(float)$row['longitude'],
			new DateTimeImmutable($row['expiration_timestamp'])
		);
	}

	// Return list of objects (might be empty if no object was found)
	return $locations;
```

* Check if specific object exists. Returns Boolean: if____ExistsById($id)
    * if*GeoLocation*ExistsBy*Id*(int $id) : bool
    
```php
	// Set up SQL-Statement
	$query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
		' WHERE id = ' . $this->db->quote($a_id, 'integer');

	// Execute statement
	$result = $this->db->query($query);

	// Return if object was found
	return $result['count'] > 0;
```

* Check if any object with given attributes exist. Returns Boolean: ifAny____ExistsBy____($attribute)
    * ifAny*GeoLocation*ExistsBy*Coordinates*(string $a_latitude, string $a_longitude) : bool

```php
	// Set up SQL-Statement
	$query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
			 ' WHERE latitude = ' . $this->db->quote($a_latitude, 'float') .
			 ' AND longitude = ' . $this->db->quote($a_longitude, 'float');

	// Execute statement
	$result = $this->db->query($query);

	// Return if any object was found
	return $result['count'] > 0;
```

**Update operations**

* Update specific object, identified by its id: update______($obj)
    * update*GeoLocation*(ilGeoLocation $a_obj)

```php
	// Update of one entire geo location object
	$this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
		// Update columns (in this case all except for id):
		array('title' => array($a_obj->getTitle(), 'text')),
		array('latitude' => array($a_obj->getLatitude(), 'float')),
		array('longitude' => array($a_obj->getLongitude(), 'float')),
		array('expiration_timestamp' => array($a_obj->getExpirationAsTimestamp(), 'timestamp')),
		// Where (in this case only the object with the given id):
		array('id' => array($a_obj->getId(), 'int'))
	);
```

* Update a set of objects with the same attributes at once: update____By____($_searched_attributes, $a_new_attributes)
    * updateGeoLocationTimestampByCoordinates(string $a_searched_latitude, string $a_searched_longitude, \DateTimeImmutable $a_update_timestamp)
    * *Note:* For a lot of use cases we should prefer updating an entire object instead 
    of just a single attribute. But if you want to update some specific attributes 
    on a set of object, we might want to introduce an update like this. The name of
	the method captures the intent while we also may gain speed over fetching a list
	of all wanted objects and update them one by one.

```php
	// Update for single attribute of a set of geo location objects
	$this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
		// Update columns (in this case only the timestamp):
		array('expiration_timestamp' => array('timestamp', $a_update_timestamp->getTimestamp())),
		// Where (in this case every object on the given location):
		array('latitude' => array($a_searched_latitude, 'float'),
			  'longitude' => array($a_searched_longitude, 'float'))
	);
```

**Delete operations**

* Delete specific object: delete____($id)
    * delete*GeoLocation*($a_id)

```php
    // Set up delete query
    $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
    ' WHERE id = ' . $this->db->quote($a_id, 'integer');

    // Execute delete query
    $this->db->manipulate($query);
```

* Delete a set of objects with a given attribute. Attributes are given as Argument: delete____By____($attribute)
    * delete*GeoLocations*By*Coordinates*($a_latitude, $a_longitude)

```php
	// Set up delete query
	$query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
		' WHERE latitude < ' . $this->db->quote($a_latitude, 'float') .
		' AND longitude = ' . $this->db->quote($a_longitude, 'float');

	// Execute delete query
	$this->db->manipulate($query);
```

* Delete a set of objects with a given attribute. Attributes are implied in the method name: delete____()
    * delete*ExpiredGeoLocations*()
    
```php
	// Set up delete query
	$query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
		' WHERE expiration_timestamp < ' . $this->db->quote(time(), 'timestamp');

	// Execute delete query
	$this->db->manipulate($query);
```

### Mock to use while developing

Imagine following scenario: You are a developer and you have the mission the 
mission to implement a plugin which works with geo locations. So you implement
a class as given with `ilGeoLocation`. In the planing phase you see, that 
there are different formats to use coordinates. Since there might be some confusion 
you go for the decimal "*degrees format*" (looks like this: 47.05016819; 8.30930720). 
This coordinates can be stored as two float values in the database.

Later during the development some requirements might change and there are some 
new ideas. Maybe the "*degrees, minutes, seconds*" (looks like this: 48° 52' 0" 
N;2° 20' 0" E) would have fitted better. And maybe an additional description to 
the title would also be a good idea. But changes like this require a change in 
the "dbupdate.php"-file, a change in the Database columns itself, a change in the
DB-queries and a change in the strict typing of all functions.

A possible solution for this type of problem is the usage of a "mocked" repository 
object. But not mocked in the case of unit tests, but mocked in the case of the 
implementation. Instead of writing into a database table, a simple file is used.
Obviously, this file doesn't care about data types and formats. They can also be 
manually edited with a simple editor. This might be a horrible idea for a production
system regarding the miserable integrity, lack of joining tables etc. But for quick changes
and tests during the development process, this flexible way of persisting and 
reading data comes in pretty useful.

With the example of the `ilGeoLocation`-object, we a text file could look like this:

```
1;Paris;48° 52' 0" N;2° 20' 0" E;1539377900
2;Berlin;52° 31' 0" N;13° 24' 0" E;1539177900
3;Bern;46° 55' 0" N;7° 28' 0" E;1539077900
```

Now we can read a line as csv like this:

```php
$file = fopen('mocked_geolocation_data.txt', 'r');
$row = fgetcsv($file);
fclose($file);
```

And write a line like this:

```php
$file = fopen('mocked_geolocation_data.txt', 'r');
$write_string = $obj_data[0].';' .$obj_data[1].';' .$obj_data[2].';' .$obj_data[3].';'                 .$obj_data[4]."\n";
fwrite($file, $write_string);
fclose($file);
```


### Mock to use in unit tests

As already mentioned: The repository pattern is really helpful in terms of writing unit 
tests if used correctly. They can be mocked to return predefined objects This could
look like this:

```php
// Create predefined return values
$now = microtime();
$before = microtime() - 1000;

// Arrange
$obj1 = new ilGeoLocation(1, "older", 0, 0, new \DateTimeImmutable($before));
$obj2 = new ilGeoLocation(1, "newer", 0, 0, new \DateTimeImmutable($now));

// Create mock
$mocked_repo = $this->createMock(ilGeoLocationRepository::class);

// Set mocked method
$mocked_repo->expects($this->once())
    ->method('getGeoLocationsByCoordinates')
	->with(1, 2)
    ->will($this->returnValue(array($obj1, $obj2)));
```

After this, the mocked repository can be injected to an object for unit tests. If the
testing object now calls the mocked repository, it will return our predefined value:

```php
// Inject mocked repository to the testing class
$calc = new ilGeoLocationCalculator($mocked_repo);

// Execute testing method
$result = $calc->calculateNearestExpiration(1, 2);

// Assert test
$this->assertEqual($result, $before);
```

# Bad examples

During the creation of this documentation, we also tried to use the repository 
pattern in combination with some different patterns. We determined, that some 
of this combinations do not work well. With the following subchapters we attempt 
to show why we considered these combinations as bad and why we do not recommend
them.

## Factory to Create Objects Inside a Repository

Some resources on the internet recommend to make the repository depend on a
factory to factor out the creation of objects. We found this to be of little
value, especially when combined with the immutable object pattern.

A repository may only pass data to the factory that it knows about. This boils
down to the exact data that is encoded in the return values of the `get`-methods
from the repository. If a factory would want to create objects containing more
data, we would need another repository or source to retrieve that data from.

This would either create a dependency on another repository to resolve that
requirement inside the repository or we would have some odd dependency graph
where a repository would depend on a factory and that factory would in turn
depend on another repository (which would again most likely depend on some
factory). In the second case we see no benefit in making the repo depend on
the factory in the first place instead of just using what the factory returns.

If we feel the need to make a repository return objects of other classes by
abstracting away object creation in the repository, we most likely will be
better of to use the data a repository returns and combine it with other data
on a level above the repository.


## Active Record inside a Repository

We determined two scenarios, how active record could be used inside a repository.
The first scenario would be, that the object which should be persisted **extends** from
`ActiveRecord`. The other scenario would be, that there is for each class an 
**additional** class, which extends from `ActiveRecord` and only is used for database
Access.

In case of updating an object with the first scenario, the method would look like this:

```php
public function updateGeoLocationObject(ilGeoLocation $a_obj) 
{
    $a_obj->update();
}
```

As you can see, it doesn't make any sense to pass an object to the repository,
just to call the `->update()`-method. Developers would just skip the repository and
would update the object direct by them self.

The other scenario would be to create an additional class, which extends from 
`ActiveRecord` and is used for database access. The following code snippet shows, 
how such a function could look like:

```php
// Class for objects to work with
class ilGeoLocation {}

// Class to interact with the database
class ilGeoLocationAR extends ActiveRecord {}

public function updateGeoLocationObject(ilGeoLocation $a_obj) 
{
    $ar_obj = new ilGeoLocationAR($a_obj);
    $ar_obj->update();
}
```

Like in the first scenario, the repository is just used to create the ActiveRecord-Object
and call the update method, which seems to be of little merit as well, especially since
we duplicate a lot of code.
