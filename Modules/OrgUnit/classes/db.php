<?php

ilOrgUnitPosition::resetDB();
ilOrgUnitAuthority::resetDB();
ilOrgUnitUserAssignment::resetDB();

$ilOrgUnitPositionEmployee = new ilOrgUnitPosition();
$ilOrgUnitPositionEmployee->setTitle("Employees");
$ilOrgUnitPositionEmployee->setDescription("Employees of a OrgUnit");
$ilOrgUnitPositionEmployee->setCorePosition(true);
$ilOrgUnitPositionEmployee->create();
$employee_position_id = $ilOrgUnitPositionEmployee->getId();

$ilOrgUnitPositionSuperior = new ilOrgUnitPosition();
$ilOrgUnitPositionSuperior->setTitle("Superiors");
$ilOrgUnitPositionSuperior->setDescription("Superiors of a OrgUnit");
$ilOrgUnitPositionSuperior->setCorePosition(true);
$ilOrgUnitPositionSuperior->create();
$superiors_position_id = $ilOrgUnitPositionSuperior->getId();

$ilOrgUnitPositionSuperior = new ilOrgUnitPosition();
$ilOrgUnitPositionSuperior->setTitle("Handlanger");
$ilOrgUnitPositionSuperior->setDescription("");
$ilOrgUnitPositionSuperior->setCorePosition(false);
$ilOrgUnitPositionSuperior->create();

$ilObjOrgUnitTree = ilObjOrgUnitTree::_getInstance();
foreach ($ilObjOrgUnitTree->getAllChildren(56) as $orgu_ref_id) {
	$employees = $ilObjOrgUnitTree->getEmployees($orgu_ref_id);
	foreach ($employees as $employee_user_id) {
		ilOrgUnitUserAssignment::findOrCreateAssignment($employee_user_id, $employee_position_id, $orgu_ref_id);
	}
	$superiors = $ilObjOrgUnitTree->getSuperiors($orgu_ref_id);
	foreach ($superiors as $superior_user_id) {
		ilOrgUnitUserAssignment::findOrCreateAssignment($superior_user_id, $superiors_position_id, $orgu_ref_id);
	}
}

ilOrgUnitOperation::resetDB();
ilOrgUnitOperationContext::resetDB();
ilOrgUnitPermission::resetDB();

ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_IASS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_CRS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_GRP, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_TST, ilOrgUnitOperationContext::CONTEXT_OBJECT);

ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OPERATION_VIEW_LEARNING_PROGRESS, '', array(
	ilOrgUnitOperationContext::CONTEXT_CRS,
	ilOrgUnitOperationContext::CONTEXT_GRP,
	ilOrgUnitOperationContext::CONTEXT_IASS,
));

ilOrgUnitOperationQueries::registerNewOperation('viewmembers', '', 'crs');
ilOrgUnitOperationQueries::registerNewOperation('viewlastaccess', '', 'crs');

ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OPERATION_VIEW_TEST_RESULTS, '', ilOrgUnitOperationContext::CONTEXT_TST);

