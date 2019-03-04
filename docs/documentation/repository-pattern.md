# Repository Patterno

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
to the knowledge in the domain it is reponsible for. Since it is a single entity
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
will most propably implement repositories over a relational database mostly, we are
not limited to that. We could well implement a repository that adds chaching over
another repository or reads its data from other sources like files.
* **The database schema is completely contained in the implementation of the repository.**
- If we implement the repository over a relational database, no other component
then the repository is allowed to access the data contained in the repositories
tables (by definition). We thus are free to change the schema whenever and however
we want, because nobody besides the repository should ever need access to that tables.
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
change these objects on the persitence layer if they also have the repository. Compared
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

* Write the interface and data-objects first while implementing consumers of the
repository.
* Inhect the repository as dependency.
* Make sure to understand that this is not about SQL. Do not try to build an
SQL-abstraction.
* Repositories work well with immutable objects. Try to think about how data flows
through the program.
* Reference other domains by ID only, only tell and acknowledge facts you know
about.
* You may write facilities that aggregate information from different repositories or
write to different repositories, but these may only know about the interfaces,
not the implementation.


## Use in ILIAS

The repository pattern may be applied under various circumstances in ILIAS:

* 

## Conventions

If you implement the repository in your component, you SHOULD folloe these
conventions:

* Call the repository `XYZRepository` where XYZ is the domain of your component
the repository controls.


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
anti patterns. They are documented under the chapter of non-examples

For simplicity reasons, the class of the objects to persist is in all following
example the same. The class is called `ilObjGeoLocation` and is immutable:

```php
    class ilObjGeoLocation {
        protected $id;
        protected $title;
        protected $latitude;
        protected $longitude;
        protected $expiration_timestamp;

        public __construct(int $a_id, string $a_title, string $a_latitude, 
                           string $a_longitude, int $a_expiration_timestamp)
        {
            $this->id = $a_id;
            $this->title = $a_title;
            $this->latitude = $latitude;
            $this->longitude = $longitude;
            $this->expiration_timestamp = $a_expiration_timestamp;
        }

        public function getId() : int { return $this->id; }
        public function getTitle() : string { return $this->title; }
        public function getLatitude() : string { return $this->latitude; }
        public function getLongitude() : string { return $this->longitude; }
        public function getExpirationTimestamp() : int { return $this->getExpirationTimestamp; }
    }
```

For these examples, we define an interface called `ilGeoLocationRepository`, which is
the base for all the other example Repository-Classes. The implementing classes
of this interface do interact with the database or whatever medium is used. In most
cases, those interactions are a set of different **CRUD**-Operations (**C**eate,
**R**ead, **U**pdate and **D**elete). E.g. the *read*-Operation contains simple
methods like returning a single object, identified by an ID. On the other hand,
others return an array of objects or just a boolean, if a requested object exists.
The same rules apply for updating or even deleting multiple objects at once.

Always keep in mind, that those CRUD-Operations are not only limited to an
SQL-Statement for a database, but also are possible for a filesystem. But for most of this
examples, we use a database. The database is injected in the constructor. The
benefit of injecting the database to the repository class is to make mocking for
unit tests easier.

The following few lines of code are a template for different `ilGeoLocation*Repository`-
class we use in the example. The function blocks are on purpose blank, since the
implementation differs from example to example.

```php
    interface ilGeoLocationRepository {
        // Create operations
        public function createGeoLocation(array $obj_data);

        // Read operations
        public function getGeoLocationById(int $a_id);
        public function getGeoLocationsByCoordinates(string $a_latitude, string $a_longitude);
        public function checkIfLocationExistsById(int $a_id) : bool;
        public function checkIfAnyLocationExistsByGeoLocation(string $a_latitude, string $a_longitude) : bool;
        
        // Update operations
        public function updateGeoLocationObject(ilObjGeoLocation $a_obj);
        public function updateGeoLocationTimestampByCoordinates(string $a_searched_latitude, string $a_searched_longitude, int $a_update_timestamp);
        
        // Delete operations
        public function deleteGeoLocationById(int $a_id);
        public function purgeGeoLocationsByCoordinates(string $a_latitude, string $a_longitude);
        public function purgeExpiredGeoLocations();
    }
```

For the full code of each example, be sure to visit the "code-examples"-folder.

### Simple example

A simple example for the Repository-Pattern would be a class, that *writes* objects
to a database and *reads* them from the database if needed.

In the following example, we want to *write* and *read* objects that contains
GeoLocations. We call the class for this objects `ilObjGeoLocation` and the class
that is used to persist them `ilGeoLocationRepository`.

In this simple example, these operations are written in *SQL* and executed with
*ilDB*. Depending on the operation, the data for the SQL instruction is read from
the object/array or an object will be create from the response of the database. Following
the different methods for different CRUD-Operations

