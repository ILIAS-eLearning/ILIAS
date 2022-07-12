<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Primary;

function with_load_anim()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->button()->primary("Goto ILIAS", "")
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
