<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Progress\Bar;

/**
 * ---
 * description: >
 *   This example shows how a Progress Bar can be rendered and used on the client.
 *   The trigger button is supplied with the according JavaScript code, which uses
 *   the clientside facility of a Progress Bar.
 *
 * expected output: >
 *   ILIAS shows the rendered Progress Bar and Standard Button. The Progress Bar is
 *   initially empty (no progress), and cannot be operated in any way. When the
 *   Standard Button is clicked, the Progress Bar value us increased by 10% each time.
 *   After the 10th click, the Progress Bar is finished showing a successful state.
 * ---
 */
function client(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $progress_bar = $factory->progress()->bar('clicking the button 10 times');

    $trigger = $factory->button()->standard('make some progress', '#');
    $trigger = $trigger->withAdditionalOnLoadCode(
        static fn(string $id) => "
            let count = 1;
            document.getElementById('$id')?.addEventListener('click', ({ target }) => {
                if (10 === count) {
                    il.UI.Progress.Bar.success('{$progress_bar->getUpdateSignal()->getId()}', 'all done!');
                    target.disabled = true;
                    return;
                }
            
                let how_much_work_done = 10 / count;
                il.UI.Progress.Bar.determinate('{$progress_bar->getUpdateSignal()->getId()}', how_much_work_done);
                count += 1;
            });
        "
    );

    return $renderer->render([$progress_bar, $trigger]);
}
