<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Container;

function with_multiple_toasts() : string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container();

    $toasts = [
        $DIC->ui()->factory()->toast()->standard(
            'Example 1',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        ),
        $DIC->ui()->factory()->toast()->standard(
            'Example 2',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        ),
        $DIC->ui()->factory()->toast()->standard(
            'Example 3',
            $DIC->ui()->factory()->symbol()->icon()->standard('mail', 'Test')
        )
    ];

    $toasts = base64_encode($DIC->ui()->renderer()->renderAsync($toasts));
    $button = $DIC->ui()->factory()->button()->standard($DIC->language()->txt('show'), '');
    $button = $button->withAdditionalOnLoadCode(function ($id) use ($toasts) {
        return "$id.addEventListener('click', () => {
            $id.parentNode.querySelector('.il-toast-container').innerHTML = atob('$toasts');
            $id.parentNode.querySelector('.il-toast-container').querySelectorAll('script').forEach(element => {
                let newScript = document.createElement('script');
                newScript.innerHTML = element.innerHTML;
                element.parentNode.appendChild(newScript);
            })
        });";
    });

    return $DIC->ui()->renderer()->render([$button,$tc]);
}
