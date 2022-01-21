<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class InterruptiveItem
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class InterruptiveItem implements \ILIAS\UI\Component\Modal\InterruptiveItem
{
    use ComponentHelper;

    protected string $id;
    protected string $title;
    protected string $description;
    protected ?Image $icon;

    public function __construct(string $id, string $title, Image $icon = null, string $description = '')
    {
        $this->id = $id;
        $this->title = $title;
        $this->icon = $icon;
        $this->description = $description;
    }

    /**
     * @inheritdoc
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getIcon() : ?Image
    {
        return $this->icon;
    }
}
