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

use PHPUnit\Framework\TestSuite;
use ILIAS\Badge\test\PresentationHeaderTest;
use ILIAS\Badge\test\BadgeParentTest;
use ILIAS\Badge\test\ModalTest;
use ILIAS\Badge\test\TileViewTest;
use ILIAS\Badge\test\TileTest;
use ILIAS\Badge\test\SortingTest;

require_once 'vendor/composer/vendor/autoload.php';

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilServicesBadgeSuite extends TestSuite
{
    public static function suite(): self
    {
        $suite = new self();

        $tests = [
            BadgeManagementSessionRepositoryTest::class,
            PresentationHeaderTest::class,
            BadgeParentTest::class,
            ModalTest::class,
            SortingTest::class,
            TileTest::class,
            TileViewTest::class,
        ];

        foreach ($tests as $test) {
            $name = current(array_reverse(explode('\\', $test)));
            require_once('./components/ILIAS/Badge/tests/' . $name . '.php');
            $suite->addTestSuite($test);
        }

        return $suite;
    }
}
