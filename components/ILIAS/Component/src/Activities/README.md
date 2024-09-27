# Activities

## Abstract

The aim of the facilities in this folder is to allow to build a discernible layer
where developers can find "Activities" with objects and services in the system.

**An Activity is a way in which a user would want to use our system or the a
subsystem thereof.** Hence, Activities target the domain layer of our system
and the various subsystems and make the things that user do there referenceable.
Examples for activities would be things like: "Create a Course", "Add Members to
a Group", "Make a Test available.".

These examples, superficially, might look as if they could be mapped onto according
calls on existing objects, such as `ilObjCourse::create`, `ilParticipants::addMember`
or `ilObjTest::setOnline`. But in reality this is not the case. When users say they
want to create a course, they do no only mean to programmatically "Create a ilObjCourse"
PHP-object. The object needs to be put in the Repository somewhere. Permissions need
to be checked. Same for "Add Members to a Group". This is what the "Domain" of our
software is, in difference to the programming that we use to implement that domain.

By explicitly introducing Activities via a common framework, the system and its
components gain a lot of benefits:

* Activities offer a way to present a domain level API to other developers, webservices
  and end users in a machine-readable way.
* Activities offer a documentation in the ILIAS GUI for end users to take advantage
  of the available services e.g. in webservice integrations or workflow definitions.
* Activities to maintain such APIs in the components where they arise but still
  present them to the complete system in a consumer agnostic way.

## Design

From the perspective of a component, every [**Activity**](./Activity.php) is provided
as the general way to use the component, together with according information and
facilities to make actual use of the activity. Activities have a name that is the
class name of the activity. They have a description in markdown format that shall
give additional information about the Activity that can not be derived from its name.
Activities describe their input as a FormInput from the UI framework, which is a
common and widely useful way to talk about user inputs to the system. The central
functions of any `Activity` are one function to check the permission for a given
user to perform a certain activity and the method to actually perform it.

