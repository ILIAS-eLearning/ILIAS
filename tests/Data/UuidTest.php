<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;
use ILIAS\Data\UUID\Factory;
use ILIAS\Data\UUID\RamseyUuidWrapper;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class UuidTest extends TestCase
{
    private const VALID_UUID4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    private const UUID4 = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
    private const NO_UUID = 'lorem ipsum dolor';

    /**
     * @doesNotPerformAssertions
     */
    public function test_init() : Factory
    {
        return new Factory();
    }

    /**
     * @depends test_init
     */
    public function test_uuid4() : void
    {
        $factory = new Factory();
        $uuid = $factory->uuid4();

        $this->assertMatchesRegularExpression(self::VALID_UUID4, $uuid->toString());
    }

    /**
     * @depends test_init
     */
    public function test_uuid4_string() : void
    {
        $factory = new Factory();
        $uuid = $factory->uuid4AsString();

        $this->assertIsString($uuid);
        $this->assertMatchesRegularExpression(self::VALID_UUID4, $uuid);
    }

    /**
     * @depends test_init
     */
    public function test_from_string() : void
    {
        $factory = new Factory();
        $uuid = $factory->fromString(self::UUID4);

        $this->assertMatchesRegularExpression(self::VALID_UUID4, $uuid->toString());
        $this->assertEquals(self::UUID4, $uuid->toString());
    }

    /**
     * @depends test_init
     */
    public function test_from_illegal_string() : void
    {
        $this->expectException(InvalidUuidStringException::class);

        $factory = new Factory();
        $factory->fromString(self::NO_UUID);
    }
}
