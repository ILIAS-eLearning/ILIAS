<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Identification;

use ILIAS\Data\UUID\Factory;

/**
 * Class UniqueIDIdentificationGenerator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UniqueIDIdentificationGenerator implements IdentificationGenerator
{

    /**
     * @return ResourceIdentification
     * @throws \Exception
     */
    public function getUniqueResourceIdentification() : ResourceIdentification
    {
        $f = new Factory();
        try {
            $unique_id = $f->uuid4AsString();
        } catch (\Exception $e) {
            throw new \LogicException('Generating uuid failed: ' . $e->getMessage());
        } finally {
            return new ResourceIdentification($unique_id);
        }
    }
}
