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
 */

class BadgeToggle {
  constructor(containerId, showButtonId, hideButtonId, hiddenClass) {
    this.badgeContainer = document.getElementById(containerId);
    this.showButton = document.getElementById(showButtonId);
    this.hideButton = document.getElementById(hideButtonId);
    this.hiddenClass = hiddenClass;

    this.init();
  }

  init() {
    this.hiddenBadges = this.badgeContainer.querySelectorAll(`.${this.hiddenClass}`);
    this.showButton.addEventListener('click', (e) => this.handleClick(e, true));
    this.hideButton.addEventListener('click', (e) => this.handleClick(e, false));
  }

  handleClick(event, show) {
    event.preventDefault();
    this.toggleButtonVisibility(show)
      .then(() => this.toggleBadgeVisibility(show));
  }

  toggleBadgeVisibility(show) {
    return new Promise((resolve) => {
      const promises = Array.from(this.hiddenBadges).map((badge) => this.animateBadge(badge, show));
      Promise.all(promises).then(resolve);
    });
  }

  animateBadge(badge, show) {
    return new Promise((resolve) => {
      const height = badge.scrollHeight;

      const stylesBeforeAnimation = show ? {
        display: 'inline-block',
        height: '0px',
        opacity: '0',
        overflow: 'hidden',
      } : {
        overflow: 'hidden',
      };

      const stylesAfterAnimation = show ? {
        height: '',
        opacity: '',
        overflow: '',
      } : {
        display: 'none',
        height: '',
        opacity: '',
        overflow: '',
      };

      const classAction = show ? 'remove' : 'add';

      Object.assign(badge.style, stylesBeforeAnimation);

      this.animateHeightOpacity(
        badge,
        show ? 0 : height,
        show ? height : 0,
        show ? 0 : 1,
        show ? 1 : 0,
      ).then(() => {
        Object.assign(badge.style, stylesAfterAnimation);
        badge.classList[classAction](this.hiddenClass);
        resolve();
      });
    });
  }

  animateHeightOpacity(element, fromHeight, toHeight, fromOpacity, toOpacity) {
    return new Promise((resolve) => {
      const animation = element.animate(
        [
          { height: `${fromHeight}px`, opacity: fromOpacity },
          { height: `${toHeight}px`, opacity: toOpacity },
        ],
        { duration: 200, easing: 'cubic-bezier(0.42, 0, 0.58, 1)' },
      );
      animation.onfinish = resolve;
    });
  }

  toggleButtonVisibility(show) {
    return new Promise((resolve) => {
      this.showButton.classList.toggle(this.hiddenClass);
      this.hideButton.classList.toggle(this.hiddenClass);
      resolve();
    });
  }
}
