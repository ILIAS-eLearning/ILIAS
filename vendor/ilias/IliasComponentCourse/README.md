# Intention - establish CQRS/DDD and Microservices 
For a long time I'm thinking about how we can create a more up-to-date architectural basis for ILIAS. I'm sure you rack your brain over matters too;-) The last few week read a lot about software architecture and worked out a concept. 

We have more and more requirements regarding the interaction of components. 

A conversation with Richard Kless the week before gave me to this final idea partly worked out here. I hope I will finish the concept in the next two weeks.

With this pull request I would like to point out a possible solution for the following problems. I would be very happy if we discuss this together and adjust it continuously.

Problems
1. We have more and more use cases where we have to build views and interactions across several ILIAS Modules.
2. In ILIAS we have not a clear concept how a maintainer has to build the structure within his module. For Example 
2.1 it is a difficult task for an external maintainer if he like to build an enrolment from his component to a course.
2.2 it is a difficult task for an external maintainer if he like to show a table with certificates or course enrolment.
...
3. In ILIAS it is a extremely difficult to understand which use cases a component covers.
4. We have more an more use cases where we should log changes in different modules. E.G. 
4.1 Who has made the permission changes on a ILIAS object
4.2 Who and when has enrolled a user to course
...
5. Sometime we need a little customizing of a workflow - but if there is no event. It's really a pain.
6. We have difficulties to find and train new developers for ILIAS because it's hard to learn how development in ILIAS works.
7. We can not refactor ILIAS in one step and we need a concept to make this step by step.
8. Many of the current processes are hard to test.

