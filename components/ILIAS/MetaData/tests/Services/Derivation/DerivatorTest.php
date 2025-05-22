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

namespace ILIAS\MetaData\Services\Derivation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\NullSet;

class DerivatorTest extends TestCase
{
    protected function getDerivator(
        SetInterface $from_set,
        bool $throw_exception = false
    ): DerivatorInterface {
        $repo = new class ($throw_exception) extends NullRepository {
            public array $transferred_md = [];
            public array $error_thrown = [];

            public function __construct(protected bool $throw_exception)
            {
            }

            public function transferMD(
                SetInterface $from_set,
                int $to_obj_id,
                int $to_sub_id,
                string $to_type,
                bool $throw_error_if_invalid
            ): void {
                if ($this->throw_exception) {
                    throw new \ilMDRepositoryException('failed');
                }

                $this->transferred_md[] = [
                    'from_set' => $from_set,
                    'to_obj_id' => $to_obj_id,
                    'to_sub_id' => $to_sub_id,
                    'to_type' => $to_type
                ];
                $this->error_thrown[] = $throw_error_if_invalid;
            }
        };
        return new class ($from_set, $repo) extends Derivator {
            public function exposeRepository(): RepositoryInterface
            {
                return $this->repository;
            }
        };

    }

    public function testForObject(): void
    {
        $from_set = new NullSet();
        $derivator = $this->getDerivator($from_set);
        $derivator->forObject(78, 5, 'to_type');

        $this->assertCount(1, $derivator->exposeRepository()->transferred_md);
        $this->assertSame(
            [
                'from_set' => $from_set,
                'to_obj_id' => 78,
                'to_sub_id' => 5,
                'to_type' => 'to_type'
            ],
            $derivator->exposeRepository()->transferred_md[0]
        );
        $this->assertCount(1, $derivator->exposeRepository()->error_thrown);
        $this->assertTrue($derivator->exposeRepository()->error_thrown[0]);
    }

    public function testForObjectWithSubIDZero(): void
    {
        $from_set = new NullSet();
        $derivator = $this->getDerivator($from_set);
        $derivator->forObject(78, 0, 'to_type');

        $this->assertCount(1, $derivator->exposeRepository()->transferred_md);
        $this->assertSame(
            [
                'from_set' => $from_set,
                'to_obj_id' => 78,
                'to_sub_id' => 78,
                'to_type' => 'to_type'
            ],
            $derivator->exposeRepository()->transferred_md[0]
        );
        $this->assertCount(1, $derivator->exposeRepository()->error_thrown);
        $this->assertTrue($derivator->exposeRepository()->error_thrown[0]);
    }

    public function testForObjectException(): void
    {
        $from_set = new NullSet();
        $derivator = $this->getDerivator($from_set, true);

        $this->expectException(\ilMDServicesException::class);
        $derivator->forObject(78, 0, 'to_type');
    }
}
