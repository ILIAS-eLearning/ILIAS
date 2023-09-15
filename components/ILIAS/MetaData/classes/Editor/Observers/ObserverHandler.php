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

namespace ILIAS\MetaData\Editor\Observers;

use ILIAS\MetaData\Paths\PathInterface;

class ObserverHandler implements ObserverHandlerInterface
{
    protected array $observers = [];

    public function addObserver(object $class, string $method, string $element): void
    {
        $this->observers[$element][] = [
            'class' => $class,
            'method' => $method
        ];
    }

    public function callObservers(string $element): void
    {
        foreach ($this->observers[$element] ?? [] as $observer) {
            $class = $observer['class'];
            $method = $observer['method'];

            $class->$method($element);
        }
    }

    public function callObserversByPath(PathInterface $path): void
    {
        $categories = [
            'general' => 'General',
            'lifecycle' => 'Lifecycle',
            'metametadata' => 'MetaMetaData',
            'technical' => 'Technical',
            'educational' => 'Educational',
            'rights' => 'Rights',
            'relation' => 'Relation',
            'annotation' => 'Annotation',
            'classification' => 'Classification'
        ];
        $category = strtolower($path->steps()->current()?->name() ?? '');
        if ($path->isRelative() || !isset($category) || !isset($categories[$category])) {
            throw new \ilMDEditorException(
                'Cannot call observers via relative or invalid path.'
            );
        }
        $this->callObservers($categories[$category]);
    }
}
