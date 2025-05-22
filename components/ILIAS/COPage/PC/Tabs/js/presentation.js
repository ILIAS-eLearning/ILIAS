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

const presentation = (function () {
  // used for slides container
  function adjustHeight(container) {
    let maxHeight = 0;
    Array.from(container.children).forEach((child) => {
      const originalDisplay = child.style.display;
      if (window.getComputedStyle(child).display === 'none') {
        child.style.display = 'block'; // Set to block to measure height
      }
      maxHeight = Math.max(maxHeight, child.offsetHeight);
      child.style.display = originalDisplay;
    });
    container.style.height = `${maxHeight}px`;
  }

  function hideAllTabs(tabsContainer, contentClass, activeHeadClass) {
    tabsContainer.querySelectorAll(`.${contentClass}`).forEach((content) => {
      content.classList.add('ilAccHideContent');
    });
    tabsContainer.querySelectorAll(`.${activeHeadClass}`).forEach((head) => {
      head.classList.remove(activeHeadClass);
    });
  }

  function showTab(toggler, contentNode, activeHeadClass) {
    contentNode.classList.remove('ilAccHideContent');
    toggler.querySelector('[role="button"]').classList.add(activeHeadClass);
    rerenderContent(contentNode);
  }

  function clickHandler(e, toggler, contentClass, toggleClass, toggleActClass, activeHeadClass) {
    const togglerParent = toggler.parentNode;
    const tabsContainer = togglerParent.parentNode;
    const contentNode = togglerParent.querySelector(`.${contentClass}`);

    e.preventDefault();

    if (contentNode.classList.contains('ilAccHideContent')) {
      // tab was hidden
      // hide all
      hideAllTabs(tabsContainer, contentClass, activeHeadClass);
      showTab(toggler, contentNode, activeHeadClass);
    } else {
      hideAllTabs(tabsContainer, contentClass);
    }
  }

  function rerenderContent(contentElement) {
    // rerender mathjax
    // eslint-disable-next-line no-undef
    if (typeof MathJax !== 'undefined' && typeof MathJax.Hub !== 'undefined') {
      // eslint-disable-next-line no-undef
      MathJax.Hub.Queue(['Reprocess', MathJax.Hub, contentElement[0]]);
    }
    // see http://docs.mathjax.org/en/latest/typeset.html

    // rerender google maps
    if (typeof ilMapRerender !== 'undefined') {
      // eslint-disable-next-line no-undef
      ilMapRerender(contentElement);
    }

    // see https://mantis.ilias.de/view.php?id=25301
    // see https://mantis.ilias.de/view.php?id=34329
    // previously we removed/re-added the player
    // in ilCOPagePres which led to #34329
    window.dispatchEvent(new Event('resize'));
  }

  function init(node) {
    node.querySelectorAll('[data-copg-tabs-type]').forEach((tabContainer) => {
      const type = tabContainer.dataset.copgTabsType;

      if (type === 'Carousel') {
        // +
        // carousel
        // +

        const displayDuration = tabContainer.dataset.copgTabsAutoAnimWait;
        // const randomStart = tabContainer.dataset.copgTabsRandomStart;
        const slides = tabContainer.querySelectorAll('& > div');
        const totalSlides = slides.length;
        let currentIndex = 0;

        // fix height of container
        adjustHeight(tabContainer);
        window.addEventListener('resize', () => {
          adjustHeight(tabContainer);
        });

        const showSlide = (index) => {
          const slide = slides[index];
          slide.style.display = 'block';
          // force reflow
          slide.getBoundingClientRect();
          // start
          slide.classList.add('active');
          setTimeout(() => {
            slide.classList.remove('active');
            // Nach Abschluss der Ausblendung 'display: none' setzen und nächstes DIV anzeigen
            slide.addEventListener('transitionend', function handler(event) {
              if (event.propertyName === 'opacity') {
                slide.style.display = 'none';
                slide.removeEventListener('transitionend', handler);
                // Nächstes Slide anzeigen
                currentIndex = (currentIndex + 1) % totalSlides;
                showSlide(currentIndex);
              }
            });
          }, displayDuration);
        };
        // start slide show
        showSlide(currentIndex);
      } else {
        // +
        // accordions
        // +

        const toggleClass = tabContainer.dataset.copgTabsToggleClass;
        const toggleActClass = tabContainer.dataset.copgTabsToggleActClass;
        const contentClass = tabContainer.dataset.copgTabsContentClass;
        const behaviour = tabContainer.dataset.copgTabsBehaviour;
        const activeHeadClass = tabContainer.dataset.copgTabsActiveHeadClass;

        // register click handler (if not all opened is forced)
        if (behaviour !== 'ForceAllOpen') {
          tabContainer.querySelectorAll(`.${toggleClass}`).forEach((toggler) => {
            toggler.querySelectorAll('a').forEach((aInToggler) => {
              aInToggler.addEventListener('click', (e) => {
                e.stopPropagation(); // enable links inside of accordion header
              });
            });
            toggler.addEventListener('click', (e) => {
              clickHandler(e, toggler, contentClass, toggleClass, toggleActClass, activeHeadClass);
            });
            toggler.addEventListener('keypress', () => {
              toggler.querySelector("div[role='button']").click();
            });
          });
          if (behaviour === 'FirstOpen') {
            const firstToggler = tabContainer.querySelector(`.${toggleClass}`);
            const firstTogglerParent = firstToggler.parentNode;
            const firstContentNode = firstTogglerParent.querySelector(`.${contentClass}`);
            showTab(firstToggler, firstContentNode, activeHeadClass);
          }
        }
      }
    });
  }

  return {
    init,
  };
}());
window.addEventListener('load', () => {
  presentation.init(document);
}, false);
