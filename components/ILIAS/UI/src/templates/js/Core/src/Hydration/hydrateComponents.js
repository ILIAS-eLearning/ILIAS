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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

/** @type {string} */
const IS_HYDRATED_ATTRIBUTE = 'data-is-hydrated';

/** @type {string} */
const HYDRATED_BY_ATTRIBUTE = 'data-hydrated-by';

/**
 * Hydrates an element by calling all registered javascript functions mapped
 * to the javascript ids of the element.
 *
 * @param {HydrationRegistry} registry
 * @param {HTMLElement} element
 */
function hydrateElement(registry, element) {
  // abort if the element is already hydrated or needs no hydration.
  if (!element.hasAttribute(HYDRATED_BY_ATTRIBUTE)
    || !element.hasAttribute(IS_HYDRATED_ATTRIBUTE)
    || element.getAttribute(IS_HYDRATED_ATTRIBUTE) === 'true'
  ) {
    return;
  }

  const hydratorId = element.getAttribute(HYDRATED_BY_ATTRIBUTE);
  const hydrator = registry.getFunction(hydratorId.trim());
  if (hydrator !== null) {
    hydrator(element);
  }

  element.setAttribute(IS_HYDRATED_ATTRIBUTE, 'true');
}

/**
 * Returns the list of components ordered by the registration of their hydrator.
 *
 * This is important due to nested components which may depend on their parents
 * business logic to be initialised, or vice-versa. Since our SSR will render
 * nested components inside-out, we need to reorder the collected DOM elements
 * to match the order of rendering.
 *
 * @param {HydrationRegistry} registry
 * @param {HTMLElement[]} components
 * @returns {HTMLElement[]}
 */
function orderComponentsByRegistration(registry, components) {
  const order = registry.getOrder();
  return components.sort((elementA, elementB) => {
    const positionA = order.get(elementA.getAttribute(HYDRATED_BY_ATTRIBUTE));
    const positionB = order.get(elementB.getAttribute(HYDRATED_BY_ATTRIBUTE));
    return (positionA - positionB);
  });
}

/**
 * Returns all components which need to by hydrated in the order of initialisation.
 *
 * @param {HydrationRegistry} registry
 * @param {HTMLElement} element
 * @returns {HTMLElement[]}
 */
function getHydratableComponentsOfElement(registry, element) {
  const componentNodeList = element.querySelectorAll(`[${IS_HYDRATED_ATTRIBUTE}="false"]`);
  return orderComponentsByRegistration(registry, Array.from(componentNodeList));
}

/**
 * Hydrates all children of the given element recursively and the element itself,
 * if the element needs hydration.
 *
 * @param {HydrationRegistry} registry
 * @param {HTMLElement} element
 */
export default function hydrateComponents(registry, element) {
  if (element.hasAttribute(HYDRATED_BY_ATTRIBUTE)) {
    hydrateElement(registry, element);
  }

  const components = getHydratableComponentsOfElement(registry, element);
  for (let i = 0; i < components.length; i += 1) {
    hydrateElement(registry, components[i]);
  }
}
