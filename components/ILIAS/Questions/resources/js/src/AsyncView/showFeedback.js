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

/**
 * @param {String} base64
 * @returns {string}
 */
function base64toUtf8(base64) {
  const binaryString = atob(base64);
  const bytes = new Uint8Array(binaryString.length);
  for (let i = 0; i < binaryString.length; i += 1) {
    bytes[i] = binaryString.charCodeAt(i);
  }
  const decoder = new TextDecoder();
  return decoder.decode(bytes);
}

/**
 * @param {Event} event
 * @param {string} panel Standard panel scafold rendered in php.
 * @param {Object} feedbackDataArray Object containing all possible feedbacks
 * @param {Object} bestResponse
 * @param {Object} response
 * @param {...string} Endpoints the retrieve feedbacks of different feedback providers
 */
export default function showFeedback(
  event,
  panel,
  feedbackDataArray,
  bestResponse,
  bestResponseOutput,
  ...feedbackCallbacks
) {
  const decodedPanel = base64toUtf8(panel);
  const decodedBestResponse = JSON.parse(
    base64toUtf8(
      bestResponse,
    ),
  );

  let feedback = '';

  if (typeof bestResponseOutput !== 'undefined') {
    feedback += base64toUtf8(bestResponseOutput);
  }
  const inputs = event.target.previousElementSibling.querySelectorAll('input, select, textarea');
  const response = Array.from(inputs).reduce(
    (resp, input) => {
      resp[input.name] = input.value;
      return response;
    },
    {},
  );

  const decodedFeedbackData = JSON.parse(base64toUtf8(feedbackDataArray));

  feedbackCallbacks.forEach(
    (v, i) => {
      feedback += v(event, decodedPanel, decodedFeedbackData[i], decodedBestResponse, response);
    },
  );

  event.target.nextElementSibling.innerHTML = feedback;
}
