<?php declare(strict_types=1);

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

/**
 * Immutable class for Web Link lists
 * @author Tim Schmitz <schmitz@leifos.de>
 */
class ilWebLinkList extends ilWebLinkBaseList
{
    protected int $webr_id;

    protected DateTimeImmutable $create_date;
    protected DateTimeImmutable $last_update;

    public function __construct(
        int $webr_id,
        string $title,
        ?string $description,
        DateTimeImmutable $create_date,
        DateTimeImmutable $last_update
    ) {
        $this->webr_id = $webr_id;
        $this->create_date = $create_date;
        $this->last_update = $last_update;
        parent::__construct($title, $description);
    }

    public function getWebrId() : int
    {
        return $this->webr_id;
    }

    public function getCreateDate() : DateTimeImmutable
    {
        return $this->create_date;
    }

    public function getLastUpdate() : DateTimeImmutable
    {
        return $this->last_update;
    }
}
