<?

use PHPUnit\Framework\TestCase;


class ilGeoLocationCalculatorTest extends TestCase
{

    /**
     * @test
     * @small
     */
    public function calculateNearestExpiration_validTime_correctNearestExpired()
    {
        // Arrange
        $obj1 = new ilObjGeoLocation(1, "older", "", "", microtime() - 1000);
        $obj2 = new ilObjGeoLocation(1, "newer", "", "", microtime());
        $mocked_repo = $this->createMock(ilGeoLocationRepository::class);
        $mocked_repo->expects($this->once())
                    ->method('getGeoLocationsByCoordinates')
                    ->with($this->equalTo("48° 52' 0\" N", "2° 20' 0\" E")
                    ->will($this->returnValue(array($obj1, $obj2)));
        $calc = new ilGeoLocationCalculator($mocked_repo);

        // Act
        $result = $calc->calculateNearestExpiration(array("48° 52' 0\" N", "2° 20' 0\" E")) 
        
        // Assert
        $this->assertEqual($result, $obj2)
    }
}