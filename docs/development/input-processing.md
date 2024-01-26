# How to Process Input Securely in ILIAS?

The inspection and processing of user input is of crucial importance for the
security of a software system. Every input to the system may become a possible
attack vector and compromise security qualities of the software when carelessly
processed. Data propagating into the system thus must be inspected and, in case
of doubt, be discarded from further processing as early as possible.

This document attempts to show the current state of art of input processing in
ILIAS and outlines a way to improve that processing by proposing changes regarding
code as well as procedures. The document was created on request of and under
involvement of the Technical Board of the ILIAS-Society.

We begin by explaining the core considerations that we take into account when
analysing and designing measures to improve the security of input processing in
ILIAS. To contrast these, we also show issues that some might find interesting
when talking about input processing but that we did not consider here. We go on
by explaining and analyzing recently implemented libraries and existing mechanisms
to improve the security in input processing and showcase the libraries at the
forms in the UI-Framework.

We then evaluate requirements from components of ILIAS that currently do only
implement little systematical approaches to security when processing input. From
there we derive which enhancements and extensions to ILIAS are required to span the
components currently not included in the systematical approach to input security
and how these can be implemented technically as well as socially.


## Core Considerations

### Primitive Obsession

A core security problem when handling data is the question whether input was already
inspected from a security perspective or if a given function or section of code
needs to perform that step itself on a given input.

Consider this snippet of pseudo-code, that someone might have written at some
point in the development of ILIAS.

```php
class ilSomeGUI {
	/**
	 * @var ilSomeService
	 */
	protected $some_service;

	public function executeCommand() {
		$input_param = $_GET["myInputParam"];
		if(!$this->checkInputParam($input_param)) {
			throw \Exception("Alert! Someone tried to temper with my input!");
		}

		$this->some_service->doWork($input_param);
	}

	protected function checkInputParam($param) {
		// some checking stuff;
		return $check_result;
	}
}

class ilSomeService {
	public function doWork($param) {
		// somehow use $param
	}
}
```

The GUI-class retrieves and checks the input provided by the user via query-
params in `$_GET`. It complains when the input doesn't match some criterion. It
hands the data over to some service that does the actual work. When the GUI
and the service were created, this design is perfectly secure regarding the
input parameter. A practical example might be some service that needs a filename
as input parameter via get an then does some operation on the file, e.g.
delivering the file to the user.

Later on (where later might actually be years later) someone (the same developer
as well as a completely different person) wants to implement a new feature in ILIAS,
reusing the same service, and adds the following (pseudo-)code to the system:

```php
class ilSomeOtherGUI {
	/**
	 * @var ilSomeService
	 */
	protected $some_service;

	public function executeCommand() {
		$input_param = $_GET["myInputParam"];
		$this->some_service->doWork($input_param);
	}
}
```

Expecting that the service itself takes care of valid input, the other developer
just calls the service with some input provided by the user and thus (possibly)
opens a hole in the security defenses of the software.

Keep in mind that the situation might look a lot more complicated than this example.
Maybe a first GUI-class checks the parameter provided by the user, passes it to a
subsequent class, which then calls the service. Maybe the service contains convoluted
code that does not clearly communicate if input is inspected or not.

There are different measures to treat this problem. Documentation of the service
may be required that outlines if and how input is checked. But then the new task
of taking care of the documentation is introduced and the documentation cannot
be checked by automatic procedures and thus must be read and understood by every
developer using the service. We might introduce some guidelines that say where
and how user input needs to be validated. But a guideline is similar to documentation,
it needs to be understood and followed and can only be verified automatically in
rare cases. The checks might be pushed down to the lowest layer of the application.
But this might mean that information required for the checks is missing at the
point where the checks need to take place.

These solutions all miss a crucial problem contained in the code above. A general
duty when programming is to give meaning to anonymous sequences of bits and bytes
contained in our computers' memories. This does not end at primitive datatypes
like `string` and `integer`. PHP offers more tools to attach meaning and sense
to data that at the same time serve as a documentation for developers.

Consider the GUI and service were implemented like this to begin with:

```php
class ilSomeGUI {
	/**
	 * @var ilSomeService
	 */
	protected $some_service;

	public function executeCommand() {
		$input_param = new ParamType($_GET["myInputParam"]);
		$this->some_service->doWork($input_param);
	}
}

final class ParamType {
	/**
	 * @var string
	 */
	protected $value;

	public function __construct(string $param) {
		if (!this->checkInputParam($param)) {
			throw new \InvalidArgumentException
				("$param is not allowed when constructing ParamType.");
		}
		$this->value = $param;
	}

	protected function checkInputParam(string $param) : bool {
		// some checking stuff;
		return $check_result;
	}
}

class ilSomeService {
	public function doWork(ParamType $param) {
		// somehow use $param
	}
}
```

The fact, that the parameter for the service needs to be validated, is communicated
and also enforced by the implementation of the `doWork`-method that takes a typed
parameter. The user can only possess such a value when he has a string that passes
the check in the constructor. In fact, the existence of a value of type ParamType
proves that the check was performed (at least in a setting where we regard "sane"
usages of PHP only and disregard usages of ReflectionClass, Serialization, ...).
The property that only these values are passed to `doWork` will be enforced by the
runtime by means of the type hint.

This approach won't fit to all scenarios in which checks need to be performed on
input, but will improve scenarios where the values that are passed between different
components are in fact more than simple strings, integers, arrays or floats.
Introducing wrappers around primitive data types will also improve other properties
of the system.

The phenomenon that primitive datatypes are used instead of semantically richer
structures is known as the antipattern "primitive obsession".


### API-Design

The native PHP-API to access values provided by the user via GET and POST is
extremely simple and handy:

```php
$foo = $_GET["foo"];
$bar = $_POST["bar"];
```

Note, that we don't even have to declare that `$_GET` and `$_POST` are globals.
Various tutorials and answers on stack overflow promote this direct usage of
the superglobals, often without adressing dangers in this approach. And of course
this approach is as dangerous as it is handy, since values are easily retrieved
at any location in the code but not checked for any property when retrieving
them.

ILIAS currently attacks this problems by treating `$_GET` with the static method
`ilInitialisation::recusivelyRemoveUnsafeCharacters` in the initialisation procedure.
This method uses `strip_tags` and removes some characters considered to be unsafe
for some reason from all values in `$_GET`. `$_POST` is not treated generally.
This procedure might prevent the most obvious attacks that attempt to introduce
malicious values via `$_GET`, but surely cannot treat every such vector as it
cannot possibly know about the circumstances in which these values will be used.

However, every future API to retrieve values from `$_GET` and `$_POST` will have
to compete with the simple superglobal API. It is indeed possible to deprecate
the direct usage of `$_GET` and `$_POST` via dicto, but this won't help when 
developers complain about some approach being hard to use or understand. It is
of course hard to compete with the superglobals regarding simplicity. Thus every
new API must provide benefits in other areas. It is unlikely that this can only
be found in security, though, as security seldomly is a core concern when writing
software.

The introduction of PSR-7 with its interfaces to HTTP-messages provides a
promising impression where these benefits might be. This API provides easy to
read and to remember methods on the message objects that allow reading
information from the request. The HTTP-messages are immutable and suggest to
be passed into the services that consume them due to their value appearance,
instead of beeing summoned from the void via a global. This has implications for
the general architecture of the application as well as for its testability.

ILIAS implements PSR-7 since 5.3, at least in some parts of the application.
This suggests, that this implementation needs to be the base for secure input
processing, at least regarding the usage of `$_GET`, `$_POST` and `$_COOKIE`.

We also need to consider carefully how a future API looks from a consumers
perspective, what the benefits of the API are and how we document and communicate
them.


### Reality of ILIAS Development

There are certain circumstances in the development iof ILIAS that need to be
recognized when thinking about a systematic approach to secure input processing
in ILIAS.

First and foremost, ILIAS is a software under 20 years of continous development.
This on the one hand means, that a systematic approach needs to somehow address
different historical layers of the software, either by integrating into them as
seamlessly as possible, by showing clear pathes to migrate these layers to new
approaches or by deprecating these old layers with intelligible arguments. On the
other hand there are habbits and approaches that are ingrained in processes and
procedures of the community that won't change immediately but might require a
long term engagement to be effectively changed.

During the development, security was historically seldomly addressed as a seperate
requirement or concern, neither in conceptual phases nor in the coding itself.
There currently aren't any tools or processes that systematically attempt to raise
security of ILIAS, besides the recently improved possibility to hand in security
reports to the community. Other approaches that are common in environments that
frequently have higher security requirements as an LMS, like pen-tests, systematic
training and instruction of developers, risk analysis, automated or manual code
reviews, are not implemented in the general developement process of ILIAS. This
on the one hand means that there certainly are low hanging fruits to be picked
regarding security. On the other hand it again shows that technical approaches
most certainly won't be enough to raise the general level of security. Even if
considering social implications, like we attempt to do in this paper, the
improvement of the security of input processing won't be enough in general and
will have a hard stand if not backed by other means. There are, however, measures
in the scope of input processing, like proper communication and good API-design,
that might help to form new habbits regarding security in the long run.

We thus try to show approaches besides technical measures for the scope of this
paper, but also ask the project leadership of ILIAS to keep thinking about and
implementing more hollistic approaches to security.


### Feedback when Rejecting Input

The circumstances in which the system receives input have a huge variety regarding
their sources. There are forms that are operated by users, who need to be informed
about success or failure of the desired input. There are types of input
that are very technical in their nature, like reading data from the database or
XML-import files, where machines are talking to other machines along a more or
less strictly defined interface. There are forms of input that seem to be machine-
to-machine like the former, but where we in fact don't really know if and how
humans that need feedback are involved, e.g. the SOAP-Interface or JSON-over-HTTP-
interfaces.

The requirements regarding the mere filtering of the data that reach the system
are very similar in all these cases: We need to make sure that only data matching
certain criteria in shape and content may pass the boundary of the system and be
subject to further processing. Data should be scrutinized as closely and early
as possible. The requirements regarding feedback to the other side of the
communication are, however, widely different.

