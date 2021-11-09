<?php

use ILIAS\Data\URI;

function ui()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1';
    $page_demo = $f->button()->primary('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $page_demo
    ]);
}


if ($_GET['new_ui'] == '1') {
    chdir('../../../../../../');
    _initIliasForPreview();

    global $DIC;

    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $breadcrumbs = pagedemoCrumbs($f);
    $metabar = pagedemoMetabar($f);
    $mainbar = pagedemoMainbar($f, $renderer);
    /**
     * You can also activate a tool initially
     * or remove all active states:
    $mainbar = $mainbar->withActive("pws")
         ->withActive("tool2")
         ->withActive($mainbar::NONE_ACTIVE);
     */

    $footer = pagedemoFooter($f);

    $entries = $mainbar->getEntries();
    $tools = $mainbar->getToolEntries();
    $content = pagedemoContent($f, $renderer, $mainbar);

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $footer,
        'UI PAGE DEMO', //page title
        'ILIAS', //short title
        'Std. Page Demo' //view title
    )->withUIDemo(true);

    echo $renderer->render($page);
}


if ($_GET['replaced'] == '1') {
    echo('Helo. Content from RPC.');
    exit();
}

/**
 * Below are helpers for the construction of demo-content
 */

function _initIliasForPreview()
{
    require_once("Services/Init/classes/class.ilInitialisation.php");
    \ilInitialisation::initILIAS();
    global $DIC;
    $DIC->globalScreen()->layout()->meta()->addCss("./templates/default/delos.css");
}

function pagedemoCrumbs($f)
{
    $crumbs = array(
        $f->link()->standard("entry1", '#'),
        $f->link()->standard("entry2", '#'),
        $f->link()->standard("entry3", '#'),
        $f->link()->standard("entry4", '#')
    );
    return $f->breadcrumbs($crumbs);
}

function pagedemoContent($f, $r, $mainbar)
{
    $tools = $mainbar->getToolEntries();

    $second_tool = array_values($tools)[1];
    $url = "./src/UI/examples/Layout/Page/Standard/ui.php?replaced=1";
    $replace_signal = $second_tool->getReplaceSignal()->withAsyncRenderUrl($url);
    $replace_btn = $f->button()->standard('replace contents in 2nd tool', $replace_signal);

    $invisible_tool = array_values($tools)[2];
    $engage_signal = $mainbar->getEngageToolSignal(array_keys($tools)[2]);
    $invisible_tool_btn = $f->button()->standard('show the hidden tool', $engage_signal);

    return array(
        $f->panel()->standard(
            'Using Signals',
            $f->legacy(
                "This button will replace the contents of the second tool-slate.<br />"
                . "Goto Tools, second entry and click it.<br />"
                . $r->render($replace_btn)
                . "<br><br>This will unhide and activate another tool<br />"
                . $r->render($invisible_tool_btn)
            )
        ),

        $f->panel()->standard(
            'Demo Content 2',
            $f->legacy("some content<br>some content<br>some content<br>x.")
        ),
        $f->panel()->standard(
            'Demo Content 3',
            $f->legacy(loremIpsum())
        ),
        $f->panel()->standard(
            'Demo Content 4',
            $f->legacy("some content<br>some content<br>some content<br>x.")
        )
    );
}



function pagedemoFooter($f)
{
    $df = new \ILIAS\Data\Factory();
    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    $footer = $f->mainControls()->footer($links, $text)
        ->withPermanentURL(
            $df->uri(
                $_SERVER['REQUEST_SCHEME'] .
                '://' .
                $_SERVER['SERVER_NAME'] .
                ':' .
                $_SERVER['SERVER_PORT'] .
                $_SERVER['SCRIPT_NAME'] .
                '?' .
                $_SERVER['QUERY_STRING']
            )
        );
    return $footer;
}

function pagedemoMetabar($f)
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    $user = $f->button()->bulky($f->symbol()->glyph()->user(), 'User', '#');
    $search = $f->maincontrols()->slate()->legacy(
        'Search',
        $f->symbol()->glyph()->search()->withCounter($f->counter()->status(1)),
        $f->legacy(substr(loremIpsum(), 0, 180))
    );
    $notes = $f->maincontrols()->slate()->legacy(
        'Notification',
        $f->symbol()->glyph()->notification()->withCounter($f->counter()->novelty(3)),
        $f->legacy('<p>some content</p>')
    );

    $metabar = $f->mainControls()->metabar()
        ->withAdditionalEntry('search', $search)
        ->withAdditionalEntry('help', $help)
        ->withAdditionalEntry('notes', $notes)
        ->withAdditionalEntry('user', $user)
        ;

    return $metabar;
}

