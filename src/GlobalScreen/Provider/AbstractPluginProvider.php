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
namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\PluginIdentificationProvider;

/**
 * Class AbstractProvider
 * @package ILIAS\GlobalScreen\Provider
 */
abstract class AbstractPluginProvider extends AbstractProvider implements PluginProvider
{
    /**
     * @var \ILIAS\GlobalScreen\Identification\PluginIdentificationProvider
     */
    private $identification_provider;

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        parent::__construct($dic);
        $this->identification_provider = $dic->globalScreen()->identification()->plugin($this->getPluginID(), $this);
    }

    /**
     * @inheritDoc
     */
    abstract public function getPluginID() : string;

    /**
     * @inheritDoc
     */
    public function id() : PluginIdentificationProvider
    {
        return $this->identification_provider;
    }
}
