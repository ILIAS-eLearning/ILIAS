# Guideline for Collaboration in the UI-Framework

The UI framework currently is in a construction phase. The current form of the rules therefore
represents the best effort for the current state of the framework. It therefore
is likely that there will be additions to or refinements of the rules in the
future. Everyone using the rules is invited to critically reflect the
rules and propose changes.

## Basics

* Rules in this proposal are formulated according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt).
* Goals of this proposal and the rules herein are as such:
    * Spawn a reliable and controlled process to introduce new components in the
      UI of ILIAS.
    * The process should result in guidelines as little as possible and result in
      machine verifiable rules as much as possible.
    * The creation of a centralized UI framework for ILIAS where everyone can
      contribute and that offers a coherent surface for its users.

## Processes

### Introduction of new UI components

* New methods on factory interfaces of the framework MUST be agreed upon by the
  Jour Fixe, as new methods on factories represent new abstract nodes in the Kitchen
  Sink taxonomy or new concrete UI components.
* To propose a new UI component a pull request against the trunk MUST be made on
  GitHub. The code in the pull request SHOULD obay the rules given in **Interfaces
  to Factories** and **Interfaces to UI components**. The existing unit tests for
  the UI framework MUST pass.
* You SHOULD only propose one component per PR. If it simplifies the discussion
  and/or makes the PR a lot easier to read and understand, you MAY bundle multiple
  new components in one PR. Note that this implies that they can only be accepted
  if every single one passes the requirements. This might be a potential risk.
* The new method MUST be backed with a stub implementation down to the methods
  that represent concrete UI components, where said methods MUST raise
  `ILIAS\UI\NotImplementedException` upon call, if the UI component is not already
  implemented.
* The proposed UI component MAY already be implemented. If the UI component is
  implemented, it SHOULD obay the rules given in **Rules for Implementors**.
* The new UI component MUST be presented on the JF, including the corresponding
  pull request. This SHOULD include some visible representation of the presented
  UI component, like a mock up or a basic implementation. To make it easy for
  non-developers to follow the discussion, the description and mock MUST be made
  available in the description of the PR, e.g. by using a link to the changed or
  added factory classes and mock MUST be provided in the description provided for
  the PR. 
