# Repository Pattern

## What, why and how?

### Architectural Perspective

**A repository is the single source of truth for a domain of an application.**

* *Repository* is the name of the entity in question
* *single source of truth*
	* *single* means that not other entity may tell or acknowledge facts
	* *truth* can be understood as facts and state in the application
* The *domain* is the area of expertise or knowledge the application (or component
of the application cares about.

### Technical Perspectice

A repository...

* ...abstracts the database.
* ...captures patterns of requests to the database.
* ...mediates all access to the database.
* ...controls where and when persistence happens.

Although a repository is a database from a technical perspective, it is not an SQL-
abstraction.

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

The following code-snippets are examples to get a bet unterstanding of the repository pattern. Furthermore, these examples can be used as templates for the developement of new features and refactoring of already existing ones.

Note that this examples are not perfect for every situation. Like always in software developemt, the developer has to decide which is the best way of usage for his or hers appliaction.

For simplicity reasons, the class of the objects to persist is in all following example the same (except for some additions that are mentioned later). The class is called `ilObjGeoLocation` and is immutble:

    public class ilObjGeoLocation() {
        protected $id;
        protected $title;
        protected $lattitude;
        protected $longitude;
        protected $expiration_timestamp;

        public __construct($a_id, $a_title, $a_lattitude, $a_longitude, $a_expiration_timestamp)
        {
            $this->id = $a_id;
            $this->title = $a_title;
            $this->lattitude = $lattitude;
            $this->longitude = $longitude;
            $this->expiration_timestamp = $a_expiration_timestamp;
        }

        public function getId() { return $this->id; }
        public function getTitle() { return $this->title; }
        public function getLattitude() { return $this->lattitude; }
        public function getLongitude() { return $this->longitude; }
        public function getExpirationTimestamp() { return $this->getExpirationTimestamp; }
    }

The class in this example that should interact with the database (or whatever medium is used) is called `ilGeoLocationRepository`. In most cases, those classes use a set of different **CRUD**-Operations (**C**eate, **R**ead, **U**pdate and **D**elete). E.g. the *read*-Operation contains simple methods like returning a single object, identified by an ID. On the other hand, others return an array of objects or just a boolean, if a requested object exists. The same rules apply for updating or even deleting multiple objects at once.

Always keep in mind, that those CRUD-Operations are not only limited to an SQL-Statement for a database, but also for a filesystem. But for most of this examples, we use a database. The database is injected in the constructor. The benefit of injecting the database to the repository class is to make mocking for unit testing easier.

The following few lines of code are the template for our `ilGeoLocationRepository`-class we use in the example. The function blocks are on purpose blank, since the implementation differs from example to example.

    public class ilGeoLocationRepository {
        
        /** @var ilDB */
        protected $db;

        /** @param ilDB $token */
        public function __construct(ilDB $a_db) {
            $this->db = $a_db
        }

        public function persistGeoLocationObject($obj) { /* Interessting function block */}
        public function getGeoLocationById($a_id) { /* Interessting function block */ }
        public function getGeoLocationsByCoordinates($a_lattitude, $a_longitude) { /* Interessting function block */ }
        public function checkIfLocationExistsById($a_id) { /* Interessting function block */ }
        public function updateGeoLocationExpirationTimestamp($a_id, $a_new_expiration_timestamp) { /* Interessting function block */ }
        public function deleteGeoLocationById($a_id) { /* Interessting function block */ }
        public function purgeExpiredGeoLocations() { /* Interessting function block */ }
    }

For the full code of each example, be sure to visit the "code-examples"-folder.

### Simple example

A simple example for the Repository-Pattern would be a class, that *writes* objects to a database and *reads* them from the database if needed.

In the following example, we want to *write* and *read* objects that contains GeoLocations. We call the class for this objects `ilObjGeoLocation` and the class that is used to persist them `ilGeoLocationRepository`.

In this simple example, these operations are written in *SQL* and executed with *ilDB*. Depending on the operation, the data for the SQL instruction is read from the object or an object will be create from the response of the database. Following the different methods for different CRUD-Operations

#### Create operations



#### Read operations

* Get spicific object by unique identifier: get____ById($id)
    * For example: get*GeoLocation*ById(int $id)
* Get all objects with specified attributes: get____By____($attribute)
    * get*GeoLocations*By*Coordinates*($a_lattitude, $a_longitude)
* Check if specific object exists. Returns Boolean: checkIf____ExistsById
    * checkIf*GeoLocation*ExistsBy*Id*(int $id) : bool
* Check if any object with given attributes exist. Returns Boolean: checkIf
  


#### Update operations

#### Delete operations


### Use with factories


### Use with active record


### Mock to use while developing


### Mock to use in unit tests