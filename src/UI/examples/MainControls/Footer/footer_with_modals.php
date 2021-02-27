<?php
declare(strict_types=1);
namespace ILIAS\UI\examples\MainControls\Footer;

function footer_with_modals()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    $footer = $f->mainControls()->footer($links, $text);

    $roundTripModal = $f->modal()->roundtrip('Withdrawal of Consent', $f->legacy('Withdrawal of Consent ...'));
    $shyButton = $f->button()->shy('Terms Of Service', '#');
    $footer = $footer->withAdditionalModalAndTrigger($roundTripModal, $shyButton);

    return $renderer->render($footer);
}