A human sitting in front of a form might just input improper data and every nice
system will try to give her feedback in the most helpful and detailed form that's
possible. Reading some data from the database seems to require no feedback to a
user at all, as every missformed data indicates some deeper problem that most
certainly cannot be solved at the interface to the database itself. A malformed
XML-file in an import might hint at incompatible versions or objects regarding the
import, as well as at some attempt to tamper with an import file and use it as a
vector to degrade security of the system. A user importing the file might require
the feedback "incompatible version", but less likely seems to be interested in
the information which exact field of which datastructure didn't match expectations.
A detailed response to a missformed SOAP-request might help a desperate developer
of a webservice-interface, but also inform a malicious hacker regarding new
approaches to degrade security.

Thus, the question how, where and which feedback is given as a reponse to a
malformed input is of interest when designing an API to secure input. It must be
possible for the consumers of the API to give detailed feedback to human users,
as well as discarding and hiding that feedback completely when the nature of
the interface to the system makes this appropriate. To be able to detect tampering
we suggest to address the logging of failed input attempts in the future. As this
paper tries to lay the ground for securing the input processing (and not the
detection of attempts to tamper with the system), the logging-topic is considered
to be out of scope for the rest of the paper.


### Structure vs. Policy

The cause for requirements to data that should cross the systems boundary can
be divided into two categories that might look similar at first sight but show
significant difference on a closer look.

The first cause for constraints on input are derived from requirements on the
structure of the input, that is which types of data the input needs to contain
in which position and shape. A multiselect input, e.g. might require a `list
of strings` as input, while a SOAP-call to copy an ILIAS object might require
a dictionary containing an integer at the key `source` and one at `target`.
If these requirements are not met the application often will stop at some point
and generate some form of more or less informative exception or error message.
If e.g. the "target" is missing in the example of the SOAP call, the operation
cannot be completed in a meaningful way.

It is not enough to expect the application to fail at some point in case the
input is not structured correctly. On the one hand, the input might propagate
deeply into the system, cause subsequent errors or make it hard to debug the
root cause of an exception or error. In the SOAP-call, for example, a missing
"target"-parameter might be interpreted as `null`, be written as `0` into the
database before the actual error happens and later on cause all kind of havoc
when read from the database again. On the other hand this will create unexpected 
effects and possibly generates helpful output for an attacker, meaning that the
available surface for an attack is unnecessarily large.

Structural requirements thus need to be checked as early as possible and deeper
layers in the system need to put requirements on their consumers that data is
in fact shaped as expected. This of course has a deep connection to the primitive
obsession antipattern explained earlier, as the requirements on the shape need
to be documented and enforced properly.

The second cause for constraints roots not in the shape of the data but in their
very content and the specific circumstances in which the data is processed. Data
that is shaped in the form of a date might be invalid under the policy that a
users birthday most possibly ain't a future date. The same date would be a
perfectly valid date for an appointment. The integer shaped target for the SOAP
copy operation might be invalid under the policy that only writeable categories
are a valid target for a certain user. This might be true or false depending on
the user that performs the operation.

Other than the shape of the data a policy seems to require more feedback to
the agent that attempts to input the data to the system. While, e.g. the shape
of the date might be guaranteed by the input field that the user used to enter
his birthday, he will need feedback when the date is out of some expected range.
Also requirements from policies are often more volatile than requirements from
shape. The answer to "Is this category writeable?" might be different from one
second to the other when some permission was changed. This also shows, that
policies often have authorities that judge and enforce them, as the RBAC-system
does for the permissions. Consequently, a policy on some data most possibly
cannot be enforced directly at the boundary of the system but will already
require some processing of the data that checks the policy.

This makes the picture of when and how policies can be enforced to secure input
processing a lot fuzzier than this is the case for structural requirements. It
will be cumbersome or even impossible to completely document policy requirements
in the PHP type system via classes. It also will be a lot harder to find a framework
for enforcing policies on data that fits all cases, since policies mostly will arise
from the business rules of the application or a specific component, possibly deeply
in the system.

However, policies still are indispensable regarding the security of the system. 
If e.g. permission policies can't be enforced, it will render the RBAC-system 
useless and hence knock out an essential security feature of the application. 
We thus will try to look how policy enforcing systems may hook into the general 
input processing, but we will not be able to exhaustively examine all requirements
from said systems in this paper. We request the maintainers of said systems to
understand the role of their systems in this regard and work towards sensible
solutions to secure input processing regarding the nature of the policies their
systems want to enforce.


### Declarative vs. Imperative

When enforcing constraints on the inputs to a software system, it is not enough
that the desired constraints are, in fact, enforced. It is also important that
other people (e.g. developers, reviewers, even the developer herself at a later
point in time) can understand and scrutinize the constraints in place. This allows
to check if constraints are sufficient and up to date, as well as to understand
which data is allowed to pass a boundary in the system and which data is
discarded.

Code written in an imperative style focusses on *how* a problem is solved, while
declarative code focusses on *what* the developer wants. Typically, well written
declarative code is easier to understand as according imperative code, as it
allows to hide intricate details in some implementation, while the writer of the
declaration can focus on what he wants. Think about the difference between CSS
and a CSS rendering engine for an extreme example of that observation.

Using declarative approaches can lead to an (embedded) domain specific language
((E)DSL) that allows to express solutions in a narrow domain of problems with a
specific set of language constructs. The language for Dicto is an example for such
a DSL. DSLs allow for a concise and readable formulation of a desired solution
that shows the information essential to the problem with little boilerplate.

To express the constraints on inputs to the system, a declarative approach or
EDSL is the right choice, since it allows the developer to focus on the task
of choosing his constraints on the input without being bothered by the question
of how a check may be conducted. For readers of her code, a well crafted set of
tools to express constraints will simplify to understand which data should be
discarded and why. The imperative part, how the checks are performed, then can
be moved to a location common for all components and be put under extra scrutiny.
This will free developers as well as reviewers of the question if constraints
actually work as desired.


## Non-Issues

### Performance

Checking inputs early and thoroughly will always require more computational
ressources than letting the data pass unscrutinized. However, ILIAS is not a
system that needs to process huge amounts of input in a time critical environment.
For that reason, performance will be considered a non-issue throughout this paper.

If at some point in the future the validation of input will become crucial for
the performance of the system, we will have some strategies to work on performance
of the validation by (e.g.):

* using external programmes to validate input, e.g. for huge blobs of data like
  movies
* giving names to complex constraints and programme them directly instead of
  composing them from smaller parts
* compiling constraints into more efficient PHP-code that e.g. uses references
  to pass data instead of copy it or uses other methods to improve PHP performance


### Escaping

"Escaping" is the procedure to prepare some data to be outputted in a certain context.
It is a measure to allow another system to correctly interpret the data in the way
our system intends it to be interpreted. This has security implications in some
contexts, since incorrect interpretation of user-provided data may lead to a
degraded security in some subsystem. Widely known vectors that use missing or
incorrect escaping are SQL-injection and Cross-Side-Scripting. The famously and
widely used `ilDB::quote`-method is an example for a procedure that defends the
database against injections of SQL by escaping it properly for its context.

Escaping is inherently connected to the context in which data is outputted from
the system and thus on the exact other end of the input data processing the system
performs. When data is inputted to the system, it certainly in general is not
possible to determine the context in which the data will be outputted later on.
The correct means of escaping thus cannot be determined at that point. That means
that escaping is a problem that needs to be tackled at the various output interfaces
of the system. Some mechanisms and approaches from this paper might also be used
for escaping, but we do not attempt to propose a global strategy for escaping here.


### Sanitizing

