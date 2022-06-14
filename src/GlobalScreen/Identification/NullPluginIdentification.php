<?php declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Identification;

/**
 * Class NullPluginIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullPluginIdentification implements IdentificationInterface
{
    private string $internal_identifier;
    private string $identification;
    private string $plugin_id;
    
    /**
     * NullPluginIdentification constructor.
     * @param string $plugin_id
     * @param string $identification
     * @param string $internal_identifier
     */
    public function __construct(string $plugin_id, string $identification = "", string $internal_identifier = "")
    {
        $this->plugin_id = $plugin_id;
        $this->identification = $identification;
        $this->internal_identifier = $internal_identifier;
    }
    
    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return $this->identification;
    }
    
    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        // nothing to do
    }
    
    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        return $this->plugin_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        return $this->internal_identifier;
    }
    
    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->plugin_id;
    }
}
