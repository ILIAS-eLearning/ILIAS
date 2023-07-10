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

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ilDclFileFieldHelper
{
    private function valueToRID(?string $value): ?ResourceIdentification
    {
        if ($value !== null && ($rid = $this->irss->manage()->find($value)) !== null) {
            return $rid;
        }
        return null;
    }

    private function valueToFileTitle(?string $value): string
    {
        return $this->valueToCurrentRevision($value)?->getTitle() ?? '';
    }

    private function valueToCurrentRevision(?string $value): ?Revision
    {
        $rid = $this->valueToRID($value);
        if ($rid !== null) {
            return $this->irss->manage()->getCurrentRevision($rid);
        }

        return null;
    }

    private function valueToResource(?string $value): ?StorableResource
    {
        $rid = $this->valueToRID($value);
        if ($rid !== null) {
            return $this->irss->manage()->getResource($rid);
        }

        return null;
    }
}
