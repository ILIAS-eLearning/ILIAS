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

use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Component\Entity\EntityRetrieval;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\MessageBox\Factory as MessageBoxFactory;
use ILIAS\UI\Component\Listing\Entity\Factory as ListingEntityFactory;

class Factory implements I\State\Factory
{
    public function __construct(
        private readonly ListingEntityFactory $listing_entity_factory,
        private readonly MessageBoxFactory $messagebox_factory,
        private readonly InputFactory $input_factory,
    ) {
    }

    public function show(I\IsPromptContent $content): State
    {
        return new State($content);
    }

    public function confirm(
        EntityRetrieval $entity_retrieval,
        URLBuilder $post_url,
        URLBuilderToken $post_parameter,
        array $entity_ids,
        string $question,
        string $title,
    ): State {
        $listing_retrieval = new SubsetEntityRetrieval($entity_retrieval, $entity_ids);
        $listing = $this->listing_entity_factory->standard($listing_retrieval);

        $message_box = $this->messagebox_factory->confirmation($question);

        $form = $this->input_factory->container()->form()->standard(
            (string) $post_url->withParameter($post_parameter, $entity_ids)->buildURI(),
            []
        );

        $content = new Confirmation(
            $message_box,
            $listing,
            $form,
            $title,
        );

        return (new State($content))->withTitle($title);
    }

    public function close(): State
    {
        return (new State(null))
            ->withCloseModal(true);
    }

    public function redirect(URI $redirect): State
    {
        return (new State(null))
            ->withRedirect($redirect);
    }
}
