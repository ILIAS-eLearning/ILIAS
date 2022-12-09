function copyOnClick(element, text, copyToClipboard)
{
    element.addEventListener('click', function(event){
        if (!window.navigator.clipboard) {
            console.warn('Cannot copy link to clipboard. Please note that the clipboard is only available in secure contexts (HTTPS). See https://developer.mozilla.org/en-US/docs/Web/API/Clipboard for more information.');
            return;
        }

        window.navigator.clipboard.writeText(copyToClipboard);
        event.stopImmediatePropagation();

        const overlay = document.createElement('div');
        overlay.classList.add('il-clipboard-overlay');
        const at = element.getBoundingClientRect();
        overlay.innerText = text;
        overlay.style.top = at.y + 'px';
        overlay.style.left = at.x + 'px';
        overlay.style.height = at.height + 'px';
        overlay.style.width = at.width + 'px';
        element.parentNode.appendChild(overlay);

        setTimeout(function(){
            overlay.remove();
        }, 1500);

        return false;
    });
}
