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

namespace ILIAS\UI\examples\Layout\Page\Standard;

use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Renderer;
use ILIAS\DI\Container;

/**
 * ---
 * description: >
 *   Example for rendering a UI.
 *
 * expected output: >
 *   ILIAS shows a box with a link "See UI in fullscreen-mode". Clicking the link will redirect to a ILIAS standard
 *   page. Clicking onto "Tools" in the navigation tree on the left side will open a slate with dummy buttons.
 *
 *   Identify the main aria roles:
 *   Use "search element" or F12 - a function from the developer tools.
 *   1. Check the HTML element which shows the Page Header including the logo, title and metabar:
 *      The Header is characterized by <header>. Therefore he ARIA landmark role "banner" is available.
 *   2. Check the HTML element which shows the Page Content:
 *      The Content is characterized by <main>. Therefore the ARIA landmark role "main" available.
 *   3. Check the HTML element which shows the Page Footer:
 *      The Footer is characterized by <footer>. Additionally it includes the attribute role="contentinfo". Therefore
 *      the ARIA landmark "contentinfo" is available.
 *   Try other versions as following:
 *   1. The screenreader tells you that you are operating the ARIA landmark role "banner" area. This is specified by the
 *      screenreaders output, e.g. "Banner", "Logo" etc.
 *   2. The screenreader tells you that you are operating the ARIA landmark role "main" area. This is specified by the
 *      screenreaders output, e.g. "Main", "Main Content", etc.
 *   3. The screenreader tells you that you are operating the ARIA landmark role "contentinfo". This is specified by the
 *      screenreaders output, e.g. "Index", "Table of Contents", "content-based information" etc.
 *
 *   Identify metabar aria roles:
 *   Use "search element" or F12- a function from the developer tools.
 *   1. Check the HTML element which shows the whole metabar.
 *      The metabar includes the attribute role="menubar".
 *   2. Check the HTML element which shows a single menu entry in the metabar.
 *      The entry includes the attribute role="menuitem".
 *   3. Check the HTML element which shows the whole slate opened. The slate has to be opened in the metabar.
 *      The slate includes the attribute role="menu".
 *   Try other versions as following:
 *   1. The screenreader tells you that you are operating a menu bar. The output can differ depending on the screenreader.
 *   2. The screenreader tells you that you are operating a menu element. The output can differ depending on the screenreader.
 *   3. The screenreader tells you that you are operating a menu. The output can differ depending on the screenreader.
 *   Please keep in mind the following answer by Timon/Amanda Mace: https://mantis.ilias.de/view.php?id=33915
 *
 *   Identify tree aria roles:
 *   Use "search element" or F12 - a function from the developer tools.
 *   1. Check the HTML element which shows the whole tree.
 *      The tree inludes the attribute role="tree".
 *   2. Check the HTML element which shows a tree node but no sub node.
 *      The tree node includes the attribute role="none".
 *   3. Check the HTML element which shows a tree node and sub nodes.
 *      The tree node includes the attribute role="treeitem".
 *   4. Check the HTML element which shows the group of sub nodes from check 3.
 *      The sub node includes the attribute role="group".
 *   Try other versions as following:
 *   1. The screenreader tells you that you are operating a tree/list. The output can differ depending on the screenreader.
 *   2. The screenreader tells you that you are operating a list element. The output can differ depending on the screenreader.
 *   3. The screenreader tells you that you are operating a tree element. The output can differ depending on the screenreader.
 *   4. The screenreader tells you that you are operating a group. The output can differ depending on the screenreader.
 *
 *   Identify breadcrumb aria roles:
 *   Use "search element" or F12 - a function from the developer tools.
 *   - Check the HTML element which shows the breadcrumbs. The breadcrumb element is named <nav>. Therefore the ARIA landmark
 *     role "navigation" is available.
 *   - Use a screenreader: the screenreader tells you that you are operating the ARIA landmark role "navigation" area. The
 *     output might be something like "Navigation", "Point of Reference" etc.
 *
 *   Mobile Revision Metabar:
 *   - Notification Counter:
 *     - The disclosure glyph has got two counters: on the top the number of new messages in the notification center are
 *        displayed. Below the number of users are displayed, but only those who are visible by the Who-is-online feature.
 *     - Some other versions:
 *        1. The counter does not change.
 *        2. The counter's number changes because a new message is available in the notification center.
 *   - Opening subentries:
 *     Step 1: The metabar's drawer opens including the main entries.
 *     Step 2: The drawer's content is exchanged with the search function.
 *     Step 3: The drawer's content is exchanged with the main entries (e.g. notification center and search).
 *     Step 4: The drawer closes.
 *
 *   Mobile Revision Breadcrumb:
 *   - Using breadcrumbs:
 *     Step 1: The course title will be displayed with a prefixed ">". It's centered in the top of the metabar.
 *     Step 2: A drawer on the top is opened. The complete path/breadcrumb is displayed.
 *     Step 3: The user is redirected to the appropriate page. The drawer closes.
 *
 *   Desktop Revision Metabar:
 *   - Breadcrumb: The user is redirected to the appropriate page.
 *   - Invoke entries:
 *     Step 1: A small "drawer" opens up including the service's contents.
 *     Step 2: The drawer closes.
 *     Step 3: The search drawer opens.
 *     Step 4: The drawer closes.
 * ---
 */
