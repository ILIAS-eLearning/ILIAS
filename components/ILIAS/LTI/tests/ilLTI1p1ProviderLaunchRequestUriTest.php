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
final class ilLTI1p1ProviderLaunchRequestUriTest extends TestCase
{
    public static function requestUriProvider(): array
    {
        return [
            'only parameter' => ['/lti.php?client_id=default', '/lti.php'],
            'first parameter' => ['/lti.php?client_id=default&ref_id=42', '/lti.php?ref_id=42'],
            'last parameter' => ['/lti.php?ref_id=42&client_id=default', '/lti.php?ref_id=42'],
            'middle parameter' => ['/lti.php?ref_id=42&client_id=default&lang=de', '/lti.php?ref_id=42&lang=de'],
            'no client id' => ['/lti.php?ref_id=42', '/lti.php?ref_id=42'],
            'no query' => ['/lti.php', '/lti.php'],
            'parameter of another name' => ['/lti.php?tool_client_id=abc', '/lti.php?tool_client_id=abc'],
        ];
    }

    #[DataProvider('requestUriProvider')]
    public function testStripClientId(string $request_uri, string $expected): void
    {
        $_SERVER['REQUEST_URI'] = $request_uri;

        ilLTI1p1ProviderLaunchRequestUri::stripClientId();

        $this->assertSame($expected, $_SERVER['REQUEST_URI']);
    }
}
