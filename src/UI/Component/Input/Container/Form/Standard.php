<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Input\Container\Form;

/**
 * This describes a standard form.
 */
interface Standard extends Form
{
    /**
     * Get the URL this form posts its result to.
     */
    public function getPostURL(): string;

    /**
     * Sets the caption of the submit button of the form
     */
    public function withSubmitCaption(string $caption): Standard;

    /**
     * Gets submit caption of the form
     */
    public function getSubmitCaption(): ?string;
}
