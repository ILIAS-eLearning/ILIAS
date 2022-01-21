<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Modal\LightboxPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Modal\LightboxTextPage as ILightboxTextPage;

/**
 * Class LightboxTextPage
 * @package ILIAS\UI\Implementation\Component\Modal
 * @author Michael Jansen <mjansen@databay.de>
 */
class LightboxTextPage implements LightboxPage, ILightboxTextPage
{
    use ComponentHelper;

    protected string $text;
    protected string $title;

    public function __construct(string $text, string $title)
    {
        $this->text = $text;
        $this->title = $title;
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
    public function getComponent() : C\Legacy\Legacy
    {
        return new Legacy($this->text, new SignalGenerator());
    }
}
