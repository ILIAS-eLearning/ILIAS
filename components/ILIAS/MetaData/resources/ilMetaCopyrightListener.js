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
    this.form = document.querySelector('#' + this.radioGroupId).form;
    this.formButton = $(':submit', this.form);
    this.initialValue = this.form.querySelector('#' + this.radioGroupId + ' input:checked').value;

    $(this.form).on(
      'submit',
      (event) => {
        const currentValue = document.querySelector('#' + il.MetaDataCopyrightListener.radioGroupId + ' input:checked').value;
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
