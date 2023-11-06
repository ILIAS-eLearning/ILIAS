<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Toast\Standard;

function with_long_description(): string
{
    global $DIC;
    $tc = $DIC->ui()->factory()->toast()->container();

    $toasts = [
        $DIC->ui()->factory()->toast()->standard(
            'Example',
            $DIC->ui()->factory()->symbol()->icon()->standard('info', 'Test')
        )->withDescription(
            'This is an example description which is very long to provide a representative view of the object when it has ' .
            'to occupy enough space to show a very long toast. This may not be the favorable way of displaying a toast, ' .
            'since toast are assumed to be readable in a short time due to the temporary visibility, therefore they only ' .
            'should contain short description which can be read withing seconds. But even if this long description softly ' .
            'violates the concepts of toast itself due to its long character it still provides a good view on the ' .
            'scalability of the object and could therefore be called to proof its responsivity which confirms its benefit ' .
            'as an example in spite of its unnatural form and missing usecase for productive systems'
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
