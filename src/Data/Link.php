<?php declare(strict_types=1);

namespace ILIAS\Data;

/**
 * A Link is the often used combination of a label and an URL.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Link
{
    protected string $label;
    protected URI $url;

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
