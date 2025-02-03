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

namespace ILIAS\UI\examples\Modal\RoundTrip;

use ILIAS\UI\Implementation\Component\ReplaceSignal;

/**
 * ---
 * description: >
 *   Example for rendering a round trip multi-step modal.
 *
 * expected output: >
 *   ILIAS shows a button titled "Signin". A click onto the button will open a modal with two buttons "Login" and
 *   "Registration". Depending on the button a click will switch betweeen the "Login Page" and "Registration Page"
 *   within the modal.
 * ---
 */
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
        $modal = $f->modal()->roundtrip("Modal Title", $f->legacy()->content("b"));
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
            $legacy = $f->legacy()->content("<p>The Login Page</p>");
            $modal = $f->modal()->roundtrip("Login", [$legacy])->withActionButtons([$button1, $button2]);
        }
        if ($page == "register") {
            $legacy = $f->legacy()->content("<p>The Registration Page</p>");
            $modal = $f->modal()->roundtrip("Registration", [$legacy])->withActionButtons([$button1, $button2]);
        }

        echo $r->renderAsync([$modal]);
        exit;
    }
}
