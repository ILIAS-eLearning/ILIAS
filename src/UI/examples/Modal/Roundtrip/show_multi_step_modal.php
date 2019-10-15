<?php
function show_multi_step_modal()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $url = $_SERVER['REQUEST_URI'];

    $page = $_GET["page"];
    if ($page == "") {
        $modal = $f->modal()->roundtrip("Modal Title", $f->legacy("b"));
        $asyncUrl = $url . '&page=login&replaceSignal=' . $modal->getReplaceSignal()->getId();
        $modal = $modal->withAsyncRenderUrl($asyncUrl);
        $button = $f->button()->standard("Sign In", '#')
            ->withOnClick($modal->getShowSignal());
        $content = $r->render([$modal, $button]);
        return $content;
    } else {
        $signalId = $_GET['replaceSignal'];
        $replaceSignal = new \ILIAS\UI\Implementation\Component\ReplaceSignal($signalId);
        $button1 = $f->button()->standard('Login', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=login&replaceSignal=' . $replaceSignal->getId()));
        $button2 = $f->button()->standard('Registration', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=register&replaceSignal=' . $replaceSignal->getId()));

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