* Examples MUST be provided, showcasing the key features of the new component.
* There MUST be Test Cases in [Testrail section UI Components](https://testrail.ilias.de/index.php?/suites/view/390) 
  so that a tester with no technical expertise can confirm that all examples work as intended. 
  They must be available and linked to the PR.
  
  
### Modification of existing UI components

* Any changes on interfaces of factories or UI components MUST be agreed upon by
  the Jour Fixe. The interfaces are the public surface of the UI framework that
  consumers rely on, so changes should not be made ad hoc. Moreover it is very
  likely that a change in an interface corresponds to some observable change in
  the corresponding UI component which is reflected in the Kitchen Sink. This
  also includes non editorial changes in the doc blocks of interfaces, excluding
  the YAML fields `description`, `background` and `context`.
* To propose a change of a factory or UI component interface, a pull request
  with the desired change MUST be made on GitHub. The code in the pull request
  MUST obay the rules given in **Interfaces to Factories** and **Interfaces to
  UI components**. The existing unit tests for the UI framework MUST pass.
* The changes in the interface SHOULD not break existing usages of the interface.
* The changes in the interface SHOULD be backed with an implementation.
* The coordinators MAY allow exceptions to these rules for changes that do not
  break existing code to not slow down development efforts unneccessarily.
  These changes MUST be agreed upon by the Jour Fixe afterwards.

## Rules for Consumers

* Consumers of the UI framework MUST only use the UI-Factory provided via the
  dependency injection container `$DIC->ui()->factory()` as entry point top the
  framework and the renderer provided via `$DIC->ui()->renderer()`. The factory
  implements the interface \ILIAS\UI\Factory, the renderer implements
  ILIAS\UI\Renderer.

## Rules for Implementors

* Classes and interfaces for the UI framework MUST be located in the namespace
  \ILIAS\UI or a subnamespace thereof, where the root directory for that 
  namespace is src/UI and the location of PHP-files is determined according to
  [PSR-4](http://www.php-fig.org/psr/psr-4/).
* Methods in the UI framework SHOULD not use arrays as parameters, unless one of
  the following conditions is met:
	- the array is used as a plain list of values or objects, where the index
	  is just 0 to n and the array is ordered accordingly
	- the array is used as a key-value dictionary and the methods does not expect
      any special keys or access the dictionary with special keys

### Interfaces to Factories

The interface to the main factory is \ILIAS\UI\Factory.

* All factory interfaces aside from the main factory MUST be located in a subnamespace
  of `ILIAS\UI\Component`, where the exact subnamespace corresponds to the path from
  the main factory to the factory in question which also corresponds to the Kitchen
  Sink taxonomy. I.e. a factory reachable via `$main_factory->a()->b()` must be located
  in `ILIAS\UI\Component\A\B`.
* Every factory interface MUST have the name Factory.
* Every factory interfaces aside from the main factory MUST be instantiable via
  the main factory interface or successive calls to factories returned by the
  main factory interface.
* All methods of every factory interface MUST only return other factories or UI-
  components. If returning another factory, the method is considered to represent
  an abstract node in the taxonomy of the Kitchen Sink. If returning an UI-component,
  the method is considered to represent a concrete UI-component in the Kitchen
  Sink. The decamelcased name of the method is considered to be the name of the
  abstract node or concrete UI component in the Kitchen Sink.
* If a method of a factory returns another factory, it MUST NOT have parameters.
* If a method of a factory returns an UI-component it MAY have parameters. The
  factory SHOULD have the minimum amount of parameters to instantiate the component.
  This also means, that parameters SHOULD have no defaults.
* All methods of every factory MUST be documented in a PHP DocBlock.
* The documentation of all methods of every factory MUST include a first part in
  YAML notation containing information for the Kitchen Sink. This block:
    * SHOULD contain a field `description` that is a dictionary containing one or
      more than one of the following text fields:
        * `purpose` - describes the usage scenario of the component
        * `composition` - describes how the component is composed from and with
           other components
        * `effect` - describes visual effects that relate to the item
        * `rival` - describes other components fulfilling a similar function
    * MAY contain a text field `background` that gives additional information.
    * SHOULD contain a text field `context` that points to at least one occurence
      of the control if the method describes a concrete UI component.
    * SHOULD contain a field `rules` that contains one or more than one of the 
      following fields `usage`, `interaction`, `wording`, `style`, `ordering`, 
      `responsiveness`, `composition` and `accessibility`.
* The parameters of every method MUST be type hinted if expressible in PHP.
* The proposed interfaces MUST pass a phpunit test extending `tests/UI/AbstractFactoryTest`.

### Interfaces to UI components

The term *Path* means the chain of successive calls to methods of factories leading to
the creation of a UI component and starting at the main factory.

* Every interface describing an UI component MUST extend the interface
  \ILIAS\UI\Component\Component.
* Interfaces to components MUST be defined as immutable objects, i.e. they should
  not provide methods to actually change the object they describe. Instead they
  MAY provide methods called `withXYZ` instead of setters, that return a copy of
  the object where the desired modification is applied.

### Implementations of Renderers for UI components.

* There MUST exists a renderer for every implementation of an UI component. The
  renderer MUST render all components whose implementations are in the same
  namespace. It MUST NOT render other components.
* Renderers SHOULD not use properties as names for CSS classes.

## Locations of resources

The term 'resources' means templates, less, css or javascript code that is required
to render a certain component.

* The resources required to render a component SHOULD be located in the folder
  src/UI/templates/$COMPONENT, where $COMPONENT is the name of the component.
* Renderers for components SHOULD only use resources of their own component.

There most propably will be changes in the handling of resources in the future, as
the seems to be the need to introduce some common patterns for handling javascript
or compiling css from different less files.