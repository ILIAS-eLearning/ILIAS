<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class MessageBox implements C\MessageBox\MessageBox
{
    use ComponentHelper;

    /**
     * @var array
     */
    private static array $types = [
        self::FAILURE,
        self::SUCCESS,
        self::INFO,
        self::CONFIRMATION
    ];

    private string $type;
    private string $message_text;
    private array $buttons = [];
    private array $links = [];

    public function __construct($type, string $message_text)
    {
        $this->checkArgIsElement("type", $type, self::$types, "message box type");
        $this->type = $type;
        $this->message_text = $message_text;
    }

    /**
     * @inheritdoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMessageText() : string
    {
        return $this->message_text;
    }

    /**
     * @inheritdoc
     */
    public function getButtons() : array
    {
        return $this->buttons;
    }

    /**
     * @inheritdoc
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function withButtons(array $buttons) : C\MessageBox\MessageBox
    {
        $types = array(C\Component::class);
        $this->checkArgListElements("buttons", $buttons, $types);

        $clone = clone $this;
        $clone->buttons = $buttons;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLinks(array $links) : C\MessageBox\MessageBox
    {
        $types = array(C\Component::class);
        $this->checkArgListElements("links", $links, $types);

        $clone = clone $this;
        $clone->links = $links;
        return $clone;
    }
}
