# Jour Fixe Proposal to introduce an UI-Framework

Rules in this proposal are formulated according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt).

## Basic

## Rules for Consumers

* Consumers of the UI framework MUST only use the UI-Factory provided via the
  dependency injection container `$DIC->UIFactory()`. The factory implements the
  interface \ILIAS\UI\Factory.

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
    * SHOULD contain a text field `example` containing a PHP example that show
      cases the usage, if the documented method represents a UI-component
    * MUST contain a field `description` that is a dictionary containing one or
      more than one of the following text fields:
        * `purpose` - describes the usage scenario of the component
        * `composition` - describes how the component is composed with other
          components
        * `effect` - describes visual effects that relate to the item
        * `rival` - describes other components fulfilling a similar function
    * MAY contain a text field `background` that gives academic information
    * MUST contain a text field `context` that describes occurences and
      prevalence of the control if the method describes a concrete UI component.
      If the method represents an abstract node in the in the Kitchen Sink
      taxonomy it MUST NOT contain a `context` field.
    * MAY contain a text field `featurewiki` that contains links to relevant
      articles in the feature wiki.
    * MAY contain a field `rules` that contains one or more than one of the 
      following fields `usage`, `interaction`, `wording`, `style`, `ordering`, 
      `responsiveness`, `accessibility`, where
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

### Interfaces to UI components.

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

### Implementations of Factories

### Implementations of UI components

### Tests for factories

### Tests for UI

## Processes

### Introduction of new UI components

### Modification of existing UI components

### Changes to the base of the framework

## Old stuff

We herby propose the introduction of an UI-Framework for ILIAS with the
following properties:

* Users of the new UI-Service will be programming against interfaces, they
  won't be able to rely on concrete classes.
* Users of the framework will be using factories for instantiation of GUI
  elements.
* The content of the Kitchen Sink will be documented in the public interface of
  the framework in a form that is machine readable.
* The UI-Framework in the ILIAS trunk will contain all KS-entries the Jour Fixe
  agreed upon.
* The users entry point to the framework, that is the main factory, is available
  via the dependency injection container as `$DIC->UIFactory();`.
* The structure of the factories of the framework is the same as the taxonomy
  given in the KS-Layout starting at the "Class"-Level in the Taxonomy. I.e. the
  main factory at some point will provide one method for each node found at the
  class level.
    - Names from the Kitchen Sink are transfered to the corresponding camelCase,
      that is Counter becomes counter and Progress Bar becomes progressBar.
* The main factory (and subsequent factories) returns factories for a method if
  the corresponding node in the KS-Taxonomy has subsequent nodes.
* The method of the factories will return a PHP-object representing the selected
  component from the KS if the KS element corresponding to the method is a leaf
  in the KS-taxonomy.
* The documentation in the source code is the initial representation of KS-entries.
  Other representations (e.g. http://www.ilias.de/docu/goto_docu_wiki_wpage_4009_1357.html)
  are derived from there.
* The KS-Entries are documented at their corresponding factory method using
  Doc-Strings. The documentation follows the YAML format for the KS-Part of the
  docu and the PHPDoc-format for the documentation of parameters and return types.
  For the KS the following information is included as a YAML-fields:
    - TBD
* The representations of the KS-components in PHP are implemented as immutable
  objects. That means:
    - those objects are compared by equality (not identity) (PHP default)
    - objects cannot be modified after creation
    - declaring a property on an existing object yields a new object, where the
      declared property has changed regarding to the original object and all
      other property are the same.

This imposes the following:

* on the proposal of new KS-Entries:
    - will be coupled to the UI-Framework
    - will need some knowledge on git/GitHub and PHP (not too much)

* on implementors of GUI-elements:
    -

* on the structure and taxonomy of the KS:
  - every element that could have a visible representation on the screen must
    be represented as a leaf in the taxonomy given by the KS-layout
  - every node in the taxonomy given by the KS-layout can not have a visible
    representation.
  - the fields for the KS-entries need to be adjusted as follows:
      * PHP-Class becomes PHP-Interface, where the description should be updated
        to "Interface to the PHP-representation of the component." 