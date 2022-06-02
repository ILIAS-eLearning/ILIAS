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
 
namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use InvalidArgumentException;

/**
 * Class Legacy
 * @package ILIAS\UI\Implementation\Component\Legacy
 */
class Legacy implements C\Legacy\Legacy
{
    use ComponentHelper;
    use JavaScriptBindable;

    private string $content;
    private SignalGeneratorInterface $signal_generator;
    private array $signal_list;

    public function __construct(string $content, SignalGeneratorInterface $signal_generator)
    {
        $this->checkStringArg("content", $content);

        $this->content = $content;
        $this->signal_generator = $signal_generator;
        $this->signal_list = array();
    }

    /**
     * @inheritdoc
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function withCustomSignal(string $signal_name, string $js_code) : C\Legacy\Legacy
    {
        $clone = clone $this;
        $clone->registerSignalAndCustomCode($signal_name, $js_code);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCustomSignal(string $signal_name) : Signal
    {
        if (!key_exists($signal_name, $this->signal_list)) {
            throw new InvalidArgumentException("Signal with name $signal_name is not registered");
        }

        return $this->signal_list[$signal_name]['signal'];
    }

    /**
     * Get a list of all registered signals and their custom JavaScript code. The list is an associative array, where
     * the key for each item is the given custom name. Each item of this list is an associative array itself.
     *
     * The items in this list have the following structure:
     * item = array (
     *     'signal'  => $signal  : Signal
     *     'js_code' => $js_code : String
     * )
     *
     * @deprecated Should only be used to connect legacy components. Will be removed in the future. Use at your own risk
     */
    public function getAllCustomSignals() : array
    {
        return $this->signal_list;
    }

    /**
     * Registers new signal with its JavaScript code in the signal list
     */
    private function registerSignalAndCustomCode(string $signal_name, string $js_code) : void
    {
        $this->signal_list[$signal_name] = array(
            'signal' => $this->signal_generator->create(),
            'js_code' => $js_code
        );
    }
}
