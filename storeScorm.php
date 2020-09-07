<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * for storing Data also without session
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */


include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SCORM);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

include_once 'Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';

if ((string) $_GET['do'] == "unload") {
    ilObjSCORMTracking::checkIfAllowed((int) $_GET['package_id'], (int) $_GET['p'], (int) $_GET['hash']);
    ilObjSCORMTracking::scorm12PlayerUnload();
} else {
    global $ilUser;
    $data = file_get_contents('php://input');
    $ilUser->setId($data->p);
    ilObjSCORMTracking::checkIfAllowed((int) $_GET['package_id'], (int) $data->p, (int) $data->hash);
    ilObjSCORMTracking::storeJsApi();
}
