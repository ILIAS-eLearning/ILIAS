# Jour Fixe Proposal to introduce a centralizes UI-Framework for ILIAS.

We suggest the following rules for the ILIAS UI framework. The UI framework
currently is in a construction phase. The current form of the rules therefore
represents the best effort for the current state of the framework. It therefore
is likely that there will be additions to or refinements of the rules in the
(near) future. Everyone using the rules is invited to critically reflect the
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
  github. The code in the pull request SHOULD obay the rules given in **Interfaces
  to Factories** and **Interfaces to UI components**. The existing unit tests for
  the UI framework SHOULD pass.
* The pull request MAY be made from the edge branch in the ILIAS-repo. If the new
  component is already implemented, the edge installation of ILIAS MAY be used
  for showcasing the component.
* The new method MUST be backed with a stub implementation down to the methods
  that represent concrete UI components, where said methods MUST raise
  ILIAS\UI\NotImplementedException upon call, if the UI component is not already
  implemented.
* The proposed UI component MAY already be implemented. If the UI component is
  implemented, it SHOULD obay the rules given in **Rules for Implementors**.
* In addition to the YAML-Block described in **Interfaces to Factories** the
  proposed interfaces, if not already implemented, SHOULD contain the following
  fields:
    * `less` - lists the LESS-variables that will be used to render the
      UI component including their purpose
* If the new UI component is not implemented, there SHOULD be an html-example in
  the examples-folder.
* The new UI component MUST be presented on the JF, including the corresponding
  pull request. This SHOULD include some visible representation of the presented
  UI component, like a mock up or a basic implementation on the edge installation.
  To make it easy for non-developers to follow the discussion, a link to the
  changed/added factory classes and mock MUST be provided in the description provided
  for the PR.

### Modification of existing UI components

* Any changes on interfaces of factories or UI components MUST be agreed upon by
  the Jour Fixe. The interfaces are the public surface of the UI framework that
  consumers rely on, so changes should not be made ad hoc. Moreover it is very
  likely that a change in an interface corresponds to some observable change in
  the corresponding UI component which is reflected in the Kitchen Sink. This
  also includes non editorial changes in the doc blocks of interfaces, excluding
  the YAML fields `description`, `background` and `context`.
* To propose a change of a factory or UI component interface, a pull request
  with the desired change MUST be made on github. The code in the pull request
  SHOULD obay the rules given in **Interfaces to Factories** and **Interfaces to
  UI components**. The existing unit tests for the UI framework SHOULD pass.
* The changes in the interface SHOULD not break existing usages of the interface.
* The changes in the interface SHOULD be backed with an implementation.
* The pull request MAY be made from the edge branch, the edge installation could
  then be used to showcase the observable part of the change.

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
* All methods of every factory interface MUST have a name in camel case starting
  with a lower case letter.
* All methods of every factory interface MUST only return other factories or UI-
  components. If returning another factory, the method is considered to represent
  an abstract node in the taxonomy of the Kitchen Sink. If returning an UI-component,
  the method is considered to represent a concrete UI-component in the Kitchen
  Sink. The decamelcased name of the method is considered to be the name of the
  abstract node or concrete UI component in the Kitchen Sink.
* If a method of a factory returns another factory, it MUST NOT have parameters.
* If a method of a factory returns an UI-component it MAY have parameters.
* All methods of every factory MUST be documented in a PHP DocBlock.
* The documentation of all methods of every factory:
    * MUST include a first part in YAML notation containing information for the
      Kitchen Sink.
    * MUST include a documentation of the parameters and return values according
      to PHPDoc-format.
    * MUST separate the block containing Kitchen Sink information from the block
      containing documentation of parameters by an empty line in the DocBlock.
