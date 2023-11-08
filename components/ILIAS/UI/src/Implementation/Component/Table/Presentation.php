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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\ViewControl\HasViewControls;
use Closure;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Presentation extends Table implements T\Presentation
{
    use ComponentHelper;
    use HasViewControls;
    use JavaScriptBindable;

    /**
     * @var array<string,mixed>
     */
    private array $environment = [];

    private array $records = [];
    protected Signal $signal_toggle_all;

    public function __construct(
        string $title,
        array $view_controls,
        protected Closure $row_mapping,
        protected SignalGeneratorInterface $signal_generator
    ) {
        parent::__construct($title);
        $this->view_controls = $view_controls;
        $this->signal_toggle_all = $signal_generator->create();
    }

    public function getSignalGenerator(): SignalGeneratorInterface
    {
        return $this->signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function withRowMapping(Closure $row_mapping): T\Presentation
    {
        $clone = clone $this;
        $clone->row_mapping = $row_mapping;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getRowMapping(): Closure
    {
        return $this->row_mapping;
    }

    /**
     * @inheritdoc
     */
    public function withEnvironment(array $environment): T\Presentation
    {
        $clone = clone $this;
        $clone->environment = $environment;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function withData(array $records): T\Presentation
    {
        $clone = clone $this;
        $clone->records = $records;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        return $this->records;
    }

    public function getExpandCollapseAllSignal(): ?Signal
    {
        return $this->signal_toggle_all;
    }
}
