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

const keyGapType = 'gap_type';
const keyLowerLimit = 'lower_limit';
const keyUpperLimit = 'upper_limit';
const keyResponseObject = 'response';
const keyGivenResponse = 'given_response';
const keyNoResponse = 'no_response';
const keyBestResponse = 'best_response';
const keyOtherResponse = 'other_response';
const keyInRange = 'i';
const keyOutOfRange = 'o';
const keyBelowRange = 'b';
const keyAboveRange = 'a';

/**
 * @param {Object} bestResponse
 * @param {string} response
 * @return {Array} All feedback keys triggered by given response.
 */
function getKeysForNumericValue(bestResponse, response) {
  const responseAsFloat = parseFloat(response);

  if (Number.isNaN(bestResponse[keyUpperLimit])
    || Number.isNaN(parseFloat(bestResponse[keyUpperLimit]))) {
    return [keyOutOfRange];
  }

  if (responseAsFloat <= bestResponse[keyUpperLimit]) {
    return [keyOutOfRange, keyBelowRange];
  }

  if ((typeof bestResponse[keyUpperLimit] === 'undefined'
      && responseAsFloat === bestResponse[keyLowerLimit])
    || responseAsFloat >= bestResponse[keyLowerLimit]) {
    return [keyInRange];
  }

  return [keyOutOfRange, keyAboveRange];
}

/**
 * @param {Object} bestResponse
 * @param {string} response
 * @return {bool}
 */
function isResponseForInputBest(bestResponse, response) {
  if ((bestResponse[keyGapType] === 'numeric'
      && getKeysForNumericValue(bestResponse[keyResponseObject], response)[0] === keyInRange)
    || (bestResponse[keyGapType] !== 'numeric'
      && response === bestResponse[keyResponseObject][keyGivenResponse])) {
    return true;
  }

  return false;
}

/**
 * @param {Object} bestResponse
 * @param {string} response
 * @return {Array} All feedback keys triggered by given response.
 */
function getKeysForOtherAnswerValue(bestResponse, response) {
  if (response === '') {
    return [keyNoResponse];
  }

  if (bestResponse[keyGapType] === 'numeric') {
    return getKeysForNumericValue(bestResponse, response);
  }

  return isResponseForInputBest(bestResponse, response)
    ? [keyBestResponse]
    : [keyOtherResponse];
}

export default {
  /**
   * @param {Object} bestResponse
   * @param {Object} response
   */
  isBestResponse(bestResponse, response) {
    if (Object.keys(bestResponse).length !== Object.keys(response).length) {
      return false;
    }

    const keys = Object.keys(response);
    for (let i = 0; i < keys.length; i += 1) {
      if (!isResponseForInputBest(bestResponse[keys[i]], response[keys[i]])) {
        return false;
      }
    }
    return true;
  },
  /**
   * @param {Object} data
   * @param {Object} bestResponse
   * @param {Object} response
   */
  retrieveSpecificFeedback(data, bestResponse, response) {
    let feedbacks = '';

    const responseKeys = Object.keys(response);
    for (let i = 0; i < responseKeys.length; i += 1) {
      const property = responseKeys[i];
      if (Object.hasOwn(data, property)) {
        if (Object.hasOwn(data[property], response[property])) {
          feedbacks += data[property][response[property]];
        }

        const keys = getKeysForOtherAnswerValue(
          bestResponse[property],
          response[property],
        );

        for (let j = 0; j < keys.length; j += 1) {
          if (Object.hasOwn(data[property], keys[i])) {
            feedbacks += data[property][keys[j]];
          }
        }
      }
    }

    return feedbacks;
  },
};
