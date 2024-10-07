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

    this.form = $(`input[id^='${this.radioGroupId}']`)[0].form;
    this.formButton = $(':submit', this.form);

    this.initialValue = $(`input[id^='${this.radioGroupId}']:checked`).val();

    $(this.form).on(
      'submit',
      (event) => {
        const currentRadioInput = $(`input[id^='${il.MetaDataCopyrightListener.radioGroupId}']:checked`);
        const currentValue = currentRadioInput.val();
        const harvestingBlockedCheckbox = currentRadioInput.parent().find('input:checkbox');

        let signal = this.modalSignalId;
        if (
          this.potentialOERValues.includes(currentValue)
          && !harvestingBlockedCheckbox.checked
        ) {
          signal = this.modalWithOERWarningSignalID;
        }

        if (
          currentValue !== il.MetaDataCopyrightListener.initialValue
          && !il.MetaDataCopyrightListener.confirmed
        ) {
          event.preventDefault();
          il.MetaDataCopyrightListener.triggerModal(signal, event);
        }
      },
    );
  },

  triggerModal(signal, event) {
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
      signal,
      {
        id: signal,
        event,
        triggerer: this.radioGroupId, // previously this was the form id
      },
    );
  },
};
