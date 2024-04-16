<?php

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
 *   ILIAS shows the rendered Component.
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
                return
                    "$('#$id').click(function(e) {
							$('#$id').html('Working...');
							setInterval(
								function(){
									$('#$id').html('Done');
									il.UI.button.deactivateLoadingAnimation('$id');
								}
							,3000);
					});";
            })
    );
}
