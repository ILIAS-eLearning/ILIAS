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
 
namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\MainControls\Slate\Prompt;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * MetaBar
 */
class MetaBar implements MainControls\MetaBar
{
    use ComponentHelper;
    use JavaScriptBindable;

    private SignalGeneratorInterface $signal_generator;
    private Signal $entry_click_signal;
    private Signal $disengage_all_signal;

    /**
     * @var array<string, Button\Bulky|Link\Bulky|Slate>
     */
    protected array $entries;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    public function getEntries() : array
    {
        return $this->entries;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry(string $id, $entry) : MainControls\MetaBar
    {
        $classes = [Button\Bulky::class, Link\Bulky::class, Slate::class];
        $check = [$entry];
        $this->checkArgListElements("Bulky Button, Bulky Link or Slate", $check, $classes);

        $clone = clone $this;
        $clone->entries[$id] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEntryClickSignal() : Signal
    {
        return $this->entry_click_signal;
    }

    /**
     * @inheritdoc
     */
    public function getDisengageAllSignal() : Signal
    {
        return $this->disengage_all_signal;
    }

    /**
     * Set the signals for this component
     */
    protected function initSignals() : void
    {
        $this->entry_click_signal = $this->signal_generator->create();
        $this->disengage_all_signal = $this->signal_generator->create();
    }

    public function withClearedEntries() : MainControls\MetaBar
    {
        $clone = clone $this;
        $clone->entries = [];
        return $clone;
    }
}
