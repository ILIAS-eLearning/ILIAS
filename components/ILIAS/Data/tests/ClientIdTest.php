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

require_once 'vendor/composer/vendor/autoload.php';

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ClientIdTest extends TestCase
{
    /** @var Data\Factory */
    private Data\Factory $f;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->f = new Data\Factory();
    }

    /**
     * @return array[]
     */
    public static function clientIdProvider(): array
    {
        return [
            'single letter' => ['c'],
            'multiple letters' => ['client'],
            'single uppercase letter' => ['C'],
            'multiple uppercase letters' => ['CLIENT'],
            'single digit' => ['1'],
            'multiple digits' => ['12'],
            'letters + underscores' => ['client_with_underscore'],
            'letters + underscores + digits' => ['client_with_12345'],
            'letters + hyphens' => ['client-with-hyphen'],
            'dots + sharps' => ['.#'] // looks weird, but is considered valid
        ];
    }

    /**
     * @return array[]
     */
    public static function invalidClientIdProvider(): array
    {
        return [
            'path traversal' => ['../../../../some/obscure/path'],
            'space in between' => ['my client'],
            'wrapped in spaces' => [' myclient '],
            'umlaut' => ['clüent'],
        ];
    }

    /**
     * @param string $value
     * @dataProvider clientIdProvider
     */
    public function testValidArguments(string $value): void
    {
        $clientId = $this->f->clientId($value);
        $this->assertEquals($value, $clientId->toString());
    }

    /**
     * @param string $value
     * @dataProvider invalidClientIdProvider
     */
    public function testInvalidArguments(string $value): void
    {
        try {
            $clientId = $this->f->clientId($value);
            $this->fail('This should not happen');
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testClientIdCannotBeCreatedByAnEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->f->clientId('');
    }
}
