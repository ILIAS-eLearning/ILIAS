<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Modal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link\Link;
use ILIAS\Data\URI;

class Footer implements MainControls\Footer
{
    use ComponentHelper;

    private string $text;
    private array $links;

    /**
     * @var array<Modal\RoundTrip, Button\Shy>
     */
    private array $modalsWithTriggers = [];
    protected ?URI $permanent_url = null;

    public function __construct(array $links, string $text = '')
    {
        $types = [Link::class,];
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

    public function withPermanentURL(URI $url) : MainControls\Footer
    {
        $clone = clone $this;
        $clone->permanent_url = $url;
        return $clone;
    }

    public function getPermanentURL() : ?URI
    {
        return $this->permanent_url;
    }

    /**
     * @return \ILIAS\UI\Component\Button\Shy[]
     */
    public function getModals() : array
    {
        return $this->modalsWithTriggers;
    }

    public function withAdditionalModalAndTrigger(
        Modal\RoundTrip $roundTripModal,
        Button\Shy $shyButton
    ) : MainControls\Footer {
        $shyButton = $shyButton->withOnClick($roundTripModal->getShowSignal());

        $clone = clone $this;
        $clone->modalsWithTriggers[] = [$roundTripModal, $shyButton];
        return $clone;
    }
}
