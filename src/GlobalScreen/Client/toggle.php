<?php
require_once('../Scope/MainMenu/Collector/Renderer/Hasher.php');
require_once('ItemState.php');
require_once('ModeToggle.php');

use ILIAS\GlobalScreen\Client\ModeToggle;

(new ModeToggle())->toggle();