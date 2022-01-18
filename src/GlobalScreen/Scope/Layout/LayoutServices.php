<?php /** @noinspection PhpIncompatibleReturnTypeInspection */

namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\Factory\ModificationFactory;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\SingletonTrait;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class LayoutServices
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{
    use SingletonTrait;

    private MetaContent $meta_content;

    /**
     * LayoutServices constructor.
     */
    public function __construct(string $resource_version)
    {
        $this->meta_content = new MetaContent($resource_version);
    }

    /**
     * @return ModificationFactory
     */
    public function factory() : ModificationFactory
    {
        return $this->get(ModificationFactory::class);
    }

    /**
     * @return MetaContent
     */
    public function meta() : MetaContent
    {
        return $this->meta_content;
    }
}