function pagedemoMainbar($f, $r)
{
    $tools_btn = $f->button()->bulky(
        $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/grid.svg', ''),
        'Tools',
        '#'
    );
    $more_btn = $f->button()->bulky(
        $f->symbol()->icon()->standard('', ''),
        'more',
        '#'
    );

    $mainbar = $f->mainControls()->mainbar()
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

    $mainbar = $mainbar
        ->withAdditionalToolEntry('tool1', $tools['tool1'], false, $f->button()->close())
        ->withAdditionalToolEntry('tool2', $tools['tool2'])
        ->withAdditionalToolEntry('tool3', $tools['tool3'], true, $f->button()->close())
        ->withAdditionalToolEntry('tool4', $tools['tool4'], false, $f->button()->close());

    return $mainbar;
}


function getDemoEntryRepository($f)
{
    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/layers.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->combined('Repository', $symbol, '');

    $icon = $f->symbol()->icon()
        ->standard('', '')
        ->withSize('small')
        ->withAbbreviation('X');

    $button = $f->button()->bulky(
        $icon,
        'Button 1',
        './src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1'
    );

    $df = new \ILIAS\Data\Factory();
    $url = $df->uri(
        $_SERVER['REQUEST_SCHEME'] .
        '://' .
        $_SERVER['SERVER_NAME'] .
        ':' .
        $_SERVER['SERVER_PORT'] .
        $_SERVER['SCRIPT_NAME'] .
        '?' .
        $_SERVER['QUERY_STRING']
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
        ->withAdditionalEntry($button->withLabel('Own Repository-Objects'))
        ;

    foreach (range(1, 20) as $cnt) {
        $slate = $slate
            ->withAdditionalEntry($button->withLabel('fillup ' . $cnt));
    }

    return $slate;
}

function getDemoEntryPersonalWorkspace($f, $r)
{
    $icon = $f->symbol()->icon()
        ->standard('', '')
        ->withSize('small')
        ->withAbbreviation('X');

    $button = $f->button()->bulky(
        $icon,
        'Button 1',
        './src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1'
    );

    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/user.svg', '')
        ->withSize('small');

    $slate = $f->maincontrols()->slate()
        ->combined('Personal Workspace', $symbol, '');

    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/bookmarks.svg', '')
        ->withSize('small');

    $bookmarks = $f->legacy(implode('<br />', [
        $r->render($f->button()->shy('my bookmark 1', '#')),
        $r->render($f->button()->shy('my bookmark 2', '#'))
    ]));
    $slate_bookmarks = $f->maincontrols()->slate()
        ->legacy('Bookmarks', $symbol, $bookmarks);

    $slate = $slate
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
        ->withAdditionalEntry($slate_bookmarks)
        ;
    return $slate;
}

function getDemoEntryAchievements($f)
{
    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/achievements.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Achievements',
        $symbol,
        $f->legacy('content: Achievements')
    );
    return $slate;
}

function getDemoEntryCommunication($f)
{
    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/communication.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Communication',
        $symbol,
        $f->legacy('content: Communication')
    );
    return $slate;
}

function getDemoEntryOrganisation($f)
{
    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/organisation.svg', '')
        ->withSize('small');

    $sf = $f->maincontrols()->slate();
    $slate = $sf->combined('Organisation', $symbol, '')
        ->withAdditionalEntry(
            $sf->combined('1', $symbol, '')
                ->withAdditionalEntry($sf->combined('1.1', $symbol, ''))
                ->withAdditionalEntry(
                    $sf->combined('1.2', $symbol, '')
                        ->withAdditionalEntry($sf->combined('1.2.1', $symbol, ''))
                        ->withAdditionalEntry($sf->combined('1.2.2', $symbol, ''))
                )
        )
        ->withAdditionalEntry(
            $sf->combined('2', $symbol, '')
                ->withAdditionalEntry($sf->combined('2.1', $symbol, ''))
        )
        ->withAdditionalEntry($sf->combined('3', $symbol, ''))
        ->withAdditionalEntry($sf->combined('4', $symbol, ''))
    ;


    return $slate;
}

function getDemoEntryAdministration($f)
{
    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/administration.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Administration',
        $symbol,
        $f->legacy('content: Administration')
    );
    return $slate;
}

function getDemoEntryTools($f)
{
    $tools = [];

    $symbol = $f->symbol()->icon()
        ->custom('./src/UI/examples/Layout/Page/Standard/question.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Help',
        $symbol,
        $f->legacy('
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
        ->custom('./src/UI/examples/Layout/Page/Standard/pencil.svg', '')
        ->withSize('small');
    $slate = $f->maincontrols()->slate()->legacy(
        'Editor',
        $symbol,
        $f->legacy('
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
        ->custom('./src/UI/examples/Layout/Page/Standard/notebook.svg', '')
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

function loremIpsum() : string
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
