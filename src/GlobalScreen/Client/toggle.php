<?php
/** @noRector  */
require_once('../Scope/MainMenu/Collector/Renderer/Hasher.php');
/** @noRector  */
require_once('ItemState.php');
/** @noRector  */
require_once('ModeToggle.php');

use ILIAS\GlobalScreen\Client\ModeToggle;

(new ModeToggle())->toggle();
