<?php

declare(strict_types=1);

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
 ********************************************************************
 */

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data;
use ILIAS\Data\Chart;
use ILIAS\Data\Dimension;
use PHPUnit\Framework\TestCase;

class DatasetTest extends TestCase
{
    protected function setUp(): void
    {
        $this->f = new Data\Factory();
    }

    protected function getSimpleDimensions(): array
    {
        $c_dimension = $this->f->dimension()->cardinal();
        $dimensions = ["A dimension" => $c_dimension, "Another dimension" => $c_dimension];

        return $dimensions;
    }

    protected function getExtendedDimensions(): array
    {
        $c_dimension = $this->f->dimension()->cardinal();
        $r_dimension = $this->f->dimension()->range($c_dimension);
        $dimensions = [
            "First dimension" => $c_dimension,
            "Second dimension" => $c_dimension,
            "Third dimension" => $r_dimension
        ];

        return $dimensions;
    }

    public function testDimensions(): void
    {
        try {
            $dimensions = $this->getSimpleDimensions();
            $dataset = $this->f->dataset($dimensions);
            $this->assertEquals($dimensions, $dataset->getDimensions());
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function testInvalidDimension(): void
    {
        try {
            $dimension = "Dimension";
            $dataset = $this->f->dataset(["A dimension" => $dimension]);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testInvalidDimensionKey(): void
    {
        try {
            $dimension = $this->f->dimension()->cardinal();
            $dataset = $this->f->dataset([1 => $dimension]);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testwithPoint(): void
    {
        $dataset = $this->f->dataset($this->getSimpleDimensions());
        $points = ["A dimension" => 1, "Another dimension" => 2];
        $dataset = $dataset->withPoint(
            "Item",
            $points
        );
        $this->assertEquals(["Item" => $points], $dataset->getPoints());
    }

    public function testwithInvalidPointsCount(): void
    {
        try {
            $dataset = $this->f->dataset($this->getSimpleDimensions());
            $points = ["A dimension" => 1, "Second dimension" => 2];
            $dataset = $dataset->withPoint(
                "Item",
                $points
            );
            $this->assertFalse("This should not happen.");
        } catch (\ArgumentCountError $e) {
            $this->assertTrue(true);
        }
    }

    public function testwithAlternativeInformation(): void
    {
        $dataset = $this->f->dataset($this->getSimpleDimensions());
        $info = ["A dimension" => "An information", "Another dimension" => null];
        $dataset = $dataset->withAlternativeInformation(
            "Item",
            $info
        );
        $this->assertEquals(["Item" => $info], $dataset->getAlternativeInformation());
    }

    public function testwithInvalidAlternativeInformationCount(): void
    {
        try {
            $dataset = $this->f->dataset($this->getSimpleDimensions());
            $info = ["A dimension" => "An information", "Second dimension" => null];
            $dataset = $dataset->withAlternativeInformation(
                "Item",
                $info
            );
            $this->assertFalse("This should not happen.");
        } catch (\ArgumentCountError $e) {
            $this->assertTrue(true);
        }
    }

    public function testwithInvalidAlternativeInformationValue(): void
    {
        try {
            $dataset = $this->f->dataset($this->getSimpleDimensions());
            $info = ["A dimension" => "An information", "Another dimension" => 1];
            $dataset = $dataset->withAlternativeInformation(
                "Item",
                $info
            );
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testwithResetDataset(): void
    {
        $dataset = $this->f->dataset($this->getSimpleDimensions());
        $points = ["A dimension" => 1, "Another dimension" => 2];
        $info = ["A dimension" => "An information", "Another dimension" => null];
        $dataset = $dataset->withPoint(
            "Item",
            $points
        );
        $dataset = $dataset->withAlternativeInformation(
            "Item",
            $info
        );

        $this->assertEquals(false, $dataset->isEmpty());

        $dataset = $dataset->withResetDataset();

        $this->assertEquals(true, $dataset->isEmpty());
        $this->assertEquals([], $dataset->getPoints());
        $this->assertEquals([], $dataset->getAlternativeInformation());
    }

    public function testMinValue(): void
    {
        $dataset = $this->f->dataset($this->getExtendedDimensions());
        $points1 = ["First dimension" => 0, "Second dimension" => 0.5, "Third dimension" => [0, 1]];
        $points2 = ["First dimension" => -3, "Second dimension" => 5, "Third dimension" => [-4, -1.5]];
        $points3 = ["First dimension" => -2, "Second dimension" => 5, "Third dimension" => [-3, -1.5]];
        $dataset = $dataset->withPoint(
            "Item 1",
            $points1
        );
        $dataset = $dataset->withPoint(
            "Item 2",
            $points2
        );
        $dataset = $dataset->withPoint(
            "Item 2",
            $points3
        );
        $this->assertEquals(-2, $dataset->getMinValueForDimension("First dimension"));
        $this->assertEquals(0.5, $dataset->getMinValueForDimension("Second dimension"));
        $this->assertEquals(-3, $dataset->getMinValueForDimension("Third dimension"));
    }

    public function testMaxValue(): void
    {
        $dataset = $this->f->dataset($this->getExtendedDimensions());
        $points1 = ["First dimension" => 0, "Second dimension" => -0.5, "Third dimension" => [0, 1]];
        $points2 = ["First dimension" => -3, "Second dimension" => -5, "Third dimension" => [-4, 1.5]];
        $dataset = $dataset->withPoint(
            "Item 1",
            $points1
        );
        $dataset = $dataset->withPoint(
            "Item 2",
            $points2
        );
        $this->assertEquals(0, $dataset->getMaxValueForDimension("First dimension"));
        $this->assertEquals(-0.5, $dataset->getMaxValueForDimension("Second dimension"));
        $this->assertEquals(1.5, $dataset->getMaxValueForDimension("Third dimension"));
    }
}
