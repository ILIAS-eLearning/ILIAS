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

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class NullRevision
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class BaseRevision implements Revision
{
    private ?string $storage_id = null;
    private ?Token $token = null;
    private ResourceIdentification $identification;

    /**
     * NullRevision constructor.
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
    }

    /**
     * @inheritDoc
     */
    public function getIdentification(): ResourceIdentification
    {
        return $this->identification;
    }

    public function setStorageID(string $storage_id): void
    {
        $this->storage_id = $storage_id;
    }

    public function getStorageID(): string
    {
        return $this->storage_id ?? '';
    }


    public function withToken(Token $token): Revision
    {
        $clone = clone $this;
        $clone->token = $token;
        return $clone;
    }

    public function maybeGetToken(): ?Token
    {
        return $this->token;
    }
}