"Sanitizing" is the attempt to clean up the data provided by users and remove
unwanted parts of the input to derive acceptable input. On the one hand,
sanitizing input data can be understood to be an implementation of [Postels Law of
robustness](https://en.wikipedia.org/wiki/Robustness_principle). On the other hand
it might be understood as security measure to remove dangerous parts of some
input in order to prevent injection attacks or at least make them less likely.

As an attempt to make the system more robust, sanitizing input certainly is a valid
approach that can be understood as a step, or even the step, in the transformation
from primitive user input to richer internal data types. As such it does not require
extra attention.

As an attempt to prevent injection, sanitizing is a very weak measure. Similar
to escaping, it is not possible to know the output context for some data and
hence the required escaping at the moment the data is handed to the system as
input. This problem will get bigger once the system gets more interfaces that
actually output data.

Instead of removing data from input that is deemed insecure in some context, it
is more advisable to reject said data, either with a detailed message to the user
or not. This on the one hand allows the user to act accordingly and modify the
data she send, while simply discarding parts of the data may leave the user in
the wrong impression that the input was actually accepted. On the other hand,
input containing data that could be used in an injection-vector could be a hint
that someone tries to tamper with the system. This attempt should be noticed
somehow and not silently be ignored by removing the injection code. Last but not
least, a sanitizing procedure might become an attack surface in its own right
when elaborate and complex enough.


## State of the Art: Libraries and Procedures

During the implementation of Form Inputs in the UI-Framework three libraries where
created that tackle various problems when processing input via forms in ILIAS. The
functionality was created in libraries to be used in other scenarios as well and thus
offers a base to design secure input processing for other components. The libraries
also already reflect some of the core considerations outlined before. We show the
current state of the art in these libraries to give an impression of what is already
there and later on derive what needs to be added. We also analyze two procedures to
secure input processing that currently attempt to create a security baseline for
`$_GET` and `$_POST` regarding these principles to understand how we could improve
the situation there.


### Data

The [Data-library](../../src/Data) aims at providing standard datatypes that are
used throughout the system and thus do not belong to a certain component in ILIAS.
Currently it contains types for `Color`, `DataSize`, `Password` and `URI`. There
also is the `Result`-type which captures the possibility of error in a calculation
by containing either some other value or some error information.

The [Data-library](../../src/Data) thus will be an important tool to tackle the
primitive obsession. When, for example, dealing with passwords, the `Password`
type in conjunction with typehints will allow PHP to help us installing guards
against unintendedly publishing a password. The security gains that can be provided
by using the library will mostly be on the structure-side of the policy/structure-
scale that was presented in the core considerations.

The library will also be a part of a good API design. Its objects will allow IDEs
to help developers, the methods on the objects are easier to find and document
than keys in some array while off-the-shelf types in the library can help developers
to save work.

A precondition for the success of the library is that it contains some
interesting types and that it is known to developers. Besides the commonly used
types captured in the library, there will still be a lot of datastructures that
belong to a certain component and not to a common library. For these types
similar strategies than those showcased in the [Data-library](../../src/Data)
will need to be deployed by the responsible maintainers.

These strategies are:

* Primitive data types should be used as little as possible. Instead semantically
richer datastructures should be used to put PHP's type system to a greater
effectiveness. This will help security as well as correctness and understandability.
* The richer datastructures should protect their integrity via a "correctness by
construction"-approach. This means, that constraints should be enforced in the
constructor and by all methods to change the datastructure, be it setters or
mutators. The integrity needs to be enforced by the datatype itself to make invalid
data unexpressable.

### Refinery

The [Refinery-library](../../src/Refinery) defines two basic abstractions to be used
for processing input securely:

* A **[`Transformation`](../../src/Refinery/Transformation.php)** is a structural
  conversion between different shapes and types of data. It does not cause 
  side effects. `Transformation`s can be combined to create complex `Transformation`s
  from simpler ones. The [Refinery](../../Refinery) aims to provide common
  `Transformation`s and combinators so that they can be reused throughout the system.
  It thus is the tool to transform primitive input data into semantically richer
  structures in a declarative way.
* A **[`Constraint`](../../src/Refinery/Constraint.php)** is a check on some data in
  conjunction with a builder for a human-readable error-message. Like `Transformation`s,
  `Constraint`s can be combined. The [Refinery](../../Refinery) offers a collection
   of readymade `Constraint`s and combinators.


Like the [Data-library](../../src/Data), the [Refinery-library](../../src/Refinery)
deals with structural constraints on the data and not policies. It will need to
be backed by semantically rich data types that protect their integrity properly to
become effective in securing input processing. If the transformations only work on
primitive datatypes, they will only amount to shuffeling array entries back and
forth without documenting the effort in the type of the created data. It thus will
be a tool to quickly and easily derive meaningful data from primitive input but
will also require additions to the [Data-library](../../src/Data) and the components
that use their own data structures.

The [Refinery-library](../../src/Refinery) also offers a perspective on how other
components that enforce policies in the system may come into play here: They may
offer sets of `Transformation`s and `Constraint`s that enforce policies of the
system in question.

### ilUtil::stripSlashes and ilInitialisation::recusivelyRemoveUnsafeCharacters

Currently ILIAS has two methods that are used to systematically secure input
processing: `ilUtil::stripSlashes` and `ilInitialisation::recusivelyRemoveUnsafeCharacters`.

`ilUtil::stripSlashes` is used in many places throughout ILIAS. Other than its
name suggests, it does not only attempt to strip slashes from some string but
also attempts to remove html-tags, where the user can define which tags will
remain in the input. PHP's standard `stripslashes` will only be called if the
ini-setting `magic_quotes_gpc` is defined, which is deprecated by PHP and thus
most likely won't be present which means `stripslashes` most likely will not be
called.

Although `ilUtil::stripSlashes` is used on input, its workings suggest that it is
actually a device that removes data according to some output context (HTML) which
means that it is a form of escaping. This also shows in the fact, that it does
not remove data that would be dangerous in other contexts, e.g. SQL for databases
or `"` for json. Also, data treated with `ilUtil::stripSlashes` won't work in
an attribute context of html, since `"` is kept. The foremost problem that
`ilUtil::stripSlashes` currently seems to tackle is that in some locations users
want to use html-markup in their input, but the system needs to ensure that the
html-markup is protected from XSS.

`ilInitialisation::recusivelyRemoveUnsafeCharacters` is called in the initialisation
process of ILIAS to remove HTML-tags and some single characters that are deemed
unsafe from `$_GET`. Since the output context is unknown, the situation is similar
to `ilUtil::stripSlashes`. Still, `ilInitialisation::recusivelyRemoveUnsafeCharacters`
seems to cover a broader range of output as it removes `"` and `'` as well. We
propose a similar approach as for `ilUtil::stripSlashes` by introducing proper
datatypes to capture the use of parameters in `$_GET`. We suspect this uses to
be very narrow, mostly ids, alphanumerics and the control path. It should be
simple to device proper datatypes for these usecases. To phase out the use of
`ilInitialisation::recusivelyRemoveUnsafeCharacters` we will additionally have
to provide a proper method to get values from `$_GET` as outlined in [API-Design](#api-design).


### HTMLPurifier

HTMLPurifier is a well known library that attempt to transform HTML to a restricted
- pure - form, where the consumer of the library can configure which tags and
attributes are allowed. ILIAS wraps that library in `Services/HTML`. Currently
the wrapper is only used in the Test & Assessment, the Forum and the Terms of Service,
where it should clean user supplied HTML to prevent XSS-attacks.


### ilPropertyFormGUI

The traditional way of building forms `Service\Forms` in ILIAS provides various
types of inputs that can be put together to forms. The base class for these inputs
`ilFormProperty` defines a `checkInput` method, that the derived classes need to
implement to check the input. The checks thus are bound to the different types of
input, if one needs another check, one either needs to implement a new input to
perform said check on the consumer side. The fact that these object scrutinize
inputs from the user is carried over to the new input implementation of the UI-
framework, while the checks themselves arent't tightly couple to the input elements
as presented in the [Showcase](#showcase-input-via-forms-in-the-ui-framework).


### Typecasting in GUIs

A lot of GUIs typecase values they retrieve from `$_GET`: `$obj_id = (int)$_GET["obj_id"];`.
This is a very basic form of sanitizing the input and make sure that only integers
are passed to deeper layers of the system. Casting to int, however, has well known
drawbacks in PHP that might lead to unintendet consequences. `(int)""` for example will be 0.
The simplicity of the casting approch is something to be conserved in a future
strategy to secure inputs, while we need to be careful regarding PHP idiosyncrasies.


## Showcase: Input via Forms in the UI-Framework

The libraries outlined in [State of the Art](#state-of-the-art) have been build
to implement input via forms in the UI-Framework. We thus want to use the form
input in the UI-Framework as a showcase for the libraries and explain their
cooperation with regards to the principles outlined in the [Core Considerations](#core-consideration).
On the other hand, the current state of the form-inputs might already hint at
some potential for future improvements in the libraries as well as in general. 
The code presented in the following was discussed in [this PR](https://github.com/ILIAS-eLearning/ILIAS/pull/1189)
and is now [part of the ILIAS-core](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/Modules/StudyProgramme/classes/class.ilObjStudyProgrammeSettingsGUI.php#L159).
Since we want to show case how input data can be secured here, we refer to the
explanation of the [Inputs in the UI-Framework](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/src/UI/Component/Input/README.md)
for further explanation regarding visual aspects of the form.

We first will have a look into [`ilObjStudyProgrammeSettingsGUI::update`](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/Modules/StudyProgramme/classes/class.ilObjStudyProgrammeSettingsGUI.php#L159) 
to get a general idea of the structure of the processing. The example is
shortened a little to highlight the essentials, while comments are added
for explanation:

```php
$form = $this

	// We first build the form, which contains the definition of the shape of the
	// expected input, the constraints on that input and the procedure to transform
	// it into the required structure (along with the visuals).
	->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "update"))

	// We then attach the actual source of that data, which is a PSR-7 `ServerRequestInterface`.
	// Note that this attaches values and (possibly) errors to the input fields in
	// the form, that directly allows the developer to again show it to the user.
	->withRequest($this->request);

// Finally we attempt to acquire the data from the form. Note, that this data will
// either be not available or already fit the structure and policies we defined
// in buildForm.
$content = $form->getData();

// If the input of the user did not fit the expected structure and policies, we won't
// be able to retreive any data and hence cannot process anything.
$update_possible = !is_null($content);
if ($update_possible) {
	// perform update process
} else {
	// print form with errors to the user
}
```

The essential part of the input processing is the definition of shape, constraints
and transformations of the input, which goes along with visual requirements when
definining forms. We thus have a look at the shortened and commented method
[`ilObjStudyProgrammeSettingsGUI::buildForm`](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/Modules/StudyProgramme/classes/class.ilObjStudyProgrammeSettingsGUI.php#L216):

```php
// We define some shortcuts for brevity in the definition later on.
$ff = $this->input_factory->field();
$tf = $this->trafo_factory;
$txt = function($id) { return $this->lng->txt($id); };

// We gather some options to be used in a select-field later on.
$sp_types = ilStudyProgrammeType::getAllTypesArray();

// We construct a form by using the factories of the UI-Framework defined
// previously.
return $this->input_factory->container()->form()->standard(
	// We need to tell where the input is posted to...
	$submit_action,
	// ... and which fields the form contains
	[
		// We assign (local!) names to these fields...
		self::PROP_TITLE =>
			// ...define the types of the fields...
			$ff->text($txt("title"))
				// ...the value that is shown initially...
				->withValue($prg->getTitle())
				// ...and (if so) whether input is required from the user.
				->withRequired(true),
		// We do this again for the remaining fields...
		self::PROP_TYPE =>
			$ff->select($txt("type"), $sp_types)
				->withValue($prg->getSubtypeId() == 0 ? "" : $prg->getSubtypeId())
				// ...and may also attach more transformations to the data in the
				// fields if our usecase requires us to. Here we need to wrap around
				// the fact, that an  non-selection in the select-field is represented
				// by an empty string in the inputs, while the Study Programme uses 0
				// to represent a Programme with no type.
				->withAdditionalTransformation($tf->custom(function($v) {
					if ($v == "") {
						return 0;
					}
					return $v;
				})),
		self::PROP_POINTS =>
			// The UI-Framework offers types of input that already carry some
			// constraints, like `numeric` that only allows for numeric values
			// in the users input.
			$ff->numeric($txt("prg_points"))
				->withValue((string)$prg->getPoints())
		)
	]
);
```
Note the key components in the construction of the input processing of forms in
the UI-framework:

* The API of the form is very small and only contains two interesting methods
with a clearly defined purpose. `withRequest` attaches user input to the form,
while `getData` allows to retrieve it. All the nitty-gritty details of how the
data is collected from `$_POST`, processed, checked, filled in the form etc. is
hidden in the definition of the form and these two methods.
* The definition of the form is declarative and the structure of the code can
be arranged in a way that closely resembles the structure of the form as it is
found on the screen. Besides the closure in `withAdditionalTransformation` no
statement code is used. This makes it easy to grasp what is going on here,
possibly even for people that don't know ILIAS or PHP in general very well.
* The syntax for the declaration of the form uses techniques well known for users
of the UI-Framework, like named factories, immutable objects and easy composition
of larger structures from smaller parts. The mechanism to process the input
was created with care to fit these techniques and maintain their properties. The
input processing is weaved naturally into the definition of the visuals.

This all amounts to an API-design that encourages the user to properly process
input received via forms. The correct approach is made easy, while incorrect
procedures are hard to implement. To stress this principle: also it might look as
if it could be possible to retreive data from `$_POST` by using `$_POST[self::PROP_TITLE]`,
this actually won't work. The name `self::PROP_TITLE` of the field only occurs
locally while the names of the field in the actual `$_POST` are set by the
abstraction. This enhances composability and disencourages incorrect procedures
at the same time.

The subject [Structure vs. Policy](#structure-vs-policy) needs to get some extra
attention since it is only present very implicitely in this example. The form
only declares a few constraints in a visible manner. First note, that `withRequired`
and `numeric` in fact are constraints. While `numeric` is a structural constraint
(only ints or floats are allowed), `withRequired` may be viewed as a policy, as
there won't be any technical problems with an empty string as title besides the
quite comprehensible expectation that a title at least contains one character.

The method `ILIAS\UI\Component\Input\Field::withAdditionalConstraint` can be used
to attach additional constraints over the ones that the input fields define by
default. If one would want, for example, a numeric field that may only contain
numbers larger than 0, one could attach a an according constraint to the field:

```php

$ff = $this->input_factory->field();
$cf = $this->constraint_factory;

$numeric_larger_than_zero =
	$ff->numeric($txt("prg_points"))
		->withAdditionalConstraint($cf->greaterThan(0));

``` 

Note that this defines a constraint on the input as well as the error message
that is shown when the input of the user didn't match the expected value. Since
there most probably is some user sitting in front of a screen showing the form,
it is nice of the system to provide him with some hint on his mistake to put
him into the position to fix it. Constraints can be added to singular fields
as well as to compositions of fields to express constraints that concern multiple
fields at the same time.

While constraints are more about policies then structure, the method
`ILIAS\UI\Component\Input\Field::withAdditionalTransformation` is used to derive
required structure from the data provided by the users. Similar to the constraints,
the input fields already bring transformations, but the user may add additional
ones on top of them. The `text` and `textarea` inputs, e.g. currently `strip_tags`
from the provided strings. Similar to `ilUtil::stripSlashes` this is a weak measure
to provide security, while the non-existing feedback about the operation might
lead users to think they actually entered html-tags when in fact they didn't (see
[Sanitizing](#sanitizing)). Currently, `ilUtil::stripSlashes` is required to allow
for simple text formatting via HTML in text-inputs or textareas. Once this problem
is solved `ilUtil::stripSlashes` and `strip_tags` can go away.

The advice to define datastructures that enforce their structural constraints
by construction (see [Data](#data)) also is not implemented in the given example,
as the data is retrieved from the form as an array. To show how this might look
like, we might imagine a data structure that carries some basic data for every
`ilObject`:

```php
class ilObjectData {
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	public function __construct(string $title, string $description) {
		// Enforce policy that titles need to contain at least one character.
		if (strlen($title) == 0) {
			throw new \InvalidArgumentException(
				"Title needs to have at least one character");
		}
		$this->title = $title;
		$this->description = $description;
	}
}
```

This could be used with the API for inputs as such:

```php
public function getObjectDataSection(string $title, string $description) {
	$ff = $this->input_factory->field();
	$tf = $this->trafo_factory;
	$cf = $this->constraint_factory;
	$txt = function($id) { return $this->lng->txt($id); };

	return $ff->section(
		[ $this
			->text($txt("title")
			->withValue($title)
			->withRequired(true)
		, $this
			->textarea($txt("description"))
			->withValue($description)
		],
		$txt("object_data"),
		""
	)->withAdditionalTransformation($tf->custom(function($vs) {
		return new ilObjectData($vs[0], $vs[1]);
	});
}
```

This approach will document the check on the "no empty title"-policy in the
datatype and bundle the two primitive strings together, like they were actually
given by the user. The API allows to handle a defined chunk of a form with its
visuals, constraints and transformations and reuse it in various locations.

This example also shows potential for improvements in the libraries that are
used. The [Transformation-library](../../src/Refinery/Transformation) currently doesn't
offer a premade solution to the common task of creating new objects and the
user needs to fall back to a `custom`-transformation. Although not very visible
in the example, the check on the "no empty title"-policy is duplicated, once
in the form (via `withRequired`) once in `ilObjectData::__construct`. It does
not seem to be possible to remove the check in this example (since `withRequired`
is not only a constraint but also adds a visual marker to that field). In general
it should be possible to check constraints in some classes constructor and hook
into the mechanisms of the [Validation-library](../../src/Validation) without
duplicating the check.

The processing of user input via forms in the UI-Framework by using the libraries
that where created thus implements the requirements outlined in the [Core
Considerations](#core-considerations) as such:

* It allows to tackle primitive obsession by considering the transformation of data
at the boundary of the system from the start and weaving it into the visual requirements
of forms.
* It offers an API that is declarative but still allows to introduce imperative
parts as required. The compositionality of the components on different levels
(fields of the form, constraints, transformations) allow to adopt to a huge
bandwith of requirements and as well as to construct reusable parts. This cannot
compete with the `$_GET`- and `$_POST`-APIs in simplicity, but still offers
compelling advantages with a pleasant surface.
* The new API can be introduced gradually and will work besides (but not with!)
the existing ilPropertyFormGUI. As the [PR for the Study Programme](https://github.com/ILIAS-eLearning/ILIAS/pull/1189)
shows, it is possible to use the new API with only minimal adjustments to the rest 
of the component.
* The API can express structural constraints as well as policy while being able
to give feedback to the user of the form.


## Evaluation

In the following we will evaluate parts of the system currently not subject to
a systematic approach to security in input processing. We will gather requirements
and assess if and how the libraries written for the form input in the UI-Framework
can be used to implement them. In this process we will derive which extensions
are required for said libraries as well as find realms that currently are not
covered by ILIAS libraries or services.


### Requirements of XML-Imports

XML is used as the format to export and import objects from and to ILIAS. The
import via XML is a huge surface for potential attacks on ILIAS, as most ILIAS
objects and some ILIAS services offer possibilities to import and export XML
files. At the same time, the surface is not only huge but also is deeply rooted
in ILIAS, since the XML-files are representing ILIAS-objects and their according
settings and contents and thus allow to set these and trigger or influence
functionality depending on them.

Thus there are different possible vectors for an attack via XML-imports:

* There is a rather large amount of well known attacks via XML that are outlined
in a [cheat sheet from OWASP](https://www.owasp.org/index.php/XML_Security_Cheat_Sheet).
These may degrade the systems security as well as its availability.
* Similar to other inputs, the XMLs contain values that are processed and
interpreted by ILIAS in different contexts. If constraints on structure and from
policies cannot be enforced on these values, similar problems like in other
contexts may arise.
* The deeply rooted nature of the import may allow for attacks that use e.g.
race conditions or problems in specific subsystems of ILIAS that are triggered
via import.

Most problems outlined in the [OWASP cheat sheet](https://www.owasp.org/index.php/XML_Security_Cheat_Sheet)
are well out of context of this investigation regarding a general input filter
service. They still are worth considering, since ILIAS seems to be indeed vulnerable
to most of them, e.g.:

* ILIAS has an XSD for the export-files, but currently uses the [PHP XML-Parser](http://php.net/manual/de/intro.xml.php),
which cannot validate provided XML-documents. Also, XSDs are considered to be too
weak by OWASP.
* ILIAS has no defenses against malformed XML or XML-bombs that attempt to blow
up the stack.

We thus urge the leadership of the project to consider XML-based attacks as
a general field of action to strengthen ILIAS security.

The problem of "Improper Data Validation" outlined in the [OWASP cheat sheet](https://www.owasp.org/index.php/XML_Security_Cheat_Sheet)
is the same problem other types of input have, but from a different perspective.
OWASP considers this problem from the perspective that stricter validation needs
to be performed on the XML-document itself, while from the perspective of this
paper, the XML can be considered to be just a deeply nested datastructure with
unknown content. Accordingly, it needs to be scrutinized and constraints regarding
structure and policies need to be enforced on the data just like this is the
case for data provided via `$_POST`. This vector thus is exactly in the scope
of this paper.

The attacks that may arise due to the deeply rooted nature of the import in ILIAS
are considered to be out of scope of this paper as well. A general framework to
filter inputs needs to outline how different subsystems may hook into the enforcement
of constraints but cannot consider all policy requirements, as explained in
[Policy vs. Structure](#policy-vs-structure).

That leaves us to look a little deeper into one set of requirements regarding the
XML-report: How can we read and validate deeply nested structures to make sure
that the provided data matches the expected constraints?

Currently the XML-parsing at import is mostly handled by classes in `Services\DataSet`
and extensions of these classes. Although it is necessary to define types for the
fields a dataset should contain, there is no discernible general check regarding
these types on the data provided via the XML, although the base classes allow
derived classes to implement said checks on their own. The import parser in the
`Services\Dataset` creates arrays according to the provided XML. As they support
different versions of ILIAS, they need to deal with optional fields in the array.
The datatypes, that the `DataSet` currently supports, are `text`, `integer` and
`timestamp`.

The requirements for a general service to secure input via XML thus are:
* It should support text, integer and timestamp.
* It should support arrays.
* It should allow to express constraints on said types.
* It should invite users to actually perform checks and validations instead of
omitting them.


### Requirements of SOAP

In ILIAS, SOAP is used for internal administration as well as to provide an
interface to ILIAS for external systems. When used for internal administration,
the according endpoint can be closed for the outside world and the SOAP-functions
are used to provide concurrent processes. When used by other systems, the SOAP-
endpoints need to be opened to the outside world and the provided functions
create a huge and potentially powerful attack surface.

Similar to XML-import, there are different vectors to discuss when looking into
attack vectors via SOAP:

* There is a [cheat sheet from OWASP](https://www.owasp.org/index.php/Web_Service_Security_Cheat_Sheet)
that contains rules attempting to secure webservices.
* Since SOAP uses XML for its messages, the attack vectors that may apply to XML
may also apply to SOAP.
* The data provided by users via SOAP needs to be subject to various constraints
derived from required structures and policies, as any other input needs to be.

The general rules to secure webservices given on the [OWASP cheat sheet](https://www.owasp.org/index.php/Web_Service_Security_Cheat_Sheet)
mostly apply to the transport layer of the communication and thus are of no
concern for this paper. Also the possible vectors regarding general XML-
processing (XML-bombs, DoS, ...) should be tackled but are considered to be
outside the scope of this paper.

Similar to XML-import, the SOAP-servers used by ILIAS seem to [perform no
validation of structure and content](https://stackoverflow.com/questions/106401/validate-an-incoming-soap-request-to-the-wsdl-in-php),
although the user may be tempted to think so, since a WSDL is supplied to the
SOAP-Server. In fact, PHPs `SoapServer` only seems to perform a typecast according
to the WSDL. To add complex objects, the ILIAS SOAP-interface uses the import
via XML under the hood.

The requirements for SOAP thus seem to be rather similar than those for the XML-
import. We need to be able to check if given data complies with some type and we
need to be able to check policy constraints on the data. We need to be able to
understand primitive types and arrays of said types, but we do not need to deal
with deeply nested types.

Also, SOAP shows very well that ILIAS currently does not has clear rules
where and how inputs need to be scrutinized. Currently the classes providing
the SOAP-functions seem to call very basic ILIAS-layers (e.g. ilObjectFactory)
and it is not clear which constraints are enforced by these layers or the
SOAP-layer itself.

However, it seems as if checks would need to be duplicated on the SOAP-layer
and in the GUI-classes (at least), since currently there often is no explicit
layer which has the responsibility to perform actions. The introduction of
a "service discovery", driven by the Workflow Engine, would introduce such a
layer, which then would be the natural place to check structural or policy
constraints on the input to the actions. We thus recommend to further pursue
that project and embrace its positive impact regarding security.


### Requirements of GET-Requests

The current state of the art in the UI-Framework is mostly concerned with input
via forms and `$_POST` at the moment, but already contains one component (the
Pagination View Control) that uses GET to pass required parameters. The Mode View
Control or general buttons are also controls that use GET to pass parameters, but
there the creation and reading of the parameters is under complete control of the
consumer of the UI-Framework. We expect the quantity of controls using GET in the
UI-Framework to grow, since using GET is required for every control that somehow
needs to control the view, i.e. isn't just a passive display for some data.

On the other hand, virtually every GUI-class of ILIAS uses GET to retrieve commands
or parameters, and the components in the UI-Framework can be viewed as pars pro
toto to understand how input via GET can be handled securely. At some point the
UI-Framework might evolve in a direction that requires it to have more knowledge
about how URLs are build in ILIAS. The problem to read GET will have persisted
then, either inside the UI-Framework or at the side of the consumers of the
framework.

Besides the already mentioned `ilInitialisation::recusivelyRemoveUnsafeCharacters`,
there currently is no systematic approach to secure `$_GET`. `grep -r "\$_GET"` in
the ILIAS folder currently shows ~4600 locations where `$_GET` is used. Although
we can find locations that guard the parameter from `$_GET` with typecasts or
handle them with due diligence, we do not expect all usage locations to be actually
careful enough to prevent attacks via `$_GET`.

The usecases for `$_GET` mostly seem to be these:
* Passing ids, be it `ref_id`s, `obj_id`s or other internal ids like ids for
questions. `grep -r "\$_GET" . | grep "_id"` reveals ~2500 locations.
* Passing commands to be executed by the GUI via `$_GET["cmd"]`.
* Passing routing information to find out which GUI-classes should be executed
to build a view or perform some action via `$_GET["baseClass"]`, `$_GET["cmdClass"]`
and `$_GET["cmdNode"]`.
* Passing information regarding the sortation and pagination in the `table_nav`
-Parameter.

The case of `$_GET["cmd"]` is especially interesting. From a consumers perspective,
the command is retrieved via `ilCtrl::getCmd`. There is no hint that the command
is received directly from `$_GET` which is what actually happens. The user might
be tempted to use the command (which is a simple string) without further inspection,
e.g. by using it as a method name `$this->$cmd()`. This pattern is actually found
in the ILIAS code base several times and allows an attacker to just call any method
on the GUI-class that uses this pattern. This again shows a situation where it is
unclear if and how constraints on input need to be applied.

The case of `ilCtrl` and `ilTable2GUI` also shows a principle that the forms in
the UI-Framework use as well: instead of accessing `$_GET` directly, the consumers
access a component that wraps the access, which gives an opportunity for the
wrapping component to control the data that the consumers see. `ilTable2GUI` has
enough information to validate the data it reads from `$_GET`, since it is fully
determined by the controls `ilTable2GUI` adds itself. `ilCtrl` on the other hand
cannot know which commands a GUI class provides and thus cannot perform the
validation. The forms in the UI-Framework handle the same situation (not knowing
which validations need to be performed in the use case at hand) by forcing the
users to add at least some constraints and transformations on the input, thus
making it impossible to simply forget to scrutinize the input. We expect that
this principle will work in similar situations as well.

The requirements to securly process input provided via GET thus can be phrased
as such:

* Move as many processing of `$_GET` into closed components and perform as much
validation as possible in these components. This will work well for components
that are "closed" in some sense, like `ilTable2GUI` is.
* Provide a pattern to be used for retrieving data from GET via some component
for cases that are "not closed" in some sense and do not know enough about the
provided data to scrutinize it thoroughly.
* Provide a wrapper around `$_GET` according to that pattern to be used by the
aforementioned components and in cases where an according component cannot be
provided or is not provided yet.


### Other Input Mechanisms

There are other input mechanism that are not analyzed in this paper in depth,
but still need to be regarded at some point from a security perspective. The
following candidates come to mind easily, while there will be even more
mechanisms not mentioned here:

* Submission of data via JSON-over-HTTP, e.g. for asynchronous form submission.
* Reading data from Cookies.
* Reading data from Sessions.
* Reading data from the database in general.
* Reading data from an LDAP-server.
* Retrieving data from SCORM-Objects.

We expect that these scenarios at least have one general requirement in common
with the examples analyzed so far: In each of the cases we have some boundary
of the system that is crossed by primitive data, constructed by ints, floats,
strings, dictionaries, lists and nothing (or very little) else, possibly nested
deeply. The task then is to find out, if that data fits some constraints from
structure or policy and transform it to an appropriate internal datatype quickly
before it is processed further in the guts of the system.

Moreover, we expect that these cases will have an object that can act as a
representation of that boundary to the user (like `$_GET`, the form in the UI-
Framework or the `ilDataSet` for XML-Imports) or that it is at least possible
to create such an object. We then expect it to be possible to either completely
internalize the validation or use a pattern to force the user to give
constraints and transformations on the data before he may retrieve it.


## Outlook

### Improve Transformation and Validation

The investigations so far suggest that the concepts of a `Transformation` and
a `Constraint` are similar from the perspective of a data validation process,
i.e. we need to perform a series of steps on some given primitive input to check
constraints and derive semantically richer structures, while it is not important
if a given step actually transforms the data or merely checks some constraint
and passes the data on if it was successfull. This also shows in the observation
that we indeed need to be able to incorporate checks into constructors of
datastructure that can be processed in a way meaningful to humans.

We thus propose to add a new method to the `ILIAS\Refinery\Transformations\Transformation`
interface:

```php
/**
 * A transformation is a function from one datatype to another.
 *
 * It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
 * how often the transformation was actually performed. It MUST NOT touch the provided
 * value, i.e. it is allowed to create new values but not to modify existing values.
 * This would be an observable sideeffect.
 *
 * If you are reading this you most certainly do not want to implement this
 * interface on your own but instead use the building blocks from the factory of
 * the Refinery instead and stick them together like legos.
 */
interface Transformation {
	/**
	 * Perform the transformation and reify possible failures.
	 *
	 * If `$data->isError()`, the method MUST do nothing. It MUST transform the value
	 * in `$data` like it would transform $data provided to `transform`. It must reify
	 * every exception thrown in this process by returning a `Result` that `isError()`
	 * and contains the exception that happened.
	 *
	 * If you simply need to implement a transformation you most probably want to
	 * implement transform and derive this via the trait `DeriveTransformationInterface`.
	 *
	 * If you simply want to call the transformation, you most probably want to use
	 * `transform`, since it simply throws expections that occured while doing the
	 * transformation.
	 *
	 * If you are implementing some entity that performs processing of input data at
	 * some boundary, the reification of exceptions might help you to write cleaner
	 * code.
	 */
	public function applyTo(Result $data) : Result

	// [..]
}
```

The current `ILIAS\Refinery\Validation\Constraint` interface can then implement this interface
by simply renaming `Constraint::restrict` to `Constraint::applyTo`. This change
will allow simplification in the current form-implementation of the UI-Framework,
on the consumer side as well as on the implementation side. This will also allow
to implement `Transformations` that perform checks and create new value as well,
like we need for constructors of datastructures. `Transformation::transform` then
can be generically implemented in terms of `applyTo`:

```php
public function transform($from) {
	$result = $this->applyTo($this->data_factory->ok($from));
	if ($result->isError()) {
		$e = $result->error();
		if ($e instanceof \Exception) {
			throw $e;
		}
		throw new \InvalidArgumentException($e);
	}
	return $result->value();
}
```

We furthermore propose to unify the two concepts on a library level as well and
propose `Refinery` as name for the new library. The existing `Validation` and
`Transformation` libraries should be merged into this new library. The factory
for its structures should then be organized as such, where the different groups
are explained in the following sections:

- **to**: Combined validations and transformations for primitive datatypes that
  establish a baseline for further constraints and more complex transformations
	- **string**
	- **int**
	- **float**
	- **bool**
	- **listOf** - with one `Transformation`-parameter that defines the content
	- **dictOf** - with one `Transformation`-parameter that defines the content
	- **tupleOf** - with an arbitrary amount of `Transformation` parameters that
	  define content
	- **recordOf** - with a dict of `Transformations` as parameters that define
	  the content at the indizes
	- **new** - create a user defined datastructure
	- **data** - create a structure from the Data-library
- **kindlyTo** - offers the same transformations like **to** but will be more
  forgiving regarding the input
- **identity** - does not check anything and returns the value as provided
- **in**
    - **series** - takes an array of transformations and performs them one after
	  another on the result of the previous transformation
    - **parallel** - takes an array of transformations and performs each on the
	  input value to form a tuple of the results
- **allOf** - takes an array of constraints and checks all of them, where the
  errors discovered in that process will be collected
- **byTrying** - takes an array of transformations and returns the result of the
  first successfull one
- **optional** - accepts values according to a provided transformation or null
- **selection** - takes an array of transformation and tries to pull values from
  a provided array according to it
- **int** - contains constraints and transformations on numbers. Each constraint
  on an int will attempt to transform to int as well.
	- **isGreaterThan**
	- **isLessThan**
	- *various other constraints on numbers*
- **string** - contains constraints and transformations on string. Each constraint
  on a string will transform to string as well.
	- **hasMinLength**
	- **hasMaxLength**
	- **fitsRegexp**
	- **splitAt**
	- **isOneOf** - to only allow strings that are in a certain set
	- **asJSON** - decodes a json-string to an array
	- *various other constraints on strings*
- **array** - contains contraints and transformations on arrays
	- **map** - apply another transformation to each element
	- **flatten** - turn a deeply nested array into a flat array, depth-first
	- **toJSON** - encodes the array to a json-string
- *groups for other types from Data*
- **customTransformation**
- **customConstraint**

To deal with nested data we need a way to define how to process lists of values,
dictionaries of values, records and tuples. We make a distinction here that is
common in many programming languages and data formats, but not present in PHP.
A list is understood as a sequence of similar values with a variable length. A
dict is a set of similar values indexed by a string key. A tuple is a sequence
with a fixed length and a defined shape for the values in each place, where the
shape may differ among places. A record then is a tuple where the places are
indexed by string key. All these different shapes are implemented together in the
PHP `array`-type, where lists are sometime notated as `SomeType[]` or `array<SomeType>`
and dicts as `array<string,SomeType>` in docstrings. There seems to be no common
notation for tuples and records, however.

The first reason to make this distinction for input processing is, that it indeed
also exists in at least some of the source formats in which data is provided. JSON
e.g. at least knows the difference between a list and a record, although it does not
know about tuples or dictionaries. The second reason to introduce the distinction
is that it makes it easier to talk about constraints and transformations, since
most code will indeed use some PHP-array in one of the ways defined above and does
not expect to be dealing with some arbitrarily shaped `array`.

To simply check and cleanup provided data to some desired target structure, implemented
as `array` and other primitive types of PHP, the factory of the `Refinery`-library
will provide a group called `to` that allows to define such primitive shapes. We
propose that these transformations all work in a strict way. This would mean, for
example, that:

* `string()` will only accept real strings and not attempt to transform something
into a string but throw instead.
* `tupleOf(int(), int())` will only allow arrays that contain exactly two integers
and not attempt to shorten an array with three integers or cast a float to an int.

This will allow developers to express strict requirements on data and get informed
if the data provided by some user did not match the expected structure. This, in
turn, will allow to detect problems, bugs or signs of tampering for cases where we
indeed know exactly which shape the data that reaches the system needs to have, e.g.
when we are dealing with asynchronous requests from the GUI.

To accomodate cases in which [Postels Law of robustness](https://en.wikipedia.org/wiki/Robustness_principle)
applies, e.g. when dealing with an interface to another machine, we provide the
section `kindlyTo` on the factory. It will provide the same transformations as
`to` but with a more forgiving implementation, e.g.:

* `string()` will accept real strings and transform `ints` or `floats` or entities
that implement `__toString` to a string.
* `tupleOf(int(), int())` will accept [1,2] but also [1,2, "foo"], while the latter
is transformed to [1,2] in the process.

Note that there is a sweet spot for this forgiveness, since the transformations
should not attempt to stipulate too much about the required transformation. It
would for example be sensible to transform an int to a float, but not so to
transform a float to an int. While the former leaves the value virtually unchanged,
the latter would need to take a decision whether to take the floor, the ceiling,
round the provided number or simply leave it undefined what happens. This decision
cannot be made sensibly by the transformation. This would be similar for `string()`.
It seems to be sensible to transform `0` to `"0"` or an object that explicitely
comes with the possibility to be transformed to a string (i.e. `__toString`). Not
so for an `array` that will just be `"Array"` when transformed to string, which has
nothing to do with the original array content.

Since using primitives is very common in the ILIAS codebase, we cannot expect
that all components will get rid of primitive obsession soon. The APIs behind
`to` and `kindlyTo` will still allow a gradual migration to the new mechanisms
to secure inputs while keeping internal implementations of the components
based on primitives. Since `kindlyTo` works differently then the currently often
used combination of integer-cast and `if`, these locations need to be migrated
carefully.

To get rid of primitive obsession we then need a way to define how to build
user-defined datastructures in a chain of transformations and constraints,
either by factory or by using new. For this purpose we propose to introduce
a transformation with the following signature:

```php
/**
 * Get a builder for a datastructure, either by using a factory or a class-name.
 *
 * @param	string|callable	$what
 * @return	mixed
 */
public function toNew($what);
```

The already existing transformation `toData` then is a special case of this
transformation. The transformation could be used as such:

```php
$class_builder = $trafo->toNew(MyClass:class);
$object = $class_builder->transform(["a", "b"]); // = new MyClass("a", "b")

$factory = new MyFactory();
$factory_builder = $trafo->toNew([$factory, "structure"]);
$object = $factory_builder->transform(["a", "b"]); // = $factory->structure("a", "b")
```

To allow the constructors or factories of datastructures to throw exceptions that
can be picked up by the `Constraint`-mechanisms, we propose to introduce a new
type of exception that resembles the interface for i18n that is passed to the
closure provided via `Constraint::withProblemBuilder`:

```php
/***
 * Signals the violation of some constraint on a value in a way that can be subject
 * to i18n.
 */
class ConstraintViolation extends \UnexpectedValueException {
	// [..]

	/**
	 * Construct a violation on a constraint.
	 *
	 * @param	string	$message	developer-readable message in english.
	 * @param	string	$lng_id		id of a human-readable string in the "violation" lng-module
	 * @param	mixed[]	$values		values to be substituted in the lng-variable
	 */
	public function __construct(string $message, string $lng_id, ...$lng_values) {
		// [..]
	}

	// [..]
}
```

This will allow for less duplication of checks in the constructors of datastructures
vs. external constraints applied via subclasses of `Constraint`, as found in the
forms-showcase.

To mangle the primitives, lists, tuples, dicts and records that are received as
input to a structure that can be consumed by a toNew-transformation (which is
a tuple) we need some facilities that allow to mix and match the structures and
transformations provided so far.

We start with `in()->series` that simply takes a list of transformations and applies
one after the other, each on the value produced by the predecessor:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->in()->series(
	$refine->kindlyTo()->int(),
	$refine->kindlyTo()->float()
);

$res = $my_trafo->transform("1");

assert($res === 1.0);
```

Note that `in()->series` will replace (and extend) the currently implemented `sequential`-
constraint.

The next facility will have a similar interface to `in()->series` but will apply each
transformation to the initially provided value and output the results as a tuple.
It main purpose is to pick interesting stuff from an array, but it might have other
interesting application as well.

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->in()->parallel(
	$refine->kindlyTo()->string(),
	$refine->kindlyTo()->float()
);

$res = $my_trafo->transform(1);

assert($res === ["1", 1.0]);
```

Note that this is not the same as the currently implemented `parallel`-constraint,
since this constraint adds the errors but only returns one value. This constraint
will be renamed to `allOf` instead.

To be able to deal with situations where some data might be matching this or that
constraints or structure, we provide the `try`-combinator:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->byTrying(
	$refine->to()->string(),
	$refine->to()->int()
);

assert($my_trafo->transform("foo") === "foo");
assert($my_trafo->transform(1) === 1);

$my_trafo->transform(1.0); // will throw
```

The `optional`-transformation is a special case of that transformation that accepts
null or a value according to a transformation:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->optional($refine->to()->int());

assert($my_trafo->transform(null) === null);
assert($my_trafo->transform(1) === 1);

$my_trafo->transform(1.0); // will throw
```

To pull values from some array we provide the `selection` transformation. It takes
an array of transformations as parameter and then selects values from a provided
array containing data with the given transformation to yield a tuple:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->selection([
	"an_int" => $refine->to()->int(),
	"a_string" => $refine->to()->string()
]);

$values1 = ["a_string" => "foo", "an_int" => 0];
$values2 = ["an_int" => 0, "a_string" => "foo"];

$expected = [0, "foo"];

assert($my_trafo->transform($values1) === $expected);
assert($my_trafo->transform($values2) === $expected);
```

Note that the order of fields in the original data does not matter and the created
tuple is always ordered according to the array given as parameter to `selection`.
The same interface works for transforming tuples as well by just providing integers
instead of strings as keys. The `selection`-transformation is kind, i.e. it does
not expect the exact keys to be available in the provided data but will accept
larger arrays. The transformations works together with `optional` to allow for
optional entries in the data:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$my_trafo = $refine->selection([
	"an_int" => $refine->to()->int(),
	"a_string" => $refine->optional($refine->to()->string())
]);

$values1 = ["an_int" => 0];

$expected = [0, null];

assert($my_trafo->transform($values) === $expected);
```

Moreover, the `Refinery`-library should provide common constraints and transformations,
grouped according to their source-type, as required, and methods to create a custom
constraint and transformation. If common usage patterns of the basic building blocks
emerge, they could very well get their own constructor on the factory to be available
more simple. Also there might be other forms of syntactic sugar that could be poured
over the basic building blocks to make the usage nicer, e.g. allowing simple lists
of transformations instead of `in()->series` in appropriate places.

The example from the showcase above then could look like this:

```php
class ilObjectData {
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	public function __construct(string $title, string $description) {
		// Enforce policy that titles need to contain at least one character.
		if (strlen($title) == 0) {
			throw new \InvalidArgumentException(
				"Title needs to have at least one character");
		}
		$this->title = $title;
		$this->description = $description;
	}
}

// ... in some class:

public function getObjectDataSection(string $title, string $description) {
	$ff = $this->input_factory->field();
	$refine = $this->refinery;
	$txt = function($id) { return $this->lng->txt($id); };

	return $ff->section(
		[ $this
			->text($txt("title")
			->withValue($title)
			->withRequired(true)
		, $this
			->textarea($txt("description"))
			->withValue($description)
		],
		$txt("object_data"),
		""
	)->withAdditionalTransformation($refine->toNew(\ilObjectData::class));
}
```

Creating `ilObjectData` from a JSON-string than would look like this:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$object_data_from_array = $refine->in()->series(
	$refine->selection([
		"title" => $refine->in()->series(
			$refine->to()->string(),
			$refine->string()->hasMinLength(1)
		),
		"description" => $refine->to()->string()
	]),
	$refine->toNew(\ilObjectData::class)
);

$object_data_from_json = $refine->in()->series(
	$refine->asJSON(),
	$object_data_from_array
);
```

The transformations build in this way are reusable, so if we for example had a
data structure that holds some information about a course, which would be the raw
object data and the start and enddate for this example, this could be created
from a JSON-string as such:

```php

/* JSON could look like this:

{
	"title" : "A title",
	"description" : "A description.",
	"start" : "1815-12-10",
	"end" : "1852-11-27"
	"some_other_data" : "... we don't care about."
}

*/

$datetime_from_string = $refine->in()->series(
	$refine->to()->string(),
	$refine->string()->fitsRegexp("%\d\d\d\d-\d\d-\d\d%"),
	$refine->toNew(DateTime::class);
);

$course_data_from_array = $refine->in()->series(
	// in()->parallel applies every transformation to the array seperately,
	// the first one thus picks titel and description and creates ilObjectData
	// form it, while the second and third each fetch a date from the array.
	$refine->in()->parallel(
		$object_data_from_array,
		$refine->selection("start" => $datetime_from_string),
		$refine->selection("end" => $refine->optional($datetime_from_string))
	),
	$refine->toCustom(\ilCourseData::class)
);

$course_data_from_json = $refine->in()->series(
	$refine->asJSON(),
	$course_data_from_array
);
```

During the design of this interface we considered the possibility of a fluent
interface instead of the explicit combination outlined above:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$object_data_from_array =
	$refine->selection([
		"title" => $refine->to()
			->string()
			->hasMinLength(1)
		),
		"description" => $refine->to()
			->string()
	])
	->andThen()
	->toNew(toNew(\ilObjectData::class))
```

We decided against such an interface for the moment. Although fluent interfaces
often provide a better readability, the implementation of such an interface is
cumbersome, even more so when a huge amount of moving parts and combinations of
them is involved. The objects created by the factory of `Refinery` would have a
double role, which is performing actual transformation and checking constraints
and creating new transformations at the same time. The latter would require the
transformations to know about the factories themselves and thus, transitively,
about all their dependencies. While a simple object structure can easily be
inspected via `var_dump`, `print_r` or some debugger, structures that contain
huge dependencies are confusing and hard to grasp. Since we consider it crucial
to review and understand what is going on when processing user provided input,
we decided against a fluent interface.

It would, however, be possible to build such an interface over the primitives
outlined above, which would be a functional equivalent. To gain a nice API we
instead suggest to look for places that would benefit from syntactic sugar,
as well as for recommondations for pleasing usage patterns. For the same reason
we consider the factory dependencies in a fluent-interface case to be bad, we
recommend to factor out the actual i18n from the constraints completely and
instead provide the functionality seperately (but in the same library) to be
used by the consumers of the `Refinery`-library.


### A Pattern to Make Developers Remember

As already outlined in the [Evaluation section](#evaluation) of this paper, there
is a pattern that allows us to make developers remember to check constraints and
apply transformations when retrieving a value supplied by a user. Instead of simply
handing the value to the developer and trust that she won't forget to check it, we
instead only hand out the value transformed according to some `Transformation` the
developer supplied. The archetype of that interface is:

```php
/**
 * Get some value according to the supplied transformation.
 *
 * @return mixed
 */
function getValue(\ILIAS\Refinery\Transformation $trafo);
```

For a wrapper around `$_GET` this would then be used as such:

```php
$refine = new ILIAS\Refinery\Factory(/* ... */);

$id = $get_wrapper->get("id", $refine->to()->int());
list($current_page, $page_size) = $get_wrapper->get($refine->selection([
	"current_page" => $refine->in()->series(
		$refine->int()->hasMin(0)
		$refine->int()->hasMax(10)
	),
	"page_size" => $refine->in()->series(
		$refine->int()->hasMin(0)
	)
]));
```

This already closely resembles a [previous example from Leifos](https://github.com/leifos-gmbh/ILIAS/blob/cc2c2243d6a1a73aad032d94c7c3ae2d55fb23fc/src/Filter/README.md#input-filters).
By implementing `ArrayAccess` on the `$get_wrapper` appropriately, we could create
an even simpler interface on top of this base:

```php
$id = $get_wrapper["id"]->int();
```

The second use case from the example above, fetching multiple inputs at the same
time, could be simplified by internally calling `selection` and by interpreting
an array of transformations as series in `selection`:

```php
$r = new ILIAS\Refinery\Factory(/* ... */);

list($current_page, $page_size) = $get_wrapper->get([
	"current_page" => [
		$r->int()->hasMin(0),
		$r->int()->hasMax(10)
	]
	"page_size" => [
		$r->int()->hasMin(0)
	]
]);
```

We propose that this pattern is implemented as follows to make some of the use cases
outlined above and in the example from Leifos possible:

* The `HTTP`-Library should implement `query`, `post` and `cookie` as a wrapper
  around the appropriate functions of the PSR-7 `ServerRequestInterface`.
* The `ilDataSetImporter` should pass a callable `$retrieve` that implements the
  archetypical `getValue`-interface outlined above to `DataSet::importRecord`
  instead of directly passing the data from the XML.

The pattern could equally be applied to other inputs represented as arrays,
e.g. the session or settings. The pattern could be expanded to the database too,
e.g. to turn a row into an object. Instead of having an `ilDataSetImportParser`
that performs the parsing and then passes the data to the `importRecord`-function,
the pattern could be implemented over a DOM-parser of XML.

To enforce the usage of the two proposed changes, we recommend to introduce a
dicto rule that forbids the usage of `$_GET`, `$_POST` and `$_COOKIE` and the
usage of `ILIAS\HTTP\GlobalHttpState::request`, with appropriate exceptions. The
change in `ilDataSetImporter` will be breaking and thus does not need a dicto rule
but instead should be explained and advertised in the JF and on developers@.


### Improving Webservice-Security

Securing the security of input processing for the SOAP-webservices will be harder
then improving the handling of XML-imports or processing of GET. The general
problem here is, that there is no obvious boundary between the general SOAP-
implementation and the different services it uses. Instead, the SOAP-implementation
directly calls methods, sometimes from deep layers of the system.

A reason for this seems to be, that the actions or activities that can be performed
with a component often do not have a direct representation in the code but instead
are spread throughout the codebase, often in GUI-classes. The SOAP-implementation
thus needs to know how these activities are performed by calling layers other
than the components themselves, effectively duplicating or mimicing code from the
GUI-classes of the components. This is a problem in and of itself, but also
regarding validation of inputs, since validation logic needs to be duplicated in
the SOAP layer.

This might be a short-term solution and can be achieved using the `Refinery`
library and the pattern showcased above. The general problem will not go away
this way, since checks will need to be implemented or changed at least twice,
once in the GUI and once in the SOAP-implementation in the future. The missing
explicit representation of activities can also be understood as an instance of
primitive obsession, where the primitive here is unabstracted statement-code
in various classes.

The duplication of activity code was already discovered in other contexts: to
implement a REST-service, we would need to triple the activities and validation
logic, to implement the Workflow-Engine we need to quadruple the activities,
and so on. The idea to extract activities and represent them in their own right
is already pursued via the Workflow-Engine (WFE). In the context of input validation
this would mean, that the creation of activities would be the missing boundary to
perform validation on inputs in the SOAP-case and other cases as well. The project
thus needs to be recognized to have a wider context than just the WFE.

We therefore recommend to embrace and support the "Service Discovery/API Alignment"
-project from the WFE. The goal might be restated to "Represent Activities" to
clarify the core issue that needs to be solved. Having a "Service Discovery" and
an "Internal API Alignment" would then be consequences, as well as "Enhanced
Security of Webservices" (and possibly even more) would be.


### Improving XML-Processing

When evaluating SOAP and XML-imports in ILIAS regarding recommendations from
[OWASP](https://www.owasp.org) we found that the mechanisms currently used to
process XML in both cases do not perform validation of XML. This is a problem
that, according to OWASP, is a known source for security issues in both cases.
We thus recommend to reconsider the current implementation of SOAP and XML-import
regarding validation of XML.


### Improvements in the UI-Framework

The UI-Framework is the place for a variety of improvements that may not be huge
in impact on their own, but will make it easier for developers to write code that
respects security concerns when writing input.

First and foremost we recommend to internalize a larger part of the input processing
into the UI-framework, as this is currently done by forms. This may encompass more
than processing of POSTed input. The filters, currently in development show, that
a pattern similar to forms can be applied. This should also be true for view controls.
They may be wrapped into a similar container, where it even might be possible to
build a model that is more locked than the forms or filters are, since the type of
data that is passed here is narrower. The pattern could also apply to parts of the
UI that currently are not in the framework, like tabs and subtabs.

If the forms (and maybe other containers as well) would also get standard way to
be executed asynchronously, we expect that a lot of scenarios where developers
currently access `$_GET` or `$_POST` directly will go away.

We furthermore recommend to find a way to implement simple text formatting
(bold, italic, enumerations) into the inputs in the UI-Framework that does not
use HTML. This will allow to get rid of `ilUtil::stripSlashes`, as explained in
the according paragraph. We would suggest to use markdown instead. This would
also imply to implement data types for markdown formatted text to clearly seperate
it from an arbitrary string.


### Improvements for Data

The evaluation of various scenarios in which input is used suggests, that there
are some datatypes that are used throughout ILIAS but are only represented by
primitives so far. We suggest to add at least these datatypes to the [Data-library](../../src/Data):

* A type for **ids**, maybe additionally a type for reference ids and object ids.
* A type for **timestamps**. The standard PHP \DateTime might be enough already, but
we recommend to have a closer look at various use cases again. Since \DateTime
exists and there are also various libraries that simplify handling dates, we propose
to get rid of `ilDateTime`.
* Various types for strings. A `Password`-type is already implemented. We suggest
to add a type `Alphanumeric` that only allows [A-Za-z0-9] that can be used for ids
in various contextes safely. Additionally we might need types for strings in various
formatting, e.g. `HTML`,`Markdown`, `Plaintext`, depending on discussion of how we
want to use HTML for user input in the future. These would also allow the Mailsystem
to make choices regarding escaping when creating mails.
* Various types for integers. A `PositiveInteger` and a `Range` would e.g. be
usefull for pagination.

Tagging strings (and other data as well) via datatypes will be crucial to allow
proper decisions on how data needs to be treated when escaping it at some output
layer of the system. This problem already emerged in the Mail-Service, where the
service needs to work with raw `string`s without really knowing if they need to
be escaped for HTML or not. Having a HTML, e.g. would mean that the content could
just be outputted in a HTML context, while a Markdown would need to be prepared
and formatted by some markdown processing and a Plaintext would need to be escaped
according to HTML. In a plain text context, on the other hand, the HTML would most
probably need to be formatted, while Markdown and Plaintext could be printed as
is.

This directly spawns the question, if and how these types should perform validation.
I.e. does a HTML-Type need to check a provided string to be wellformed HTML? Would
we want a Plaintext-type to discard tags in a provided string? These questions are
hard to answer from the general perspective taken in this paper. The underlying
questions could well be addressed by introducing more finegrained types on top
of the proposed ones (e.g. `WellformedHTML` or `SecureHTML`) or by introducing
smarter factories to create these types, e.g. by wrapping `HTMLPurifier` into a
factory for HTML.

The full advantages of these types will only be gained once the UI-framework (and
other output layers) incorporate these types and the UI-framework is in fact used
throughout the system. There still will be advantages without that steps, as the
types will force developers to think about escaping even when they simply echo
something, because they cannot just concatenate a `HTML` into a string.

There might be even more types that could go into the common library. Finding these
would be a good occasion to integrate the larger developer community into the efforts.
We thus propose to inform the developers about the general plans regarding security
of input, the phenomenon of primitive obsession in particular and than ask them
about common types that should be included in the [Data-library](../../src/Data).

However, we do not propose to blindly wrap every primitive type into an object in
every situation. Since PHP > 7.0 offers type hints for primitive types, a general
`Int` wrapper for integers will add inconveniences but no additional information
or support for developers. Wrappers around single primitives only help if they
add additional information to the primitive. This might concern checks that have
been performed previously, like `URL` does, or contexts in which the primitive
is intended to be used, like `Password` does. We also expect to see compound data
types that bundle multiple primitives to a single value, like e.g. Geo-Location
bundles two floats (for latitude and longitude) into one. These seem to be a more
typical case for data types than the wrappers around single primitives.


### Fighting Primitive Obsession

As shown above, fighting the phenomenon of primitive obsession is crucial to improve
security when processing input. On the one hand, having objects encapsulating data
and protecting their integrity and constraints is a crucial part of an object
oriented design. On the other hand, using `interface`s and `class`es together with
type hints allows PHP to actively help developers to stick to interfaces of other
developers.

From a design perspective, an object does not necessarily need to represent a part
of the problem. Objects like `ilObjCourse` or `ilObjUser` may be seen as such objects.
They resemble entities found in the problem domain that ILIAS wants to take care
of, i.e. people and the events they attend to in a educative setting. Objects may
also represent parts of the solution domain (like e.g. `ilObjectDataCache` does)
or data that is processed during some sequence of operation. In last part ILIAS
falls short at the moment, huge shares of data in the "domain-objects" and the
"service-objects" is processed in a primitive form.

From a hands-on perspective, we need to recognize that we just are not using PHPs
possibilities to their full potential when passing `array`s, `int`s and `string`s
instead of `ilObjectData`s, `ilObjectID`s or `PlainText`s between components of
the system. This effectively means that we are wasting developers time with the
requirement to read, write and update text-based documentation (in the best case)
instead of allowing tools (IDE, Static Analysis Tools, PHP itself) to understand
how data that is passed to a foreign component.

The scope of this problem goes well beyond securing system inputs. Passing simple
`string`s through the system causes problems when outputting the data to other
systems (e.g. browser, mailing, database) as well, since it might be impossible
to determine the correct encoding due to insufficient information about the content
of the string.

The awareness for these problems and the benefits of alternatives won't be something
that may be created in a short term, as well as the necessary changes in the code
itself won't be implemented soon. To keep on working on this topic, we propose two 
measures:

* This document should be gradually turned into the goto ressource regarding input
security. In the long run it should start with a ~10 item list of do's and don'ts
regarding input security that links to in-depth ressources regarding the different
topics (maybe based on the [Core Considerations](#core-consideration) and libraries
and services that are used to implement various aspects of input security. The
libraries and services themselves should be held to a high standard of documentation
as well.
* General architectural and design questions and issues need to get a regular space
among developers in the community. The recently held VC about "Repository Pattern"
received positive feedback and the general requests for similar spaces has been
made at several occasions. We thus recommend to establish a regular date and format
to explain and discuss architectural patterns and questions in- and outside ILIAS.


### Policy Enforcement by Various Subsystems

As discussed in [Structure vs. Policy](#structure-vs-policy), a streamlined
treatment of policies seems to be impossible, since the nature of policies and
thus the times and places of enforcement differs widely among different system.

It should, however, be possible to identify systems that have policies that may
be enforced during input already. A good example would be `Service\Password` that
should know about configurable constraints on Password. To include these services
into the proposed `Refinery` and `Data`-libraries there seem two viable options:

* The types and constraints required by the service are implemented directly in
the libraries. Currently, the `Password`-type is exactly this.
* The service uses interfaces and procedures from the library, but implements
factories and types itself.

It is indeed possible to implement a solution between these two options, like
using types from Data, but implement constraints in the service. The decision,
which of these option is best for a given services may be based on these (and
possibly other) criteria:

* How widely are types and constraints of the service used?
* Does every user of the types "know" the service directly?
* Which resource are required to check policies? Do we e.g. need a database
connection?

The identification and discussion of the questions, which and how services in ILIAS
need to be regarded when processing user input, will be a good point to include
ILIAS developers in the discussion and concrete implementation and design of the
principals and mechanism proposed in this paper. This could also be widened to
a discussion on which system in ILIAS enforce security constraints, which will
allow to bring larger security questions into focus. Including developers will also
help to find promising targets for an implementation and finally help adoption.


## Conclusion

The current state of the art regarding validation of user input is twofold: on
the one hand there is a lot of code and components that only use weak or no measures
to make sure that data from users complies with structural and policy constraints.
On the other hand there is groundwork to tackle this problems in a principled
manner. This will only work if the general level of awareness regarding security
when processing input is raised, which will be a long term goal that will require
education of and discussion among developers. This is similar to other issues
regarding security or even more general software design issues. The technical
groundwork needs some improvement and finally will only become effective when
adopted in the code that uses input from user. This will require a long breath
as well, but a good API design and visible benefits of the new mechanism will
carry this process. During this implementation it is absolutely crucial to tackle
the primitive obsession phenomenon in the ILIAS codebase since this allows to
communicate requirements on data in a way that can be automatically verified and
analysed. Finally, the question of input security is connected with other issues
in the ILIAS codebase and processes, e.g. the escaping at output or education of
developers in the community, and needs to be understood as a part in larger efforts
to improve ILIAS and its community.
