<?php
/**
 * Entry Point for Async calls from the Notification Center
 */
namespace ILIAS\GlobalScreen\Client;
chdir("../../../");
require_once('./libs/composer/vendor/autoload.php');
(new Notifications())->run();

