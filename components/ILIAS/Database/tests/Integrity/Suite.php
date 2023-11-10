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

namespace ILIAS\Tests\Services\Database\Integrity;

use PHPUnit\Framework\TestSuite;
use Iterator;
use GlobIterator;
use FilterIterator;
use SplFileInfo;

class Suite extends TestSuite
{
    public static function suite(): Suite
    {
        $suite = new self();
        // Get namespace path from class name.
        $namespace = preg_replace('/\\\\[^\\\\]+$/', '\\', self::class);

        foreach (self::collectTestFiles() as $file) {
            require_once $file->getRealPath();
            $suite->addTestSuite($namespace . preg_replace('/.php$/', '', $file->getFileName()));
        }

        return $suite;
    }

    private static function collectTestFiles(): Iterator
    {
        return new class (new GlobIterator(__DIR__ . '/*.php')) extends FilterIterator {
            public function accept(): bool
            {
                return $this->current() instanceof SplFileInfo && __FILE__ !== $this->current()->getRealPath();
            }
        };
    }
}
