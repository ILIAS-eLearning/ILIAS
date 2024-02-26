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

use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class ilWACSignedResourceStorage
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWACSignedResourceStorage implements ilWACCheckingClass
{
    private StreamAccess $stream_access;
    private StorageHandlerFactory $storage_handlers;
    private \ILIAS\ResourceStorage\Manager\Manager $manager;

    public function __construct()
    {
        global $DIC;
        $this->stream_access = $DIC[InitResourceStorage::D_STREAM_ACCESS];
        $this->storage_handlers = $DIC[InitResourceStorage::D_STORAGE_HANDLERS];
        $this->manager = $DIC->resourceStorage()->manage();
    }

    public function canBeDelivered(ilWACPath $ilWACPath): bool
    {
        $token = $ilWACPath->getAppendix();
        $token = $this->stream_access->getTokenFactory()->check($token);
        $rid = $this->resolveRidFromStreamURI($token->resolveStream()->getMetadata('uri') ?? '');
        if ($rid === null) {
            return false;
        }

        $resource = $this->manager->getResource($rid);
        foreach ($resource->getStakeholders() as $stakeholder) {
            if ($stakeholder->canBeAccessedByCurrentUser($rid)) {
                return true;
            }
        }

        return false;
    }

    private function resolveRidFromStreamURI(string $uri): ?ResourceIdentification
    {
        return $this->storage_handlers->getRidForURI($uri);
    }
}
