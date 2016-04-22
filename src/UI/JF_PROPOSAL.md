# Jour Fixe Proposal to introduce a centralizes UI-Framework for ILIAS.


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
  for showcasing the instance.
* The new method MUST be backed with a stub implementation down to the methods
  that represent concrete UI components, where said methods MUST raise
  ILIAS\UI\NotImplementedException upon call, if the UI component is not already
  implemented.
* The proposed UI component MAY already be implemented. If the UI component is
  implemented, it SHOULD obay the rules given in **Implementations of Factories**.
* In addition to the YAML-Block described in **Interfaces to Factories** the
  proposed interfaces, if not already implemented, SHOULD contain the following
  fields:
    * `html` - gives an example of HTML-code that will be rendered by the
      UI component
    * `less` - lists the LESS-variables that will be used to render the
      UI component including their purpose
* The new UI component MUST be presented on the JF, including the corresponding
  pull request. This SHOULD include some visible representation of the presented
  UI component, like a mock up or a basic implementation on the edge installation.

### Modification of existing UI components

* Any changes on interfaces of factories or UI components MUST be agreed upon by
  the your fixe. The interfaces are the public surface of the UI framework that
  consumers rely on, so changes should not be made ad hoc. Moreover it is very
  likely that a change in an interface corresponds to some observable change in
  the corresponding UI component which is reflected in the Kitchen Sink. This
  also includes non editorial changes in the doc blocks of interfaces, excluding
  the YAML fields `description`, `background` and `context`.
* To propose a change in of a factory or UI component interface, a pull request
  with the desired change MUST be made on github. The code in the pull reques
  SHOULD obay the rules given in **Interfaces to Factories** and **Interfaces to
  UI components**. The existing unit tests for the UI framework SHOULD pass.
* The changes in the interface SHOULD not break existing usages of the interface.
* The changes in the interface SHOULD be backed with an implementation.
* The pull request MAY be made from the edge branch, the edge installation could
  then be used to showcase the observable part of the change.

## Rules for Consumers

* Consumers of the UI framework MUST only use the UI-Factory provided via the
  dependency injection container `$DIC->UIFactory()` as entry point top the
  framework. The factory implements the interface \ILIAS\UI\Factory.

## Rules for Implementors

* Classes and interfaces for the UI framework MUST be located in the namespace
  \ILIAS\UI or a subnamespace thereof, where the root directory for that 
  namespace is src/UI and the location of PHP-files is determined according to
  [PSR-4](http://www.php-fig.org/psr/psr-4/).

### Interfaces to Factories

The interface to the main factory is \ILIAS\UI\Factory.

* All factory interfaces aside from the main factory MUST be located in the 
  namespace \ILIAS\UI\Factory.
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
    * SHOULD contain a field `description` that is a dictionary containing one or
      more than one of the following text fields:
        * `purpose` - describes the usage scenario of the component
        * `composition` - describes how the component is composed with other
          components
        * `effect` - describes visual effects that relate to the item
        * `rival` - describes other components fulfilling a similar function
    * MAY contain a text field `background` that gives academic information
    * SHOULD contain a text field `context` that describes occurences and
      prevalences of the control if the method describes a concrete UI component.
      If the method represents an abstract node in the in the Kitchen Sink
      taxonomy it MUST NOT contain a `context` field.
    * MAY contain a text field `featurewiki` that contains links to relevant
      articles in the feature wiki.
    * MUST contain a field 'javascript' if the method represents an UI component
      and the implementation of the component uses a javascript library other
      than jquery and bootstrap or if the component is not yet implemented but a
      javascript lib other than jquery and bootstrap is planned to be used, where
      the field contains the names and versions of all said javascript libraries.
    * SHOULD contain a field `rules` that contains one or more than one of the 
      following fields `usage`, `interaction`, `wording`, `style`, `ordering`, 
      `responsiveness`, `accessibility` and `accessibility, where
        * each of the contained fields is a dictionary from a number to a text
        * each of the contained fields MUST contain at least one rule for the
          proposed Kitchen Sink entry
        * every rule MUST have a successive number
        * the number of a rule MUST NOT be changed
        * a rule MAY keep its number upon minor changes in the rule
        * rules MUST be formulated according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt)
      This makes sure that each rule can be referenced by a unique name.
* The parameters of every method SHOULD be type hinted.
* There MAY be more than one method in a factory declaring to return an instance
  of the same interface.
* There MUST be at most one factory per interface declaring to return instances of
  that interface.

### Interfaces to UI components

The word *path* in this chapter means the chain of successive calls to methods
of factories leading to the creation of a UI component.

* Every interface describing an UI component MUST extend the interface
  \ILIAS\UI\Component.
* Every interface describing an UI element MUST implement the interface
  \ILIAS\UI\Element.
* Every interface describing an UI collection MUST implement the interface
  \ILIAS\UI\Collection.
* Every interface describing a UI component MUST be located in the namespace
  \ILIAS\UI\Component or a subnamespace thereof.
* Per interface to a UI component, there MUST be exactly one factory interface
  declaring to return instances of the interface type.

### Implementations of Factories

### Implementations of UI components

### Tests for factories

### Tests for UI

