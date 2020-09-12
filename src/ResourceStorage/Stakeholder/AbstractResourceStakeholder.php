<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\Stakeholder;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class AbstractResourceStakeholder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractResourceStakeholder implements ResourceStakeholder
{

    /**
     * @var string
     */
    private $provider_name_cache = '';


    /**
     * @inheritDoc
     */
    public function getFullyQualifiedClassName() : string
    {
        return static::class;
    }


    /**
     * @inheritDoc
     */
    public function isResourceInUse(ResourceIdentification $identification) : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function resourceHasBeenDeleted(ResourceIdentification $identification) : void
    {
        // TODO: Implement resourceHasBeenDeleted() method.
    }


    /**
     * @inheritDoc
     */
    public function getOwnerOfResource(ResourceIdentification $identification) : int
    {
        return 6;
    }


    /**
     * @inheritDoc
     */
    public function getConsumerNameForPresentation() : string
    {
        if ($this->provider_name_cache !== '' && is_string($this->provider_name_cache)) {
            return $this->provider_name_cache;
        }
        $reflector = new \ReflectionClass($this);

        $re = "/.*[\\\|\\/](?P<provider>(Services|Modules)[\\\|\\/].*)[\\\|\\/]classes/m";

        preg_match($re, str_replace("\\", "/", $reflector->getFileName()), $matches);

        $this->provider_name_cache = isset($matches[1]) ? is_string($matches[1]) ? $matches[1] : self::class : self::class;

        return $this->provider_name_cache;
    }
}
