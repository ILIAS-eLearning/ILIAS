https://blog.programster.org/using-php-with-apache-

https://www.digitalocean.com/community/tutorials/how-to-install-apache-kafka-on-ubuntu-18-04kafka


https://www.youtube.com/watch?v=EMz_GCurv4o


http://172.28.128.3/ilias.php?cmdClass=ilias\assessmentquestion\example\ilasqexamplegui&cmdNode=12h:d0&baseClass=iluipluginroutergui


//sudo apt install php-sqlite3

//UNDO Memento

//https://github.com/topdeveloppement/DDD-Symfony/blob/bd58f3533c0c46bf0dea01ca7ba03090cb686ac2/src/DDD/UserInterface/WEB/Action/Security/ConfirmationAction.php


JMS Serializer
serialize/unserialize native PHP strategies have a problem when dealing with class and
namespace refactoring. One alternative is use your own serialization mechanism, for example,
concatenating the amount, a one character separator such as “|” and the currency ISO code. However,
there is another better favored approach, using an open-source serializer library such as JMS
Serializer14. Let’s see an example of applying it for serializing a Money object.


We do not use a dependency injector!

We use an other form of message bus than in the book

Command vs Eventbus
Marc and Florian answered the question already, but I'd like to repeat the answer here. The main difference is: events tell you that something has happened, while a command tells you that something needs to happen. From this it follows that nobody might be interested in an event, but somebody should be interested in a command, because it has to be handled somehow.


From Command to Handler
https://matthiasnoback.nl/2015/01/from-commands-to-events/

https://github.com/mishudark/eventhus

command instance -> comandhandler command by handler why
I really think it should be passed as an argument of the handle() method. Otherwise, one handler instance = one command. You wouldn't be able to process 2 different command instances with the same handler instance.

Hence, the constructor should be reserved for injecting dependencies. Then the same handler can be used to handle multiple instances of the same type of command.

That's a very good question. And again, Florian provided some great answers already. Several kinds of problems may occur if we handle a command in the same process:

The user may have provided invalid input data
The provided input data may result in an invalid state
An unexpected runtime error may occur (e.g. network failure)
We can already catch validation errors before we hand the command over to the command bus. In fact, we should verify that the command itself contains valid data and provide human-oriented error messages to the user if it doesn't. The other two kind of problems will result in regular failures, just like they would in a non-command oriented application. They can be handled in any way you like (e.g. allowing the user to retry the action, or show an error page).

Any other execution path should be considered the happy path: everything goes well. So if the command bus finally returns control to the controller that asked it to handle a command, you can assume that no problems occurred and, for instance, redirect to a "thank you" page.


https://matthiasnoback.nl/2015/01/from-commands-to-events/

An excellent way to solve issues with SRP and OCP and to separate primary tasks from secondary tasks is to introduce events. Events, like many other things, are objects. And like commands, they only contain some data and display no behavior at all. They are messages.
