<?php
ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

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
