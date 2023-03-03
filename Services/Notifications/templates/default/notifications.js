var OSDNotifier, OSDNotifications = settings => {
    const evalInCleanEnv = codeAsString => new Function('', codeAsString).call();

    const playSound = () => {
        const sound = document.createElement('audio');

        const mp3 = document.createElement('source');
        mp3.src = 'Modules/Chatroom/sounds/receive.mp3';
        mp3.type = 'audio/mp3';
        sound.append(mp3);

        const ogg = document.createElement('source');
        ogg.src = 'Modules/Chatroom/sounds/receive.ogg';
        ogg.type = 'audio/ogg';
        sound.append(ogg);
        document.querySelector('body').append(sound);

        sound.play().then(() => {
            console.debug("Played sound successfully!");
        }).catch((e) => {
            console.info("Could not play sound, autoplay policy changes: https://developers.google.com/web/updates/2017/09/autoplay-policy-changes");
            console.warn(e);
        });
    };

    const createContentSetter = container => {
        return html => {
            container.innerHTML = html;
            container.querySelectorAll('script').forEach(element => {
                evalInCleanEnv(element.innerHTML);
            });
            container.querySelectorAll('.il-toast-wrapper').forEach(element => {
                element.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        il.UI.toast.closeToast(element.querySelector('.il-toast'), true);
                    });
                });
                element.addEventListener('removeToast', () => {
                    document.dispatchEvent(new Event('rerenderNotificationCenter'));
                });
            });
        };
    };

    const poll = (container) => {
        let lastRequest = 0;

        return () => {
            const time = parseInt(new Date().getTime() / 1000);
            const max_age = time - lastRequest;
            const xhr = new XMLHttpRequest();
            const setContent = createContentSetter(container);
            xhr.open('GET', 'ilias.php?baseClass=ilNotificationGUI&cmd=getOSDNotifications&cmdMode=asynch&max_age=' + max_age);
            xhr.onload = () => {
                if (xhr.status === 200) {
                    setContent(xhr.responseText);
                    if (settings.playSound && xhr.responseText !== '') {
                        playSound();
                    }
                    lastRequest = time;
                } else {
                    container.innerHTML = '';
                    console.error(xhr.status + ': ' + xhr.responseText);
                }
            };
            xhr.send();
        };
    };

    const init = () => {
        const container = il.UI.page.getOverlay().querySelector('.il-toast-container');
        const interval = settings.pollingInterval;
        if (interval) {
            window.setInterval(poll(container), interval);
        }
    };

    return init();
};
