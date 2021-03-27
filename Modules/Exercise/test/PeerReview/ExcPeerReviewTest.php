<?php

use PHPUnit\Framework\TestCase;

/**
 * Test peer reviews
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ExcPeerReviewTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    protected function getDistribution($user_ids, $num_assignments)
    {
        include_once("./Modules/Exercise/PeerReview/class.ExcPeerReviewDistribution.php");
        return new \ILIAS\Exercise\PeerReview\ExcPeerReviewDistribution($user_ids, $num_assignments);
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testDistributionNumberOfPeers()
    {
        $user_ids = [100,200,300,400,500];
        $num_assignments = 3;

        $distribution = $this->getDistribution($user_ids, $num_assignments);

        foreach ($user_ids as $user_id) {
            $this->assertEquals(count($distribution->getPeersOfRater($user_id)), $num_assignments);
        }
    }

    /**
     * Test if each peer is assigned to $num_assignments raters
     */
    public function testDistributionNumberOfRaters()
    {
        $user_ids = [10,20,30,40,50];
        $num_assignments = 4;

        $distribution = $this->getDistribution($user_ids, $num_assignments);

        $peer_raters = [];
        foreach ($user_ids as $user_id) {
            foreach ($distribution->getPeersOfRater($user_id) as $peer) {
                $peer_raters[$peer][$user_id] = $user_id;
            }
        }

        $this->assertEquals(count($peer_raters), count($user_ids));

        foreach ($peer_raters as $peer => $raters) {
            $this->assertEquals(count($raters), $num_assignments);
        }
    }

    /**
     * Test if raters are not assigned as peers to themselves
     */
    public function testDistributionNoSelfAssignment()
    {
        $user_ids = [10,20,30,40,50];
        $num_assignments = 4;

        $distribution = $this->getDistribution($user_ids, $num_assignments);

        foreach ($user_ids as $user_id) {
            foreach ($distribution->getPeersOfRater($user_id) as $peer) {
                $this->assertNotEquals($user_id, $peer);
            }
        }
    }
}
