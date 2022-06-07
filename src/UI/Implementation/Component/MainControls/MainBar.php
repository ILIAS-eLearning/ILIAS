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
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use InvalidArgumentException;
use LogicException;

/**
 * MainBar
 */
class MainBar implements MainControls\MainBar
{
    use ComponentHelper;
    use JavaScriptBindable;

    public const ENTRY_ACTION_TRIGGER = 'trigger';
    public const ENTRY_ACTION_REMOVE = 'remove';
    public const ENTRY_ACTION_TRIGGER_MAPPED = 'trigger_mapped';
    public const ENTRY_ACTION_TOGGLE_TOOLS = 'toggle_tools';
    public const ENTRY_ACTION_DISENGAGE_ALL = 'disengage_all';
    public const NONE_ACTIVE = '_none';

    /**
     * @var array <string, Bulky|Slate>
     */
    protected array $entries = [];

    /**
     * @var string[]
     */
    private array $initially_hidden_ids = [];

    /**
     * @var array<string, Signal>
     */
    private array $tool_signals = [];

    /**
     * @var array<string, Button\Close>
     */
    private array $close_buttons = [];

    /**
     * @var array <string, Slate>
     */
    private array $tool_entries = [];

    private ?Button\Bulky $tools_button = null;
    private Button\Bulky $more_button;
    private ?string $active = null;
    private string $mainbar_tree_position;
    private SignalGeneratorInterface $signal_generator;
    private Signal $entry_click_signal;
    private Signal $tools_click_signal;
    private Signal $tools_removal_signal;
    private Signal $disengage_all_signal;
    protected Signal $toggle_tools_signal;

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
    public function withAdditionalEntry(string $id, $entry) : MainControls\MainBar
    {
        $classes = [
            Button\Bulky::class,
            Link\Bulky::class,
            MainControls\Slate\Slate::class
        ];
        $check = [$entry];
        $this->checkArgListElements("Bulky or Slate", $check, $classes);

        if (array_key_exists($id, $this->entries)) {
            throw new InvalidArgumentException("The id of this entry is already taken.", 1);
        }

        $clone = clone $this;
        $clone->entries[$id] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getToolEntries() : array
    {
        return $this->tool_entries;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalToolEntry(
        string $id,
        Slate $entry,
        bool $initially_hidden = false,
        Button\Close $close_button = null
    ) : MainControls\MainBar {
        if (!$this->tools_button) {
            throw new LogicException("There must be a tool-button configured to add tool-entries", 1);
        }

        if (array_key_exists($id, $this->tool_entries)) {
            throw new InvalidArgumentException("The id of this entry is already taken.", 1);
        }

        $clone = clone $this;
        $clone->tool_entries[$id] = $entry;
        $signal = $this->signal_generator->create();
        $signal->addOption('entry_id', $id);
        $signal->addOption('action', self::ENTRY_ACTION_TRIGGER_MAPPED);
        $clone->tool_signals[$id] = $signal;

        if ($initially_hidden) {
            $clone->initially_hidden_ids[] = $id;
        }

        if ($close_button) {
            $clone->close_buttons[$id] = $close_button;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withToolsButton(Button\Bulky $button) : MainControls\MainBar
    {
        $clone = clone $this;
        $clone->tools_button = $button;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getToolsButton() : Button\Bulky
    {
        return $this->tools_button;
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
    public function getToolsClickSignal() : Signal
    {
        return $this->tools_click_signal;
    }

    /**
     * @inheritdoc
     */
    public function getToolsRemovalSignal() : Signal
    {
        return $this->tools_removal_signal;
    }

    /**
     * @inheritdoc
     */
    public function getDisengageAllSignal() : Signal
    {
        return $this->disengage_all_signal;
    }

    /**
     * @inheritdoc
     */
    public function getToggleToolsSignal() : Signal
    {
        return $this->toggle_tools_signal;
    }

    /**
     * Set the signals for this component
     */
    protected function initSignals() : void
    {
        $this->entry_click_signal = $this->signal_generator->create();
        $this->tools_click_signal = $this->signal_generator->create();
        $this->tools_removal_signal = $this->signal_generator->create();
        $this->disengage_all_signal = $this->signal_generator->create();
        $this->disengage_all_signal->addOption('action', self::ENTRY_ACTION_DISENGAGE_ALL);
        $this->toggle_tools_signal = $this->signal_generator->create();
        $this->toggle_tools_signal->addOption('action', self::ENTRY_ACTION_TOGGLE_TOOLS);
    }

    public function withResetSignals() : MainControls\MainBar
    {
        $clone = clone $this;
        $clone->initSignals();
        foreach (array_keys($this->tool_entries) as $tool_id) {
            $this->tool_signals[$tool_id] = $this->signal_generator->create();
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActive() : ?string
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function withActive(string $active) : MainControls\MainBar
    {
        $valid_entries = array_merge(
            array_keys($this->entries),
            array_keys($this->tool_entries),
            [self::NONE_ACTIVE]
        );
        if (!in_array($active, $valid_entries)) {
            throw new InvalidArgumentException("Invalid entry to activate: $active", 1);
        }

        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getInitiallyHiddenToolIds() : array
    {
        return array_unique($this->initially_hidden_ids);
    }

    /**
     * @inheritdoc
     */
    public function getEngageToolSignal(string $tool_id) : Signal
    {
        return $this->tool_signals[$tool_id];
    }

    /**
     * @inheritdoc
     */
    public function getCloseButtons() : array
    {
        return $this->close_buttons;
    }


    public function withClearedEntries() : MainControls\MainBar
    {
        $clone = clone $this;
        $clone->entries = [];
        $clone->tool_entries = [];
        return $clone;
    }

    public function getTriggerSignal(
        string $entry_id,
        string $action
    ) : Signal {
        if (!in_array($action, [self::ENTRY_ACTION_TRIGGER, self::ENTRY_ACTION_REMOVE])) {
            throw new InvalidArgumentException("invalid action for mainbar entry: $action", 1);
        }
        $signal = $this->signal_generator->create();
        $signal->addOption('entry_id', $entry_id);
        $signal->addOption('action', $action);
        return $signal;
    }

    public function withMainBarTreePosition(string $tree_pos) : MainBar
    {
        $clone = clone $this;
        $clone->mainbar_tree_position = $tree_pos;
        return $clone;
    }

    public function withMappedSubNodes(callable $f) : MainBar
    {
        $clone = clone $this;

        $counter = 0;
        foreach ($clone->getEntries() as $k => $v) {
            $clone->entries[$k] = $f($counter, $v, false);
            $counter++;
        }

        $counter = 0;
        foreach ($clone->getToolEntries() as $k => $v) {
            $clone->tool_entries[$k] = $f($counter, $v, true);
            $counter++;
        }

        return $clone;
    }
}
