<?php

$b = new arBuilder(new ilOrgUnitPosition());
$b = new arBuilder(new ilOrgUnitAuthority());
$b = new arBuilder(new ilOrgUnitUserAssignment());
$b = new arBuilder(new ilOrgUnitOperation());
$b = new arBuilder(new ilOrgUnitOperationContext());
$b = new arBuilder(new ilOrgUnitPermission());
//$b->generateDBUpdateForInstallation();


ilOrgUnitPosition::resetDB();
ilOrgUnitAuthority::resetDB();
ilOrgUnitUserAssignment::resetDB();
ilOrgUnitOperation::resetDB();
ilOrgUnitOperationContext::resetDB();
ilOrgUnitPermission::resetDB();


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

// Authority
$Sup = new ilOrgUnitAuthority();
$Sup->setOver(ilOrgUnitAuthority::SCOPE_SAME_ORGU);
$Sup->setScope($ilOrgUnitPositionEmployee->getId());
$ilOrgUnitPositionSuperior->setAuthorities([ $Sup ]);
$ilOrgUnitPositionSuperior->create();
$superiors_position_id = $ilOrgUnitPositionSuperior->getId();

$ilOrgUnitPositionSuperior = new ilOrgUnitPosition();
$ilOrgUnitPositionSuperior->setTitle("Abteilungsleiter");
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


ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_IASS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_CRS, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_GRP, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_TST, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_EXC, ilOrgUnitOperationContext::CONTEXT_OBJECT);
ilOrgUnitOperationContextQueries::registerNewContext(ilOrgUnitOperationContext::CONTEXT_SVY, ilOrgUnitOperationContext::CONTEXT_OBJECT);

ilOrgUnitOperationQueries::registerNewOperationForMultipleContexts(ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS, 'Read the learning Progress of a User', array(
	ilOrgUnitOperationContext::CONTEXT_CRS,
	ilOrgUnitOperationContext::CONTEXT_GRP,
	ilOrgUnitOperationContext::CONTEXT_IASS,
	ilOrgUnitOperationContext::CONTEXT_EXC,
	ilOrgUnitOperationContext::CONTEXT_SVY,
));

ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a course', ilOrgUnitOperationContext::CONTEXT_CRS);
ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_MANAGE_MEMBERS, 'Edit Members in a group', ilOrgUnitOperationContext::CONTEXT_GRP);
ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_EDIT_SUBMISSION_GRADES, '', ilOrgUnitOperationContext::CONTEXT_EXC);
ilOrgUnitOperationQueries::registerNewOperation(ilOrgUnitOperation::OP_ACCESS_RESULTS, '', ilOrgUnitOperationContext::CONTEXT_SVY);




