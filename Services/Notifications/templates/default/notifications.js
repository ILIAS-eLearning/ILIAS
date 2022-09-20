var OSDNotifier, OSDNotifications = settings => {
    const createThrottle = function(timeout){
        let clear = function(){};
        return function(callback){
            clear();
            clear = clearTimeout.bind(window, setTimeout(callback, timeout));
        };
    };
    const evalInCleanEnv = codeAsString => new Function('', codeAsString).call();
    const template = function(template, values){
        return Object.entries(values).reduce(function(template, [key, value]){
            return template.split('[' + key + ']').join(value);
        }, template);
    };

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

	const attach = new Promise((resolve) => {
	    document.querySelector('body').append(sound);
	    resolve();
	});
	attach.then(
	    function() {
		sound.play().then(() => {
		    console.log("Played sound successfully!");
		}).catch((e) => {
		    console.log("Could not play sound, autoplay policy changes: https://developers.google.com/web/updates/2017/09/autoplay-policy-changes");
		    console.log(e);
		});
	    }
	);
    };

    const createContentSetter = function(container){
        const updateCenter = createThrottle(100);

        return function(html){
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
                    updateCenter(() => document.dispatchEvent(new Event('rerenderNotificationCenter')));
	        });
	    });
        };
    };

    const createPoll = function(lastRequest, container){
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

    const toast = container => (title, options) => {
        options = Object.assign({
            icon: '',
            action: '',
            description: '',
        }, options);

        const setContent = createContentSetter(container);

        setContent(template(settings.notificationPrototype, Object.assign(options, {title})));
    };

    const init = function(){
        const container = il.UI.page.getOverlay().querySelector('.il-toast-container');
        const interval = settings.pollingInterval * 1000;
        if (interval) {
	    window.setInterval(createPoll(settings.lastRequestedTime, container), interval);
        }

        const setContent = createContentSetter(container);
        setContent(settings.initialNotifications);

        return {
            toast: toast(container)
        };
    };

    return init();
};
