<?php namespace ILIAS\GlobalScreen\ScreenContext;

use ILIAS\Data\ReferenceId;

/**
 * Class ContextRepository
 *
 * The Collection of all available Contexts in the System. You can use them in
 * your @see ScreenContextAwareProvider to announce you are interested in.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextRepository
{

    /**
     * @var array
     */
    private $contexts = [];
    const C_MAIN = 'main';
    const C_DESKTOP = 'desktop';
    const C_REPO = 'repo';
    const C_ADMINISTRATION = 'administration';
    const C_LTI = 'lti';


    /**
     * @return ScreenContext
     */
    public function main() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, self::C_MAIN);
    }


    /**
     * @return ScreenContext
     */
    public function internal() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, 'internal');
    }


    /**
     * @return ScreenContext
     */
    public function external() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, 'external');
    }


    /**
     * @return ScreenContext
     */
    public function desktop() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, self::C_DESKTOP);
    }


    /**
     * @return ScreenContext
     */
    public function repository() : ScreenContext
    {
        $context = $this->get(BasicScreenContext::class, self::C_REPO);
        $context = $context->withReferenceId(new ReferenceId((int) ($_GET['ref_id'] ?? 0)));

        return $context;
    }


    /**
     * @return ScreenContext
     */
    public function administration() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, self::C_ADMINISTRATION);
    }


    /**
     * @return ScreenContext
     */
    public function lti() : ScreenContext
    {
        return $this->get(BasicScreenContext::class, self::C_LTI);
    }


    /**
     * @param string $class_name
     * @param string $identifier
     *
     * @return ScreenContext
     */
    private function get(string $class_name, string $identifier)
    {
        if (!isset($this->contexts[$identifier])) {
            $this->contexts[$identifier] = new $class_name($identifier);
        }

        return $this->contexts[$identifier];
    }
}