* The block in the documentation containing Kitchen Sink information:
    * MUST be enclosed by comment lines containing only the delimiter `---`.
    * SHOULD contain a field `description` that is a dictionary containing one or
      more than one of the following text fields:
        * `purpose` - describes the usage scenario of the component
        * `composition` - describes how the component is composed from and with
           other components
        * `effect` - describes visual effects that relate to the item
        * `rival` - describes other components fulfilling a similar function
    * MAY contain a text field `background` that gives academic information
    * SHOULD contain a text field `context` that describes occurences and
      prevalences of the control if the method describes a concrete UI component.
      If the method represents an abstract node in the in the Kitchen Sink
      taxonomy it MUST NOT contain a `context` field.
    * MAY contain a text field `featurewiki` that contains links to relevant
      articles in the feature wiki.
    * MUST contain a field `javascript` if the method represents an UI component
      and the implementation of the component uses a javascript library other
      than jquery and bootstrap or if the component is not yet implemented but a
      javascript lib other than jquery and bootstrap is planned to be used, where
      the field contains the names and versions of all said javascript libraries.
    * SHOULD contain a field `rules` that contains one or more than one of the 
      following fields `usage`, `interaction`, `wording`, `style`, `ordering`, 
      `responsiveness`, `composition` and `accessibility`, where
        * each of the contained fields is a dictionary from a number to a text
        * each of the contained fields MUST contain at least one rule for the
          proposed Kitchen Sink entry
        * every rule in one field MUST have a successive number
        * the number of a rule MUST NOT be changed
        * a rule MAY keep its number upon minor changes in the rule
        * rules MUST be formulated according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt)
      This makes sure that each rule can be referenced by a unique name.
* The parameters of every method SHOULD be type hinted.
* There MAY be more than one method in a factory declaring to return an instance
  of the same interface.
* There MUST be at most one factory per interface declaring to return instances of
  that interface.
* The proposed interfaces SHOULD pass a phpunit test extending tests/UI/AbstractFactoryTest.
  The final test
  * must have a public static $factoryTitle defined, containing the fully qualified
    factory interface name
  * may contain an associative array, public $kitchensink_info_settings, mapping method
    names to an array of kitchensink info yaml fields mapping to bools, meaning that
    the test will check the existence of these fields. Example:
    public $kitchensink_info_settings =
        array( "method1" => array ("rules" => false, "javascript" => true));
    If no settings are defined for a method, defaults deriving from above rules for
    Kitchen Sink information will be used, where 'SHOULD' is interpreted as true and
    'MAY' is interpreted as false.
    Obligatory fields (MUST/MUST NOT) are always checked and MUST NOT be overwritten.


### Interfaces to UI components

The term *Path* means the chain of successive calls to methods of factories leading to
the creation of a UI component and starting at the main factory.

* Every interface describing an UI component MUST extend the interface
  \ILIAS\UI\Component\Component.
* Every component MUST be described by a single interface, where the name of
  the interface corresponds to the name of the component, unless they only differ
  in a type and share a common prefix to their pathes and all components below
  that path prefix only differ in a type. Those components SHOULD be described
  by a common interface with a getType-method, where the interface name corresponds
  to the last element in the common prefix of the path. I.e. the interface for
  the component `$main_factory->a()->b()->c()` must be called C. If
  `$main_factory->a()->b()->c()` and `$main_factory->a()->b()->d()` only differ
  in the type, they should be described by an interface B.
* Every interface describing a UI component MUST be located in the a subnamespace
  of \ILIAS\UI\Component, where the exact subnamespace corresponds to the path
  from the main factory to the component or the common prefix of the path to the
  components it implements. I.e. a component instantiated via
  `$main_factory->a()->b()->c()` must be located in the namespace `ILIAS\UI\Component\A\B`.
  The interface for `$main_factory->a()->b()->c()` and `$main_factory->a()->b()->d()`
  must be located in the namespace ILIAS\UI\Component\A\B.
* Per interface to a UI component, there MUST be exactly one factory interface
  declaring to return instances of the interface type.
