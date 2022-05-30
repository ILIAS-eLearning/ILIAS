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

namespace ILIAS\Awareness\User;

/**
 * User provider
 * @author Alexander Killing <killing@leifos.de>
 */
interface Provider
{
    public function getProviderId() : string;

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle() : string;

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo() : string;

    /**
     * Get initial set of users
     * @param ?int[] $user_ids if not null, only a subset of these IDs should be retrieved
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null) : array;

    /**
     * Is highlighted
     * @return bool return true, if user group should be highlighted (using extra highlighted number)
     */
    public function isHighlighted() : bool;
}
