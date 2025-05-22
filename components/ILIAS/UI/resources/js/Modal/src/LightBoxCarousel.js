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

const LightBoxCarousel = {
  /**
   * @param {HTMLDialogElement} component
   */
  maybeInitCarousel(component) {
    const container = component.querySelector('.carousel-inner');
    if (!container || container.querySelectorAll('.item').length < 2) {
      return;
    }

    const left = component.querySelector('.carousel-control.left');
    const right = component.querySelector('.carousel-control.right');
    const indicators = component.querySelectorAll('.carousel-indicators > li');

    left.addEventListener('click', () => this.nextPage(component, -1));
    right.addEventListener('click', () => this.nextPage(component, 1));
    indicators.forEach(
      (i) => i.addEventListener(
        'click',
        () => this.gotoPage(component, Number(i.getAttribute('data-slide-to'))),
      ),
    );
  },

  /**
   * @param {HTMLDialogElement} component
   * @param {int} direction
   */
  nextPage(component, direction) {
    const pages = component.querySelectorAll('.carousel-inner > .item');
    let index = 0;
    let current = 0;
    pages.forEach((p) => {
      if (p.classList.contains('active')) {
        current = index;
      }
      index += 1;
    });
    let next = current + direction;
    if (next < 0) {
      next = index - 1;
    }
    if (next === index) {
      next = 0;
    }
    this.gotoPage(component, next);
  },

  /**
   * @param {HTMLDialogElement} component
   * @param {int} number
   */
  gotoPage(component, number) {
    const pages = component.querySelectorAll('.carousel-inner > .item');
    const indicators = component.querySelectorAll('.carousel-indicators > li');
    this.setActiveInList(pages, number);
    this.setActiveInList(indicators, number);

    const title = component.querySelector('.modal-title');
    pages.forEach((item) => {
      if (item.classList.contains('active')) {
        title.innerHTML = item.getAttribute('data-title');
      }
    });
  },

  /**
   * @param {NodeList} list
   * @param {int} number
   */
  setActiveInList(list, number) {
    let index = 0;
    list.forEach((item) => {
      if (index !== number) {
        item.classList.remove('active');
      } else {
        item.classList.add('active');
      }
      index += 1;
    });
  },
};
export default LightBoxCarousel;
