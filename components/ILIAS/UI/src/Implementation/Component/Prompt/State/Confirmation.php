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

namespace ILIAS\UI\Implementation\Component\Prompt\State;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Listing\Entity\Entity as EntityListing;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Prompt\IsPromptContentInternal;

class Confirmation implements IsPromptContentInternal
{
    use ComponentHelper;

    public function __construct(
        private readonly MessageBox $message_box,
        private readonly EntityListing $entity_listing,
        private readonly Form $form,
        private readonly string $title,
    ) {
    }

    public function getMessageBox(): MessageBox
    {
        return $this->message_box;
    }

    public function getEntityListing(): EntityListing
    {
        return $this->entity_listing;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getPromptTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Button[]
     */
    public function getPromptButtons(): array
    {
        return [];
    }
}
