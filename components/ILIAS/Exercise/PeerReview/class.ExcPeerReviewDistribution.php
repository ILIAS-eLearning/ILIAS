<?php

declare(strict_types=1);

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

namespace ILIAS\Exercise\PeerReview;

/**
 * Calculates peer review distribution (rater to peer assignments)
 *
 * This is a simple algorithm, that ensures that each rater has
 * $num_assignments peers to review and each peer has $num_assignments raters.
 *
 * It starts by creating a random order of the users. After that it assignes the
 * next $num_assignments users to a rater with index $i ($i+1 to $i+$numassignments) as peers.
 * (Starting back from 0 if the counter exceeds the number of users)
 *
 * Note that this will not include all theoretical combinations, but the randomization is
 * good enough and the code much easier to maintain.
 *
 *    u0 u1 u2 u3 u4 (raters)
 * u0           2  1
 * u1  1           2
 * u2  2  1
 * u3     2  1
 * u4        2  1
 * (peers)
 *
 * Example above for 5 users and 2 assignments.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ExcPeerReviewDistribution
{
    protected array $user_ids = [];
    protected array $user_order = [];
    protected int $num_assignments;

    /**
     * ExcPeerReviewDistribution constructor.
     * @param int[] $user_ids
     */
    public function __construct(array $user_ids, int $num_assignments)
    {
        $this->user_ids = array_values($user_ids);  // ensure numerical indexing

        // we cannot assign more users to a single user than count($user_ids) - 1
        $num_assignments = min($num_assignments, count($user_ids) - 1);

        // we cannot create a negative number of assignments
        $num_assignments = max($num_assignments, 0);

        $this->num_assignments = $num_assignments;
        $this->initDistribution();
    }

    protected function initDistribution(): void
    {
        $this->user_order = $this->randomUserOrder($this->user_ids);
    }

    /**
     * Random user order
     * @param int[]
     * @return int[]
     */
    protected function randomUserOrder(array $user_ids): array
    {
        $order = [];
        while (count($user_ids) > 0) {
            $next = rand(0, count($user_ids) - 1);
            $order[] = $user_ids[$next];
            unset($user_ids[$next]);
            $user_ids = array_values($user_ids);    // re-index
        }
        return $order;
    }

    public function getUserOrder(): array
    {
        return $this->user_order;
    }

    /**
     * Get peers of rater
     *
     * @return int[]
     */
    public function getPeersOfRater(int $user_id): array
    {
        $peers = [];
        $key = array_search($user_id, $this->user_order);
        if ($key === false) {
            return [];
        }
        for ($j = 1; $j <= $this->num_assignments; $j++) {
            $peer_key = ($key + $j) % (count($this->user_order));
            $peers[] = $this->user_order[$peer_key];
        }
        return $peers;
    }
}
