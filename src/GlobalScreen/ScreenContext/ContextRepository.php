<?php

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

declare(strict_types=1);
namespace ILIAS\GlobalScreen\ScreenContext;

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
     * @var mixed[]
     */
    private $contexts = [];
    private const C_MAIN = 'main';
    private const C_DESKTOP = 'desktop';
    private const C_REPO = 'repo';
    private const C_ADMINISTRATION = 'administration';
    private const C_LTI = 'lti';


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

    private function get(string $class_name, string $identifier) : ScreenContext
    {
        if (!isset($this->contexts[$identifier])) {
            $this->contexts[$identifier] = new $class_name($identifier);
        }

        return $this->contexts[$identifier];
    }
}
