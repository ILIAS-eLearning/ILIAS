<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessCheckerDevlivery.php');
ilWebAccessCheckerDevlivery::run($_SERVER['REQUEST_URI']);
