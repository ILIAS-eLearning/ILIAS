<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessCheckerDelivery.php');
ilWebAccessCheckerDelivery::run($_SERVER['REQUEST_URI']);
