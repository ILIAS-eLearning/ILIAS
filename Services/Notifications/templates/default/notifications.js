var OSDNotifier, OSDNotifications = (settings) => {
	return (function () {
		return new function () {
			let	lastRequest = 0;
			let osdNotificationContainer = il.UI.page.getOverlay().querySelector('.il-toast-container');

			let playSound = () => {
				let sound = document.createElement('audio');

				let mp3 = document.createElement('source');
				mp3.src = 'Modules/Chatroom/sounds/receive.mp3';
				mp3.type = 'audio/mp3';
				sound.append(mp3);

				let ogg = document.createElement('source');
				ogg.src = 'Modules/Chatroom/sounds/receive.ogg';
				ogg.type = 'audio/ogg';
				sound.append(ogg);

				let attach = new Promise((resolve) => {
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
				)
			}

			let init = true;
			let poll = () => {
				let time = parseInt(new Date().getTime() / 1000);
				let max_age = time - lastRequest;
				let xhr = new XMLHttpRequest();
				let updateCenter;
				xhr.open('GET', 'ilias.php?baseClass=ilNotificationGUI&cmd=getOSDNotifications&cmdMode=asynch&max_age=' + max_age);
				xhr.onload = () => {
					if (xhr.status === 200) {
						osdNotificationContainer.innerHTML = xhr.responseText;
						osdNotificationContainer.querySelectorAll('script').forEach( element => {
							eval(element.innerHTML);
						})
						osdNotificationContainer.querySelectorAll('.il-toast-wrapper').forEach(element => {
							element.querySelectorAll('a').forEach(link => {
								link.addEventListener('click', () => {
									il.UI.toast.closeToast(element.querySelector('.il-toast'), true)
								})
							})
							element.addEventListener('removeToast', () => {
								if(updateCenter !== undefined) {
									clearTimeout(updateCenter)
								}
								updateCenter = setTimeout(() => {document.dispatchEvent(new Event('rerenderNotificationCenter'))}, 500);
							})
						})

						if (!init && settings.playSound && xhr.responseText !== '') {
							playSound();
						}

						lastRequest = time;
					} else {
						osdNotificationContainer.innerHTML = '';
						console.error(xhr.status + ': ' + xhr.responseText);
					}
					init = false;
				};
				xhr.send();
			};

			poll();
			if (settings.pollingIntervall * 1000) {
				window.setInterval(poll, settings.pollingIntervall * 1000);
			}
		};
	})();
};