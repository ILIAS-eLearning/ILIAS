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

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Component\Dependencies\InType;
use ILIAS\Component\Dependencies\Mocks\EvalLightMockBuilder;
use ILIAS\Component\Dependencies\Reader;
use PHPUnit\Framework\TestCase;

/**
 * The declared dependencies of the Language component: RBAC is used (use).
 */
class LanguageComponentDependenciesTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function names(InType $type): array
    {
        $result = (new Reader(new EvalLightMockBuilder()))->read(new ILIAS\Language());
        $names = [];
        foreach ($result->getInDependenciesOf($type) as $dependency) {
            $names[] = $dependency->getName();
        }

        return $names;
    }

    public function testRbacIsUsed(): void
    {
        self::assertContains(RBAC::class, $this->names(InType::USE));
    }
}
