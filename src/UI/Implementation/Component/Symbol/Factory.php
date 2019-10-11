<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Symbol\Icon as IIcon;
use ILIAS\UI\Component\Symbol\Glyph as IGlyph;

class Factory implements Component\Symbol\Factory
{

    /**
     * @var Icon\Factory
     */
    protected $icon_factory;

    /**
     * @var Glyph\Factory
     */
    protected $glyph_factory;


    /**
     * @param Icon\Factory $icon_factory
     * @param Glyph\Factory $glyph_factory
     */
    public function __construct(
        Icon\Factory $icon_factory,
        Glyph\Factory $glyph_factory
    ) {
        $this->icon_factory = $icon_factory;
        $this->glyph_factory = $glyph_factory;
    }

    /**
     * @inheritdoc
     */
    public function icon() : IIcon\Factory
    {
        return $this->icon_factory;
    }

    /**
     * @inheritdoc
     */
    public function glyph() : IGlyph\Factory
    {
        return $this->glyph_factory;
    }
}
