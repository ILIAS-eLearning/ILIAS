<?php declare(strict_types=1);

namespace ILIAS\Data;

/**
 * A Link is the often used combination of a label and an URL.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Link
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var URI
     */
    protected $url;

    public function __construct(string $label, URI $url)
    {
        $this->label = $label;
        $this->url = $url;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getURL() : URI
    {
        return $this->url;
    }
}
