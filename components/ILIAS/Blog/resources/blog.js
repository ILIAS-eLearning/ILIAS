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

il = il || {};
il.blog = (function () {
  const modalTemplate = [];

  function setModalTemplate(signalId, t) {
    modalTemplate[signalId] = t;
  }

  function getModalTemplate(signalId) {
    return JSON.parse(modalTemplate[signalId]);
  }

  function showModal(signalId) {
    const modalT = getModalTemplate(signalId);

    // eslint-disable-next-line no-undef
    $('body').append(`<div>${modalT}</div>`);

    // eslint-disable-next-line no-undef
    $(document).trigger(signalId, {
      id: signalId,
      // eslint-disable-next-line no-undef
      triggerer: $(document),
      options: JSON.parse('[]'),
    });
  }

  return {
    setModalTemplate,
    showModal,
  };
}());
