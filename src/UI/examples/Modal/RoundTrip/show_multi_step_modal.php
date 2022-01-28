<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\RoundTrip;

use ILIAS\UI\Implementation\Component\ReplaceSignal;

function show_multi_step_modal()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $_SERVER['REQUEST_URI'];

    $page = "";
    if ($request_wrapper->has('page')) {
        $page = $request_wrapper->retrieve('page', $refinery->kindlyTo()->string());
    }
    if ($page == "") {
        $modal = $f->modal()->roundtrip("Modal Title", $f->legacy("b"));
        $asyncUrl = $url . '&page=login&replaceSignal=' . $modal->getReplaceSignal()->getId();
        $modal = $modal->withAsyncRenderUrl($asyncUrl);
        $button = $f->button()->standard("Sign In", '#')
            ->withOnClick($modal->getShowSignal());
        return $r->render([$modal, $button]);
    } else {
        $signalId = "";
        if ($request_wrapper->has('replaceSignal')) {
            $signalId = $request_wrapper->retrieve('replaceSignal', $refinery->kindlyTo()->string());
        }
        $replaceSignal = new ReplaceSignal($signalId);
        $button1 = $f->button()->standard('Login', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=login&replaceSignal=' . $replaceSignal->getId()));
        $button2 = $f->button()->standard('Registration', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=register&replaceSignal=' . $replaceSignal->getId()));

        $modal = null;
        if ($page == "login") {
            $legacy = $f->legacy("<p>The Login Page</p>");
            $modal = $f->modal()->roundtrip("Login", [$button1, $button2, $legacy]);
        }
        if ($page == "register") {
            $legacy = $f->legacy("<p>The Registration Page</p>");
            $modal = $f->modal()->roundtrip("Registration", [$button1, $button2, $legacy]);
        }

        echo $r->renderAsync([$modal]);
        exit;
    }
}
