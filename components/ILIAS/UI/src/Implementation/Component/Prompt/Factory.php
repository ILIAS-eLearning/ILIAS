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

namespace ILIAS\UI\Implementation\Component\Prompt;

use ILIAS\Data\URI;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Component\Entity\EntityRetrieval;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\MessageBox\Factory as MessageBoxFactory;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Listing\Entity\Factory as ListingEntityFactory;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;

class Factory implements I\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        protected State\Factory $state_factory,
        private readonly ListingEntityFactory $listing_entity_factory,
        private readonly MessageBoxFactory $messagebox_factory,
        private readonly InputFactory $input_factory,
    ) {
    }

    public function standard(URI $async_url): Standard
    {
        return new Standard($this->signal_generator, $async_url);
    }

    public function confirmation(
        EntityRetrieval $entity_retrieval,
        URLBuilder $post_url,
        URLBuilderToken $post_parameter,
        array $entity_ids,
        string $question,
        string $title,
    ): Confirmation {
        $listing = $this->listing_entity_factory->standard(
            new SubsetEntityRetrieval($entity_retrieval, $entity_ids)
        );

        return new Confirmation(
            $this->messagebox_factory->confirmation($question),
            $listing,
            $this->entityIdsForm(
                (string) $post_url->deleteParameter($post_parameter)->buildURI(),
                $post_parameter,
                $entity_ids,
            ),
            $title,
            $post_parameter->getName(),
        );
    }

    public function state(): State\Factory
    {
        return $this->state_factory;
    }

    /**
     * @param array<int|string> $entity_ids
     */
    private function entityIdsForm(
        string $post_url,
        URLBuilderToken $post_parameter,
        array $entity_ids,
    ): StandardForm {
        $hidden_inputs = [];
        foreach (array_values($entity_ids) as $index => $entity_id) {
            $hidden_inputs[(string) $index] = $this->input_factory->field()
                ->hidden()
                ->withValue((string) $entity_id);
        }

        return $this->input_factory->container()->form()->standard(
            $post_url,
            [
                $post_parameter->getName() => $this->input_factory->field()
                    ->group($hidden_inputs)
                    ->withDedicatedName($post_parameter->getName()),
            ]
        );
    }
}
