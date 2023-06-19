class Sortation {
  /**
   * @type {jQuery}
   */
  #jquery;

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {Event} event
   * @param {array} signalData
   * @param {string} signal
   * @param {string} component_id
   * @return {void}
   */
  onInternalSelect(event, signalData, signal, component_id) {
    const triggerer = signalData.triggerer[0];            //the shy-button
    const param = triggerer.getAttribute('data-action');  //the actual value
    const sortation = this.#jquery('#' + component_id);   //the component itself
    const dd = sortation.find('.dropdown-toggle');        //the dropdown-toggle
    const sigdata = {
      'id' : signal,
      'event' : 'sort',
      'triggerer' : sortation,
      'options' : {
        'sortation': param
      }
    };
    const label = signalData.triggerer.contents()[0].data;
    dd.dropdown('toggle');
    dd.contents()[0].data = 
      signalData.options.label_prefix
      + ' '
      + label
      + ' ';
    dd.parent().find('li').each(
      function (idx, li) {
        if(li.getElementsByTagName('button')[0].innerHTML === label) {
          li.className = 'selected';
        } else {
          li.className = '';
        }
      } 
    );

    sortation.trigger(signal, sigdata);
  };

}

il.UI = il.UI || {};
il.UI.viewcontrol = il.UI.viewcontrol || {};
il.UI.viewcontrol.sortation = new Sortation($);
