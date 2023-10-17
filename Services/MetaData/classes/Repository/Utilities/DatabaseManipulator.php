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

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Elements\Markers\Action as MarkerAction;
use ILIAS\MetaData\Repository\Utilities\Queries\DatabaseQuerierInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentRowInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\Action;

class DatabaseManipulator implements DatabaseManipulatorInterface
{
    protected DictionaryInterface $dictionary;
    protected DatabaseQuerierInterface $querier;
    protected AssignmentFactoryInterface $assignment_factory;
    protected \ilLogger $logger;

    public function __construct(
        DictionaryInterface $dictionary,
        DatabaseQuerierInterface $querier,
        AssignmentFactoryInterface $assignment_factory,
        \ilLogger $logger
    ) {
        $this->dictionary = $dictionary;
        $this->querier = $querier;
        $this->assignment_factory = $assignment_factory;
        $this->logger = $logger;
    }

    public function deleteAllMD(RessourceIDInterface $ressource_id): void
    {
        $this->querier->deleteAll($ressource_id);
    }

    public function manipulateMD(
        SetInterface $set
    ): void {
        foreach ($set->getRoot()->getSubElements() as $sub) {
            /**
             * Note that the following is necessary here, since the function needs
             * to run through fully before using the yielded rows, as the rows are
             * filled after being yielded.
             */
            $rows = [];
            foreach ($this->collectAssignmentsFromElementAndSubElements(
                0,
                $sub
            ) as $row) {
                $rows[] = $row;
            }
            foreach ($rows as $row) {
                $this->querier->manipulate(
                    $set->getRessourceID(),
                    $row
                );
            }
        }
    }

    /**
     * @return AssignmentRowInterface[]
     */
    protected function collectAssignmentsFromElementAndSubElements(
        int $depth,
        ElementInterface $element,
        AssignmentRowInterface $current_row = null,
        bool $delete_all = false
    ): \Generator {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }
        $marker = $this->marker($element);
        if (!isset($marker) && !$delete_all) {
            return;
        }
        $id = $element->getMDID();
        $tag = $this->tag($element);
        $table = $tag?->table() ?? '';
        if ($table && $current_row?->table() !== $table) {
            yield $current_row = $this->assignment_factory->row(
                $table,
                is_int($id) ? $id : 0,
                $current_row?->id() ?? 0
            );
        }

        $action = $marker?->action();
        if ($delete_all) {
            $action = MarkerAction::DELETE;
        }
        switch ($action) {
            case MarkerAction::NEUTRAL:
                if ($element->isScaffold()) {
                    return;
                }
                break;

            case MarkerAction::CREATE_OR_UPDATE:
                if (!is_null($tag)) {
                    $this->createOrUpdateElement(
                        $element,
                        $tag,
                        $marker->dataValue(),
                        $current_row
                    );
                }
                break;

            case MarkerAction::DELETE:
                if (!is_null($tag)) {
                    $current_row->addAction($this->assignment_factory->action(
                        Action::DELETE,
                        $tag
                    ));
                }
                $delete_all = true;
        }

        foreach ($element->getSubElements() as $sub) {
            yield from $this->collectAssignmentsFromElementAndSubElements(
                $depth + 1,
                $sub,
                $current_row,
                $delete_all
            );
        }
    }

    protected function createOrUpdateElement(
        ElementInterface $element,
        TagInterface $tag,
        string $data_value,
        AssignmentRowInterface $row
    ): void {
        if (!$element->isScaffold()) {
            $row->addAction($this->assignment_factory->action(
                Action::UPDATE,
                $tag,
                $data_value
            ));
        } else {
            $row->addAction($this->assignment_factory->action(
                Action::CREATE,
                $tag,
                $data_value
            ));
            if (!$row->id()) {
                $row->setId($this->querier->nextID($row->table()));
            }
        }
    }

    protected function tag(
        ElementInterface $element,
    ): ?TagInterface {
        return $this->dictionary->tagForElement($element);
    }

    protected function marker(
        ElementInterface $element
    ): ?MarkerInterface {
        if (!($element instanceof MarkableInterface) || !$element->isMarked()) {
            return null;
        }
        return $element->getMarker();
    }
}
