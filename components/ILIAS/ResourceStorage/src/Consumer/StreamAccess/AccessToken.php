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

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class AccessToken
{
    protected string $type = 'rid';
    protected int $leased_by = 0;
    protected \DateTimeImmutable $leased_at;
    protected ResourceIdentification $for_rid;
    protected string $stream_uri;
    private Packaging $packagin;

    public function __construct(
        int $leased_by,
        \DateTimeImmutable $leased_at,
        ResourceIdentification $for_rid,
        string $stream_uri
    ) {
        $this->packagin = new Packaging();
        $this->leased_by = $leased_by;
        $this->leased_at = $leased_at;
        $this->for_rid = $for_rid;
        $this->stream_uri = $stream_uri;
    }

    public function leasedBy(): int
    {
        return $this->leased_by;
    }

    public function leasedAt(): \DateTimeImmutable
    {
        return $this->leased_at;
    }

    public function leasedForRid(): ResourceIdentification
    {
        return $this->for_rid;
    }

    public function leasedForStreamUri(): string
    {
        return $this->stream_uri;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function pack(): string
    {
        return $this->packagin->pack($this);
    }

    public function unpack(string $packed): void
    {
        $from = $this->packagin->unpack($packed);
        $this->leased_by = $from->leasedBy();
        $this->leased_at = $from->leasedAt();
        $this->for_rid = $from->leasedForRid();
        $this->stream_uri = $from->leasedForStreamUri();
        $this->type = $from->type();
    }
}
