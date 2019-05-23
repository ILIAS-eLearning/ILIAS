# Intention
For a long time I'm thinking about how we can create a more up-to-date architectural basis for ILIAS. We have more and more requirements regarding the interaction of components. 

The conversation with Richard Kless last week brought me to this final idea worked out here.

With this pull request I would like to point out a possible solution for the following problems. I'm shure it's not completed. I would be very happy if we discuss this together and adjust it continuously.

Problems
1. We have more and more use cases where we have to build views and interactions across several ILIAS Modules.
2. In ILIAS we have not a clear concept how a maintainer has to build the structure within his module. For Example 
2.1 it is a difficult task for a maintainer if he like to build an enrolment from his component to a course.
2.2 it is a diffcult taks for a maintainer if he like to show a table with certificates or course enromlents.
...
3. In ILIAS it is a extremly difficult task if I would like to understand which use cases a component covers.
4. We have more an more use cases where we should log changes in different modules. E.G. 
4.1 Who has made the permission changes on a ILIAS object
4.2 Who and when has enrolled a user to course
...
5. Sometime we need a little customizing of a workflow - but if there is no event. It's really a pain.
6. We have difficulties to find an train new developers for ILIAS because it's hard to learn how development in ILIAS works.
7. We can not refactore ILIAS in one step and we need a concept to make this step by step.
8. Many of the current proccesses are hard to test.

# Proposal
1. We introduce the principle of cqrs, DDD and Event Sourcing. I d'like to explain it with an example!
2. We use parts of frame works which are etablished and make fun. We use them so, that we can them change if the will no longer be maintained once.
3. In cqrs the reading and writing side will be splitted. At least for the reading side I would be very pleased to use symfony/doctrine in several use cases.
4. We make this change step by step. It is possible to make this also step by step within a single ILIAS Module.

## Guiding Principle
1. Make the Implicit Explicit
2. The lifetime of value objects should fully depend on the lifetime of their parent entities.

## Layers

### Bounded Context
DDD deals with large models by dividing them into different Bounded Contexts and being explicit about their interrelationships.
https://martinfowler.com/bliki/BoundedContext.html 

The communication between these contextes is to be done through well-defined interfaces.

In my opinion each ILIAS Repository Object is a bounded context.

Folder Structure
* Domain
* Application
* Infrastructure


Ubiquitous Language

To allow for the fluent sharing of knowledge, DDD calls for cultivation of a shared, business-domain-oriented language: Ubiquitous Language. This language should resemble the business domain, its terms, entities, and processes. The Ubiquitous Language should be extensively used throughout the project. All communication should be done in the Ubiquitous Language, and all documentation should be formulated in it. Even the code should “speak” the Ubiquitous Language as well. The Ubiquitous Language becomes the model of the business domain implemented in code.
https://vladikk.com/2018/01/21/bounded-contexts-vs-microservices/

#### Layer 1 - Domain / Aggregates
A collection of objects (Domain Models) that are bound together by a root entity, otherwise known as an aggregate root. The aggregate root guarantees the consistency of changes being made within the aggregate by forbidding external objects from holding references to its members.
Example: When you drive a car, you do not have to worry about moving the wheels forward, making the engine combust with spark and fuel, etc.; you are simply driving the car. In this context, the car is an aggregate of several other objects and serves as the aggregate root to all of the other systems.
https://en.wikipedia.org/wiki/Domain-driven_design

There’s a good rule for working with aggregates that says that we should not update more than one aggregate per transaction.

This layer should act like a micro service.


##### Domain Model
For a Domain Model I propose the following folder structure:
* [Domain Model]
	* Entity
	* ValueObject
	* Event
	* Reposotiry
	* Service
	* Factory

##### Entity
1. Entities are domain classes that are uniquely defined by a unique identifier.