Activities are contributed to the system via the [according mechanisms of the
component framework](docs/development/components-and-directories.md#contribute-to-service-or-functionality).
Activities can use then use the mechanisms from the component framework to use
facilities from other components. An Activity can and should also use activities
from other components, if it requires cooperation from other domains. Depending
on the case at hand, the implementor should then decide if a permission check for
the Activity at hand should or should not delegate to checks to sub permissions.
Any dependency of an Activity should be introduced via dependency injection.

Activities come in two flavors:

* A `Command` changes the state of the system, but does not return data from the
  system. It can and should of course return information e.g. regarding the success
  of the state change. And it can and should of course use data from the system
  to perform the command.
* A `Query` returns data from the system, but does not induce state changes. A state
  change is understood as a change in the business data of the system, even queries
  could e.g. cause entries in logs.

This differentiation is important to allow the system to make various decisions,
such as:

* If the Activity is performed via an HTTP endpoint: Would it be GET or POST?
* Can I reorder this sequence of Activities without changing meaning?
* Can I cache the results of that Activity? Do I expect the same results upon
  performing the same Activity twice?

That means that implementors of Activities should carefully decide which type an
Activity actually has to prevent side effects.

The flavors are represented as a property on activities to allow the framework to
combine activities. Some `Query` that returns a list of users and some `Command`
that acts on one user could be combined to a `Command` that act on multiple users.
Facilities to allow for combinations won't be implemented in the first iterations
on the framework, though.

## Usage

### As User of a Specific Activity

For many use cases you will want to perform a specific activity. A table of members,
for example, will need a specific `Query` to get the data for this exact table. If
we want to do something with a row in that table, we also should know which exact
`Command` it is that should be performed.

For this cases, you most likely should [pull](docs/development/components-and-directories.md#pull-code)
the required Activity as a dependency into the location where you need it, e.g.
a GUI class. You will need this exact `Activity` after all, not any similar `Activity`
or a some stand in.

If the `Activity` at hand is an activity of your component, or some very closely
related component, you might already have the correct `$parameters` at hand anyway
and you could use `isAllowedToPerform` and `perform` in appropriate locations. An
activity "Remove Member" for example, belongs to the component that shows the table
of members.

If the components are only losely related, you might be better of using `maybePerformAs`
with the parameters provided as primitives in an array. This method will internally
mangle the parameters accordingly via the `InputDescription`. This should be more
robust with regards to changes in the other component, such as changes in internal
data representation or inputs that are accepted. "Send Mail to Member" for example,
could be considered an activity from a losely related component for our member table.

### As User of Generic Activities

For some use cases, you won't be interested in any specific `Activity` but instead
you will want to have access to all activities provided by the system. Just similar
to the Kitchen Sink, we may want to build an automated documentation about all
available activities some day.

For this case, you most likely will want to use the [`Repository`](components/ILIAS/Component/src/Activities/Repository.php)
of activities. The Repository will allow you to select some or all Activities from
the system.

To deal with a single but generic `Activity` from the `Repository`, keep in mind
that the input to the `Activity` as described by `getInputDescription` is a quite
generic tool, although being defined in terms of the UI framework:

* the `FormInput` can be used verbatim to collect input from users via the GUI
* `FormInput` supports input in JSON format, so it should be simple to use it when
  serialization comes into play
* `FormInputs` provide information about the single fields that can be used as
  a description of the expected inputs
* `FormInput` can be used to get appropriatly represented data to be used as
  `$parameters` for `isAllowedToPerform` and `perform` without needing to know
  the actual content or representation of said data

By using the `Repository` and the information and methods from a single `Activity`,
you should be able to get a firm grip on each and every `Activity` in the system
without the requirement to actually know any `Activity` individually.

### As Implementor

Please take the documentation in `Activity` seriously. The methods in every `Activity`
have a specific relation to each other that need to be maintained to allow the
coherent usage of activities, be it specific activities or generic activities as
described above. This e.g. means:

* If an input is accepted by `getInputDescription` it shall not make `isAllowedToPerform` or `perform` crash.
* `maybePerformAs` shall use `getInputDescription`, `isAllowedToPerform` and `perform`
 in a very specific pattern.
* If `getType` is `Query`, perform shouldn't cause side effects.

To simplify the implementation, the documentation for the class lists some base
classes that could be used for the implementation.

If you have implemented activities for your component you should integrate them
with the system according to the [existing integration mechanisms](docs/development/components-and-directories.md):

* You should [provide them with as specific name](docs/development/components-and-directories.md#pull-code), so other components can pull them.
* You should [contribute them to the system](docs/development/components-and-directories.md#contribute-to-service-or-functionality) so generic facilities such as the `Repository` can discover them as well.

## Introduction to the System

Currently (in late 2024), the Activities only exist as an idea in this readme and
some code to support the usage of them. But this is very thin, as there are no
actual Activities on the one hand but also no users of the Activities on the other
hand. We do not aim to implement all possible Activities at once, neither we expect
that all possible Activities will be implemented ever.

The next phase, after this general idea and the supporting code is approved by the
community, we aim to:

* implement a webservice interface over the activities
* pick one specific component (currently we think Study Programme) and implement
  some useful Activities for the Study Programme

This should showcase how the Activities are useful, but also should provide actual
value and lay the groundwork to quickly provide more value by adding additional
activities.

We also will be looking to replace the current SOAP implementation with an Activity
based implementation and provide the according actions as Activities.

In this process we will also introduce the Activities that we implement into the
according GUIs to demonstrate how Activities also will provide value from a code
maintenance perspective.

Once this phase is finished, we will leave it to the community to create further
momentum. If we can demonstrate the value of the Activities and the connected implementations,
it will be as easy as never before to expose functionality via webservice. We expect
to create a win/win situation, where funding will be available for new Activities and
developers will be eager to move code from GUIs to Activities as well.

We also expect interesting new uses of Activities to arise that could be implemented
as proof-of-concept components without the ILIAS community as a component provided
by a vendor, or be included in existing ILIAS infrastructure.
