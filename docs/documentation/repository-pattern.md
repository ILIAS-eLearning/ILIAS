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

* *Active Record* - objects carry their persistence logic around
* *Just Query* - gather data by an appropriate SQL-query

### Advantages

* The explicit interface makes writing tests easy.
* The explicit interface makes writing alternative implementations (import/export,
caching, ...) easy.
* The database schema is completely contained in the implementation of the repository.
* Database integrity can be handled in one place.
* Query and write patterns get meaningful names.
* The repository forces you think about access to the persistence layer.

### Disadvantages

* You write an additional interface.
* You cannot just query or change some data on the database.
* You have to establish clear boundaries between different domains.
* Integrity over domains is hard.
* The repository pattern does not fit "reporting queries" well.
* The repository pattern is not about performance.
* Current ILIAS access patterns or components may be in your way.

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