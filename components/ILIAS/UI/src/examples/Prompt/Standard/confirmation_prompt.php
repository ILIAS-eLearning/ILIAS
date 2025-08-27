<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Prompt\Standard;

use Generator;
use ILIAS\Data\Range;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Entity\Entity;
use ILIAS\UI\Component\Listing\Entity\Mapping;
use ILIAS\UI\Component\Listing\Entity\DataRetrieval;
use ILIAS\UI\Component\Listing\Entity\RecordToEntity;

/**
 * ---
 * description: >
 *
 * expected output: >
 * ---
 */
function confirmation_prompt()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];

    $here_uri = $df->uri($DIC->http()->request()->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);

    $record_to_entity = new class () implements RecordToEntity {
        public function map(UIFactory $ui_factory, mixed $record): Entity
        {
            list($abbreviation, $login, $email, $name, $last_seen, $active) = $record;
            $avatar = $ui_factory->symbol()->avatar()->letter($abbreviation);

            return $ui_factory->entity()->standard($name, $avatar)
                ->withMainDetails(
                    $ui_factory->listing()->property()
                        ->withProperty('login', $login)
                        ->withProperty('mail', $email, false)
                );
        }
    };

    $data = new class () implements DataRetrieval {
        protected array $data = [
            ['jw', 'jimmywilson', 'jimmywilson@example.com', 'Jimmy Wilson', '2022-03-15 13:20:10', true],
            ['eb', 'emilybrown', 'emilybrown@example.com', 'Emily Brown', '2022-03-16 10:45:32', false],
            ['ms', 'michaelscott', 'michaelscott@example.com', 'Michael Scott', '2022-03-14 08:15:05', true],
            ['kj', 'katiejones', 'katiejones@example.com', 'Katie Jones', '2022-03-17 15:30:50', true]
        ];

        public function getEntities(
            Mapping $mapping,
            ?Range $range,
            ?array $additional_parameters
        ): Generator {
            foreach ($this->data as $usr) {
                yield $mapping->map($usr);
            }
        }
    };

    $buttons = [$factory->button()->standard('Confirm', '#'), $factory->button()->standard('Cancel', '#')];

    $message = $factory->messageBox()->confirmation('some message box')
        ->withButtons($buttons)
        ->withEntityListing($factory->listing()->entity()->standard($record_to_entity)->withData($data));

    // when expecting a state, we do not want to render other examples
    $example_namespace = ['prompt', 'endpoints'];
    [$url_builder, $endpointtoken] = $url_builder->acquireParameters($example_namespace, 'endpoint');
    $url_builder = $url_builder->withParameter($endpointtoken, 'true');

    // build the prompt
    $query_namespace = ['prompt', 'example_conf'];
    [$url_builder, $token] = $url_builder->acquireParameters($query_namespace, 'show');
    $url_builder = $url_builder->withParameter($token, 'true');
    $prompt = $factory->prompt()->standard($url_builder->buildURI());

    // build the endpoint returning the wrapped message
    $query = $DIC->http()->wrapper()->query();
    if ($query->has($token->getName())) {
        $response = $factory->prompt()->state()->show($message);
        echo $renderer->renderAsync($response);
        exit;
    }

    // a button to open the prompt:
    $show_button = $factory->button()->standard('Show Simple Prompt', $prompt->getShowSignal());

    if (!$query->has($endpointtoken->getName())) {
        return $renderer->render([
            $message,
            $prompt,
            $show_button
        ]);
    }
}
