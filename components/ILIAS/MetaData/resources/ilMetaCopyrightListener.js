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

/* eslint-env browser */
il.MetaDataCopyrightListener = {

  form: HTMLFormElement,
  formButton: HTMLButtonElement,

  confirmed: false,

  init(message, messageWithOERWarning, potentialOERValues, radioGroupId) {
    potentialOERValues = JSON.parse(potentialOERValues);

    this.form = document.querySelector(`#${radioGroupId}`).form;
    this.formButton = this.form.querySelectorAll('button[type="submit"], button:not([type])')[0];

    const modal = document.querySelector('.c-modal--interruptive');
    const modalMessage = modal.querySelector('.c-modal--interruptive__message');
    const initialValue = this.form.querySelector(`#${radioGroupId} input[type=radio]:checked`).value;

    this.form.addEventListener(
      'submit',
      (event) => {
        const currentRadioInput = document.querySelector(`#${radioGroupId} input[type=radio]:checked`);
        const currentValue = currentRadioInput.value;

        if (potentialOERValues.includes(currentValue)) {
          modalMessage.innerHTML = messageWithOERWarning;
        } else {
          modalMessage.innerHTML = message;
        }

        if (
          currentValue !== initialValue
          && !this.confirmed
        ) {
          event.preventDefault();
          this.triggerModal(modal);
        }
      }
    );
  },

  triggerModal(modal) {
    const modalForm = modal.querySelector('form');
    const modalFormSubmitButton = modalForm.querySelector('input[type=submit]');

    modalFormSubmitButton.value = this.formButton.textContent;
    modalForm.addEventListener(
      'submit',
      (event) => {
        // cancel buttons in modals also trigger submit event
        if (event.submitter !== modalFormSubmitButton) {
          return true;
        }
        event.preventDefault();
        this.confirmed = true;
        this.formButton.click();
        return false;
      },
    );

    modal.showModal();
  },
};
