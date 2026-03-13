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

namespace ILIAS\UI\examples\Layout\Page\Mail;

use ILIAS\Data\Factory;
use ILIAS\Data\URI;
use ILIAS\DI\Container;
use ilInitialisation;

/**
 * ---
 * description: >
 *   Example for rendering a Mail Page.
 *
 * expected output: >
 *   ILIAS shows a mail page with a header which contains a logo and an installation-text.
 *   The content of the page is a headline and a paragraph.
 *   The footer contains an installation-text and a link to the ILIAS website.
 * ---
 */
function base(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $factory->symbol()->icon()->standard('root', '')->withSize('large');
    $target = new URI(
        $DIC->http()->request()->getUri()->__toString() . '&new_ui=1'
    );

    return $renderer->render(
        $factory->link()->bulky($icon, 'See UI in fullscreen-mode', $target),
    );
}

global $DIC;
$request_wrapper = $DIC->http()->wrapper()->query();
$refinery = $DIC->refinery();

if ($request_wrapper->has('new_ui')
    && $request_wrapper->retrieve('new_ui', $refinery->kindlyTo()->int()) === 1
) {
    ilInitialisation::initILIAS();
    echo(renderFullDemoPage($DIC));
    exit();
}

function renderFullDemoPage(Container $dic): string
{
    $factory = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();
    $dataFactory = new Factory();

    $page = $factory->layout()->page()->mail(
        'assets/ui-examples/css/mail_examples.css',
        'data:image/svg+xml;base64,' . base64_encode(file_get_contents('assets/images/logo/HeaderIcon.svg')),
        'ILIAS e-Learning',
        $factory->legacy()->content('<h1>Mail Page Content</h1><p>Dear John Doe, ...</p>'),
        $dataFactory->link('https://www.ilias.de', $dataFactory->uri('https://www.ilias.de')),
    );

    return $renderer->render($page);
}
