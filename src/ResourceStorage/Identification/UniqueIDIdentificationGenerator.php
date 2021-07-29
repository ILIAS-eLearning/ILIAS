<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Identification;

use ILIAS\Data\UUID\Factory;

/**
 * Class UniqueIDIdentificationGenerator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class UniqueIDIdentificationGenerator implements IdentificationGenerator
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * UniqueIDIdentificationGenerator constructor.
     */
    public function __construct()
    {
        $this->factory = new Factory();
    }

    /**
     * @return ResourceIdentification
     * @throws \Exception
     */
    public function getUniqueResourceIdentification() : ResourceIdentification
    {
        try {
            $unique_id = $this->factory->uuid4AsString();
        } catch (\Exception $e) {
            throw new \LogicException('Generating uuid failed: ' . $e->getMessage());
        } finally {
            return new ResourceIdentification($unique_id);
        }
    }
}
