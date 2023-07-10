<?php

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

/**
 * News feed script.
 * @author Alexander Killing <killing@leifos.de>
 */
include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_RSS);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
global $DIC;

$getPar = static function (string $key, string $func) {
    global $DIC;
    return $DIC->http()->wrapper()->query()->has($key)
        ? $DIC->http()->wrapper()->query()->retrieve(
            $key,
            $DIC->refinery()->kindlyTo()->$func()
        ) : ($func === "int" ? 0 : '');
};

$requested_user_id = $getPar("user_id", "int");
$requested_ref_id = $getPar("ref_id", "int");
$requested_purpose = $getPar("purpose", "string");
$requested_blog_id = $getPar("blog_id", "int");
$requested_hash = $getPar("hash", "string");

if ($requested_user_id > 0) {
    $writer = new ilUserFeedWriter($requested_user_id, $requested_hash);
    $writer->showFeed();
} elseif ($requested_ref_id > 0) {
    $writer = new ilObjectFeedWriter($requested_ref_id, false, $requested_purpose);
    $writer->showFeed();
} elseif ($requested_blog_id > 0) {
    ilObjBlog::deliverRSS($requested_blog_id);
}
