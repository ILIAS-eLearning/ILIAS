<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once("Services/Init/classes/class.ilInitialisation.php");
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
    $data = json_decode(file_get_contents('php://input'));
    $ilUser->setId((int) $data->p);
    ilObjSCORMTracking::checkIfAllowed($packageId, (int) $data->p, (int) $data->hash);
    ilObjSCORMTracking::storeJsApi();
}
