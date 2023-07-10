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
 */

declare(strict_types=1);

/**
 * Presentation of ecs uril (http://...campusconnect/courselinks)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSUriList
{
    public array $uris = array();

    /**
     * Add uri
     */
    public function add(string $a_uri, int $a_link_id): void
    {
        $this->uris[$a_link_id] = $a_uri;
    }

    /**
     * Get link ids
     */
    public function getLinkIds(): array
    {
        return array_keys($this->uris);
    }

    /**
     * Get uris
     */
    public function getUris(): array
    {
        return $this->uris;
    }
}
