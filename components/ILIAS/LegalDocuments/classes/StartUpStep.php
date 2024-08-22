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

namespace ILIAS\LegalDocuments;

use ILIAS\LegalDocuments\Intercept\LazyIntercept;
use ILIAS\LegalDocuments\Intercept\NullIntercept;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\UI\NotImplementedException;
use ILIAS\Init\StartupSequence\StartUpSequenceStep;
use ilCtrl;
use ilObjLegalDocumentsGUI;
use ILIAS\UI\Component\Component;
use Closure;
use ilRepositoryGUI;

class StartUpStep extends StartUpSequenceStep
{
    /** @var list<Intercept> */
    private readonly array $all;
    private readonly Intercept $current;

    public function __construct(private readonly ilCtrl $ctrl, Conductor $legal_documents)
    {
        $this->all = $legal_documents->intercepting();
        $this->current = new LazyIntercept($this->findCurrent(...));
    }

    public function shouldStoreRequestTarget(): bool
    {
        return true;
    }

    public function shouldInterceptRequest(): bool
    {
        return $this->current->intercept();
    }

    public function execute(): void
    {
        $target = $this->current->target();
        $this->ctrl->setParameterByClass($target->guiName(), 'id', $this->current->id());
        $this->ctrl->redirectToURL($this->ctrl->getLinkTargetByClass($target->guiPath(), $target->command()));
    }

    public function isInFulfillment(): bool
    {
        return in_array(
            strtolower($this->ctrl->getCmdClass() ?? ''),
            $this->allInterceptingPaths(),
            true
        );
    }

    /**
     * @return list<string>
     */
    private function allInterceptingPaths(): array
    {
        return array_map(
            fn($intercept) => strtolower($intercept->target()->guiName()),
            array_filter($this->all, fn($i) => $i->intercept())
        );
    }

    private function findCurrent()
    {
        return $this->find(
            fn($x) => $x->intercept(),
            $this->all
        )->except(
            fn() => new Ok(new NullIntercept())
        )->value();
    }

    private function find($predicate, $array)
    {
        foreach ($array as $x) {
            if ($predicate($x)) {
                return new Ok($x);
            }
        }
        return new Error('Not found.');
    }
}
