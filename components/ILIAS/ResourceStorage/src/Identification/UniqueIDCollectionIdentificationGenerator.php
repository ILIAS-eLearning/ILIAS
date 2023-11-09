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

namespace ILIAS\ResourceStorage\Identification;

/**
 * Class UniqueIDCollectionIdentificationGenerator
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class UniqueIDCollectionIdentificationGenerator implements CollectionIdentificationGenerator
{
    use UUIDStringTrait;

    public function getUniqueResourceCollectionIdentification(): ResourceCollectionIdentification
    {
        $unique_id = null;
        try {
            $unique_id = $this->factory->uuid4AsString();
        } catch (\Exception $e) {
            throw new \LogicException('Generating uuid failed: ' . $e->getMessage(), $e->getCode(), $e);
        } finally {
            return new ResourceCollectionIdentification($unique_id);
        }
    }

    public function validateScheme(string $existing): bool
    {
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $existing) === 1;
    }
}
