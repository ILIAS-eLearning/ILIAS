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

namespace ILIAS\User\Profile\Fields\Custom;

use ILIAS\User\Context;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;

interface Type
{
    public function getLabel(Language $lng): string;

    /**
     * MAY return a valid FormInput that will be added to the form to edit/create
     * the custom field to specify the input shown to the user. The Input MUST
     * always return a string value as it will be saved in the table `udf_definition`.
     * Use Transformations to generate it and for validation.
     */
    public function getAdditionalEditFormInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        ?string $data
    ): ?FormInput;

    /**
     * MUST return a ilFormPropertyGUI to be shown to the user when entering the
     * information. If you need complex inputs, you will need to extend
     * ilFormPropertyGUI. This will change when we can move these inputs to the
     * new UI-Components Inputs.
     * You don't need to add a post_var to the input as the User will handle this
     * for you, thus you can also not rely on the post_var anywhere else, as it
     * will be changed.
     */
    public function getLegacyInput(
        Language $lng,
        Context $context,
        array $user_value,
        string $label,
        ?string $data
    ): \ilFormPropertyGUI;

    /**
     * @return array|null Returning null will lead to the deletion of all
     * current values.
     */
    public function prepareUserInputForStorage(mixed $input, ?string $data): ?array;
}
