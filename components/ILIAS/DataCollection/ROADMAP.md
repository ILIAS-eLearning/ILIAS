DataCollection Roadmap
======================

This document is a roadmap for the ILIAS Object 'Data Collection'. It is a living document and will be updated as the module is developed. The [FeatureWiki](https://docu.ilias.de/go/wiki/wpage_2340_1357/) always contains the current requirements for the Data Collection. Feel free to suggest a functional change or extension.

# From the functional point of view

## Short Term
* Revision of existing field types (Integer, Date, Date-Time and possibly more)


## Mid Term
* Removal of legacy user interface components and usage of the KitchenSink<br/>
  An additional aim is to create a better overview in this complex ILIAS tool.
* Easier collaborative work on entries in a Data Collection (Keyword ‘Contributor’)
* Easier importing of existing data (Keyword ‘Import Template’)
* Improvements to the notification functions
* Abandon or revise comments for Data Collection

## Long Term
* More logging of relevant information (History for entries of Data Collections, Number of visits of entries)
* Customisable and multi-step forms for Data Collection

<br/>

# From the point of view of the code

## Short Term

* Formula parser in Refinery<br/>
  As soon as we look at the topic of formula parsers again, we would like to check whether the calculation of formulas or parts of it could move into the refinery.

* UniTests for Formula parser<br/>
  With the refactoring of the formula parser, we would like to create unit tests for the parser (which is now possible to test).

* Stack<br/>
  We do not know if we really need the `\ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Stack` or what is really the needed difference to a regular php-array.

## Mid Term

* Removal of legacy user interface components and usage of the KitchenSink.
* Correction of dysfunctional combinations of setting options.

## Long Term

* …