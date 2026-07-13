(() => {
  const MAX_HEIGHT = 100;

  function isClipped(el) {
    // needs overflow hidden/auto/scroll to actually clip
    // eslint-disable-next-line no-undef
    const style = getComputedStyle(el);
    const clips = (style.overflowY !== 'visible' || style.overflowX !== 'visible');
    if (!clips) return false;
    // If content is bigger than the visible box, it's clipped
    return el.scrollHeight > el.clientHeight + 1; // +1 to avoid rounding issues
  }

  function checkInitialDisplay(button) {
    const contentDiv = button.closest('[data-exc-show-more]').querySelector('div:first-child');
    if (!isClipped(contentDiv)) {
      contentDiv.style.maxHeight = null;
      button.style.display = 'none';
    }
  }

  // eslint-disable-next-line no-undef
  const io = new IntersectionObserver((entries) => {
    // eslint-disable-next-line no-restricted-syntax
    for (const e of entries) {
      if (e.isIntersecting) {
        // safe to measure heights, apply truncation, etc.
        checkInitialDisplay(e.target);
      }
    }
  });

  function initContainer(container) {
    const firstDiv = container.querySelector('div:first-child');
    firstDiv.style.maxHeight = `${MAX_HEIGHT}px`;
    firstDiv.style.marginBottom = '10px';
    firstDiv.style.overflow = 'hidden';
    const firstButton = container.querySelector('button');
    firstButton.addEventListener('click', () => {
      firstDiv.style.maxHeight = null;
      firstButton.style.display = 'none';
    });
    io.observe(firstButton);
  }

  function initAll() {
    document.querySelectorAll('[data-exc-show-more]').forEach(initContainer);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
})();
