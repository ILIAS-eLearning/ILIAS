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

namespace ILIAS\UI\examples\Button\Standard;

/**
 * ---
 * description: >
 *   In this example we create a button that ships with the on load animation on click.
 *
 * note: >
 *   Note that the button will trigger a page-reload as soon as the work is done,
 *   no additional magic is needed.
 *   However, in Async scenario, one can make use of the il.UI.button interface
 *   containing the functions activateLoadingAnimation and deactivateLoadingAnimation
 *   as shown below.
 *
 * expected output: >
 *   ILIAS shows an active button with a title. The color of the button will change after clicking the button
 *   and the word "Working" will appear, which is referencing to the loading status. After a while the button's content
 *   will change to "Done".
 * ---
 */
function with_load_anim()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        //Create a button with the LoadingAnimation on click and some additional JS-Magic.
        $f->button()->standard("Do Something", "")
            ->withLoadingAnimationOnClick(true)
            ->withOnLoadCode(function ($id) {
                return " $('#$id').click(
                    function(e) {
                       $('#$id').html('Working...');
                       setInterval(
                            function(){
                                $('#$id').html('Done');
                                il.UI.button.deactivateLoadingAnimation('$id');
                            }
                            ,3000
                        );
                    });";
            })
    );
}
