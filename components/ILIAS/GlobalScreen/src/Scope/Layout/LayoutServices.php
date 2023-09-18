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
/** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Factory\ModificationFactory;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class LayoutServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{
    private MetaContent $meta_content;
    private ModificationFactory $modification_factory;

    /**
     * LayoutServices constructor.
     */
    public function __construct(string $resource_version)
    {
        $this->meta_content = new MetaContent($resource_version);
        $this->modification_factory = new ModificationFactory();
    }

    /**
     * @return ModificationFactory
     */
    public function factory(): ModificationFactory
    {
        return $this->modification_factory;
    }

    /**
     * @return MetaContent
     */
    public function meta(): MetaContent
    {
        return $this->meta_content;
    }
}
