<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal\LightboxPage;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Legacy\Legacy;

/**
 * Class LightboxTextPage
 * @package ILIAS\UI\Implementation\Component\Modal
 * @author Michael Jansen <mjansen@databay.de>
 */
class LightboxTextPage implements LightboxPage
{
    use ComponentHelper;

    /** @var string */
    protected $text = '';

    /** @var string */
    protected $title = '';

    /**
     * @param string $text
     * @param string $title
     */
    public function __construct(string $text, string $title)
    {
        $this->checkStringArg('text', $text);
        $this->checkStringArg('title', $title);
        $this->text = $text;
        $this->title = $title;
    }


    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getComponent()
    {
        return new Legacy($this->text);
    }
}
