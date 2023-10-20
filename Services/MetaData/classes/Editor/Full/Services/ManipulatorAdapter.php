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

namespace ILIAS\MetaData\Editor\Full\Services;

use ILIAS\MetaData\Editor\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Editor\Http\RequestForFormInterface;

class ManipulatorAdapter
{
    protected ManipulatorInterface $manipulator;
    protected FormFactory $form_factory;
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        ManipulatorInterface $manipulator,
        FormFactory $form_factory,
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->manipulator = $manipulator;
        $this->form_factory = $form_factory;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
    }

    public function prepare(
        SetInterface $set,
        PathInterface $path
    ): SetInterface {
        return $this->manipulator->addScaffolds($set, $path);
    }

    /**
     * Returns false if the data from the request is invalid.
     */
    public function createOrUpdate(
        SetInterface $set,
        PathInterface $base_path,
        PathInterface $action_path,
        RequestForFormInterface $request
    ): bool {
        $action_element = $this->navigator_factory->navigator($action_path, $set->getRoot())
                                                  ->lastElementAtFinalStep();
        $form = $this->form_factory->getCreateForm(
            $base_path,
            $action_element,
        );
        $data = [];
        if (
            !empty($form->getInputs()) &&
            !($data = $request->applyRequestToForm($form)->getData())
        ) {
            return false;
        }

        /**
         * Make sure the element is created/not deleted, even when the form
         * is left empty.
         */
        $set = $this->manipulator->prepareCreateOrUpdate($set, $action_path, '');

        $data = $data[0] ?? [];
        foreach ($data as $path_string => $value) {
            $path = $this->path_factory->fromString($path_string);
            if ($value !== '' && $value !== null) {
                $set = $this->manipulator->prepareCreateOrUpdate($set, $path, $value);
            } else {
                $set = $this->manipulator->prepareDelete($set, $path);
            }
        }
        $this->manipulator->execute($set);
        return true;
    }

    /**
     * Returns a trimmed node path if deleted element was the only one at the end
     * of the path.
     */
    public function deleteAndTrimBasePath(
        SetInterface $set,
        PathInterface $base_path,
        PathInterface $action_path,
    ): PathInterface {
        $set = $this->manipulator->prepareDelete($set, $action_path);
        $this->manipulator->execute($set);

        $base_elements = $this->navigator_factory->navigator($base_path, $set->getRoot())
                                                 ->elementsAtFinalStep();
        $action_element = $this->navigator_factory->navigator($action_path, $set->getRoot())
                                                  ->lastElementAtFinalStep();
        $base_elements = iterator_to_array($base_elements);
        if (count($base_elements) === 1 && $action_element === $base_elements[0]) {
            $base_path = $this->path_factory->toElement(
                $action_element->getSuperElement(),
                true
            );
        }
        return $base_path;
    }
}
