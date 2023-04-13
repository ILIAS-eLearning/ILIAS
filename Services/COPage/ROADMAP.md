# Roadmap

## Without Priority

The following issues are mostly usability issues, that could be tackled as part of the ongoing PER project.
- Action to edit translations is hard to find: https://mantis.ilias.de/view.php?id=33957


## Short Term

### Remove dependency to include/inc.xml5compliance.php

Dom handling should be move to internal service, the dependency to include/inc.xml5compliance.php should be removed. More unit tests for dom transformations should be introduced.

### Remove YUI and jQuery dependencies

- Migrate from jQueryUI draggable to a non jQuery lib, e.g. https://shopify.github.io/draggable/ 

### Continue Page Editor Refactoring (started with ILIAS 7)

https://docu.ilias.de/goto_docu_wiki_wpage_6254_1357.html

**Major Goals**

- Increase Client side code quality. (JS coding style, use common patterns, bundling techniques, client-server-communication)
- Adapt to PLR layout, make use of tool slate.
- Increase usability, reduce clicks, auto-save.

### Increase Robustness of Link Handling / User Input Processing

Link formattings lead to subtle issues (e.g. #30906) sometimes, since they are not part of the DOM structure on the client side and added later via PHP. This may result in invalid XML. There are different possible ways to handle this, e.g. make links part of DOM already on the client side will allow Tiny to tidy up the structure. In PHP the replacement of [xln] ans similar tags could be improved, by processing pairs of opening and closing tags and check their inner content for validity before replacing them with XML counterparts.

In general the old string manipulations should be replaced by DOM manipulations whenever possible when transforming the client side data.

### Remove remaining hardcoded styles from code

E.g. style_selector_reset and similar places.

## Mid Term

### Performance

Large pages, especially with a high number of elements, e.g. data tables with lots of cells decrease the performance. This is mainly due to the way the model is retrieved in the "all" command ($o->pcModel = $this->getPCModel()). An alternative would be to use an xml -> xslt -> json approach at least for paragraphs and to "bulk-query" them.
See https://mantis.ilias.de/view.php?id=29680

### Replace or refactor mediawiki word-level-diff code

This code is copied from an older mediawiki version. I compares two versions of page HTML outputs and marks differences. The code should either be replaced by a lib that provides the same functionality, refactored and integrated into own code or at least replaced by an up-to-date code excerpt from mediawiki.

### Lower Cyclomatic Complexity

This component suffers from record high cyclomatic complexity numbers. Refactorings should target and split up methods and classes to gain better maintainability.

E.g.

* XSL processing should be outfactored to a separate class
* Rendering should b outfacored to a separate class

Apr. 2019:
```
> phploc Services/COPage
...
Cyclomatic Complexity
  Average Complexity per LLOC                     0.25
  Average Complexity per Class                   28.46
    Minimum Class Complexity                      1.00
    Maximum Class Complexity                    492.00
  Average Complexity per Method                   2.95
    Minimum Method Complexity                     1.00
    Maximum Method Complexity                   114.00
...
```

## Long Term

### Integration of new question service

The new questions service should be integrated into the page editor. Especially the client side "self-assessment" player part should be implemented (and factored out into a separate component).

### Refactor page question handling

Note this is an older entry. Should be done with integration of the question service.

#### Render page questions

- ilPCQuestion::getJavascriptFiles loads
  - ./Modules/Scorm2004/scripts/questions/pure.js 
  - ./Modules/Scorm2004/scripts/questions/question_handling.js 
  - Modules/TestQuestionPool/js/ilAssMultipleChoice.js
  - Modules/TestQuestionPool/js/ilMatchingQuestion.js
  - (./Services/COPage/js/ilCOPageQuestionHandler.js)
- ilPCQuestion::getJSTextInitCode loads
  - ilias.question.txt... strings
- ilPCQuestion::getQuestionJsOfPage
  - uses Services/COPage/templates/default/tpl.question_export.html
  - returns basix HTML of question (qtitle content)
  - adds function renderILQuestion<NR>
    - this function is declared early here, BUT not called yet
    - it contains jQuery('div#container{VAL_ID}').autoRender call (pure.js rendering)
- ilPCQuestion::getOnloadCode
  - adds calls for all renderers renderILQuestion<NR>
  - inits question answers and callback

#### Saving page questions

Saving of page question answers is quite strange and includes dependencies to the SCORM component. This should be refactored.

**Page Rendering**

* ilPCQuestion->getOnloadCode()
* -> ilCOPageQuestionHandler.initCallback('".$url."');


**Clicking Answer**

* Modules/Scorm2004/scripts/questions/question_handling.js
	* ilias.questions.checkAnswers
    * calls ilias.questions.<questiontype>(), e.g. ilias.questions.assMultipleChoice()
* -> ilCOPageQuestionHandler.js
	* ->processAnswer
	* -> sendAnswer sends async request to
* -> ilPageObjectGUI->processAnswer
	* ->ilPageQuestionProcessor->saveQuestionAnswer


## Long Term

