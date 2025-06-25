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

namespace ILIAS\Component\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Control\Form\FormInput;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Description;

/**
 * An Activity is an action on the domain layer action of a component.
 *
 * This defines the interface to any activity. When implementing Activities,
 * you should use one of these base classes:
 *
 *
 *
 */
interface Activity
{
    /**
     * Shall return classname of the Activity, wrapped in Name.
     */
    public function getName(): Name;

    public function getType(): ActivityType;

    public function getDescription(): Text\SimpleDocumentMarkdown;

    public function getInputDescription(): FormInput; // might better be ILIAS/UI/Input/Input, but we would need to promote many properties there before.

    public function getOutputDescription(Description\Factory $f): Description\Description;

    /**
     * This shall check if the given user is allowed to perform the activity based
     * on business rules of this component. This shall, for example, check if the
     * given user may add this other user to a course based on RBAC and position
     * permissions, but this shall not check overall business rules such as: root
     * may do everything. This shall not cause any observable side effects.
     *
     * @param mixed $parameters whatever the `FormInput` from `getInputDescription` produces.
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool;

    /**
     * This shall perform the activity. This shall not check if a user is allowed to
     * perform the activity. The returned data should match the Description given by
     * `getOutputDescription`.
     *
     * @throws any SPL Exception (https://www.php.net/manual/en/spl.exceptions.php)
     * @param mixed $parameters whatever the `FormInput` from `getInputDescription` produces.
     */
    public function perform(mixed $parameters): mixed;

    /**
     * Grinds the $raw_parameters through the input description, checks if the users
     * is allowed to perform the action as requested and, if so, then attempts to
     * performs it. Wraps the result and possible errors in the `Result` type.
     */
    public function maybePerformAs(int $usr_id, array $raw_parameters): Result;
}
