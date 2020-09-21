<?php
require_once("libs/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;
use ILIAS\Data\UUID\Factory;
use ILIAS\Data\UUID\RamseyUuidWrapper;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class UuidTest extends TestCase
{
    const VALID_UUID4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    const UUID4 = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
    const NO_UUID = 'lorem ipsum dolor';

    /**
     * @doesNotPerformAssertions
     */
    public function test_init()
    {
        return new Factory();
    }

    /**
     * @depends test_init
     */
    public function test_uuid4()
    {
        $factory = new Factory();
        $uuid = $factory->uuid4();

        $this->assertTrue(get_class($uuid) === RamseyUuidWrapper::class);
        $this->assertEquals(1, preg_match(self::VALID_UUID4, $uuid->toString()));
    }

    /**
     * @depends test_init
     */
    public function test_uuid4_string()
    {
        $factory = new Factory();
        $uuid = $factory->uuid4AsString();

        $this->assertTrue(is_string($uuid));
        $this->assertEquals(1, preg_match(self::VALID_UUID4, $uuid));
    }

    /**
     * @depends test_init
     */
    public function test_from_string()
    {
        $factory = new Factory();
        $uuid = $factory->fromString(self::UUID4);

        $this->assertTrue(get_class($uuid) === RamseyUuidWrapper::class);
        $this->assertEquals(1, preg_match(self::VALID_UUID4, $uuid->toString()));
        $this->assertTrue($uuid->toString() === self::UUID4);
    }

    /**
     * @depends test_init
     */
    public function test_from_illegal_string()
    {
        $this->expectException(InvalidUuidStringException::class);

        $factory = new Factory();
        $factory->fromString(self::NO_UUID);
    }
}