<?php

declare(strict_types=1);

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

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\LocatorBuilder;
use ILIAS\KioskMode\TOCBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\JavaScriptBindable;

class LSControlBuilder implements ControlBuilder
{
    public const CMD_START_OBJECT = 'start_legacy_obj';
    public const CMD_CHECK_CURRENT_ITEM_LP = 'ccilp';

    /**
     * @var Component[]
     */
    protected array $controls = [];

    /**
     * @var Component[]
     */
    protected array $toggles = [];

    /**
     * @var Component[]
     */
    protected array $mode_controls = [];

    protected ?Component $exit_control = null;
    protected ?Component $previous_control = null;
    protected ?Component $next_control = null;
    protected ?Component $done_control = null;
    protected ?TOCBuilder $toc = null;
    protected ?LocatorBuilder $loc = null;
    protected ?JavaScriptBindable $start = null;
    protected ?string $additional_js = null;
    protected Factory $ui_factory;
    protected LSURLBuilder $url_builder;
    protected ilLanguage $lng;
    protected LSGlobalSettings $global_settings;
    protected LSURLBuilder $lp_url_builder;

    public function __construct(
        Factory $ui_factory,
        LSURLBuilder $url_builder,
        ilLanguage $language,
        LSGlobalSettings $global_settings,
        LSURLBuilder $lp_url_builder
    ) {
        $this->ui_factory = $ui_factory;
        $this->url_builder = $url_builder;
        $this->lng = $language;
        $this->global_settings = $global_settings;
        $this->lp_url_builder = $lp_url_builder;
    }

    /**
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getToggles(): array
    {
        return $this->toggles;
    }

    /**
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getModeControls(): array
    {
        return $this->mode_controls;
    }

    /**
     * @return \ILIAS\UI\Component\Component[]
     */
    public function getControls(): array
    {
        return $this->controls;
    }

    public function getExitControl(): ?Component
    {
        return $this->exit_control;
    }

    public function getPreviousControl(): ?Component
    {
        return $this->previous_control;
    }

    public function getNextControl(): ?Component
    {
        return $this->next_control;
    }

    public function getDoneControl(): ?Component
    {
        return $this->done_control;
    }

    public function getToc(): ?TOCBuilder
    {
        return $this->toc;
    }

    public function getLocator(): ?LocatorBuilder
    {
        return $this->loc;
    }

    public function exit(string $command): ControlBuilder
    {
        if ($this->exit_control) {
            throw new \LogicException("Only one exit-control per view...", 1);
        }
        $cmd = $this->url_builder->getHref($command);

        $label = 'lso_player_suspend';
        if ($command === ilLSPlayer::LSO_CMD_FINISH) {
            $label = 'lso_player_finish';
        }

        $exit_button = $this->ui_factory->button()->bulky(
            $this->ui_factory->symbol()->glyph()->close(),
            $this->lng->txt($label),
            $cmd
        );

        $this->exit_control = $exit_button;
        return $this;
    }

    public function next(string $command, int $parameter = null): ControlBuilder
    {
        if ($this->next_control) {
            throw new \LogicException("Only one next-control per view...", 1);
        }
        $label = $this->lng->txt('lso_player_next');
        $cmd = $this->url_builder->getHref($command, $parameter);
        $btn = $this->ui_factory->button()->standard($label, $cmd);
        if ($command === '') {
            $btn = $btn->withUnavailableAction();
        }
        $this->next_control = $btn;
        return $this;
    }

    public function previous(string $command, int $parameter = null): ControlBuilder
    {
        if ($this->previous_control) {
            throw new \LogicException("Only one previous-control per view...", 1);
        }
        $label = $this->lng->txt('lso_player_previous');
        $cmd = $this->url_builder->getHref($command, $parameter);
        $btn = $this->ui_factory->button()->standard($label, $cmd);
        if ($command === '') {
            $btn = $btn->withUnavailableAction();
        }
        $this->previous_control = $btn;
        return $this;
    }

