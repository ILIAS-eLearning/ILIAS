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
  radioGroupId: '',
  form: HTMLFormElement,
  formButton: HTMLButtonElement,

  confirmed: false,

  initialValue: '',

  init(modalSignalId, radioGroupId) {
    this.modalSignalId = modalSignalId;
    this.radioGroupId = radioGroupId;
    this.form = document.querySelector(`#${this.radioGroupId}`).form;
    this.formButton = $(':submit', this.form);
    this.initialValue = this.form.querySelector(`#${this.radioGroupId} input:checked`).value;

    $(this.form).on(
      'submit',
      (event) => {
        const currentValue = document.querySelector(`#${il.MetaDataCopyrightListener.radioGroupId} input:checked`).value;
        if (currentValue !== il.MetaDataCopyrightListener.initialValue) {
          if (!il.MetaDataCopyrightListener.confirmed) {
            event.preventDefault();
            il.MetaDataCopyrightListener.triggerModal(event);
          }
        }
      },
    );
  },

  triggerModal(event) {
    const buttonName = il.MetaDataCopyrightListener.formButton[0].textContent;
    $('.modal-dialog').find('form').find('input').prop('value', buttonName);
    $('.modal-dialog').find('form').on(
      'submit',
      () => {
        $(il.MetaDataCopyrightListener.form).off();
        $(il.MetaDataCopyrightListener.formButton).off();
        il.MetaDataCopyrightListener.confirmed = true;
        $(il.MetaDataCopyrightListener.formButton).click();
        return false;
      },
    );

    // Show modal
    $(document).trigger(
      il.MetaDataCopyrightListener.modalSignalId,
      {
        id: this.modalSignalId,
        event,
        triggerer: this.radioGroupId, // previously this was the form id
      },
    );
  },
};
