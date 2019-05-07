<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\In;

use ILIAS\In\Transformation\Parallel;
use ILIAS\In\Transformation\Series;
use ILIAS\Refinery\In\Group;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class BasicGroupTest extends TestCase
{
	/**
	 * @var Group
	 */
	private $group;

	public function setUp() : void
	{
		$this->group = new Group();
	}

	public function testParallelInstanceCreated()
	{
		$transformation = $this->group->parallel(array(new StringTransformation(), new IntegerTransformation()));
		$this->assertInstanceOf(Parallel::class, $transformation);
	}

	public function testSeriesInstanceCreated()
	{
		$transformation = $this->group->series(array(new StringTransformation(), new IntegerTransformation()));
		$this->assertInstanceOf(Series::class, $transformation);
	}
}
