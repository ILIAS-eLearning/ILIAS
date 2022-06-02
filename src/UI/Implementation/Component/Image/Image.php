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
 
namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class Image
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Image implements C\Image\Image
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    private static array $types = [
            self::STANDARD,
            self::RESPONSIVE
    ];

    private string $type;
    private string $src;
    private string $alt;
    protected ?string $action = '';

    public function __construct(string $type, string $source, string $alt)
    {
        $this->checkArgIsElement("type", $type, self::$types, "image type");

        $this->type = $type;
        $this->src = $source;
        $this->alt = $alt;
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
    public function withSource(string $source) : C\Image\Image
    {
        $clone = clone $this;
        $clone->src = $source;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getSource() : string
    {
        return $this->src;
    }

    /**
     * @inheritdoc
     */
    public function withAlt(string $alt) : C\Image\Image
    {
        $clone = clone $this;
        $clone->alt = $alt;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAlt() : string
    {
        return $this->alt;
    }

    /**
     * @inheritdoc
     */
    public function withAction($action) : C\Image\Image
    {
        $this->checkStringOrSignalArg("action", $action);
        $clone = clone $this;
        if (is_string($action)) {
            $clone->action = $action;
        } else {
            /**
             * @var $action Signal;
             */
            $clone->action = null;
            $clone->setTriggeredSignal($action, "click");
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        if ($this->action !== null) {
            return $this->action;
        }
        return $this->getTriggeredSignalsFor("click");
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal) : C\Clickable
    {
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal) : C\Clickable
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }
}
