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

((global) => {
  const BuddySystem = {
    config: {},

    setConfig(config) {
      this.config = config;
    },
  };

  const BuddySystemButton = {
    config: {},

    setConfig(config) {
      this.config = config;
    },

    init() {
      const toggleSelector = '[data-toggle="dropdown"]';
      const triggerSelector = 'a[data-target-state], button[data-target-state]';

      const onWidgetClick = (e) => {
        e.preventDefault();
        e.stopPropagation();

        const triggerButton = e.target.closest(triggerSelector);
        const container = triggerButton.closest(`.${this.config.bnt_class}`);
        if (triggerButton.dataset.submitted === 'true') return Promise.resolve();

        const values = new FormData();
        values.append('usr_id', container.dataset.buddyId);
        values.append('action', triggerButton.dataset.action);
        values.append(`cmd[${BuddySystem.config.transition_state_cmd}]`, 1);

        return disableButtons(container)
          .then(() => fetch(BuddySystem.config.http_post_url, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: values,
          }))
          .then((response) => {
            if (!response.ok) throw new Error('Request failed');
            return response.json();
          })
          .then((data) => processResponse(container, data))
          .then(() => {
            container.querySelector(toggleSelector).focus();
          })
          .catch((error) => {
            console.error(error);
            enableButtons(container);
            container.querySelector(toggleSelector).focus();
          });
      };

      const disableButtons = (container) => new Promise((resolve) => {
        document.querySelectorAll(`.${this.config.bnt_class}`).forEach((btnContainer) => {
          if (btnContainer.dataset.buddyId === container.dataset.buddyId) {
            btnContainer.querySelectorAll(triggerSelector).forEach((btn) => {
              btn.dataset.submitted = 'true';
              btn.disabled = true;
            });
          }
        });
        resolve();
      });

      const enableButtons = (container) => new Promise((resolve) => {
        document.querySelectorAll(`.${this.config.bnt_class}`).forEach((btnContainer) => {
          if (btnContainer.dataset.buddyId === container.dataset.buddyId) {
            btnContainer.querySelectorAll(triggerSelector).forEach((btn) => {
              btn.dataset.submitted = 'false';
              btn.disabled = false;
            });
          }
        });
        resolve();
      });

      const processResponse = (container, data) => {
        const { currentState } = container.dataset;

        if (data.success) {
          if (data.state && data.state_html) {
            if (currentState !== data.state) {
              return triggerEvent('il.bs.stateChange.beforeButtonWidgetReRendered', {
                buddyId: container.dataset.buddyId,
                newState: data.state,
                oldState: currentState,
              })
                .then(() => updateContainers(container.dataset.buddyId, data.state, data.state_html))
                .then(() => triggerEvent('il.bs.stateChange.afterButtonWidgetReRendered', {
                  buddyId: container.dataset.buddyId,
                  newState: data.state,
                  oldState: currentState,
                }));
            }
          }
        }

        return enableButtons(container)
          .then(() => showPopover(container, data.message))
          .then(() => triggerEvent('il.bs.stateChange.afterStateChangePerformed', {
            buddyId: container.dataset.buddyId,
            newState: container.dataset.currentState,
            oldState: currentState,
          }));
      };

      const updateContainers = (buddyId, newState, stateHtml) => new Promise((resolve) => {
        document.querySelectorAll(`.${this.config.bnt_class}`).forEach((container) => {
          if (container.dataset.buddyId === buddyId) {
            container.querySelector('.button-container').innerHTML = stateHtml;
            container.dataset.currentState = newState;
          }
        });
        resolve();
      });

      const showPopover = (container, message) => new Promise((resolve) => {
        if (message) {
          // We currently don't have a nice way to present errors with client-side APIs
          alert(message);
          resolve();
        } else {
          resolve();
        }
      });

      const clearAllDropdowns = (e) => new Promise((resolve) => {
        const triggerButtons = document.querySelectorAll(toggleSelector);

        triggerButtons.forEach((triggerButton) => {
          const parent = triggerButton.parentElement;

          if (!parent.classList.contains('open')) return;

          if (e && e.defaultPrevented) return;

          triggerButton.setAttribute('aria-expanded', 'false');
          parent.classList.remove('open');

          const dropdownMenu = parent.querySelector(':scope > .dropdown-menu');
          if (dropdownMenu) dropdownMenu.style.display = 'none';
        });

        if (global.il && global.il.UI && global.il.UI.dropdown && global.il.UI.dropdown.opened) {
          global.il.UI.dropdown.opened.hide();
        }

        resolve();
      });

      const toggleDropdown = (triggerButton, isActive, e) => new Promise((resolve) => {
        const parent = triggerButton.parentElement;

        if (!isActive) {
          const dropdownMenu = parent.querySelector(':scope > .dropdown-menu');
          if (dropdownMenu) dropdownMenu.style.display = 'block';

          const availableWidth = parent.ownerDocument.documentElement.clientWidth;
          const buttonPosition = triggerButton.getBoundingClientRect().left;
          const listWidth = dropdownMenu.getBoundingClientRect().width;

          if (buttonPosition + listWidth > availableWidth) {
            dropdownMenu.classList.remove('dropdown-menu__right');
            dropdownMenu.classList.add('dropdown-menu__left');
          } else {
            dropdownMenu.classList.remove('dropdown-menu__left');
            dropdownMenu.classList.add('dropdown-menu__right');
          }

          triggerButton.setAttribute('aria-expanded', 'true');
          parent.classList.add('open');
        } else {
          triggerButton.setAttribute('aria-expanded', 'false');
          parent.classList.remove('open');

          const dropdownMenu = parent.querySelector(':scope > .dropdown-menu');
          if (dropdownMenu) dropdownMenu.style.display = 'none';
        }

        resolve();
      });

      const onKeydownSelection = (e) => {
        if (!['Enter', ' '].includes(e.key)) return;
        onWidgetClick(e);
      };

      const onKeydownDropdown = (container, e) => {
        if (!['ArrowUp', 'ArrowDown', 'Escape', 'Enter', ' '].includes(e.key)) return;

        const actedOnOption = !!e.target.closest(triggerSelector);

        if (actedOnOption && ['Enter', ' '].includes(e.key)) {
          onKeydownSelection(e);
          return;
        }

        const triggerButton = container.querySelector(toggleSelector);
        const parent = triggerButton.parentElement;
        const isActive = parent.classList.contains('open');

        if (!isActive) {
          clearAllDropdowns(e);
        }

        e.preventDefault();
        e.stopPropagation();

        if (!isActive && e.key !== 'Escape') {
          return toggleDropdown(triggerButton, false, e);
        }

        if (isActive && e.key === 'Escape') {
          return toggleDropdown(triggerButton, true, e).then(() => triggerButton.focus());
        }

        const items = Array.from(parent.querySelectorAll('.dropdown-menu li a')).filter((item) => item.offsetParent !== null);
        if (!items.length) return;

        let index = items.indexOf(e.target);
        if (e.key === 'ArrowUp' && index > 0) index--;
        if (e.key === 'ArrowDown' && index < items.length - 1) index++;
        if (index === -1) index = 0;

        items[index].focus();
      };

      const onClickDropdown = (triggerButton, e) => {
        if (e.button === 2) return; // Ignore right clicks

        const parent = triggerButton.parentElement;
        const isActive = parent.classList.contains('open');

        clearAllDropdowns(e)
          .then(() => toggleDropdown(triggerButton, isActive, e))
          .catch(console.error);

        e.preventDefault();
      };

      const onClickSelection = (e) => {
        if (e.button === 2) return; // Ignore right clicks
        onWidgetClick(e);
      };

      document.querySelectorAll(`.${this.config.bnt_class}`).forEach((element) => {
        element.addEventListener('keydown', (e) => {
          const container = e.target.closest(`.${this.config.bnt_class}`);
          if (container) {
            onKeydownDropdown(container, e);
          }
        });

        element.addEventListener('click', (e) => {
          if (e.target.closest(triggerSelector)) {
            onClickSelection(e);
          } else {
            const triggerButton = e.target.closest(toggleSelector);
            if (triggerButton) {
              onClickDropdown(triggerButton, e);
            }
          }
        });
      });

      document.addEventListener('click', clearAllDropdowns);
    },
  };

  const triggerEvent = (eventName, details) => new Promise((resolve) => {
    document.dispatchEvent(new CustomEvent(eventName, { detail: details, bubbles: true }));
    resolve();
  });

  global.il.BuddySystem = BuddySystem;
  global.il.BuddySystemButton = BuddySystemButton;

  document.addEventListener('il.bs.stateChange.afterStateChangePerformed', (event) => {
    const { buddyId, newState, oldState } = event.detail;

    const shouldReloadAwarenessTool = (
      ['ilBuddySystemLinkedRelationState', 'ilBuddySystemRequestedRelationState'].includes(oldState)
      && newState !== oldState
    );
    if (shouldReloadAwarenessTool) {
      if (typeof global.il.Awareness !== 'undefined') {
        global.il.Awareness.updateList('');
      }
    }
  });
})(window);