function ui(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard('root', '')->withSize('large');
    $target = new \ILIAS\Data\URI(
        $DIC->http()->request()->getUri()->__toString() . '&new_ui=1'
    );
    return $renderer->render(
        $f->link()->bulky($icon, 'See UI in fullscreen-mode', $target),
    );
}



global $DIC;
$request_wrapper = $DIC->http()->wrapper()->query();
$refinery = $DIC->refinery();

if ($request_wrapper->has('new_ui')
    && $request_wrapper->retrieve('new_ui', $refinery->kindlyTo()->int()) === 1
) {
    \ilInitialisation::initILIAS();
    echo(renderFullDemoPage($DIC));
    exit();
}

function renderFullDemoPage(\ILIAS\DI\Container $dic)
{
    $refinery = $dic->refinery();
    $request_wrapper = $dic->http()->wrapper()->query();

    $f = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();
    $logo = $f->image()->responsive("assets/images/logo/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("assets/images/logo/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = pagedemoCrumbs($f);
    $metabar = pagedemoMetabar($f);
    $mainbar = pagedemoMainbar($f, $renderer);
    $footer = pagedemoFooter($f);
    $content = pagedemoContent($f, $renderer, $mainbar);
    $tc = $dic->ui()->factory()->toast()->container();

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        "./assets/images/logo/favicon.ico",
        $tc,
        $footer,
        'UI PAGE DEMO', //page title
        'ILIAS', //short title
        'Std. Page Demo' //view title
    )
    ->withHeaders(true)
    ->withUIDemo(true);

    return $renderer->render($page);
}

if (isset($request_wrapper) && isset($refinery) && $request_wrapper->has('replaced') && $request_wrapper->retrieve('replaced', $refinery->kindlyTo()->string()) == '1') {
    echo('Helo. Content from RPC.');
    exit();
}

/**
 * Below are helpers for the construction of demo-content
 */
function pagedemoCrumbs($f)
{
    $crumbs = [
        $f->link()->standard("entry1", '#'),
        $f->link()->standard("entry2", '#'),
        $f->link()->standard("entry3", '#'),
        $f->link()->standard("entry4", '#')
    ];
    return $f->breadcrumbs($crumbs);
}

function pagedemoContent(\ILIAS\UI\Factory $f, Renderer $r, MainBar $mainbar): array
{
    $tools = $mainbar->getToolEntries();

    $second_tool = array_values($tools)[1];
    $url = "./components/ILIAS/UI/src/examples/Layout/Page/Standard/ui.php?replaced=1";
    $replace_signal = $second_tool->getReplaceSignal()->withAsyncRenderUrl($url);
    $replace_btn = $f->button()->standard('replace contents in 2nd tool', $replace_signal);

    $engage_signal = $mainbar->getEngageToolSignal(array_keys($tools)[2]);
    $invisible_tool_btn = $f->button()->standard('show the hidden tool', $engage_signal);

    return [
        $f->panel()->standard(
            'Using Signals',
            $f->legacy()->content(
                "This button will replace the contents of the second tool-slate.<br />"
                . "Goto Tools, second entry and click it.<br />"
                . $r->render($replace_btn)
                . "<br><br>This will unhide and activate another tool<br />"
                . $r->render($invisible_tool_btn)
            )
        ),

        $f->panel()->standard(
            'Demo Content 2',
            $f->legacy()->content("some content<br>some content<br>some content<br>x.")
        ),
        $f->panel()->standard(
            'Demo Content 3',
            $f->legacy()->content(loremIpsum())
        ),
        $f->panel()->standard(
            'Demo Content 4',
            $f->legacy()->content("some content<br>some content<br>some content<br>x.")
        )
    ];
}

