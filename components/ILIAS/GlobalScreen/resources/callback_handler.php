<?php

namespace ILIAS\GlobalScreen\Client;

/** @noRector  */
require_once(__DIR__ . '/../vendor/composer/vendor/autoload.php');

if (php_sapi_name() !== 'cli') {
    (new CallbackHandler())->run();
}
