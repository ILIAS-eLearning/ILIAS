<?php
/*
* bookmark frameset
*
* @author Alex Killing <alex.killing@gmx.de>
* @package ilias-core
* @version $Id$
*/

require_once "./include/inc.header.php";

// output frameset for personal bookmark administration
$tpl = new ilTemplate("tpl.bookmark_frameset.html", false, false);
$tpl->show();

?>
