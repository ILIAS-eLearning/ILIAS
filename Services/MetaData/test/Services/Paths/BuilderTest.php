<?php

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

declare(strict_types=1);

namespace ILIAS\MetaData\Services\Paths;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\NullBuilder as NullInternalBuilder;
use ILIAS\MetaData\Paths\BuilderInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;

class BuilderTest extends TestCase
{
    protected function getBuilder(): Builder
    {
        $internal_builder = new class ('') extends NullInternalBuilder {
            public function __construct(public string $path)
            {
            }

            public function withNextStep(string $name, bool $add_as_first = false): BuilderInterface
            {
                return new self($this->path . ':' . $name);
            }

            public function withNextStepToSuperElement(bool $add_as_first = false): BuilderInterface
            {
                return new self($this->path . ':^');
            }

            public function withAdditionalFilterAtCurrentStep(
                FilterType $type,
                string ...$values
            ): BuilderInterface {
                $filter = '{' . $type->value . ';' . implode(',', $values) . '}';
                return new self($this->path . $filter);
            }

            public function get(): PathInterface
            {
                return new class ($this->path) extends NullPath {
                    public function __construct(public string $path_string)
                    {
                    }
                };
            }
        };

        return new class ($internal_builder) extends Builder {
            public function __construct(BuilderInterface $internal_builder)
            {
                parent::__construct($internal_builder);
            }

            public function exposePath(): string
            {
                return $this->internal_builder->path;
            }
        };
    }

    public function testWithNextStep(): void
    {
        $builder = $this->getBuilder();
        $builder1 = $builder->withNextStep('step');

        $this->assertSame(
            '',
            $builder->exposePath()
        );
        $this->assertSame(
            ':step',
            $builder1->exposePath()
        );
    }

    public function testWithNextStepToSuperElement(): void
    {
        $builder = $this->getBuilder();
        $builder1 = $builder->withNextStepToSuperElement();

        $this->assertSame(
            '',
            $builder->exposePath()
        );
        $this->assertSame(
            ':^',
            $builder1->exposePath()
        );
    }

    public function testWithAdditionalFilterAtCurrentStep(): void
    {
        $builder = $this->getBuilder();
        $builder1 = $builder->withAdditionalFilterAtCurrentStep(FilterType::DATA, 'v1', 'v2');

        $this->assertSame(
            '',
            $builder->exposePath()
        );
        $this->assertSame(
            '{data;v1,v2}',
            $builder1->exposePath()
        );
    }

    public function testGet(): void
    {
        $builder = $this->getBuilder()
            ->withNextStep('step1')
            ->withNextStepToSuperElement()
            ->withNextStep('step2')
            ->withAdditionalFilterAtCurrentStep(FilterType::MDID, '12');
        $builder3 = $builder
            ->withNextStep('step3')
            ->withNextStepToSuperElement();

        $this->assertSame(
            ':step1:^:step2{id;12}',
            $builder->get()->path_string
        );
        $this->assertSame(
            ':step1:^:step2{id;12}:step3:^',
            $builder3->get()->path_string
        );
    }
}
