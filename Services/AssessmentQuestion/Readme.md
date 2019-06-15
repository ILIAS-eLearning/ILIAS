# AssessmentQuestion /  Fundamental Work for a auditable and stable system

With this pullrequest I like to give a continuus prereview on our Work for the AssessmentQuestion Service and at the fundamentals work which is necsessary for us, to cover the future requirements.

This will be the branch which we will like to merge

## @Technical Board 

Would it be possible that have a look at it and give me a feedback if it is OK when we the services provide for all developers and what the work has to fullfill.

Following Libaries / Services ar new:
## Command / Event and Query Buss
Path: src/Messaging - our Work; third party service at composer/vendor/simple-bus
Third party library can be exchanged as needed. The coupling is extremly loose

Use

```
$command_busbuilder = new CommandBusBuilder();
  
$command_bus = $command_busbuilder->getCommandBus();
  			
$command_bus->handle(new CreateQuestionCommand($title, $description, $creator_id)``
```

Documentation:
http://docs.simplebus.io/en/latest/

## src / Data / Domain
Standard Data Types for the CQRS and DDD Patterns

## Refinery Object to Json
src/Refinery/Object

Hint: Domain-Driven Design in PHP, 2014 - 2016 Carlos Buenosvinos,Christian Soronellas and Keyvan Akbary, Chapter: 3.7.1.4.1:  <<...serialize/unserialize native PHP strategies have a problem when dealing with class and namespace refactoring. One alternative is use your own serialization mechanism for example, concatenating the amount, a one character separator such as “|” an the currency ISO code.>>

# New concept proposals for the Assessment Question Service
We will use the Pattern DDD, CQRS (incl. Event Sourcing)

## Advantages
 1. It's a concept, in which the software processes is realy near to our language.
 2. It's "not" possible that there were super classes with business logik with 800 and more lines. The core of the software - the business processes are always clear and maintainable.
 3. It is possible that more than one devloper can develop here becauese the code is until the maximum decapsulated.
 4. Each change of a state of an object will be separtly like in the Database. 
 5. Requirements like a Worldwide Unique ID are state of the art for those patterns.
 6. For a developer it is a good way to develop step by step - because the separation. It's - after a learning cure - a easy but lovely way to develop.
 7. Because of the Hexagonal structure it is a good possibility for unit tests: https://herbertograca.files.wordpress.com/2018/11/100-explicit-architecture-svg.png?w=1200

# Concepts to discuss
1. I d'like to have a Unique ID and a Revisions ID per Testquestion.
2. I d'like to have the possibilitychange questions asslo the are allreadyy in a test. The Questions Service counts per question and revision.
3. I propose that the question service will have a data storage with external IDs of his consuments. Primary because of the data. IT ist no possible an one of the key feature the date for reporting parrallel in addition to write.
4... 


Final Schedule and a presentation with technical details at wednesday.

We will inroduce zwo API (Author / Student)
* Services/AssessmentQuestion/src/Authoring/_PublicApi/AsqAuthoringService.php
* CreateQuestionHandler.php

The architecture is theoreticaly also already redy for offline tests and to 100% fit for REST.
 
 I you have any questions don't hesistade to ask!