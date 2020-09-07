<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* News feed script.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/


include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_RSS);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if ($_GET["user_id"] != "") {
    include_once("./Services/Feeds/classes/class.ilUserFeedWriter.php");
    $writer = new ilUserFeedWriter($_GET["user_id"], $_GET["hash"]);
    $writer->showFeed();
} elseif ($_GET["ref_id"] != "") {
    include_once("./Services/Feeds/classes/class.ilObjectFeedWriter.php");
    $writer = new ilObjectFeedWriter($_GET["ref_id"], false, $_GET["purpose"]);
    $writer->showFeed();
} elseif ($_GET["blog_id"] != "") {
    include_once("Modules/Blog/classes/class.ilObjBlog.php");
    ilObjBlog::deliverRSS($_GET["blog_id"]);
}