function pagedemoFooter(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Footer
{
    $df = new \ILIAS\Data\Factory();
    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    return $f->mainControls()->footer()
             ->withAdditionalLink(...$links)
             ->withAdditionalText($text)
             ->withPermanentURL(
                 $df->uri(
                     ($_SERVER['REQUEST_SCHEME'] ?? "http") . '://'
                     . ($_SERVER['SERVER_NAME'] ?? "localhost") . ':'
                     . ($_SERVER['SERVER_PORT'] ?? "80")
                     . ($_SERVER['SCRIPT_NAME'] ?? "") . '?'
                     . ($_SERVER['QUERY_STRING'] ?? "")
                 )
             );
}

function pagedemoMetabar(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\MetaBar
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    $user = $f->button()->bulky($f->symbol()->glyph()->user(), 'User', '#');
    $search = $f->maincontrols()->slate()->legacy(
        'Search',
        $f->symbol()->glyph()->search()->withCounter($f->counter()->status(1)),
        $f->legacy()->content(substr(loremIpsum(), 0, 180))
    );
    $notes = $f->maincontrols()->slate()->legacy(
        'Notification',
        $f->symbol()->glyph()->notification()->withCounter($f->counter()->novelty(3)),
        $f->legacy()->content('<p>some content</p>')
    );

    return $f->mainControls()->metaBar()
             ->withAdditionalEntry('search', $search)
             ->withAdditionalEntry('help', $help)
             ->withAdditionalEntry('notes', $notes)
             ->withAdditionalEntry('user', $user);
}

function pagedemoMainbar(\ILIAS\UI\Factory $f, Renderer $r): MainBar
{
    $tools_btn = $f->button()->bulky(
        $f->symbol()->icon()->custom('./assets/ui-examples/images/Page/grid.svg', ''),
        'Tools',
        '#'
    );

    $mainbar = $f->mainControls()->mainBar()
        ->withToolsButton($tools_btn);

    $entries = [];
    $entries['repository'] = getDemoEntryRepository($f);
    $entries['pws'] = getDemoEntryPersonalWorkspace($f, $r);
    $entries['achievements'] = getDemoEntryAchievements($f);
    $entries['communication'] = getDemoEntryCommunication($f);
    $entries['organisation'] = getDemoEntryOrganisation($f);
    $entries['administration'] = getDemoEntryAdministration($f);

    foreach ($entries as $id => $entry) {
        $mainbar = $mainbar->withAdditionalEntry($id, $entry);
    }

    $tools = getDemoEntryTools($f);

    return $mainbar
        ->withAdditionalToolEntry('tool1', $tools['tool1'], false, $f->button()->close())
        ->withAdditionalToolEntry('tool2', $tools['tool2'])
        ->withAdditionalToolEntry('tool3', $tools['tool3'], true, $f->button()->close())
        ->withAdditionalToolEntry('tool4', $tools['tool4'], false, $f->button()->close());
}


function getDemoEntryRepository(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Slate\Combined
{
    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/layers.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->combined('Repository', $symbol);

    $icon = $f->symbol()->icon()
        ->standard('', '')
        ->withSize('small')
        ->withAbbreviation('X');

    $button = $f->button()->bulky(
        $icon,
        'Button 1',
        './components/ILIAS/UI/src/examples/Layout/Page/Standard/ui.php?new_ui=1'
    );

    $df = new \ILIAS\Data\Factory();
    $url = $df->uri(
        $_SERVER['REQUEST_SCHEME'] . '://'
        . $_SERVER['SERVER_NAME'] . ':'
        . $_SERVER['SERVER_PORT']
        . $_SERVER['SCRIPT_NAME'] . '?'
        . $_SERVER['QUERY_STRING']
    );
    $link1 = $f->link()->bulky($icon, 'Favorites (Link)', $url);
    $link2 = $f->link()->bulky($icon, 'Courses (Link2)', $url);
    $link3 = $f->link()->bulky($icon, 'Groups (Link)', $url);

    $slate = $slate
        ->withAdditionalEntry($button->withLabel('Repository - Home'))
        ->withAdditionalEntry($button->withLabel('Repository - Tree'))
        ->withAdditionalEntry($button->withLabel('Repository - Last visited'))
        ->withAdditionalEntry($link1)
        ->withAdditionalEntry($link2)
        ->withAdditionalEntry($link3)
        ->withAdditionalEntry($button->withLabel('Study Programme'))
        ->withAdditionalEntry($button->withLabel('Own Repository-Objects'));

    foreach (range(1, 20) as $cnt) {
        $slate = $slate->withAdditionalEntry($button->withLabel('fillup ' . $cnt));
    }

    return $slate;
}

function getDemoEntryPersonalWorkspace(\ILIAS\UI\Factory $f, Renderer $r): \ILIAS\UI\Component\MainControls\Slate\Combined
{
    $icon = $f->symbol()->icon()
        ->standard('', '')
        ->withSize('small')
        ->withAbbreviation('X');

    $button = $f->button()->bulky(
        $icon,
        'Button 1',
        './components/ILIAS/UI/src/examples/Layout/Page/Standard/ui.php?new_ui=1'
    );

    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/user.svg', '')
        ->withSize('small');

    $slate = $f->maincontrols()->slate()
        ->combined('Personal Workspace', $symbol);

    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/bookmarks.svg', '')
        ->withSize('small');

    $bookmarks = $f->legacy()->content(implode('<br />', [
        $r->render($f->button()->shy('my bookmark 1', '#')),
        $r->render($f->button()->shy('my bookmark 2', '#'))
    ]));
    $slate_bookmarks = $f->maincontrols()->slate()
        ->legacy('Bookmarks', $symbol, $bookmarks);

    return $slate
        ->withAdditionalEntry($button->withLabel('Overview'))
        ->withAdditionalEntry($slate_bookmarks)
        ->withAdditionalEntry($button->withLabel('Calendar'))
        ->withAdditionalEntry($button->withLabel('Task'))
        ->withAdditionalEntry($button->withLabel('Portfolios'))
        ->withAdditionalEntry($button->withLabel('Personal Resources'))
        ->withAdditionalEntry($button->withLabel('Shared Resources'))
        ->withAdditionalEntry($button->withLabel('Notes'))
        ->withAdditionalEntry($button->withLabel('News'))
        ->withAdditionalEntry($button->withLabel('Background Tasks'))
        ->withAdditionalEntry($slate_bookmarks);
}

function getDemoEntryAchievements(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Slate\Legacy
{
    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/achievements.svg', '')
        ->withSize('small');
    return $f->maincontrols()->slate()->legacy(
        'Achievements',
        $symbol,
        $f->legacy()->content('content: Achievements')
    );
}

function getDemoEntryCommunication(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Slate\Legacy
{
    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/communication.svg', '')
        ->withSize('small');
    return $f->maincontrols()->slate()->legacy(
        'Communication',
        $symbol,
        $f->legacy()->content('content: Communication')
    );
}

function getDemoEntryOrganisation(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Slate\Combined
{
    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/organisation.svg', '')
        ->withSize('small');

    $sf = $f->maincontrols()->slate();
    return $sf->combined('Organisation', $symbol)
              ->withAdditionalEntry(
                  $sf->combined('1', $symbol)
                ->withAdditionalEntry($sf->combined('1.1', $symbol))
                ->withAdditionalEntry(
                    $sf->combined('1.2', $symbol)
                        ->withAdditionalEntry($sf->combined('1.2.1', $symbol))
                        ->withAdditionalEntry($sf->combined('1.2.2', $symbol))
                )
              )
              ->withAdditionalEntry(
                  $sf->combined('2', $symbol)
                ->withAdditionalEntry($sf->combined('2.1', $symbol))
              )
              ->withAdditionalEntry($sf->combined('3', $symbol))
              ->withAdditionalEntry($sf->combined('4', $symbol));
}

function getDemoEntryAdministration(\ILIAS\UI\Factory $f): \ILIAS\UI\Component\MainControls\Slate\Legacy
{
    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/administration.svg', '')
        ->withSize('small');
    return $f->maincontrols()->slate()->legacy(
        'Administration',
        $symbol,
        $f->legacy()->content('content: Administration')
    );
}

function getDemoEntryTools(\ILIAS\UI\Factory $f): array
{
    $tools = [];

    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/question.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Help',
        $symbol,
        $f->legacy()->content('
            <h2>Help</h2>
            <p>
                Some Text for help entry
            </p>
            <p>
                <button onclick="alert(\'helo - tool 1 \');">Some Dummybutton</button>
                <br>
                <button onclick="alert(\'helo - tool 1, button 2 \');">some other dummybutton</button>
            </p>
        ')
    );
    $tools['tool1'] = $slate;

    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/pencil.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Editor',
        $symbol,
        $f->legacy()->content('
            <h2>Editor</h2>
            <p>
                Some Text for editor entry
                <br><br>
                <button onclick="alert(\'helo\');">Some Dummybutton</button>
                <br><br>
                end of tool.
            </p>
        ')
    );
    $tools['tool2'] = $slate;

    $symbol = $f->symbol()->icon()
        ->custom('./assets/ui-examples/images/Page/notebook.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Initially hidden',
        $symbol,
        $f->legacy(loremIpsum())
    );
    $tools['tool3'] = $slate;

    $slate = $f->maincontrols()->slate()->legacy(
        'Closable Tool',
        $symbol,
        $f->legacy(loremIpsum())
    );
    $tools['tool4'] = $slate;


    return $tools;
}

function loremIpsum(): string
{
    return <<<EOT
	<h2>Lorem ipsum</h2>
	<p>
	Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
	tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
	At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,
	no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
	consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
	dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo
	duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus
	est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur
	sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore
	magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo
	dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est
	Lorem ipsum dolor sit amet.
	</p>
	<p>
	Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie
	 consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan
	  et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis
	   dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer
	   adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore
	   magna aliquam erat volutpat.
	</p>
	<p>
	Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
	lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure
	dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore
	eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui
	blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
	</p>
	<p>
	Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming
	id quod mazim placerat facer
	</p>
EOT;
}
