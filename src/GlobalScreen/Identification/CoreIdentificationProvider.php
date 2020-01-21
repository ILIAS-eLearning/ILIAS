<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class CoreIdentificationProvider
 *
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreIdentificationProvider extends AbstractIdentificationProvider implements IdentificationProviderInterface
{

    /**
     * @inheritdoc
     */
    public function identifier(string $identifier_string) : IdentificationInterface
    {
        if (isset(self::$instances[$identifier_string])) {
            return self::$instances[$identifier_string];
        }

        $core_identification = new CoreIdentification($identifier_string, $this->class_name, $this->serializer, $this->provider->getProviderNameForPresentation());
        $this->map->addToMap($core_identification);

        return self::$instances[$identifier_string] = $core_identification;
    }
}
