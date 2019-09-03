<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion;

use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

/**
 * Class Base
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
abstract class AbstractBaseUnitTest extends TestCase
{
	protected $backupGlobals = false;
}