#### CRUD-Operations

**Create operations**

* Create an object out of the given data: create_____($obj_data)
    * For example: createGeoLocation(array: $obj_data)
    * *Note:* There is a good reason, why the argument is an array (or something 
    else) instead of an actual object. The actual object needs to have an id.
    Since the only object which interacts with the database is the repository 
    object, the repository object is the only one who can read the next id from
    the database.

```php
    // Get next free id for object
    $id = $this->db->nextId($this->db->quoteIdentifier(self::TABLE_NAME));

    // Insert in database
    $this->db->insert($this->db->quoteIdentifier(self::TABLE_NAME), array(
        'id' => array('integer', $id),
        'title' => array('text', $obj_data['title']),
        'latitude' => array('float', $obj_data['latitude'],
        'longitude' => array('float', $obj_data['longitude']),
        'expiration_timestamp' => array('timestamp', $obj_data['expirationAsTimestamp'])
    )));

    // Return the new created object or just the id
    return new ilObjGeoLocation($id,
                                $obj_data['title'],
                                $obj_data['latitude'],
                                $obj_data['longitude'],
                                $obj_data['expiration_timestamp']);
```

**Read operations**

* Get specific object by unique identifier. Returns object: get____ById($id)
    * For example: get*GeoLocation*ById(int $id) : ilObjGeoLocation

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
        return new ilObjGeoLocation($row['id'],
                                    $row['title'],
                                    $row['latitude'],
                                    $row['longitude'],
                                    new DateTimeImmutable($row['expiration_timestamp']));
    }
    else
    {
        // Return NULL if nothing was found (throw an exception is also a possiblity)
        return NULL;
    }
```

* Get all objects with specified attributes. Returns Array: get____By____($attribute)
    * get*GeoLocations*By*Coordinates*($a_latitude, $a_longitude) : array

```php
    // Set up SQL-Statement
    $query = 'Select * FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
             ' WHERE id = ' . $this->db->quote($a_id, 'integer');
    // Execute query
    $result = $this->db->query($query);

    // Fill array with all matching objects
    $locations = array();
    while($row = $this->db->fetchAssoc($result))
    {
        // Create object and add it to list
        $locations[] = new ilObjGeoLocation($row['id'],
                                            $row['title'],
                                            $row['latitude'],
                                            $row['longitude'],
                                            new DateTimeImmutable($row['expiration_timestamp']));
    }

    // Return list of objects (might be empty if no object was found)
    return $locations;
```

* Check if specific object exists. Returns Boolean: checkIf____ExistsById($id)
    * checkIf*GeoLocation*ExistsBy*Id*(int $id) : bool
    
```php
    // Set up SQL-Statement
    $query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
             ' WHERE id = ' . $this->db->quote($a_id, 'integer');
    
    // Execute statement
    $result = $this->db->query($query);
    
    // Return if object was found
    return $result['count'] > 0;
```

* Check if any object with given attributes exist. Returns Boolean: checkIfAny____ExistsBy____($attribute)
    * checkIfAny*GeoLocation*ExistsBy*Coordinates*(string $a_latitude, string $a_longitude) : bool

```php
    // Set up SQL-Statement
    $query = 'Select count(*) AS count FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
             ' WHERE latitude = ' . $this->db->quote($a_latitude, 'text') .
             ' AND longitude = ' . $this->db->quote($a_longitude, 'text');

    // Execute statement
    $result = $this->db->query($query);

    // Return if any object was found
    return $result['count'] > 0;
```

**Update operations**

* Update specific object, identified by its id: update______Object($id)
    * update*GeoLocation*Object(ilObjGeoLocation $a_obj)

```php
    // Update of one entire geo location object
    $this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
        // Update columns (in this case all except for id):
        array('title' => array($a_obj->getTitle(), 'text')),
        array('latitude' => array($a_obj->getLatitude(), 'text')),
        array('longitude' => array($a_obj->getLongitude(), 'text')),
        array('expiration_timestamp' => array($a_obj->getExpirationAsTimestamp(), 'timestamp')),
        // Where (in this case only the object with the given id):
        array('id' => array($a_obj->getId(), 'int'))
    );
```

* Update a set of objects with the same attributes at once: update____By____($_searched_attributes, $a_new_attributes)
    * update*GeoLocation*By*Coordinates*($a_searched_latitude, $a_searched_longitude, $a_new_timestamp)
    * *Note:* This function seems to be anti-pattern. For a lot of use cases 
    this is true. You should prefer updating an entire object instead 
    of just a single attribute. But if you want to update some specific attributes 
    on a set of object, an update query like this is a lot faster than fetching 
    a list of all wanted objects and update them one by one.

```php
    // Update for single attribute of a set of geo location objects
    $this->db->update($this->db->quoteIdentifier(self::TABLE_NAME),
        // Update columns (in this case only the timestamp):
        array('expiration_timestamp' => array('timestamp', $a_update_timestamp)),
        // Where (in this case every object on the given location):
        array('latitude' => array($a_searched_latitude, 'latitude'),
              'longitude' => array($a_searched_longitude, 'longitude'))
    );
