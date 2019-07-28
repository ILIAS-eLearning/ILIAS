# CQRS with Event Sourcing

## Why we introduce a new software paradigm for the Assessment Question Service
We had a look to the actual behavior and chalenges and the future requirements for the ILIAS Test Assessment and searched a software paradigm which helps us to solve the problems and covers the future requirements. The software paradigm we searched should also help us to make it possible to have more than one developer who can fix Bugs in the Assessment Question Service and that we can cover most of the service with unit tests.

The current problems and future requirements that were relevant for the Assessment Question Servcice are as follows

1 Questions from a question pool are copied for use in a test.  There is Back-Reference-ID to the question pool. However, the implementation of... 
* _Unique IDs for Test Questions_ (https://docu.ilias.de/goto_docu_wiki_wpage_4571_1357.html_docu_wiki_wiki_page_4571_1357.html)
* And _Item Statistic in Pool_ (https://docu.ilias.de/goto_docu_wiki_wpage_5321_1357.html)

...is a chalenge to realize with the classical way of ILIAS Development.

2 In the future we could think about to have the possibility to change questions in a test no matter if it already contains user data records or not - and we would like to offer this function audit-proof!

3 There are several authors who can work on a question. There is no history and no easy way to reset a question to a previous state. A question history is a current feature request for the ILIAS test [https://docu.ilias.de/goto_docu_wiki_wpage_5312_1357.html]

4 When I use a question from a pool in one or more tests. Now I edit the question in the pool. This change is not currently applied to the test. If you then (possibly another user) customize the question in the test, he will be asked if he should also apply his customization to the question in the question pool. With this he(!) overwrites the previous adjustment, which was made directly in the question pool - without knowing it! To prevent such things requires a very clean code base and it must be easy to introduce appropriate validations to reverse it.


## Good to know
The CQRS with Event Sourcing paradigm offers a very good solution for the above requirements. It is not a paradigm that covers every use case outside the Assessment Question Service within ILIAS. But using this paradigm in similar contexts could, however, be very helpful.
 

## Terminology
### CQRS
CQRS is the the acronym of Command Query Responsibility Segregation. It is a pattern defined by Greg Young:
* Summary by Greg Young: http://codebetter.com/gregyoung/2010/02/16/cqrs-task-based-uis-event-sourcing-agh/
* CQRS Documents by Greg Young: https://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf

CQRS suggests to separate the read- from the write- / the command side. This is especially valuable as we can provide on the side where the student gets a question for answering only questions of a willingly provided revision.

### CQRS and Event Sourcing
We use the Event Sourcing as the data storage mechanism for the domain. So we use the event model as persistence model on the write side. At the read side we use a classical data structure well known from other ILIAS Modules.

### Command Side - Domain Driven Design Approach
We use the Domain Driven Design Approach for the command side. With this approach we are able to to express a domain model and a business logic/domain behaviourin the clearest possible object oriented form. With Domain Driven Design we have a standardized approach how to design a domain model, so our developers can understand the implementation more easily, and can effectively communicate with each other using the Domain Driven Design terms.
 
The domain entities are implemented within a specific layer, which knows nothing about the database persistenceand the user interface. The data are stored within the entities as data properties, and the persistence layer makes sure that these data are correctly persisted into the data base.

The domain entities have a rich behaviour like calculations and validations implemented. Important to know is therefore, that the entities are are not only bags of data properties without any behaviour.

### Read Side
The Read Side is optimized for reading. There we return DTOs or simple arrays.

# Internal Implementation of the Command Side - Overview
Note: For the public external API see Pullrequest: https://github.com/ILIAS-eLearning/ILIAS/pull/2016

## Create a Question
**1. Start the Bus with a new CreateQuestionCommand**
A command is just a class with some mandatory properties. Commands are handled by exactly one CommandHandler. For each Task to fulfill we have a separate Command Object. Like this example for creating Question. This ensures that we have all the necessary data from the start of the process.
```
class ilAsqQuestionAuthoringGUI {
[...]

//Fill the generic QuestionData Value Object
$question_data = new QuestionData('title', 'descritption', 'my question', 'author@example.com')

//Get the Command Bus and send the CreateQuestionCommand on a journey ;-)
CommandBusBuilder::getCommandBus()->handle(new CreateQuestionCommand($data, $user_id));
```


**2. Delegate the command to the Command Handler**
Currently our Command Bus has only one task. Distributing a Command to the corresponding CommandHandler. Later we could think about providing the command bus with additional middlerwares, which will do further actions before or after handling the command or implement an other behavior if necessary. For example, execute a command asynchronously.

The Command Bus delegates the Command to the Command Handler as follow. We decided that we connect the Commands using naming conventions:
* CreateQuestionCommand -> CreateQuestionCommandHandler
```
//
class QuestionCommandBus {
[...]

        /**
    	 * @param Command $command
    	 *
    	 * @throws DomainException
    	 */
    	public function handle(Command $command): void {
    
    		foreach ($this->middlewares as $middleware) {
    			$command = $middleware->handle($command);
    		}
    
    		$handler_name = get_class($command).'Handler';
    		/** @var CommandHandlerContract $handler */
    		$handler = new $handler_name;
    
    		if (!is_object($handler)) {
    			throw new DomainException(sprintf("No handler found for command %s", $command));
    		}
    
    		$handler->handle($command);
    	}

[...]

```

**3. Handle the Command**
The Command Handler handles the Command. In this Case
 3.1 A new Question Aggregate is Created. 
 3.2 The setData-Method of the Question is executed.
 3.3 The Save-Method of the Repository is executed.
```
class CreateQuestionCommandHandler {

[...]

	public function handle(Command $command) {
		$question = Question::createNewQuestion($command->getUuid(), $command->getIssuingUserId());
		$question->setData($command->getData(), $command->getIssuingUserId());

		QuestionRepository::getInstance()->save($question);
	}

[...]
}
```
**3.1 Create the Question Aggregate Root Object**
After Creating the Question Aggregate Root Object - before the setData-Method of the Question is executed (3.2) - the QuestionCreatedEvent (3.1.1) is executed. As you can see for creating the question we only require DomainObjectId and a creator UserId at this point.

Note: If we will change not any FormGUI we will also save the question type at this point. But for our example this is the better way to understand CQRS with event sourcing.
```
class Question
[..]
public static function createNewQuestion(int $creator_id) {
		$question = new Question();
		$question->ExecuteEvent(new QuestionCreatedEvent(new DomainObjectId(), $creator_id));
		return $question;
	}
```
**3.1.1 Execute the Question Created Event**
The Event will be applied 3.1.1.1 and recorded 3.1.1.2
 ```
 class Question
 [..]
    protected function ExecuteEvent(DomainEvent $event) {
 		// apply results of event to class, most events should result in some changes
 		$this->applyEvent($event);
 
 		// always record that the event has happened
 		$this->recordEvent($event);
 	}
 ```
**3.1.1.1 Determine the Apply Method for the Question Created Event**
For Applying an event, we have to determine the concret apply method. This could be done by an EventBus - or like we do this here under the responsibility of the Question Aggregate.

Note: The applyEvent-Method is in our case a genereric methods for determine the concrete method to apply the concrete event. We use it for any aggregate event. In our Case we work like by the commands with a naming convention. The method name within the Question class - in this case is _applyQuestionCreatedEvent_.
```
class Question {
[...]
    const APPLY_PREFIX = 'apply';

    protected function applyEvent(DomainEvent $event) {
		$action_handler = $this->getHandlerName($event);

		if (method_exists($this, $action_handler)) {
			$this->$action_handler($event);
		}
	}

[...]

    private function getHandlerName(DomainEvent $event) {
		return self::APPLY_PREFIX . join('', array_slice(explode('\\', get_class($event)), - 1));
	}

```
**3.1.1.1.1 Handle applyQuestionCreatedEvent**

By appling the event _QuestionCreated_ the question object will be filled with data. In our case with the UUID and the CreatorUserId.

Note: Until now nothing is saved at the database! This because the command CreateQuestion has not finished the whole work!

```
protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
		$this->id = $event->getAggregateId();
		$this->creator_id = $event->getInitiatingUserId();
	}
```
**3.1.1.2 Record Event**
We record now, that the event has happend. We need this for later - for saving / persist the data in the database.
```
class Question {
[...]
    protected function recordEvent(DomainEvent $event) {
            $this->recordedEvents->addEvent($event);
        }
```
 3.2 The setData-Method of the Question is executed.



**Aggregate Question**

The aggregate question execute the event QuestionCreatedEvent
```
class Question {

[...]

	public static function createNewQuestion(DomainObjectId $uuId, int $creator_id) {
		$question = new Question();
		$question->ExecuteEvent(new QuestionCreatedEvent($uuId, $creator_id));
		return $question;
	}

[...]
}
```


**QuestionCreatedEvent**

The execute event at the question side looks like follow. There are two additional methods in the process:
3.1.1.1.1.1 applyEvent and 3.1.1.1.1.2 record Event
```
class Question {

[...]

    protected function ExecuteEvent(DomainEvent $event) {
        // apply results of event to class, most events should result in some changes
        $this->applyEvent($event);
    
        // always record that the event has happened
        $this->recordEvent($event);
    }

[...]
```
**3.1.1.1.1.1 applyEvent**


applyQuestionCreatedEvent

```
class Question {

[...]

    protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
            $this->id = $event->getAggregateId();
            $this->creator_id = $event->getInitiatingUserId();
    }
[...]
}
```
By recording the event the question object add the event to the record store
```
class Question {
[...]

    protected function recordEvent(DomainEvent $event) {
        $this->recordedEvents->addEvent($event);
    }

[...]
}
```
After that the CreateQuestionCommandHandler calls the save method at the QuestionRepository. The save method at the QuestionRepository gets all events - in this case the QuestionCreatedEvent - and commits them to the Eventstore. 

If there are consumer for this event they get notified.
```
class QuestionRepository {
[...]

    public function save(EventSourcedAggregateRoot $aggregate) {
            $events = $aggregate->getRecordedEvents();
            $this->getEventStore()->commit($events);
            $aggregate->clearRecordedEvents();
    
            if ($this->has_cache) {
                self::$cache->set($aggregate->getAggregateId()->getId(), $aggregate);
            }
    
            $this->notifyAboutNewEvents();
        }

[...]
```
The EventStore will save the data to the database.
```
class ilDBQuestionEventStore implements EventStore {
[...]

	public function commit(DomainEvents $events) : void {
		/** @var DomainEvent $event */
		foreach ($events->getEvents() as $event) {
			$stored_event = new ilDBQuestionStoredEvent();
			$stored_event->setEventData(
				$event->getAggregateId()->getId(),
				$event->getEventName(),
				$event->getOccurredOn(),
				$event->getInitiatingUserId(),
				$event->getEventBody());

			$stored_event->create();
		}
	}
[...]



## Bounded Context
Bounded Context is one of the most important concepts of Domain Driven Design. A Bounded Context is a conceptual limit where a domain model is applicable.

A single Bounded Context can include many Aggregate Roots, or we can organise a single aggregate root into a single Bounded Context.

### Aggregate
A collection of objects (Domain Models) that are bound together by a root entity, otherwise known as an aggregate root. An Aggregate has a boundary. The Boundary defines what is inside the aggregate. 

There’s a good rule for working with aggregates that says that we should not update more than one aggregate per transaction.

We decided to declare the object called Question to the aggregate root.


[../../src/Authoring/DomainModel/Question/Question.php](../../src/Authoring/DomainModel/Question/Question.php])