# Proposal
1. We introduce the principle of CQRS & DDD (https://martinfowler.com/bliki/CQRS.html) and Microservices. In cqrs the reading and writing side will be splitted. At least for the reading side I would be very pleased to use symfony/doctrine in several use cases.
4. We make this change step by step. It is possible to make this also step by step within a single ILIAS Module.

## Guiding Principle
1. Make the Implicit Explicit
2. The lifetime of value objects should fully depend on the lifetime of their parent entities.
... tbc.

## Explanation

![](https://martinfowler.com/bliki/images/cqrs/cqrs.png)

### Bounded Context
DDD deals with large models by dividing them into different Bounded Contexts and being explicit about their interrelationships.
https://martinfowler.com/bliki/BoundedContext.html 

The communication between these contextes is to be done through well-defined interfaces.

With CQRS any request from users is either a Command or a Query which gets executed by their corresponding CommandHandler or QueryHandler respectively. CQRS defines Command and Query as follows.

#### Command
Represents a user request that has a side effect by changing the system's state. Requests which lead to any of the Update, Create, Delete operations on system's data, are considered as a command.

#### Query
Query: represents a user request that has no side effect on system's state and only wants to Read data.

#### Domain Model
A collection of objects (Domain Models) that are bound together by a root entity, otherwise known as an aggregate root. The aggregate root guarantees the consistency of changes being made within the aggregate by forbidding external objects from holding references to its members.
Example: When you drive a car, you do not have to worry about moving the wheels forward, making the engine combust with spark and fuel, etc.; you are simply driving the car. In this context, the car is an aggregate of several other objects and serves as the aggregate root to all of the other systems.
https://en.wikipedia.org/wiki/Domain-driven_design

There’s a good rule for working with aggregates that says that we should not update more than one aggregate per transaction.

For a Domain Model I propose the following folder structure:
* [DomainModel]
	Aggregate
		[AggregateRootEntitit.php]
		* Entity2.php
		* ValueObject.php
	* Command
	* Event
	* (Eventlistener)
	
##### Root Entity	
    Root Entities have global identity.  Entities inside the boundary have local identity, unique only within the Aggregate.
    Nothing outside the Aggregate boundary can hold a reference to anything inside, except to the root Entity.  The root Entity can hand references to the internal Entities to other objects, but they can only use them transiently (within a single method or block).
    Only Aggregate Roots can be obtained directly with database queries.  Everything else must be done through traversal.
    Objects within the Aggregate can hold references to other Aggregate roots.
    A delete operation must remove everything within the Aggregate boundary all at once
    When a change to any object within the Aggregate boundary is committed, all invariants of the whole Aggregate must be satisfied.
    
   [vendor/ilias/IliasComponentCourse/src/Course/DomainModel/Aggregate/Course/Course.php](./vendor/ilias/iliascomponentcourse/src/Course/DomainModel/Aggregate/Course/Course.php)

##### Entity
1. Entities are domain classes that are uniquely defined by a unique identifier - but are not the root entity. 

##### Value Object
1. A value object should always belong to one or several entities, it can’t live by its own.
2. The concept of identifier equality refers to entities, whereas the concept of structural equality – to value objects.
3. Martin Fowler (https://martinfowler.com/bliki/ValueObject.html) describes them like this: You can usually tell them because their notion of equality isn't based on identity, instead two value objects are equal if all their fields are equal.

[/vendor/ilias/IliasComponentCourse/src/Course/DomainModel/Aggregate/Course/CourseMember.php](./vendor/ilias/iliascomponentcourse/src/Course/DomainModel/Aggregate/Course/CourseMember.php)


##### Commands via Command Bus
The Command Bus pattern is trying to decouple as much as it can the controller, to the domain layer. It keeps the user interface logic separated from your models.

##### Command
A command is a strictly defined message. The Command is not more than a Data Transfer Object which can be used by the Command Handler. It represents the outside request structured in a well formalized way. The command is not repsonsible for handle the command!
1. It handles commands, i.e. imperative messages
2. Commands are handled by exactly one command handler
3. In the middleware may run things before or after handling the command. AND that's realy great:-)

[/vendor/ilias/IliasComponentCourse/src/Course/DomainModel/Command/AddCourseMemberToCourseCommand.php](./vendor/ilias/iliascomponentcourse/src/Course/DomainModel/Command/AddCourseMemberToCourseCommand.php)

##### Command Bus
The Command Bus is used to dispatch a given Command into the Bus and maps a Command to a Command Handler.

###### CommandHandlerMap
'Directory' which maps a command to a command bus

###### Command Handler Locator
Retrieves the corresponding Command Handler from the Map

##### Command Handler
The Command Handler component is the place where the request is being dispatched and handled.
[/vendor/ilias/IliasComponentCourse/src/Course/DomainModel/Command/AddCourseMemberToCourseCommandHandler.php](./libs/composer/vendor/ilias/iliascomponentcourse/src/Course/DomainModel/Command/AddCourseMemberToCourseCommandHandler.php)

##### Events via via Event Bus
An Event inform zero or more event subscribers of the occurance of an event. In the middleware may run things before or after handling an event.

[/vendor/srag/IliasComponentCourse/src/Course/DomainModel/Event/CourseMemberWasAdded.phpt](./libs/composer/vendor/ilias/iliascomponentcourse/src/Course/DomainModel/Event/CourseMemberWasAdded.php);

#### Infrastructure
The infrastructure layer contains any code that is needed to expose the use cases to the world and make the application communicate with real users and external services.

The query model for reading data and the update model for writing data may access the same physical store, perhaps by using SQL views or by generating projections on the fly.


## Example
Use Case: 
1. Displaying the members of a course.
2. Add a member to a course.
3. Reset Password of User

				


# Bibliography
* http://docs.simplebus.io/en/latest/Guides/command_bus.html
* https://de.slideshare.net/_leopro_/clean-architecture-with-ddd-layering-in-php-35793127
* https://stefanoalletti.wordpress.com/2018/08/10/cqrs-is-easy-with-symfony-4-and-his-messanger-component/
* https://www.fabian-keller.de/blog/domain-driven-design-with-symfony-a-folder-structure
* https://github.com/msgphp/msgphp
* https://www.rabbitmq.com/tutorials/tutorial-one-php.html
* https://www.heise.de/developer/artikel/CQRS-neues-Architekturprinzip-zur-Trennung-von-Befehlen-und-Abfragen-1797489.html?seite=all
* https://beberlei.de/2012/08/18/oop_business_applications__command_query_responsibility_seggregation.html
* https://enterprisecraftsmanship.com/2016/01/11/entity-vs-value-object-the-ultimate-list-of-differences/
* https://medium.com/web-engineering-vox/building-a-php-command-bus-a65e6ae6a6ac
* https://matthiasnoback.nl/2017/08/layers-ports-and-adapters-part-2-layers/
* https://marcolabarile.me/notes/building-microservices/splitting-the-monolith/
* https://cqrs.nu/Faq
* https://entwickler.de/online/php/ddd-patterns-domain-driven-design-185328.html
* https://blog.arkency.com/application-service-ruby-rails-ddd/
* https://docs.microsoft.com/en-us/azure/architecture/microservices/design/data-considerations
* http://ziobrando.blogspot.com/2010/06/about-entities-aggregates-and-data.html
* http://docs.simplebus.io/en/latest/
* https://symfonycasts.com/screencast/symfony-forms/form-dto
* https://martinfowler.com/bliki/ReportingDatabase.html
* https://ookami86.github.io/event-sourcing-in-practice/#further-reading-1.md
* https://microservices.io/patterns/data/cqrs.html
* https://www.confluent.io/blog/turning-the-database-inside-out-with-apache-samza/
* https://microservices.io/patterns/data/shared-database.html
* https://auth0.com/blog/introduction-to-microservices-part-4-dependencies/
database changes
* https://martinfowler.com/articles/evodb.html
* https://www.vinaysahni.com/best-practices-for-building-a-microservice-architecture
* https://martinfowler.com/articles/dont-start-monolith.html
* https://github.com/ejsmont-artur/php-circuit-breaker
* https://docs.microsoft.com/de-de/azure/architecture/patterns/cqrs
* https://pilsniak.com/cqrs-es-php-prooph/
* http://getprooph.org/
* https://www.youtube.com/watch?v=8JKjvY4etTY
* https://www.youtube.com/watch?v=GzrZworHpIk
* https://github.com/dotnet/docs/blob/master/docs/standard/microservices-architecture/microservice-ddd-cqrs-patterns/microservice-domain-model.md
* https://github.com/dotnet-architecture/eShopOnContainers/blob/master/src/Services/Ordering/Ordering.Domain/AggregatesModel/OrderAggregate/Order.cs
* https://github.com/dddinphp/blog-cqrs/blob/master/src/CQRSBlog/BlogEngine/Infrastructure/Persistence/EventStore/PostRepository.php
* https://getpocket.com/explore/cqrs
* https://www.future-processing.pl/blog/cqrs-simple-architecture/
* http://xeroxmobileprint.net/DiscoveryTable/test/folder1/Domain-Driven_Design_in_PHP.pdf
* https://fideloper.com/how-we-code
* https://github.com/CodelyTV/cqrs-ddd-php-example/tree/master/src/Mooc/Courses
* https://entwickler.de/online/php/ddd-patterns-domain-driven-design-185328.html
* https://docs.microsoft.com/en-us/previous-versions/msp-n-p/jj591573(v=pandp.10)
* https://foreverframe.net/cqrses-2-domain-objects/
* http://udidahan.com/2009/06/29/dont-create-aggregate-roots/
* https://scalified.com/2018/11/01/java-ee-cqrs-setup-axon-framework/
* https://medium.com/@drozzy/long-running-processes-event-sourcing-cqrs-c87fbb2ca644
* https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/
* https://medium.com/@developeruldeserviciu/ddd-usually-means-at-least-3-layers-application-services-domain-service-and-infrastructure-967e80403615


### Usage

#### Composer
First add the following to your `composer.json` file:
```json
"require": {
  "srag/iliascomponentcourse": ">=0.1.0"
},
```
And run a `composer install`.

If you deliver your plugin, the plugin has it's own copy of this library and the user doesn't need to install the library.

Tip: Because of multiple autoloaders of plugins, it could be, that different versions of this library exists and suddenly your plugin use an older or a newer version of an other plugin!

So I recommand to use [srag/librariesnamespacechanger](https://packagist.org/packages/srag/librariesnamespacechanger) in your plugin.

### Requirements
* ILIAS 6.0
* PHP >=7.2

### Adjustment suggestions
* Adjustment suggestions by pull requests
* Adjustment suggestions which are not yet worked out in detail by Jira tasks under https://jira.studer-raimann.ch/projects/LILCOMP
* Bug reports under https://jira.studer-raimann.ch/projects/LILCOMP
* For external users you can report it at https://plugins.studer-raimann.ch/goto.php?target=uihk_srsu_LILCOMP