* If an interface declares a getType method, it MUST also declare the valid types
  as constants in the interface. These types MUST only be used via the names of
  the constants, one MUST NOT assume anything about their values. I.e. it must not
  make a difference if someone decides to e.g. replace the definition of the
  constant by a new value.
* Interfaces to components MUST be defined as immutable objects, i.e. they should
  not provide methods to actually change the object they describe. Instead they
  MAY provide methods called `withXYZ` instead of setters, that return a copy of
  the object where the desired modification is applied.

### Implementations of Factories

* Every implementation of a factory MUST be located in a subnamespace of
  `ILIAS\UI\Implementation\Component`, where the exact subnamespace corresponds
  to the name of the abstract component the factory provides. I.e., the
  implementation for the factory interface `ILIAS\UI\Component\A\B\C\Factory`
  must be located in `ILIAS\UI\Implementation\Component\C`.
* Every factory implementation MUST be named Factory.
* Every implementation of a factory MUST adhere to the interface it implements,
  which means the method signatures as well as the docstring, as long as the rules
  described in *Introduction of new UI components* do not state it differently.

### Implementations of UI components

* The implementing class MUST be named after the interface it implements.
  I.e. the implementation of `ILIAS\UI\Components\A\B\C` must be called C.
* Every implementation of a component MUST be located in a subnamespace of
  `ILIAS\UI\Implementation\Component`, where the exact subnamespace corresponds
  to the name of the implemented interface. I.e., the implementation for the
  interface `ILIAS\UI\Component\A\B\C` must be located in B.
* Implementations of components MUST adhere to the interface they implement,
  which means the method signatures as well as the docstrings. Implementations
  SHOULD also maintain the invariants and constraints stated in the rules of
  the component, where they MUST use a typehint to enforce the constraint or
  invariant or throw an `\InvalidArgumentExceptions`. Implementations of
  components MAY use the trait \ILIAS\UI\Implementation\Component\Helper to
  ease the checking of said invariants and constraints.
* Implementations of components MUST only act as data objects, i.e. maintain
  their content and provide it to consumers. They MUST NOT switch behaviour
  based on any properties, e.g. return different values from a getter based
  on their type.

### Implementations of Renderers for UI components.

* There MUST exists a renderer for every implementation of an UI component. The renderer
  MUST render all components whose implementaions are in the same namespace. It MUST NOT
  render other components.
* Every renderer MUST extend the class `ILIAS\UI\Implementation\Renderer\AbstractComponentRenderer`.
* The renderer MUST be located in the same namespace as the UI component
  implementation and it MUST be named Renderer.
* Renderers SHOULD not use properties as names for CSS classes.
* Renderers MUST use the subset of the ILIAS templating engine, provided via
  `AbstractComponentRenderer::getTemplate`, to render their component.

## Locations of resources

The term 'resources' means templates, less, css or javascript code that is required
to render a certain component.

* Every component interface SHOULD correspond to one template.
* The resources required to render a component SHOULD be located in the folder
  templates/$COMPONENT, where $COMPONENT is the name of the component.
* If a renderer needs a certain resource other then a template, it SHOULD register
  said resource via the renderers registerResource-method.
* Renderers for components SHOULD only use resources of their own component.
* If a component has a less-resource, that resource MUST be wired by hand to the
  delos.less-file.

There most propably will be changes in the handling of resources in the future, as
the seems to be the need to introduce some common patterns for handling javascript
or compiling css from different less files.

## Examples

* There SHOULD be examples for every implemented component that showcase
  the usage of the component from developers perspective.
* If there are examples, they MUST be put in a subfolder of examples
  that is named like the showcases component interface.
* Every example MUST be a php-file with one function inside. The function
  must have the name $COMPONENT_$EXAMPLE, where $COMPONENT is the name of
  the showcased component interface, and $EXAMPLE is the prefix of the name
  of the php file.
* The function MUST return a string.

