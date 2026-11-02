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
 */

const placeholderPanelTitle = '-panel-title-to-replace-';
const placeholderPanelContent = '-panel-content-to-replace-';

const keyBestResponse = 'best';
const keyOtherResponse = 'other';

/**
 * This function is used to call a namespaced function that has been handed in
 * the form of two Strings. This is needed to be able to initialize most of the
 * js in a file, but still define proper endpoints in php without some funky
 * template parsing of js files.
 *
 * @param {Event} event
 * @param {string} namespace
 * @param {string} func
 * @param {Array} args
 * @returns {string}
 */
function executeSpecificEndPointFunction(event, namespace, func, args) {
  const context = namespace.split('.').reduce(
    (c, v) => c[v],
    event.target.ownerDocument.defaultView,
  );
  return context[func].apply(context, args);
}

/**
 * @param {Event} event
 * @param {string} panel Standard panel scafold rendered in php.
 * @param {Object} data Object containing all possible feedbacks
 * @param {Object} bestResponse
 * @param {Object} response
 * @returns {string}
 */
export default function retrieve(event, panel, data, bestResponse, response) {
  const { panelTitle } = data;
  delete data.panelTitle;
  const { specificFeedbackEndPoint } = data;
  delete data.specificFeedbackEndpoint;

  const key = executeSpecificEndPointFunction(
    event,
    specificFeedbackEndPoint,
    'isBestResponse',
    [bestResponse, response],
  ) ? keyBestResponse : keyOtherResponse;

  let genericFeedback = '';
  if (Object.hasOwn(data, key)) {
    genericFeedback = data[key];
  }

  return panel.replace(
    placeholderPanelTitle,
    panelTitle,
  ).replace(
    placeholderPanelContent,
    genericFeedback + executeSpecificEndPointFunction(
      event,
      specificFeedbackEndPoint,
      'retrieveSpecificFeedback',
      [data, bestResponse, response],
    ),
  );
}
