/**
 * Changes visibility of (sub)items of groups
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
il.UI.Input.groups = il.UI.Input.groups || {};
(function () {
  il.UI.Input.groups.optional = (function ($) {
    const init = function (id) {
      const control = $(`#${id}`);
      control.change(onchange);
      control.change();
    };

    var onchange = function () {
      const control = $(this);
      const group = control.siblings('.form-group');

      if (control[0].checked) {
        group.show();
      } else {
        group.hide();
      }
    };

    return {
      init,
    };
  }($));

  il.UI.Input.groups.switchable = (function ($) {
    const init = function (id) {
      const control = $(`#${id}`);
      control.change(onchange);
      control.change();
    };

    var onchange = function () {
      const control = $(this);
      const options = control.children('.il-input-radiooption').children('input');
      options.each((index, opt) => {
        const groups = opt.parentNode.querySelectorAll('.form-group');
        if (opt.checked) {
          groups.forEach((group) => group.style.display = 'block');
        } else {
          groups.forEach((group) => group.style.display = 'none');
        }
      });
    };

    return {
      init,
    };
  }($));
}());