```

**Delete operations**

* Delete specific object: delete____Object($id)
    * delete*GeoLocation*Object($a_id)

```php
    // Set up delete query
    $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
    ' WHERE id = ' . $this->db->quote($a_id, 'integer');

    // Execute delete query
    $this->db->manipulate($query);
```

* Delete a set of objects with a given attribute. Attributes are given as Argument: purge____By____($attribute)
    * purge*GeoLocations*By*Coordinates*($a_latitude, $a_longitude)

```php
    // Set up delete query
    $query = 'DELETE FROM ' . $this->db->quoteIdentifier(self::TABLE_NAME) .
        ' WHERE latitude < ' . $this->db->quote($a_latitude, 'text') .
        ' AND longitude = ' . $this->db->quote($a_longitude, 'text');

    // Execute delete query
    $this->db->manipulate($query);
```

* Delete a set of objects with a given attribute. Attributes are implied in function title: purge____()
    * purge*ExpiredGeoLocations*()
    
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
an obj-class as given with `ilObjGeoLocation`. In the planing phase you see, that 
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

With the example of the `ilObjGeoLocation`-object, we a text file could look like this:

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
$obj1 = new ilObjGeoLocation(1, "older", "", "", microtime() - 1000);
$obj2 = new ilObjGeoLocation(1, "newer", "", "", microtime());

// Create mock
$mocked_repo = $this->createMock(ilGeoLocationRepository::class);

// Set mocked method
$mocked_repo->expects($this->once())
    ->method('getGeoLocationsByCoordinates')
    ->with($this->equalTo("48° 52' 0\" N", "2° 20' 0\" E")
    ->will($this->returnValue(array($obj1, $obj2)));
```

After this, the mocked repository can be injected to an object for unit tests. If the
testing object now calls the mocked repository, it will return our predefined value:

```php
// Inject mocked repository to the testing class
$calc = new ilGeoLocationCalculator($mocked_repo);

// Execute testing method
$result = $calc->calculateNearestExpiration(array("48° 52' 0\" N", "2° 20' 0\" E")) 

// Assert test
$this->assertEqual($result, $obj2)
```

# Bad examples

During the writing of this documentation, we also tried to use the repository 
pattern in combination with some different patterns. We determined, that some 
of this combinations are bad examples. With the following subchapters, we 
show, why we considered these examples as bad and why we do not recommend
them.

## Factory Pattern inside a Repository

Don't get us wrong, a repository can absolutely be used inside of a factory and 
and we would even recommend it. Sadly, the other way doesn't work so well.
In most cases, the factory would also need a repository if it needs to load data
from the database to construct a new object.

In this case, a factory would either use another repository for database access.
Or worse, it uses the repository in which it exists inside. This would create a
circular dependency between a repository and a factory which should be avoided.

The following code snippet shows how both case would look like:

```php
// Scenario with 2 repositories
public function getGeoLocationById(int $a_id) : ilObjGeoLocation
{
    /* Insert read data from DB part here */
    
    // Create second repository
    $second_repo = new ilOtherGeoLocationRepository($this->db);
    $obj = $this->geo_location_factory->createGeoLocation($obj, $second_repo);
    return $obj;
}

// Scenario with circular dependency
public function getGeoLocationById(int $a_id) : ilObjGeoLocation
{
    /* Insert read data from DB part here */
    $obj = $this->geo_location_factory->createGeoLocation($obj, $this);
    return $obj;
}
```

## Active Record inside a Repository

We determined two scenarios, how active record could be used inside a repository.
The first scenario would, that the object which should be persisted **extends** from
`ActiveRecord`. The other scenario would be, that there is for each class an 
**additional** class, which extends from `ActiveRecord` and only is used for database
Access.

In case of updating an object with the first scenario, the method would look like this:

```php
public function updateGeoLocationObject(ilObjGeoLocation $a_obj) 
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
class ilObjGeoLocation {}

// Class to interact with the database
class ilObjGeoLocationAR extends ActiveRecord {}

public function updateGeoLocationObject(ilObjGeoLocation $a_obj) 
{
    $ar_obj = new ilObjGeoLocationAR($a_obj);
    $ar_obj->update();
}
```

Like in the first scenario, the repository is just used to create the ActiveRecord-Object
and call the update method.