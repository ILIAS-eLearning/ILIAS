<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Page\Standard;

use ILIAS\DI\Container;
use Psr\Http\Message\RequestInterface;
use ILIAS\Data\Factory;
use ILIAS\UI\Component\MainControls\MainBar;

function ui_mainbar() : string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/Layout/Page/Standard/ui_mainbar.php?ui_mainbar=1';
    $mainbar = $f->link()->standard('Mainbar', $url);
    $url2 = 'src/UI/examples/Layout/Page/Standard/ui_mainbar.php?ui_mainbar=2';
    $mainbar_combined = $f->link()->standard('Mainbar Combined', $url2);



    return $renderer->render([
        $f->listing()->ordered([$mainbar,$mainbar_combined])
    ]);
}

function getUIMainbar(\ILIAS\UI\Factory $f, \ILIAS\Data\URI $uri, bool $condensed = false) : MainBar
{
    $symbol = $f->symbol()->icon()->standard('rcat', 'Fischotter');
    $link010 = $f->link()->bulky($symbol, '2021 - Fischotter', $uri->withParameter('c', 1));
    $symbol = $f->symbol()->icon()->standard('rcat', 'Maulwurf');
    $link011 = $f->link()->bulky($symbol, '2020 - Maulwurf', $uri->withParameter('c', 2));
    $symbol = $f->symbol()->icon()->standard('rcat', 'Reh');
    $link012 = $f->link()->bulky($symbol, '2019 - Reh', $uri->withParameter('c', 3));

    $symbol = $f->symbol()->icon()->standard('rcat', 'Bachflohkrebs');
    $link020 = $f->link()->bulky($symbol, '2021 - Bachflohkrebs', $uri->withParameter('c', 4));
    $symbol = $f->symbol()->icon()->standard('rcat', 'Wildkatze');
    $link021 = $f->link()->bulky($symbol, '2020 - Wildkatze', $uri->withParameter('c', 5));
    $symbol = $f->symbol()->icon()->standard('rcat', 'Glühwürmchen');
    $link022 = $f->link()->bulky($symbol, '2019 - Glühwürmchen', $uri->withParameter('c', 6));

    $link10 = $f->link()->bulky($symbol, 'Frühbarock', $uri->withParameter('c', 7));
    $link11 = $f->link()->bulky($symbol, 'Hochbarock', $uri->withParameter('c', 8));
    $link12 = $f->link()->bulky($symbol, 'Spätbarock', $uri->withParameter('c', 9));

    $symbol = $f->symbol()->icon()->standard('cat', 'Deutschland');
    $slate01 = $f->mainControls()->slate()->combined('Deutschland', $symbol)
        ->withAdditionalEntry($link010)
        ->withAdditionalEntry($link011)
        ->withAdditionalEntry($link012);

    $contents = <<<EOT
    <p>Leider gibt es im Takatuka Land kein Tier des Jahres.
    <br />
    <b>Aber:</b> Slates in der Main Bar können andere Inhalte als Links enthalten. </p>
    <p>Zum Beispiel könnten sich hier Inhalte wie der Magazinbaum oder der
    Mailbaum, komplexe Elemente wie das Notifikation Center, die Hilfe oder
    auch dieser Text befinden.</p>
    <p> Die Main Bar ist ganz bewusst nicht nur als 'Menü' gedacht sondern dient auch dazu,
    komplexe Bedienelemente darzustellen.</p>
EOT;

    $symbol = $f->symbol()->icon()->standard('cat', 'Takatuka Land');
    $slate02 = $f->mainControls()->slate()->legacy('Takatuka Land', $symbol, $f->legacy($contents));

    $symbol = $f->symbol()->icon()->standard('cat', 'Schweiz');
    $slate03 = $f->mainControls()->slate()->combined('Schweiz', $symbol)
        ->withAdditionalEntry($link020)
        ->withAdditionalEntry($link021)
        ->withAdditionalEntry($link022);

    $symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/layers.svg', '')->withSize('small');
    $slate0 = $f->mainControls()->slate()->combined('Tier des Jahres', $symbol)
        ->withAdditionalEntry($slate01)
        ->withAdditionalEntry($slate02)
        ->withAdditionalEntry($slate03);

    $slate1 = $f->mainControls()->slate()->combined('Barock', $symbol)
        ->withAdditionalEntry($link10)
        ->withAdditionalEntry($link11)
        ->withAdditionalEntry($link12);


    if (!$condensed) {
        $mainbar = $f->mainControls()->mainBar()
            ->withAdditionalEntry('slate0', $slate0)
            ->withAdditionalEntry('slate1', $slate1);

        $tools_btn = $f->button()->bulky(
            $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/grid.svg', ''),
            'Tools',
            '#'
        );
        $mainbar = $mainbar->withToolsButton($tools_btn);

        $symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/question.svg', '')->withSize('small');
        $slate = $f->mainControls()->slate()->legacy('Help', $symbol, $f->legacy('<h2>tool 1</h2><p>Some Text for Tool 1 entry</p>'));
        $tools = ['tool1' => $slate];
        foreach ($tools as $id => $entry) {
            $mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
        }

        return $mainbar;
    }

    $slate_base = $f->mainControls()->slate()->combined('Menu', $symbol)
        ->withAdditionalEntry($slate0)
        ->withAdditionalEntry($slate1);
    return $f->mainControls()->mainBar()
        ->withAdditionalEntry('slate0', $slate_base);
}

