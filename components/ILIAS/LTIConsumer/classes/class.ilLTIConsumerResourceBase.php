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
 * Class ilLTIConsumerResourceBase
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

abstract class ilLTIConsumerResourceBase
{
    /**  HTTP Post method */
    public const HTTP_POST = 'POST';

    /**  HTTP Get method */
    public const HTTP_GET = 'GET';

    /**  HTTP Put method */
    public const HTTP_PUT = 'PUT';

    /**  HTTP Delete method */
    public const HTTP_DELETE = 'DELETE';

    /** Service associated with this resource. */
    private ilLTIConsumerServiceBase $service;

    /** Type for this resource. */
    protected string $type;

    /** ID for this resource. */
    protected string $id;

    /** Template for this resource. */
    protected string $template;

    /** Custom parameter substitution variables associated with this resource. */
    protected array $variables;

    /** Media types supported by this resource. */
    protected array $formats;

    /** HTTP actions supported by this resource. */
    protected array $methods;

    /** Template variables parsed from the resource template. */
    protected array $params;


    /**
     * Class constructor.
     *
     *  Service instance
     */
    public function __construct(ilLTIConsumerServiceBase $service)
    {
        $this->service = $service;
        $this->type = 'RestService';
        $this->id = '';
        $this->template = '';
        $this->variables = array();
        $this->formats = array();
        $this->methods = array();
        $this->params = array();
    }

    /**
     * Get the resource template.
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Get the resource media types.
     */
    public function getFormats(): array
    {
        return $this->formats;
    }

    /**
     * Get the resource methods.
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the resource's service.
     */
    public function getService(): ilLTIConsumerServiceBase
    {
        return $this->service;
    }

    /**
     * Parse the template for variables.
     */
    protected function parseTemplate(): array
    {
        if (empty($this->params)) {
            $this->params = array();
            if (!empty($_SERVER['PATH_INFO'])) {
                $path = explode('/', $this->service->getResourcePath());
                $template = preg_replace('/\([0-9a-zA-Z_\-,\/]+\)/', '', $this->getTemplate());
                $parts = explode('/', $template);
                for ($i = 0; $i < count($parts); $i++) {
                    //if ((substr($parts[$i], 0, 1) == '{') && (substr($parts[$i], -1) == '}')) {
                    if ((str_starts_with($parts[$i], '{')) && (str_ends_with($parts[$i], '}'))) {
                        $value = '';
                        if ($i < count($path)) {
                            $value = $path[$i];
                        }
                        $this->params[substr($parts[$i], 1, -1)] = $value;
                    }
                }
            }
        }
        return $this->params;
    }

    /**
     * Check to make sure the request is valid.
     */
    public function checkTool(array $scopes = array()): ?object
    {
        $token = $this->getService()->checkTool();
        $permittedScopes = $this->getService()->getPermittedScopes();
        if (empty($scopes) || empty(array_intersect($permittedScopes, $scopes))) {
            $token = null;
        }
        return $token;
    }
}
