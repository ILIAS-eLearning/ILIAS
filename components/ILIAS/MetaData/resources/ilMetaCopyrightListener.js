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

/* eslint-env jquery */
/* eslint-env browser */
il.MetaDataCopyrightListener = {

  modalSignalId: '',
  modalWithOERWarningSignalID: '',
  potentialOERValues: [],
  radioGroupId: '',
  form: HTMLFormElement,
  formButton: HTMLButtonElement,

  confirmed: false,

  initialValue: '',

  init(modalSignalId, modalWithOERWarningSignalID, potentialOERValues, radioGroupId) {
    this.modalSignalId = modalSignalId;
    this.modalWithOERWarningSignalID = modalWithOERWarningSignalID;
    this.potentialOERValues = JSON.parse(potentialOERValues);
    this.radioGroupId = radioGroupId;

    this.form = document.querySelector(`#${this.radioGroupId}`).form;
    this.formButton = this.form.querySelectorAll('button[type="submit"], button:not([type])');
    this.initialValue = this.form.querySelector(`#${this.radioGroupId} input[type=radio]:checked`).value;

    $(this.form).on(
      'submit',
      (event) => {
        const currentRadioInput = document.querySelector(`#${this.radioGroupId} input[type=radio]:checked`);
        const currentValue = currentRadioInput.value;
        const harvestingBlockedCheckbox = currentRadioInput.closest('fieldset').querySelector('input[type=checkbox]');

        let signal = this.modalSignalId;
        if (
          this.potentialOERValues.includes(currentValue)
          && (harvestingBlockedCheckbox == null || !harvestingBlockedCheckbox.checked)
        ) {
          signal = this.modalWithOERWarningSignalID;
        }

        if (
          currentValue !== this.initialValue
          && !this.confirmed
        ) {
          event.preventDefault();
          this.triggerModal(signal, event);
        }
      },
    );
  },

  triggerModal(signal, event) {
    const buttonName = this.formButton[0].textContent;
    $('.modal-dialog').find('form').find('input').prop('value', buttonName);
    $('.modal-dialog').find('form').on(
      'submit',
      () => {
        $(this.form).off();
        $(this.formButton).off();
        this.confirmed = true;
        $(this.formButton).click();
        return false;
      },
    );

    // Show modal
    $(document).trigger(
      signal,
      {
        id: signal,
        event,
        triggerer: this.radioGroupId, // previously this was the form id
      },
    );
  },
};
