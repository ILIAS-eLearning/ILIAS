<?php declare(strict_types=1);
ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

global $DIC;
$packageId = $DIC->http()->wrapper()->query()->retrieve('package_id', $DIC->refinery()->kindlyTo()->int());

$doUnload = false;
if ($DIC->http()->wrapper()->query()->has('do')) {
    if ($DIC->http()->wrapper()->query()->retrieve('do', $DIC->refinery()->kindlyTo()->string()) == "unload") {
        $doUnload = true;
    }
}

if ($doUnload) {
    $p = $DIC->http()->wrapper()->query()->retrieve('p', $DIC->refinery()->kindlyTo()->int());
    $hash = $DIC->http()->wrapper()->query()->retrieve('hash', $DIC->refinery()->kindlyTo()->int());
    ilObjSCORMTracking::checkIfAllowed($packageId, $p, $hash);
    ilObjSCORMTracking::scorm12PlayerUnload();
} else {
    global $ilUser;
    $data = file_get_contents('php://input');
    $ilUser->setId($data->p);
    ilObjSCORMTracking::checkIfAllowed($packageId, (int) $data->p, (int) $data->hash);
    ilObjSCORMTracking::storeJsApi();
}
