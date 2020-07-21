<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Modal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Button;

/**
 * Footer
 */
class Footer implements MainControls\Footer
{
    use ComponentHelper;

    private $text = '';

    private $links = [];

    /** @var array<Modal\RoundTrip, Button\Shy>[] */
    private $modalsWithTriggers = [];

    /**
     * @var string
     */
    protected $permanent_url = "";

    public function __construct(array $links, string $text = '')
    {
        $types = [\ILIAS\UI\Component\Link\Link::class,];
        $this->checkArgListElements('links', $links, $types);
        $this->links = $links;
        $this->text = $text;
    }

    public function getLinks() : array
    {
        return $this->links;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function withPermanentURL(\ILIAS\Data\URI $url) : MainControls\Footer
    {
        $clone = clone $this;
        $clone->permanent_url = $url;
        return $clone;
    }

    public function getPermanentURL()
    {
        return $this->permanent_url;
    }

    public function getModals() : array
    {
        return $this->modalsWithTriggers;
    }

    public function withAdditionalModalAndTrigger(
        Modal\RoundTrip $roundTripModal,
        Button\Shy $shyButton
    ) : \ILIAS\UI\Component\MainControls\Footer {
        $clone = clone $this;
        $clone->modalsWithTriggers[] = [$roundTripModal, $shyButton];
        return $clone;
    }
}
