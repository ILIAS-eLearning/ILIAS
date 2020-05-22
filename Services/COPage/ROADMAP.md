# Roadmap

## Short Term

### Page Editor Refactoring (ILIAS 7, at least partially)

https://docu.ilias.de/goto_docu_wiki_wpage_6254_1357.html

**Major Goals**

- Increase Client side code quality. (JS coding style, use common patterns, bundling techniques, client-server-communication)
- Adapt to PLR layout, make use of tool slate.
- Increase usability, reduce clicks, auto-save.


## Mid Term

### Lower Cyclomatic Complexity (should also be done with ILIAS 7)

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

### Integration of new question service

The new questions service should be integrated into the page editor. Especially the client side "self-assessment" player part should be implemented (and factored out into a separate component).

### Refactor page question handling

Note this is an older entry. Should be done with integration of the question service.

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

