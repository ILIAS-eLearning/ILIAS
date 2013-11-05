<?php exit; ?>

ilTaxNodeAssignment -> introduce obj_id
=======================================

ilTaxNodeAssignment, usages:
- ilGlossaryTermGUI ok
- ilObjGlossary ok
- ilTestRandomQuestionSetSourcePoolTaxonomiesDuplicator
  - function transferAssignmentsFromOriginalToDuplicatedTaxonomy
- ilTestRandomQuestionSetStagingPoolQuestionList
  - function getTaxonomyFilterExpressions
- ilAssQuestionList
  - function getTaxonomyFilterExpressions (use $this->parentObjId?)
  - function loadTaxonomyAssignmentData (use $this->parentObjId?)
  

- ilObjTaxonomy::getSubTreeItems add obj_id, usages:
  - ilGlossaryPresentationGUI ok
  - ilGlossaryTerm ok
  - ilObjQuestionPool $this->getId() + alte Anpassung
  
ilObjTaxonomyGUI::activateAssignedItemSorting add obj_id, update usages
- ilTaxAssignedItemsTableGUI adapt: ok
- ilObjQuestionPoolTaxonomyEditingCommandForwarder
  - function forward
  
ilTaxAssignInputGUI adapt (saveInput und setCurrentValues)
- ilGlossaryPresentationGUI ok
- ilGlossaryTermGUI ok
- assQuestionGUI
  - function saveTaxonomyAssignments
  - function populateTaxonomyFormSection

- ilTaxonomyDataSet import/export adapt

