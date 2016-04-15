<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @deprecated Thias file will be removed with ILIAS 5.2. Use wac.php instead
 */

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
ilWebAccessChecker::run(rawurldecode($_SERVER['REQUEST_URI']));