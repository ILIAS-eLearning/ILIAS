<?php exit; ?>

ilTaxNodeAssignment -> introduce obj_id
=======================================

ilTaxNodeAssignment, usages:
- ilGlossaryTermGUI ok
- ilObjGlossary ok
- ilTestRandomQuestionSetSourcePoolTaxonomiesDuplicator ok
  - function transferAssignmentsFromOriginalToDuplicatedTaxonomy ok
- ilTestRandomQuestionSetStagingPoolQuestionList ok
  - function getTaxonomyFilterExpressions ok
- ilAssQuestionList ok
  - function getTaxonomyFilterExpressions ok
  - function loadTaxonomyAssignmentData ok
  

- ilObjTaxonomy::getSubTreeItems add obj_id, usages:
  - ilGlossaryPresentationGUI ok
  - ilGlossaryTerm ok
  - ilObjQuestionPool $this->getId() + alte Anpassung ok
  
ilObjTaxonomyGUI::activateAssignedItemSorting add obj_id, update usages
- ilTaxAssignedItemsTableGUI adapt: ok
- ilObjQuestionPoolTaxonomyEditingCommandForwarder ok
  - function forward ok
  
ilTaxAssignInputGUI adapt (saveInput und setCurrentValues)
- ilGlossaryPresentationGUI ok
- ilGlossaryTermGUI ok
- assQuestionGUI ok
  - function saveTaxonomyAssignments ok
  - function populateTaxonomyFormSection ok

- ilTaxonomyDataSet import/export adapt