##### Value Object
1. Entities have their own intrinsic identity, value objects don’t.
2. A value object should always belong to one or several entities, it can’t live by its own.
3. The concept of identifier equality refers to entities, whereas the concept of structural equality – to value objects.
4. Martin Fowler (https://martinfowler.com/bliki/ValueObject.html) describes them like this: You can usually tell them because their notion of equality isn't based on identity, instead two value objects are equal if all their fields are equal.

##### Event
An Event inform zero or more event subscribers of the occurance of an event. In the middleware may run things before or after handling an event.

##### Reposotiry


##### Service

##### Factory

#### Layer 2 (wrapping Domain): Application

##### Commands via Command Bus
The Command Bus pattern is trying to decouple as much as it can the controller, to the domain layer. It keeps the user interface logic separated from your models.
##### Command
A command is a strictly defined message. The Command is not more than a Data Transfer Object which can be used by the Command Handler. It represents the outside request structured in a well formalized way. The command is not repsonsible for handle the command!
1. It handles commands, i.e. imperative messages
2. Commands are handled by exactly one command handler
3. In the middleware may run things before or after handling the command.
##### Command Bus
The Command Bus is used to dispatch a given Command into the Bus and maps a Command to a Command Handler.
###### CommandHandlerMap
'Directory' which maps a command to a command bus
###### Command Handler Locator
Retrieves the corresponding Command Handler from the Map
##### Command Handler
The Command Handler component is the place where all the magic happens. This is the place here where the request is being dispatched and handled, where the things happen.


#### Layer 3 (wrapping Application): Infrastructure
The infrastructure layer contains any code that is needed to expose the use cases to the world and make the application communicate with real users and external services.


TODO
https://tactician.thephpleague.com/
https://entwickler.de/online/php/ddd-patterns-domain-driven-design-185328.html



## Explanation / Example
Use Case: 
1. Displaying the members of a course.
2. Add a member to a course.

Software Structure

- src
	- App
		- CoreApp
			- Application
			- Domain
				- Course
					- Entity
					- Events
					- Repository
					- Service
					- ValueObject
				- Member
					- Entity
                    ...
			- Infrastructure
				
					

# ToDo
https://medium.com/@drozzy/long-running-processes-event-sourcing-cqrs-c87fbb2ca644
### wir brauchen
https://webmozart.io/blog/2015/09/09/value-objects-in-symfony-forms/
### zu PRüfen
Das deckt sich mit dem Entwurfsmuster CQS (Command Query Separation), das von Bertrand Meijer erdacht wurde. Es besagt, dass jede Funktion eines Objekts entweder als Command oder als Query entworfen sein soll.
### Validation Folder
https://medium.com/@developeruldeserviciu/ddd-usually-means-at-least-3-layers-application-services-domain-service-and-infrastructure-967e80403615
Event-Folder
Exception filder
### Kursrepositories aufteilen in Read und Write (siehe Member)
### folgendes ist zu kompliziert:
'$member_entity_repository = new MemberEntityRepository(DoctrineEntityManage'
'$bus = new MessageBus([                                                    
'		new CommandHandlerMessageMiddleware(new HandlersLocator($member_entity_repository)'                                                                      
'	$course_service = new MemberWriteonlyService($bus);'                       
' 	$course_service->addMember(ilObject::_lookupObjectId($_GET['ref_id']),292);'

### zusätzliches Beispiel mit MySQL-Repositories umsetzen.
### Events im Sinne von Erfolgsmeldungen ebenfalls im cqrs stil?
### Readme pro App
### Einfaches REST?
### CourseRepository->getMembers()
### Aggregates


$crs_entity_repository = new CourseEntityRepository($entityManager);
$crs_repository = new CourseRepository($crs_entity_repository);
$crs_object = $crs_repository->find(ilObject::_lookupObjectId($_GET['ref_id']));
$crs_member_via_course = [];
foreach ($crs_object->getMembers() as $crs_members) {
$crs_member_via_course[] = $crs_members->getUser()->getLastname();
}

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

#Weshalb
* DB-Schicht extrem einfach austauschbar.
* sämtliche Aktionen könnten geloggt werden da jede Aktion eindeutig und einmalig!
* Jede Aktion wird über einen Message-Bus geschickt. Dort könnte man beispielsweise per Plugin beliebig reinhooken!


# Install AMPQ
https://www.rabbitmq.com/download.html
https://www.rabbitmq.com/tutorials/tutorial-one-php.html



### Weitere Überlegungen
Contexte, da diese nicht strikt miteinander verbunden sind, könnten auf unabhängigen infrastrukturen betrieben werden.

WICHTIG: Datenhaltung / Lesen nicht zwingend DB. Könnte sogar pro Context unterschiedlich sein.