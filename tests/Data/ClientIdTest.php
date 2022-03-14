<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ClientIdTest extends TestCase
{
    /** @var Data\Factory */
    private $f;

    /**
     *
     */
    protected function setUp() : void
    {
        $this->f = new Data\Factory();
    }

    /**
     * @return array[]
     */
    public function clientIdProvider() : array
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
    public function invalidClientIdProvider() : array
    {
        return [
            'path traversal' => ['../../some/obscure/path'],
            'space in between' => ['my client'],
            'wrapped in spaces' => [' myclient '],
            'umlaut' => ['clüent'],
        ];
    }

    /**
     * @param string $value
     * @dataProvider clientIdProvider
     */
    public function testValidArguments(string $value)
    {
        $clientId = $this->f->clientId($value);
        $this->assertEquals($value, $clientId->toString());
    }

    /**
     * @param string $value
     * @dataProvider invalidClientIdProvider
     */
    public function testInvalidArguments(string $value)
    {
        try {
            $clientId = $this->f->clientId($value);
            $this->fail('This should not happen');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testClientIdCannotBeCreatedByAnEmptyString() : void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->f->clientId('');
    }
}
