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
namespace ILIAS\GlobalScreen\Client;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;

/**
 * Class ItemState
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ItemState
{
    use Hasher;

    public const LEVEL_OF_TOOL = 1;
    public const COOKIE_NS_GS = 'gs_active_items';
    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
     */
    private $identification;
    /**
     * @var mixed[]
     */
    private $storage;

    /**
     * @var \ILIAS\HTTP\Wrapper\WrapperFactory
     */
    protected $wrapper;
    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery;

    /**
     * ItemState constructor.
     *
     * @param IdentificationInterface $identification
     */
    public function __construct(IdentificationInterface $identification)
    {
        $this->identification = $identification;
        $this->storage = $this->getStorage();
        \ilInitialisation::initILIAS();
        global $DIC;
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
    }

    public function isItemActive() : bool
    {
        $hash = $this->hash($this->identification->serialize());
        $b = isset($this->storage[$hash]) && $this->storage[$hash] == true;

        return $b;
    }

    /**
     * @return mixed[]
     */
    public function getStorage() : array
    {
        static $json_decode;
        if (!isset($json_decode)) {
            $cookie_value = $this->wrapper->cookie()->has(self::COOKIE_NS_GS)
                ? $this->wrapper->cookie()->retrieve(self::COOKIE_NS_GS, $this->refinery->to()->string())
                : '{}';

            $json_decode = json_decode($cookie_value, true, 512);
            $json_decode = is_array($json_decode) ? $json_decode : [];
        }

        return $json_decode;
    }
}
