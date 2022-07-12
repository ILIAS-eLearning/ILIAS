<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;
use ILIAS\Data\Factory as DataFactory;

class AddLabelTest extends TestCase
{
    /** @var string[]  */
    private static array $labels = ["A", "B", "C"];
    /** @var int[]  */
    private static array $test_array = [1, 2, 3];
    /** @var array<string, int>  */
    private static array $result_array = ["A" => 1, "B" => 2, "C" => 3];

    private ?Refinery $f;
    private ?Transformation $add_label;

    protected function setUp() : void
    {
        $dataFactory = new DataFactory();
        $language = $this->createMock(ilLanguage::class);

        $this->f = new Refinery($dataFactory, $language);
        $this->add_label = $this->f->container()->addLabels(self::$labels);
    }

    protected function tearDown() : void
    {
        $this->f = null;
        $this->add_label = null;
    }

    public function testTransform() : void
    {
        $with = $this->add_label->transform(self::$test_array);
        $this->assertEquals(self::$result_array, $with);
    }

    public function testTransformFails() : void
    {
        $raised = false;
        try {
            $with = null;
            $next_with = $this->add_label->transform($with);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = [1, 2, 3, 4];
            $with = $this->add_label->transform($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = "1, 2, 3";
            $with = $this->add_label->transform($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $this->add_label->transform($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testInvoke() : void
    {
        $add_label = $this->f->container()->addLabels(self::$labels);
        $with = $add_label(self::$test_array);
        $this->assertEquals(self::$result_array, $with);
    }

    public function testInvokeFails() : void
    {
        $add_label = $this->f->container()->addLabels(self::$labels);

        $raised = false;
        try {
            $with = null;
            $next_with = $add_label($with);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = [1, 2, 3, 4];
            $with = $add_label($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = "1, 2, 3";
            $with = $add_label($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $add_label($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