    public function done(string $command, int $parameter = null): ControlBuilder
    {
        if ($this->done_control) {
            throw new \LogicException("Only one done-control per view...", 1);
        }
        $label = $this->lng->txt('lso_player_done');
        $cmd = $this->url_builder->getHref($command, $parameter);
        $btn = $this->ui_factory->button()->primary($label, $cmd);
        $this->done_control = $btn;
        return $this;
    }

    public function generic(string $label, string $command, int $parameter = null): ControlBuilder
    {
        $cmd = $this->url_builder->getHref($command, $parameter);
        $this->controls[] = $this->ui_factory->button()->standard($label, $cmd);
        return $this;
    }

    public function genericWithSignal(string $label, Signal $signal): ControlBuilder
    {
        $this->controls[] = $this->ui_factory->button()->standard($label, '')
            ->withOnClick($signal);
        return $this;
    }

    public function toggle(string $label, string $on_command, string $off_command): ControlBuilder
    {
        throw new \Exception("NYI: Toggles", 1);
    }

    public function mode(string $command, array $labels): ControlBuilder
    {
        $actions = [];
        foreach ($labels as $parameter => $label) {
            $actions[$label] = $this->url_builder->getHref($command, $parameter);
        }
        $this->mode_controls[] = $this->ui_factory->viewControl()->mode($actions, '');
        return $this;
    }

    public function locator(string $command): LocatorBuilder
    {
        if ($this->loc) {
            throw new \LogicException("Only one locator per view...", 1);
        }
        $this->loc = new LSLocatorBuilder($command, $this);
        return $this->loc;
    }

    public function tableOfContent(
        string $label,
        string $command,
        int $parameter = null,
        $state = null
    ): TOCBuilder {
        if ($this->toc) {
            throw new \LogicException("Only one ToC per view...", 1);
        }
        $this->toc = new LSTOCBuilder($this, $command, $label, $parameter, $state);
        return $this->toc;
    }

    /**
     * Add a "start"-button as primary.
     * This is NOT regular behavior, but a special feature for the LegacyView
     * of LearningSequence's sub-objects that do not implement a KioskModeView.
     *
     * The start-control is exclusively used to open an ILIAS-Object in a new windwow/tab.
     */
    public function start(string $label, string $url, int $obj_id): ControlBuilder
    {
        if ($this->start) {
            throw new \LogicException("Only one start-control per view...", 1);
        }

        $this_cmd = $this->url_builder->getHref(self::CMD_START_OBJECT, 0);
        $lp_cmd = $this->lp_url_builder->getHref(self::CMD_CHECK_CURRENT_ITEM_LP, $obj_id);

        $this->setListenerJS($lp_cmd, $this_cmd);
        $this->start = $this->ui_factory->button()
            ->primary($label, '')
            ->withOnLoadCode(function ($id) use ($url) {
                $interval = $this->global_settings->getPollingIntervalMilliseconds();
                return "$('#$id').on('click', function(ev) {
                    var _lso_win = window.open('$url');
				});
                window._lso_current_item_lp = -1;
                window.setInterval(lso_checkLPOfObject, $interval);
                ";
            });

        return $this;
    }

    public function getStartControl(): ?JavaScriptBindable
    {
        return $this->start;
    }

    public function getAdditionalJS(): ?string
    {
        return $this->additional_js;
    }

    protected function setListenerJS(
        string $check_lp_url,
        string $on_lp_change_url
    ): void {
        $this->additional_js =
<<<JS
function lso_checkLPOfObject()
{
    if(! il.UICore.isPageVisible()) {
        return;
    }

	$.ajax({
		url: "$check_lp_url",
	}).done(function(data) {
		if(window._lso_current_item_lp === -1) {
			window._lso_current_item_lp = data;
		}
		if (window._lso_current_item_lp !== data) {
			location.replace('$on_lp_change_url');
		}
	});
}
JS;
    }
}
