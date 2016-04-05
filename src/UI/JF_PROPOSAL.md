# Jour Fixe Proposal to introduce an UI-Framework

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
  

This imposes the following rules:

* for the proposal of new KS-Entries:
    - will be coupled to the UI-Framework
    - will need some knowledge on git/GitHub and PHP (not too much)

* for implementors of GUI-elements:
    -

* for the structure and taxonomy of the KS:
  - every element that could have a visible representation on the screen must
    be represented as a leaf in the taxonomy given by the KS-layout
  - every node in the taxonomy given by the KS-layout can not have a visible
    representation.
  - the fields for the KS-entries need to be adjusted as follows:
      * PHP-Class becomes PHP-Interface, where the description should be updated
        to "Interface to the PHP-representation of the component." 