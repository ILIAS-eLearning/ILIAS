<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\LightboxCardPage;

function show_modal_on_button_click(): string
{
    global $DIC;

    $ui = $DIC->ui()->factory();

    $item = $ui->item()
               ->standard('Some information')
               ->withDescription('A very long text.');


    $another_item = $ui->item()
                       ->standard('Some other information')
                       ->withDescription('Another very long text.')
                       ->withProperties([
                           'Belongs to' => 'No one',
                           'Created on' => 'June the 15th',
                           'Awarded by' => 'John Doe',
                           'Valid until' => 'Forever',
                       ]);

    $card = $ui->card()
               ->standard('A card title')
               ->withSections([$item])
               ->withHiddenSections([$another_item]);
    $box = $ui->modal()->lightboxCardPage($card);
    $modal = $ui->modal()->lightbox($box);

    $button = $ui
        ->button()
        ->standard('Show Modal', '')
        ->withOnClick($modal->getShowSignal());

    return $DIC->ui()->renderer()->render([$button, $modal]);
}
