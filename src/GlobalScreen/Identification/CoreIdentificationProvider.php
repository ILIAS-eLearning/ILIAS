<?php namespace ILIAS\GlobalScreen\Identification;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class CoreIdentificationProvider
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
        
        $core_identification = new CoreIdentification(
            $identifier_string,
            $this->class_name,
            $this->serializer,
            $this->provider->getProviderNameForPresentation()
        );
        $this->map->addToMap($core_identification);
        
        return self::$instances[$identifier_string] = $core_identification;
    }
}