function getUIContent(\ILIAS\UI\Factory $f, RequestInterface $request) : array
{
    $params = $request->getQueryParams();
    $cidx = -1;
    if (array_key_exists('c', $params)) {
        $cidx = $params['c'];
    }


    switch ($cidx) {
        case 1:
            $t = 'Tier des Jahres: Fischotter3';
            $c = [
                $f->legacy('<h1>Fischotter</h1><p>Der Fischotter (Lutra lutra) ist ein an das Wasserleben angepasster Marder, der zu den besten Schwimmern unter den Landraubtieren zählt.</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
            break;
        case 2:
            $t = 'Tier des Jahres: Maulwurf';
            $c = [
                $f->legacy('<h1>Maulwurf</h1><p>Der Europäische Maulwurf ist ein mittelgroßer Vertreter der Eurasischen Maulwürfe (Talpa). Er erreicht eine Kopf-Rumpf-Länge von 11,3 bis 15,9 cm, der Schwanz wird 2,5 bis 4,0 cm lang.</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
             break;
        case 3:
            $t = 'Tier des Jahres: Reh';
            $c = [
                $f->legacy('<h1>Reh</h1><p>Das Reh springt hoch,<br> das Reh springt weit.<br> Warum auch nicht? <br>Es hat ja Zeit.</p>')
            ];
            break;
        case 4:
            $t = 'Tier des Jahres: Bachflohkrebs';
            $c = [
                $f->legacy('<h1>Bachflohkrebs</h1><p>Der Bachflohkrebs (Gammarus fossarum) ist ein Flohkrebs aus der Familie der Gammaridae und ein typischer Bachbewohner. <br> Er reagiert als sogenanntes Zeigertier äußerst empfindlich auf Gewässerverschmutzungen.</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
            break;
        case 5:
            $t = 'Tier des Jahres: Wildkatze';
            $c = [
                $f->legacy('<h1>Wildkatze</h1><p>Die Europäische Wildkatze oder Waldkatze (Felis silvestris) ist eine Kleinkatze, die in Europa von der Iberischen Halbinsel bis Osteuropa (westliche Ukraine), in Italien, auf dem Balkan, in Anatolien, im Kaukasus und in den schottischen Highlands vorkommt.</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
            break;
        case 6:
            $t = 'Frühbarock';
            $c = [
                $f->legacy('<h1>Glühwürmchen</h1><p>Der Große Leuchtkäfer bzw. das Große Glühwürmchen oder Große Johannisglühwürmchen (Lampyris noctiluca) ist ein Käfer aus der Familie Leuchtkäfer (Lampyridae).</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
            break;
     
        case 7:
            $t = 'Frühbarock';
            $c = [
                $f->legacy('<h1>Frühbarock</h1><p><b>etwa 1600 bis 1650</b><br>unter italienischer Dominanz, mit etwa Monteverdi, Gabrieli.</p>')
                ,$f->link()->standard("Quelle: Wikipedia", "https://de.wikipedia.org/wiki/Tier_des_Jahres")
            ];
            break;
        case 8:
            $t = 'Hochbarock';
            $c = [$f->legacy('<h1>Hochbarock</h1><p><b>etwa 1650 bis 1710</b><br>Das französische Musikleben des späten 17. Jahrhunderts wurde maßgeblich von Jean-Baptiste Lully (1632–1687) am Hofe Ludwigs XIV. geprägt.</p>')];
            break;
        case 9:
            $t = 'Spätbarock';
            $c = [$f->legacy('<h1>Spätbarock</h1><p><b>etwa 1710 bis 1750</b><br>Entwickelte sich im Hochbarock die Musik noch unabhängig in verschiedenen Regionen Europas, so zeichnete sich der Spätbarock durch eine grenzübergreifende Verbreitung der Stile aus. Im deutschen Raum trieb Georg Philipp Telemann (1681–1767) diese Entwicklung voran und wurde schließlich zur „Ikone“ unter den Tonkünstlern.</p>')];
            break;
     
        default:
            $t = 'Mainbar-Demo';
            $c = [$f->legacy('Dies ist ein reduziertes Beispiel für die Mainbar des UI-Frameworks.')];
    }

    return[$t, $c];
}

//Render Footer in Fullscreen mode
global $DIC;
if (basename($_SERVER["SCRIPT_FILENAME"]) == "ui_mainbar.php") {
    chdir('../../../../../../');
    require_once("libs/composer/vendor/autoload.php");
    \ilInitialisation::initILIAS();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
}


if (isset($request_wrapper) && isset($refinery) && $request_wrapper->has('ui_mainbar')) {
    if ($request_wrapper->retrieve('ui_mainbar', $refinery->kindlyTo()->string()) == '1') {
        echo getUIMainbarExampleCondensed($DIC);
    }
    if ($request_wrapper->retrieve('ui_mainbar', $refinery->kindlyTo()->string()) == '2') {
        echo getUIMainbarExampleFull($DIC);
    }
}

function getURI() : \ILIAS\Data\URI
{
    $df = new Factory();
    return $df->uri(
        ($_SERVER['REQUEST_SCHEME'] ?? "http") . '://'
        . ($_SERVER['SERVER_NAME'] ?? "localhost") . ':'
        . ($_SERVER['SERVER_PORT'] ?? "80")
        . ($_SERVER['SCRIPT_NAME'] ?? "") . '?'
        . ($_SERVER['QUERY_STRING'] ?? "")
    );
}

function getRenderedPage(Container $dic, MainBar $mainbar) : string
{
    $f = $dic->ui()->factory();
    list($page_title, $content) = getUIContent($f, $dic->http()->request());

    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS");

    $breadcrumbs = null;
    $metabar = null;
    $footer = null;
    $short_title = 'DEMO';
    $view_title = 'UI Mainbar';
    $tc = $dic->ui()->factory()->toast()->container();

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        "./templates/default/images/favicon.ico",
        $tc,
        $footer,
        $page_title,
        $short_title,
        $view_title
    )->withUIDemo(true);

    return $dic->ui()->renderer()->render($page);
}

function getUIMainbarExampleFull(Container $dic) : string
{
    return getRenderedPage($dic, getUIMainbar($dic->ui()->factory(), getURI()));
}

function getUIMainbarExampleCondensed(Container $dic) : string
{
    return getRenderedPage($dic, getUIMainbar($dic->ui()->factory(), getURI(), true));
}
