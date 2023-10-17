<?php

use PHPUnit\Framework\TestCase;

class ilGeoLocationCalculatorTest extends TestCase
{

    /**
     * @test
     * @small
     */
    public function calculateNearestExpiration_validTime_correctNearestExpired()
    {
        $now = microtime();
        $before = microtime() - 1000;

        // Arrange
        $obj1 = new ilGeoLocation(1, "older", 0, 0, new \DateTimeImmutable($before));
        $obj2 = new ilGeoLocation(1, "newer", 0, 0, new \DateTimeImmutable($now));
        $mocked_repo = $this->createMock(ilGeoLocationRepository::class);
        $mocked_repo->expects($this->once())
                    ->method('getGeoLocationsByCoordinates')
                    ->with(1, 2)
                    ->will($this->returnValue(array($obj1, $obj2)));
        $calc = new ilGeoLocationCalculator($mocked_repo);

        // Act
        $result = $calc->calculateNearestExpiration(1, 2);

        // Assert
        $this->assertEqual($result, $before);
    }
}
