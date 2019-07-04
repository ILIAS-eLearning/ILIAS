<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context;

use ILIAS\Data\ReferenceId;

/**
 * Class ContextRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextRepository
{

    /**
     * @var array
     */
    private static $contexts = [];
    const C_MAIN = 'main';
    const C_DESKTOP = 'desktop';
    const C_REPO = 'repo';
    const C_ADMINISTRATION = 'administration';

    /**
     * @return ToolContext
     */
    public function main() : ToolContext
    {
        return $this->get(BasicToolContext::class, self::C_MAIN);
    }


    /**
     * @return ToolContext
     */
    public function internal() : ToolContext
    {
        return $this->get(BasicToolContext::class, 'internal');
    }


    /**
     * @return ToolContext
     */
    public function external() : ToolContext
    {
        return $this->get(BasicToolContext::class, 'external');
    }


    /**
     * @return ToolContext
     */
    public function desktop() : ToolContext
    {
        return $this->get(BasicToolContext::class, self::C_DESKTOP);
    }


    /**
     * @return ToolContext
     */
    public function repository() : ToolContext
    {
        $context = $this->get(BasicToolContext::class, self::C_REPO);
        $context = $context->withReferenceId(new ReferenceId((int) $_GET['ref_id']));

        return $context;
    }


    /**
     * @return ToolContext
     */
    public function administration() : ToolContext
    {
        return $this->get(BasicToolContext::class, self::C_ADMINISTRATION);
    }


    /**
     * @param string $class_name
     * @param string $identifier
     *
     * @return ToolContext
     */
    private function get(string $class_name, string $identifier)
    {
        if (!isset(self::$contexts[$identifier])) {
            self::$contexts[$identifier] = new $class_name($identifier);
        }

        return self::$contexts[$identifier];
    }
}
