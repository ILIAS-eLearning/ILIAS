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
 
use PHPUnit\Framework\TestCase;

/**
 * Test peer reviews
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ExcPeerReviewTest extends TestCase
{
    protected function tearDown() : void
    {
    }

    protected function getDistribution($user_ids, $num_assignments) : \ILIAS\Exercise\PeerReview\ExcPeerReviewDistribution
    {
        return new \ILIAS\Exercise\PeerReview\ExcPeerReviewDistribution($user_ids, $num_assignments);
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testDistributionNumberOfPeers() : void
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
    public function testDistributionNumberOfRaters() : void
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

        $this->assertSameSize($peer_raters, $user_ids);

        foreach ($peer_raters as $raters) {
            $this->assertEquals(count($raters), $num_assignments);
        }
    }

    /**
     * Test if raters are not assigned as peers to themselves
     */
    public function testDistributionNoSelfAssignment() : void
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
