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
        $mocked_repo = $this->createMock(ilGeoLocationRepository::class);
        
    }

    /**
     * @test
     * @small
     */
    public function calculateNearestExpiration_exiredTimestamp_throwException()
    {
        $mocked_repo = $this->createMock(ilGeoLocationRepository::class);
    }
}