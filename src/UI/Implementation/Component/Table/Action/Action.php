<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Action;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Table\Action as I;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

abstract class Action implements I\Action
{
    use ComponentHelper;
    /**
     * JS needs to know about the type of an action
     * and where to find the options (in case of signal)
     * Theses constants are passed to il.UI.table.data.init
     */
    public const OPT_ACTIONID = 'actId';
    public const OPT_ROWID = 'rowid';

    /**
     * @var Signal|URI $target
     */
    protected $target;
    protected bool $async = false;

    protected string $label;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $row_id_parameter;
    
    public function __construct(
        string $label,
        URLBuilder $url_builder,
        URLBuilderToken $row_id_parameter
    ) {
        $this->target = $url_builder->buildURI();
        $this->label = $label;
        $this->url_builder = $url_builder;
        $this->row_id_parameter = $row_id_parameter;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return Signal|URI
     */
    public function getTarget()
    {
        return $this->target;
    }

    public function withSignalTarget(Signal $target): self
    {
        $clone = clone $this;
        $clone->target = $target;
        return $clone;
    }

    public function withAsync(bool $async = true): self
    {
        $clone = clone $this;
        $clone->async = $async;
        return $clone;
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function withRowId(string $row_id): self
    {
        $clone = clone $this;
        $target = $clone->getTarget();

        if ($target instanceof Signal) {
            $target->addOption('rowid', $row_id);
        }
        if ($target instanceof URI) {
            $target = $this->url_builder->withParameter(
                $this->row_id_parameter,
                [$row_id]
            )
            ->buildURI();
        }
        $clone->target = $target;
        return $clone;
    }

    public function getURLBuilderJS(): string
    {
        return $this->url_builder->renderObject([$this->row_id_parameter]);
    }
    public function getURLBuilderTokensJS(): string
    {
        return $this->url_builder->renderTokens([$this->row_id_parameter]);
    }
}
