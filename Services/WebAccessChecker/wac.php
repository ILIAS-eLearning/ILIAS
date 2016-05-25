<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
ilWebAccessChecker::run(rawurldecode($_SERVER['REQUEST_URI']));
