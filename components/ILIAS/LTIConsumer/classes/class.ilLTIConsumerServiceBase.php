<?php

declare(strict_types=1);

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

/**
 * Class ilLTIConsumerServiceBase
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

abstract class ilLTIConsumerServiceBase
{
    public const LTI_VERSION2P0 = 'LTI-2p0';

    public const SERVICE_ENABLED = 1;

    /** lti services (for further use) */
    private array $services;

    /**  ID for the service */
    protected string $id;

    /** Human readable name for the service. */
    protected string $name;

    /** if requests for this service do not need to be signed. */
    protected bool $unsigned;

    /** Tool proxy object for the current service request. */
    private ?stdClass $toolproxy;

    /** LTI type object for the current service request. */
    private ?stdClass $type;

    /** LTI type config array for the current service request. */
    private ?array $typeconfig;

    /** Instances of the resources associated with this service. */
    protected ?array $resources;

    /** cleaned requested resourcePath */
    protected string $resourcePath;

    public function __construct()
    {
        $this->services = array(
          'gradeservice'
        );
        $this->id = '';
        $this->name = '';
        $this->unsigned = false;
        $this->toolproxy = null;
        $this->type = null;
        $this->typeconfig = null;
        $this->resources = null;
        $this->resourcePath = '';
    }

    /**
     * Parse a string for custom substitution parameter variables supported by this service's resources.
     */
    protected function parseValue(string $value): string
    {
        if (empty($this->resources)) {
            $this->resources = $this->getResources();
        }
        if (!empty($this->resources)) {
            foreach ($this->resources as $resource) {
                $value = $resource->parseValue($value);
            }
        }
        return $value;
    }

    /**
     * Get the resources for this service.
     */
    abstract public function getResources(): array;

    /**
     * Set the cleaned resourcePath without service part
     */
    public function setResourcePath(string $resourcePath): void
    {
        $this->resourcePath = $resourcePath;
    }

    /**
     * Get cleaned resourcePath without service part
     */
    public function getResourcePath(): string
    {
        return $this->resourcePath;
    }

    /**
     * Check that the request has been properly signed and is permitted.
     */
    public function checkTool(): ?object
    {
        return ilObjLTIConsumer::verifyToken();
    }

    /**
     * Get lti services (for further use)
     */
    /*
    public function getLtiServices(): array {
        return array();
    }
    */
}
