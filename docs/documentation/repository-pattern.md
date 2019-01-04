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

The following code-snippets are examples to get a bet unterstanding of the
repository pattern. Furthermore, these examples can be used as templates for
the developement of new features and refactoring of already existing ones.

Note that this examples are not perfect for every situation. Like always in
software developemt, the developer has to decide which is the best way of usage
for his or hers appliaction.

For simplicity reasons, the class of the objects to persist is in all following
example the same (except for some additions that are mentioned later). The class
is called `ilObjGeoLocation` and is immutble:

```php
    class ilObjGeoLocation {
        protected $id;
        protected $title;
        protected $lattitude;
        protected $longitude;
        protected $expiration_timestamp;

        public __construct(int $a_id, string $a_title, string $a_lattitude, 
                           string $a_longitude, int $a_expiration_timestamp)
        {
            $this->id = $a_id;
            $this->title = $a_title;
            $this->lattitude = $lattitude;
            $this->longitude = $longitude;
            $this->expiration_timestamp = $a_expiration_timestamp;
        }

        public function getId() : int { return $this->id; }
        public function getTitle() : string { return $this->title; }
        public function getLattitude() : string { return $this->lattitude; }
        public function getLongitude() : string { return $this->longitude; }
        public function getExpirationTimestamp() : int { return $this->getExpirationTimestamp; }
    }
```

In this example, we define an interface called `ilGeoLocationRepository`, which is
the basis for all the other example Repository-Classes. The implementing classes
of this interface do interact with the database or whatever medium is used. In most
cases, those interactions are a set of different **CRUD**-Operations (**C**eate,
**R**ead, **U**pdate and **D**elete). E.g. the *read*-Operation contains simple
methods like returning a single object, identified by an ID. On the other hand,
others return an array of objects or just a boolean, if a requested object exists.
The same rules apply for updating or even deleting multiple objects at once.

Always keep in mind, that those CRUD-Operations are not only limited to an
SQL-Statement for a database, but also for a filesystem. But for most of this
examples, we use a database. The database is injected in the constructor. The
benefit of injecting the database to the repository class is to make mocking for
unit testing easier.

The following few lines of code are a template for different `ilGeoLocation*Repository`-
class we use in the example. The function blocks are on purpose blank, since the
implementation differs from example to example.

```php
    interface ilGeoLocationRepository {
        // Create operations
        public function createGeoLocation(ilObjGeoLocation $obj);

        // Read operations
        public function getGeoLocationById(int $a_id);
        public function getGeoLocationsByCoordinates(string $a_lattitude, string $a_longitude);
        public function checkIfLocationExistsById(int $a_id) : bool;

        // Update operations
        public function updateGeoLocationObject(ilObjGeoLocation $a_obj);

        // Delete operations
        public function deleteGeoLocationById(int $a_id);
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
the object or an object will be create from the response of the database. Following
the different methods for different CRUD-Operations

#### CRUD-Operations

**Create operations**

```php
    // Get next free id for object
    $id = $this->db->nextId($this->sql_table_name);

    // Insert in database
    $this->db->insert($this->sql_table_name, array(
        'id' => array('integer', $id),
        'title' => array('text', $obj->getTitle()),
        'lattitude' => array('float', $obj->getLattitude()),
        'longitude' => array('float', $obj->getLongitude()),
        'expiration_timestamp' => array('timestamp', $obj->getIExpirationTimestamp())
    ));
```


**Read operations**

* Get spicific object by unique identifier: get____ById($id)
    * For example: get*GeoLocation*ById(int $id)
  
```php
        // Setup SQL-Statement
        $query = 'Select * FROM ' . $this->sql_table_name . ' WHERE id = ' . $this->db->quote($a_id, 'integer');

        // Execute query
        $result = $this->db->query($query);

        // Fetch row for returning
        if($row = $this->db->fetchAssoc($result))
        {
            // Create object out of fetched data and return it
            return new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp']);
        }
        else
        {
            // Return NULL if nothing was found (throw an exception is also a possiblity)
            return NULL;
        }
```

* Get all objects with specified attributes: get____By____($attribute)
    * get*GeoLocations*By*Coordinates*($a_lattitude, $a_longitude)

```php
    // Setup SQL-Statement
    $query = 'Select * FROM ' . $this->sql_table_name . ' WHERE lattitude = ' . $this->db->quote($a_lattitude, 'float') . ' AND longitude = ' . $this->db->quote($a_longitude, 'float');

    // Execute query
    $result = $this->db->query($query);

    // Fill array with all matching objects
    $locations = array();
    while($row = $this->db->fetchAssoc($result))
    {
        $locations[] = new ilObjGeoLocation($row['id'], $row['title'], $row['lattitude'], $row['longitude'], $row['expiration_timestamp']);
    }

    // Return list of objects (might be empty if no object was found)
    return $locations;
```

* Check if specific object exists. Returns Boolean: checkIf____ExistsById
    * checkIf*GeoLocation*ExistsBy*Id*(int $id) : bool
* Check if any object with given attributes exist. Returns Boolean: checkIfAny____ExistsBy____
    * checkIfAny*GeoLocation*ExistsBy*Coordniates*
  


**Update operations**

**Delete operations**


### Use with factories


### Use with active record


### Mock to use while developing


### Mock to use in unit tests