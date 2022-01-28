<?php
/**
 * Entry Point for Async calls from the Notification Center
 */
namespace ILIAS\GlobalScreen\Client;

chdir("../../../");
/** @noRector  */
require_once('./libs/composer/vendor/autoload.php');
\ilInitialisation::initILIAS();
(new Notifications())->run();
