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

/**
 * ilWorkflowEngineElement Interface is part of the petri net based workflow engine.
 *
 * It describes the mandatory members all elements throughout the workflow engine
 * have in common.
 * This interface is part of all elements, used within the workflows. This excludes
 * the workflow instance itself.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
interface ilWorkflowEngineElement
{
    /**
     * This method returns the context of the element. Due to the hierarchical
     * structure of the workflow engine, this is a reference to the parent object.
     * Using this, the caller can traverse through the tree of elements.
     */
    public function getContext();// TOOD PHP8-REVIEW Missing return type or corresponding PHPDoc comment

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);// TOOD PHP8-REVIEW Missing type hint or corresonding PHPDoc comment

    /**
     * @return mixed
     */
    public function getName();
}
