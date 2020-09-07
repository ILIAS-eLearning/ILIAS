<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Map\IdentificationMap;
use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

/**
 * Class AbstractIdentificationProvider
 *
 * @package ILIAS\GlobalScreen\Identification
 */
abstract class AbstractIdentificationProvider implements IdentificationProviderInterface
{

    /**
     * @var IdentificationMap
     */
    protected $map;
    /**
     * @var Provider
     */
    protected $provider;
    /**
     * @var Serializer\SerializerInterface
     */
    protected $serializer;
    /**
     * @var string
     */
    protected $class_name = '';
    /**
     * @var array
     */
    protected static $instances = [];


    /**
     * CoreIdentificationProvider constructor.
     *
     * @param Provider            $provider
     * @param SerializerInterface $serializer
     * @param IdentificationMap   $map
     */
    public function __construct(Provider $provider, SerializerInterface $serializer, IdentificationMap $map)
    {
        $this->map = $map;
        $this->provider = $provider;
        $this->class_name = get_class($provider);
        $this->serializer = $serializer;
        ;
    }


    /**
     * @param string $serialized_string
     *
     * @return IdentificationInterface
     */
    public function fromSerializedString(string $serialized_string) : IdentificationInterface
    {
        if ($this->map->isInMap($serialized_string)) {
            return $this->map->getFromMap($serialized_string);
        }
        $identification = $this->serializer->unserialize($serialized_string);
        $this->map->addToMap($identification);

        return $identification;
    }
}
