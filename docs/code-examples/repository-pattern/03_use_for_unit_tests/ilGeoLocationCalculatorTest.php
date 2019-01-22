<?

class ilGeoLocationCalculatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @small
     */
    public function calculateNearestExpiration_validTime_correctNearestExpired()
    {
        $mocked_repo = \Mockery::mock('ilGeoLocationRepository');
    }

    /**
     * @test
     * @small
     */
    public function calculateNearestExpiration_exiredTimestamp_throwException()
    {
        $mocked_repo = \Mockery::mock('ilGeoLocationRepository');
    }
}