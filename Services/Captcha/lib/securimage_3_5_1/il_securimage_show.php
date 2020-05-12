<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../../..');
require_once 'include/inc.header.php';
require_once 'Services/Captcha/classes/class.ilSecurImage.php';
$si = new ilSecurImage();
$si->setImageHeight((int) $_GET['height']);
$si->setImageWidth((int) $_GET['width']);
$si->showImage();
