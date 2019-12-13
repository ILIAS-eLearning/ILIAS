<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News feed script.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_RSS);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if ($_GET["user_id"] != "") {
    $writer = new ilUserFeedWriter($_GET["user_id"], $_GET["hash"]);
    $writer->showFeed();
} elseif ($_GET["ref_id"] != "") {
    $writer = new ilObjectFeedWriter($_GET["ref_id"], false, $_GET["purpose"]);
    $writer->showFeed();
} elseif ($_GET["blog_id"] != "") {
    ilObjBlog::deliverRSS($_GET["blog_id"]);
}
