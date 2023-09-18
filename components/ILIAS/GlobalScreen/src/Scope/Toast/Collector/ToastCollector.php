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

namespace ILIAS\GlobalScreen\Scope\Toast\Collector;

use ILIAS\GlobalScreen\Collector\AbstractBaseCollector;
use ILIAS\GlobalScreen\Scope\Toast\Provider\ToastProvider;
use Iterator;
use ILIAS\GlobalScreen\Scope\Toast\Factory\isStandardItem;

class ToastCollector extends AbstractBaseCollector
{
    /** @var ToastProvider[] */
    private array $providers;
    /** @var isStandardItem[] */
    private array $toasts = [];

    /**
     * @param ToastProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
        $this->collectOnce();
    }

    /**
     * @return Iterator <\ILIAS\GlobalScreen\Scope\Toast\Factory\isItem[]>
     */
    private function returnToastsFromProviders(): Iterator
    {
        foreach ($this->providers as $provider) {
            yield $provider->getToasts();
        }
    }

    public function collectStructure(): void
    {
        $this->toasts = array_merge([], ...iterator_to_array($this->returnToastsFromProviders()));
    }

    public function filterItemsByVisibilty(bool $async_only = false): void
    {
    }

    public function prepareItemsForUIRepresentation(): void
    {
    }

    public function cleanupItemsForUIRepresentation(): void
    {
    }

    public function sortItemsForUIRepresentation(): void
    {
    }

    /**
     * @return isStandardItem[]
     */
    public function getToasts(): array
    {
        return $this->toasts;
    }
}
