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
 ******************************************************************** */

il.Notifications = il.Notifications || {};

il.Notifications.initToggle = (element, key, target) => {
  const subscribe = (toggle, asKey, url) => {
    Notification.requestPermission()
      .then((permission) => {
        if (permission === 'granted') {
          navigator.serviceWorker.ready.then((reg) => {
            reg.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: asKey,
            })
              .then((newSub) => {
                const data = new FormData();
                data.append('subscription', JSON.stringify(newSub.toJSON()));
                fetch(url, { method: 'POST', body: data });
              });
          });
        }
        if (permission === 'default') {
          setInactive(toggle);
        }
        if (permission === 'denied') {
          location.href = location.href;
        }
      });
  };

  const unsubscribe = (url) => {
    navigator.serviceWorker.ready.then((reg) => {
      reg.pushManager.getSubscription().then((sub) => {
        sub.unsubscribe()
          .then(() => {
            const data = new FormData();
            data.append('auth', sub.toJSON().keys.auth);
            fetch(url, { method: 'POST', body: data });
          });
      });
    });
  };

  const setActive = (element) => {
    element.classList.remove('off');
    element.classList.add('on');
    element.ariaPressed = true;
    element.querySelector('.il-toggle-label-off').style.display = 'none';
    element.querySelector('.il-toggle-label-on').style.display = '';
  };

  const setInactive = (element) => {
    element.classList.remove('on');
    element.classList.add('off');
    element.ariaPressed = false;
    element.querySelector('.il-toggle-label-on').style.display = 'none';
    element.querySelector('.il-toggle-label-off').style = '';
  };

  element.addEventListener('click', () => {
    if (element.classList.contains('on')) {
      subscribe(element, key, target.replace('default', 'addSubscription'));
    }
    if (element.classList.contains('off')) {
      unsubscribe(target.replace('default', 'removeSubscription'));
    }
  });
};
