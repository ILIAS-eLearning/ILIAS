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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTIAdvantageKeyPairTest extends TestCase
{
    public static function authorizationProvider(): array
    {
        return [
            'bearer token' => ['Bearer abc.def.ghi', 'abc.def.ghi'],
            'scheme in any case and spaces around' => ['  bearer   abc.def.ghi ', 'abc.def.ghi'],
            'no header' => ['', ''],
            'basic scheme' => ['Basic dXNlcjpwYXNz', ''],
            'scheme without token' => ['Bearer', ''],
            'two tokens' => ['Bearer abc def', ''],
        ];
    }

    #[DataProvider('authorizationProvider')]
    public function testBearerToken(string $authorization, string $expected): void
    {
        $this->assertSame($expected, ilLTIAdvantageKeyPair::bearerToken($authorization));
    }
}
