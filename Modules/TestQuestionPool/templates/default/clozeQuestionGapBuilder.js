const ClozeGlobals = {
  clone_active: -1,
  active_gap: -1,
  cursor_pos: '',
  gap_count: 0,
  scrollable_page_element: 'il-layout-page-content',
  form_class: '#form_assclozetest',
  form_class_adjustment: '#form_adjustment',
  form_footer_class: '.ilFormFooter',
  form_footer_buttons: '.col-sm-6.ilFormCmds',
  form_value: 'col-lg-10 col-md-9 col-sm-8',
  form_value_class: '.col-lg-10 .col-md-9 .col-sm-8',
  form_header: 'ilFormHeader',
  form_header_class: '.ilFormHeader',
  form_header_value: 'ilFormCmds',
  form_options: 'col-lg-2 col-md-3 col-sm-4',
  form_options_class: '.col-lg-2 .col-md-3 .col-sm-4',
  form_row: 'form-group',
  form_error: 'form_error',
  form_warning: 'form_warning',
  best_combination: '',
  best_possible_solution_error: false,
  debug: false,
  jour_fixe_incompatible: false,
  gap_restore: true,
};

let ClozeSettings = {};

const ClozeQuestionGapBuilder = (function () {
  const pub = {}; const
    pro = {};

  pro.deferredCallbackFactory = (function () {
    const namespaces = {};

    return function (ns) {
      if (!namespaces.hasOwnProperty(ns)) {
        namespaces[ns] = (function () {
          let timer = 0;

          return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
          };
        }());
      }

      return namespaces[ns];
    };
  }());

  pro.checkJSONArraysOnEntry = function () {
    if (ClozeSettings.gaps_php === null) {
      ClozeSettings.gaps_php = [];
    }

    ClozeSettings.gaps_php[0].forEach(
      (gap) => {
        if (gap.type === 'text' || gap.type === 'select') {
          gap.values.forEach(
            (value) => {
              if (value.answer === undefined) {
                value.answer = '';
              }
              value.answer = value.answer.replace('&#123;', '{');
              value.answer = value.answer.replace('&#125;', '}');
            },
          );
        }
      },
    );

    if (ClozeSettings.gaps_combination === null) {
      ClozeSettings.gaps_combination = [];
    }
  };

  pro.moveDeleteGapButton = function (counter) {
    $(`#remove_gap_${counter}`).parent().appendTo($(`#gap_error_${counter}`).find(ClozeGlobals.form_value_class));
  };

  pro.addModalFakeFooter = function () {
    if (ClozeGlobals.jour_fixe_incompatible === false) {
      if ($('.modal-fake-footer').length === 0) {
        const footer = $(`<div class="modal-fake-footer"><input type="button" class="btn btn-default btn-sm btn-dummy modal_ok_button" value="${ClozeSettings.ok_text}"> <input type="button" class="btn btn-default btn-sm btn-dummy modal_cancel_button" value="${ClozeSettings.cancel_text}"></div>`);
        $(footer).find('.modal_ok_button').on('click', () => {
          pro.closeModalWithOkButton();
        });
        $(footer).find('.modal_cancel_button').on('click', () => {
          pro.closeModalWithCancelButton();
        });
        footer.appendTo('.modal-content');
      }
    }
  };

  pro.moveFooterBelow = function () {
    $('#gap_json_post').appendTo(ClozeGlobals.form_class);
    $('#gap_json_combination_post').appendTo(ClozeGlobals.form_class);
    $(ClozeGlobals.form_footer_class).appendTo(ClozeGlobals.form_class);
  };

  pro.closeModalWithOkButton = function () {
    ClozeGlobals.gap_restore = false;
    $('#ilGapModal').modal('hide');
  };

  pro.closeModalWithCancelButton = function () {
    pro.restoreSavedGap();
    $('#ilGapModal').modal('hide');
  };

  pro.restoreSavedGap = function () {
    if (ClozeGlobals.jour_fixe_incompatible === false && ClozeGlobals.gap_restore === true) {
      ClozeSettings.gaps_php[0][ClozeSettings.gap_backup.id] = ClozeSettings.gap_backup.values;
      pro.editTextarea(ClozeSettings.gap_backup.id);
      pub.paintGaps();
    }
  };

  pro.getTextAreaValue = function () {
    let text;
    if (typeof (tinymce) !== 'undefined') {
      text = tinymce.get('cloze_text').getContent();
    } else {
      const textarea = $('textarea#cloze_text');
      text = textarea.val();
    }
    return text;
  };

  pro.setTextAreaValue = function (text) {
    let cursor; let
      inGap;
    if (typeof (tinymce) !== 'undefined') {
      if (navigator.userAgent.indexOf('Firefox') !== -1) {
        text = text.replace(new RegExp('(<p>(&nbsp;)*<\/p>(\n)*)', 'g'), '');
      }
      // ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
      const inst = tinyMCE.activeEditor;
      cursor = pro.getCursorPositionTiny(inst);
      tinymce.get('cloze_text').setContent(text);
      inGap = pro.cursorInGap(cursor);
      if (inGap[1] != '-1') {
        // var newIndex = parseInt(inGap[1], 10);
        // ClozeGlobals.active_gap = newIndex;
        ClozeGlobals.active_gap = parseInt(inGap[1], 10);
      }
      pro.setCursorPositionTiny(inst, ClozeGlobals.active_gap);
    } else {
      const textarea = $('textarea#cloze_text');
      cursor = textarea.prop('selectionStart');
      textarea.val(text);
      inGap = pro.cursorInGap(cursor + 1);
      if (inGap != '-1') {
        if (ClozeGlobals.active_gap == '-1') {
          pro.setCaretPosition(textarea, cursor);
        } else {
          textarea.prop('selectionStart', ClozeGlobals.active_gap);
          textarea.prop('selectionEnd', ClozeGlobals.active_gap);
        }
        ClozeGlobals.active_gap = parseInt(inGap[1], 10);
      }
    }
  };

  pro.getCursorPositionTiny = function (editor) {
    const scrollableElement = document.getElementsByClassName(ClozeGlobals.scrollable_page_element)[0];
    const bm = editor.selection.getBookmark(0);
    const selector = '[data-mce-type=bookmark]';
    const bmElements = editor.dom.select(selector);
    editor.selection.select(bmElements[0]);
    editor.selection.collapse();
    const elementID = '######cursor######';
    const windowPosition = scrollableElement.scrollTop;
    const positionString = `<span id="${elementID}"></span>`;
    editor.selection.setContent(positionString);
    scrollableElement.scrollTop = windowPosition;
    const content = editor.getContent({ format: 'html' });
    const index = content.indexOf(positionString);
    editor.dom.remove(elementID, false);
    editor.selection.moveToBookmark(bm);
    return index;
  };

  pro.setCursorPositionTiny = function (editor, index) {
    const content = editor.getContent({ format: 'html' });
    if (index == '-1') {
      index = 0;
    }
    const part1 = content.substr(0, index);
    const part2 = content.substr(index);
    const bookmark = editor.selection.getBookmark(0);
    const positionString = `<span id="${bookmark.id}_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>`;
    const contentWithString = part1 + positionString + part2;
    editor.setContent(contentWithString, ({ format: 'raw' }));
    editor.selection.moveToBookmark(bookmark);
    return bookmark;
  };

  pro.setCaretPosition = function (element, pos) {
    if (element.setSelectionRange) {
      element.focus();
      element.setSelectionRange(pos, pos);
    } else if (element.createTextRange) {
      const range = element.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  };

  pro.bindTextareaHandlerTiny = function (ed) {
    if (ed.id !== 'cloze_text') {
      return;
    }

    ed.off([
      'keydown',
      'keyup',
      'click',
      'mouseleave',
      'blur',
      'paste',
    ].join(' '));

    ed.on('keyup', (e) => {
      if (e.keyCode == 8 || e.keyCode == 46) {
        pro.deferredCallbackFactory('TinyMceKeyup')(() => {
          pro.checkTextAreaAgainstJson();
        }, 200);
      }
    });
    ed.on('click', () => {
      pro.deferredCallbackFactory('TinyMceClick')(() => {
        const inst = tinyMCE.activeEditor;
        const cursorPosition = pro.getCursorPositionTiny(inst, false);
        ClozeGlobals.cursor_pos = cursorPosition;
        const pos = pro.cursorInGap(cursorPosition);
        pro.checkTextAreaAgainstJson();
        if (pos[1] != -1) {
          pro.setCursorPositionTiny(inst, pos[1]);
          pro.focusOnFormular(pos);
        }
      }, 200);
    });
    ed.on('blur', () => {
      pro.deferredCallbackFactory('TinyMceBlur')(() => {
        pro.checkTextAreaAgainstJson();
      }, 200);
    });

    ed.on('mouseleave', () => {
      pro.deferredCallbackFactory('TinyMceMouseLeave')(() => {
        const inst = tinyMCE.activeEditor;
        const cursorPosition = pro.getCursorPositionTiny(inst, false);
        ClozeGlobals.cursor_pos = cursorPosition;
      }, 200);
    });

    ed.on('paste', (event) => {
      event.preventDefault();
      let clipboard_text = (event.originalEvent || event).clipboardData.getData('text/plain');
      clipboard_text = clipboard_text.replace(/\[gap[\s\S\d]*?\]/g, '[gap]');
      const text = pro.getTextAreaValue();
      const textBefore = text.substring(0, ClozeGlobals.cursor_pos);
      const textAfter = text.substring(ClozeGlobals.cursor_pos, text.length);
      pro.setTextAreaValue(textBefore + clipboard_text + textAfter);
      pro.createNewGapCode('text');
      pro.cleanGapCode();
      ClozeGlobals.cursor_pos = parseInt(ClozeGlobals.cursor_pos) + clipboard_text.length;
      pro.correctCursorPositionInTextarea();
    });
  };
  pro.insertGapToJson = function (index, values, gaptype) {
    const newObjects = new Array({
      answer: '',
      points: 0,
    });
    if (values !== null) {
      const objects = values.split(',');
      if (objects !== null) {
        for (let i = 0; i < objects.length; i++) {
          newObjects[i] = ({
            answer: objects[i],
            points: 0,
          });
        }
      }
    }
    const insert = new Object({
      type: gaptype,
      values: newObjects,
    });
    ClozeSettings.gaps_php[0].splice(index, 0, insert);
  };

  pro.cursorInGap = function (position) {
    const text = pro.getTextAreaValue();
    let end = 0;
    let inGap = -1;
    let gapNumber;
    for (let i = 0; i < ClozeSettings.gaps_php[0].length; i++) {
      const start = text.indexOf('[gap ', end);
      end = text.indexOf('[/gap]', parseInt(end, 10)) + 5;
      if (start < position && end >= position) {
        inGap = parseInt(end, 10) + 1;
        const gapSize = parseInt(end, 10) - parseInt(start, 10);
        let gapContent = text.substr(parseInt(start, 10) + 5, gapSize);
        gapContent = gapContent.split(']');
        gapNumber = gapContent[0];
      }
    }
    return [gapNumber, inGap];
  };

  pro.removeFromTextarea = function (gap_count) {
    const text = pro.getTextAreaValue();
    const pos = parseInt(gap_count, 10) + 1;
    const regexExpression = `\\[gap ${pos}\\](.*?)\\[\\/gap\\]`;
    const regex = new RegExp(regexExpression, 'i');
    const newText = text.replace(regex, '');
    pro.setTextAreaValue(newText);
    pro.cleanGapCode();
  };
  pro.createNewGapCode = function (gaptype) {
    let newText = pro.getTextAreaValue();
    const iterator = newText.match(/\[gap[\s\S\d]*?\](.*?)\[\/gap\]/g);
    let last = 0;
    if (iterator) {
      for (let i = 0; i < iterator.length; i++) {
        last = i;
        if (iterator[i].match(/\[gap\]/)) {
          let values = iterator[i].replace(/\[gap\]/, '');
          values = values.replace(/\[\/gap\]/, '');
          const gap_id = parseInt(i, 10) + 1;
          newText = newText.replace(/\[gap\]/, `[gap ${gap_id}]`);
          pro.insertGapToJson(last, values, gaptype);
        }
      }
    }
    pro.setTextAreaValue(newText);
    pub.paintGaps();
    pro.cleanGapCode();
  };

  pro.cleanGapCode = function () {
    const text = pro.getTextAreaValue();
    let newText = text.replace(/\[gap[\s\S\d]*?\]/g, '[temp]');
    newText = newText.replace(/\[\/gap\]/g, '[/temp]');
    for (let i = 0; i < ClozeSettings.gaps_php[0].length; i++) {
      const gap_id = parseInt(i, 10) + 1;
      newText = newText.replace(/\[temp\]/, `[gap ${gap_id}]`);
      newText = newText.replace(/\[\/temp\]/, '[/gap]');
    }
    pro.setTextAreaValue(newText);
  };

  pro.bindSelectHandler = function () {
    let selector = $('.form-control.gap_combination');
    selector.off('change');
    selector.on('change', function () {
      let value; let
        id;
      if ($(this).attr('class') == 'form-control gap_combination gap_comb_values') {
        value = $(this).val();
        id = $(this).attr('id').split('_');
        ClozeSettings.gaps_combination[id[3]][1][id[4]][id[5]] = value;
      } else if ($(this).attr('label') == 'select') {
        value = parseInt($(this).val(), 10);
        id = $(this).attr('id').split('_');
        const old_value = ClozeSettings.gaps_combination[id[3]][0][id[4]];
        ClozeSettings.gaps_combination[id[3]][0][id[4]] = value;
        ClozeSettings.unused_gaps_comb[old_value] = false;
        ClozeSettings.unused_gaps_comb[value] = true;
      }
      pub.paintGaps();
    });
    selector = $('.clozetype.form-control');
    selector.off('change');
    selector.on('change', function () {
      let value; let
        id;
      value = parseInt($(this).val(), 10);
      id = $(this).attr('id').split('_');
      if (value === 0) {
        ClozeSettings.gaps_php[0][id[1]].type = 'text';
      } else if (value == 1) {
        ClozeSettings.gaps_php[0][id[1]].type = 'select';
      } else if (value == 2) {
        let points = 0;
        let float = parseFloat(ClozeSettings.gaps_php[0][id[1]].values[0].answer);
        if (!isNaN(float)) {
          points = ClozeSettings.gaps_php[0][id[1]].values[0].points;
        } else {
          float = '';
        }
        ClozeSettings.gaps_php[0][id[1]].values = new Object(new Array({
          answer: float,
          lower: float,
          upper: float,
          points,
        }));
        ClozeSettings.gaps_php[0][id[1]].type = 'numeric';
        pro.editTextarea(id[1]);
      }
      pub.paintGaps();
    });
  };

  pro.buildSelectionField = function (type, counter) {
    const prototype_head = $('#select_field');
    prototype_head.clone().attr({
      id: `${type}-gap-r-${counter}`,
      class: `${ClozeGlobals.form_row} interactive row`,
    }).appendTo(ClozeGlobals.form_class);
    const select_field_selector = $(`#${type}-gap-r-${counter}`);
    pub.appendFormClasses(select_field_selector);
    select_field_selector.children(ClozeGlobals.form_options_class).attr('id', `${type}-gap-r-${counter}`);
    select_field_selector.children().children('.form-control').attr(
      {
        id: `clozetype_${counter}`,
        name: `clozetype_${counter}`,
      },
    );
    $(`#clozetype_${counter} option`).attr('selected', false);
    if (type == 'text') {
      $(`#clozetype_${counter} option[value="0"]`).attr('selected', true);
    } else if (type == 'select') {
      $(`#clozetype_${counter} option[value="1"]`).attr('selected', true);
    } else if (type == 'numeric') {
      $(`#clozetype_${counter} option[value="2"]`).attr('selected', true);
    }
  };

  pro.editTextarea = function (gap_count) {
    const text = pro.getTextAreaValue();
    gap_count = parseInt(gap_count, 10) + 1;
    const regexExpression = `\\[gap ${gap_count}\\]([\\s\\S]*?)\\[\\/gap\\]`;
    const regex = new RegExp(regexExpression, 'i');
    let stringBuild = '';
    ClozeSettings.gaps_php[0][gap_count - 1].values.forEach((entry) => {
      if (entry.answer !== undefined) {
        stringBuild += `${entry.answer.replace(/\[/g, '[&hairsp;')},`;
      }
    });
    stringBuild = stringBuild.replace(/,+$/, '');
    const newText = text.replace(regex, `[gap ${gap_count}]${stringBuild}[/gap]`);
    pro.setTextAreaValue(newText);
  };

  pro.buildNumericFormObjectHelper = function (row, type, value) {
    $(`#numeric_prototype_numeric${type}`).clone().attr({
      id: `numeric_answers${type}_${row}`,
      class: `${ClozeGlobals.form_row} interactive row`,
    }).appendTo(ClozeGlobals.form_class);
    const form = $(`#numeric_answers${type}_${row}`);
    pub.appendFormClasses(form);
    form.find(`#gap_a_numeric${type}`).attr({
      id: `gap_${row}_numeric${type}`,
      name: `gap_${row}_numeric${type}`,
      value,
      class: `numeric_gap gap_${row}_numeric${type}`,
    });
  };

  pro.buildFormObject = function (type, counter, values, gap_field_length, shuffle, upper, lower) {
    pro.buildTitle(counter);
    pro.buildSelectionField(type, counter);
    if (type === 'text' || type == 'numeric') {
      $('#prototype_gapsize').clone().attr({
        id: `gap_${counter}_gapsize_row`,
        name: `gap_${counter}_gapsize_row`,
        class: `${ClozeGlobals.form_row} interactive row`,
      }).appendTo(ClozeGlobals.form_class);
      const gapsize_row = $(`#gap_${counter}_gapsize_row`);
      pub.appendFormClasses(gapsize_row);
      gapsize_row.find('#gap_a_gapsize').attr({
        id: `gap_${counter}_gapsize`,
        name: `gap_${counter}_gapsize`,
        class: 'gapsize form-control',
        value: gap_field_length,
      });
    }
    if (type === 'text') {
      pro.changeIdentifierTextField(type, counter, values);
    } else if (type === 'select') {
      $('#shuffle_answers').clone().attr({
        id: `shuffle_answers_${counter}`,
        class: `${ClozeGlobals.form_row} interactive row`,
      }).appendTo(ClozeGlobals.form_class);
      pub.appendFormClasses($(`#shuffle_answers_${counter}`));
      pro.changeIdentifierTextField(type, counter, values);
      if (shuffle === true) {
        $(`#shuffle_${counter}`).prop('checked', true);
      }
    } else if (type === 'numeric') {
      pro.buildNumericFormObjectHelper(counter, '', values[0].answer);
      pro.buildNumericFormObjectHelper(counter, '_lower', values[0].lower);
      pro.buildNumericFormObjectHelper(counter, '_upper', values[0].upper);
      pro.buildNumericFormObjectHelper(counter, '_points', values[0].points);
      $(`#numeric_answers_points_${counter}`).find('.gap_counter').attr(
        {
          id: `gap[${counter}]`,
          name: `gap[${counter}]`,
        },
      );
      $('#numeric_prototype_remove_button').clone().attr({
        id: `remove_gap_container_${counter}`,
        name: `remove_gap_container_${counter}`,
        class: `${ClozeGlobals.form_row} interactive row`,
      }).appendTo(ClozeGlobals.form_class);
      $(`#remove_gap_container_${counter}`).find('.btn.btn-default.remove_gap_button').attr(
        {
          id: `remove_gap_${counter}`,
        },
      );
    }
    $('#error_answer').clone().attr({
      id: `gap_error_${counter}`,
      class: `${ClozeGlobals.form_row} interactive row`,
    }).appendTo(ClozeGlobals.form_class);
    const gap_error = $(`#gap_error_${counter}`);
    pub.appendFormClasses(gap_error);
    gap_error.find('#error_answer_val').attr({
      id: '',
      class: `error_answer_${counter}`,
      name: `error_answer_${counter}`,
    });
    pro.moveDeleteGapButton(counter);
    ClozeGapCombinationBuilder.appendGapCombinationButton();
  };

  pro.changeIdentifierTextField = function (type, counter_question, answers) {
    let c = 0;
    let text_row_selector;
    answers.forEach((s) => {
      if (c === 0) {
        $('#answer_text').clone().attr(
          {
            id: `text_row_${counter_question}_${c}`,
            class: `${ClozeGlobals.form_row} interactive row`,
          },
        ).appendTo(ClozeGlobals.form_class);
        text_row_selector = $(`#text_row_${counter_question}_${c}`);
        pub.appendFormClasses(text_row_selector);
        text_row_selector.find('#table_body').attr(
          {
            id: `table_body_${counter_question}`,
          },
        );
        $(`#table_body_${counter_question}`).find('tr').attr({
          class: `${ClozeGlobals.form_row} interactive form-inline`,
        });
        text_row_selector.find('.btn.btn-default.remove_gap_button').attr(
          {
            id: `remove_gap_${counter_question}`,
          },
        );
      } else {
        $('#inner_text').clone().attr(
          {
            id: `text_row_${counter_question}_${c}`,
            class: `${ClozeGlobals.form_row} interactive form-inline`,
          },
        ).appendTo(`#table_body_${counter_question}`);
      }
      text_row_selector = $(`#text_row_${counter_question}_${c}`);
      text_row_selector.find('.gap_counter').attr(
        {
          id: `gap[${counter_question}]`,
          name: `gap[${counter_question}]`,
        },
      );
      text_row_selector.find('#gap_points').attr(
        {
          id: `gap_${counter_question}` + `[points][${c}]`,
          name: `gap_${counter_question}` + `[points][${c}]`,
          class: `gap_points gap_points_${counter_question} form-control`,
          value: s.points,
        },
      );
      text_row_selector.find('.text_field').attr(
        {
          name: `gap_${counter_question}` + `[answer][${c}]`,
          id: `gap_${counter_question}` + `[answer][${c}]`,
          value: s.answer,
          class: 'text_field form-control',
        },
      );
      $(`#shuffle_answers_${counter_question}`).find('#shuffle_dummy').attr(
        {
          name: `shuffle_${counter_question}`,
          class: 'shuffle',
          id: `shuffle_${counter_question}`,
        },
      );
      text_row_selector.find('.clone_fields_add:first-child').attr(
        {
          name: `add_gap_${counter_question}_${c}`,
        },
      );
      text_row_selector.find('.clone_fields_remove:first-child').attr(
        {
          name: `remove_gap_${counter_question}_${c}`,
        },
      );
      c++;
    });
  };

  pro.buildTitle = function (counter) {
    $('#gap_title').clone().attr({
      id: `title_${counter}`,
      name: `title_${counter}`,
      class: `${ClozeGlobals.form_row} interactive`,
    }).appendTo(ClozeGlobals.form_class);
    pub.appendFormHeaderClasses($(`#tile_${counter}`));
    $(`#title_${counter}`).find('h3').text(`${ClozeSettings.gap_text} ${counter + 1}`);
  };

  pro.bindTextareaHandler = function () {
    const cloze_text_selector = $('#cloze_text');
    cloze_text_selector.on('keydown', () => {
      const cursorPosition = $('#cloze_text').prop('selectionStart');
      const pos = pro.cursorInGap(cursorPosition);
      ClozeGlobals.cursor_pos = cursorPosition;
      if (pos[1] != -1) {
        pro.setCaretPosition(document.getElementById('cloze_text'), pos[1]);
        pro.focusOnFormular(pos);
      }
    });

    cloze_text_selector.on('keyup', (e) => {
      if (e.keyCode == 8 || e.keyCode == 46) {
        pro.checkTextAreaAgainstJson();
      }
    });
    cloze_text_selector.on('click', () => {
      const cursorPosition = $('#cloze_text').prop('selectionStart');
      const pos = pro.cursorInGap(cursorPosition);
      ClozeGlobals.cursor_pos = cursorPosition;
      if (pos[1] != -1) {
        pro.setCaretPosition(document.getElementById('cloze_text'), pos[1]);
        pro.focusOnFormular(pos);
      }
      return false;
    });
    cloze_text_selector.on('paste', (event) => {
      event.preventDefault();
      let clipboard_text = (event.originalEvent || event).clipboardData.getData('text/plain');
      clipboard_text = clipboard_text.replace(/\[gap[\s\S\d]*?\]/g, '[gap]');
      const text = pro.getTextAreaValue();
      const textBefore = text.substring(0, ClozeGlobals.cursor_pos);
      const textAfter = text.substring(ClozeGlobals.cursor_pos, text.length);
      pro.setTextAreaValue(textBefore + clipboard_text + textAfter);
      pro.createNewGapCode('text');
      pro.cleanGapCode();
      pub.paintGaps();
      ClozeGlobals.cursor_pos = parseInt(ClozeGlobals.cursor_pos, 10) + clipboard_text.length;
      pro.setCaretPosition(cloze_text_selector, parseInt(ClozeGlobals.cursor_pos, 10));
    });
  };

  pro.checkTextAreaAgainstJson = function () {
    const text = pro.getTextAreaValue();
    const text_match = text.match(/\[gap[\s\S\d]*?\](.*?)\[\/gap\]/g);
    const to_be_removed = [];
    if (ClozeSettings.gaps_php[0] !== null && ClozeSettings.gaps_php[0].length !== 0 && text_match !== null && text_match.length !== null) {
      let i;
      if (ClozeSettings.gaps_php[0].length != text_match.length) {
        const gap_exists_in_txtarea = [];
        for (i = 0; i < text_match.length; i++) {
          let gap_exists = text_match[i].split(']');
          gap_exists = gap_exists[0].split('[gap ');
          gap_exists_in_txtarea.push(gap_exists[1]);
        }
        for (i = 0; i < ClozeSettings.gaps_php[0].length; i++) {
          const j = i + 1;
          if (gap_exists_in_txtarea.indexOf(`${j}`) == -1) {
            to_be_removed.push(i);
          }
        }
        let allready_removed = 0;
        for (i = 0; i < to_be_removed.length; i++) {
          const k = to_be_removed[i] - allready_removed;
          ClozeSettings.gaps_php[0].splice(k, 1);
          allready_removed++;
        }
        pro.cleanGapCode();
        pub.paintGaps();
        pro.correctCursorPositionInTextarea();
      }
    } else {
      ClozeSettings.gaps_php[0] = [];
      pub.paintGaps();
    }
  };

  pro.correctCursorPositionInTextarea = function () {
    if (typeof (tinymce) !== 'undefined') {
      setTimeout(() => {
        const pos = pro.cursorInGap(ClozeGlobals.cursor_pos);
        if (pos[1] != -1) {
          pro.setCursorPositionTiny(tinyMCE.activeEditor, pos[1]);
        } else {
          pro.setCursorPositionTiny(tinyMCE.activeEditor, parseInt(ClozeGlobals.cursor_pos, 10));
        }
      }, 0);
    } else {
      setTimeout(() => {
        const cloze_text_selector = document.getElementById('cloze_text');
        const pos = pro.cursorInGap(ClozeGlobals.cursor_pos);
        if (pos[1] != -1) {
          pro.setCaretPosition(cloze_text_selector, parseInt(pos[1], 10));
        } else {
          pro.setCaretPosition(cloze_text_selector, parseInt(ClozeGlobals.cursor_pos, 10));
        }
      }, 0);
    }
  };

  // @todo wird das noch gebraucht?!
  pro.createGapListener = function () {
    const selector = $('#createGaps');
    selector.off('click');
    selector.on('click', () => {
      if (pro.getTextAreaValue().match(/\[gap\]/g)) {
        pro.createNewGapCode();
      }
      pro.checkTextAreaAgainstJson();
    });
    // return false;
  };

  pro.getPositionFromInputs = function (selector, single_value) {
    const getPosition = selector.attr('name');
    let pos = getPosition.split('_');
    if (single_value) {
      return pos;
    }

    pos = pos[1].split('[');
    const answer = pos[2].split(']');
    return [pos[0], answer[0]];
  };

  pro.bindInputHandler = function () {
    let listener = 'blur';
    let selector = $('.text_field');
    selector.off('blur');
    selector.on(listener, function (event) {
      const pos = pro.getPositionFromInputs($(this));
      ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].answer = $(this).val();
      pro.editTextarea(pos[0]);
      if (ClozeGlobals.clone_active != -1) {
        if (event.type == 'blur') {
          $('.interactive').find(`#gap_${pos[0]}\\[answer\\]\\[${pos[1]}\\]`).val($(this).val());
        }
      }
      pro.checkForm();
    });
    listener = 'keyup';
    selector.off(listener);
    selector.on(listener, function (event) {
      pro.checkTextBoxQuick($(this));
    });
    selector = $('.gapsize');
    selector.off('blur');
    selector.blur(function () {
      const pos = pro.getPositionFromInputs($(this), true);
      ClozeSettings.gaps_php[0][pos[1]].text_field_length = $(this).val();
      if (ClozeGlobals.clone_active != -1) {
        $('.interactive').find(`#gap_${pos[1]}_gapsize`).val($(this).val());
      }
      pro.checkForm();
    });
    selector = $('.gap_points');
    selector.off('keyup');
    selector.keyup(function () {
      const pos = pro.getPositionFromInputs($(this));
      ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].points = $(this).val();
      if (ClozeGlobals.clone_active != -1) {
        $('.interactive').find(`#gap_${pos[0]}\\[points\\]\\[${pos[1]}\\]`).val($(this).val());
      }
      pro.checkForm();
    });

    selector = $('.gap_combination_points');
    selector.off('keyup');
    selector.keyup(function () {
      const pos = $(this).attr('id').split('_');
      ClozeSettings.gaps_combination[pos[3]][2][pos[4]] = $(this).val();
      pro.checkForm();
    });

    selector = $('.shuffle');
    selector.off('change');
    selector.on('change', function () {
      const pos = pro.getPositionFromInputs($(this), true);
      const checked = $(this).is(':checked');
      ClozeSettings.gaps_php[0][pos[1]].shuffle = checked;
      if (ClozeGlobals.clone_active != -1) {
        $('.interactive').find(`#shuffle_${pos[1]}`).attr('checked', checked);
      }
      pro.checkForm();
    });

    selector = $('.numeric_gap');
    selector.off('blur');
    selector.blur(function () {
      const pos = pro.getPositionFromInputs($(this), true);
      $(this).val($(this).val().replace(/ /g, ''));
      if (pos.length == 3) {
        ClozeSettings.gaps_php[0][pos[1]].values[0].answer = $(this).val();
        pro.editTextarea(pos[1]);
        if (ClozeGlobals.clone_active != -1) {
          $('.interactive').find(`#gap_${pos[1]}_numeric`).val($(this).val());
        }
      } else if (pos[3] == 'lower') {
        ClozeSettings.gaps_php[0][pos[1]].values[0].lower = $(this).val();
        if (ClozeGlobals.clone_active != -1) {
          $('.interactive').find(`#gap_${pos[1]}_numeric_lower`).val($(this).val());
        }
      } else if (pos[3] == 'upper') {
        ClozeSettings.gaps_php[0][pos[1]].values[0].upper = $(this).val();
        if (ClozeGlobals.clone_active != -1) {
          $('.interactive').find(`#gap_${pos[1]}_numeric_upper`).val($(this).val());
        }
      } else if (pos[3] == 'points') {
        ClozeSettings.gaps_php[0][pos[1]].values[0].points = $(this).val();
        if (ClozeGlobals.clone_active != -1) {
          $('.interactive').find(`#gap_${pos[1]}_numeric_points`).val($(this).val());
        }
      }
      pro.checkForm();
    });
  };

  pro.checkTextBoxQuick = function (selector) {
    const error_counter = pro.checkInputElementNotEmpty(selector, selector.val());
    let more_errors = 0;
    const find_gap_id = selector.attr('id').split('_')[1].split('[');
    const gap_id = parseInt(find_gap_id[0], 10);
    if (error_counter === 1) {
      $(`#gap_error_${gap_id}`).find('.value.form_error').removeClass('prototype');
    } else {
      more_errors = 0;
      const count = ClozeSettings.gaps_php[0][gap_id].values.length;
      let value = '';
      for (let i = 0; i < count; i++) {
        value = $(`#gap_${gap_id}\\[answer\\]\\[${i}\\]`).val();
        if (value === '' || value === null) {
          more_errors++;
        }
      }
      if (more_errors === 0) {
        $(`#gap_error_${gap_id}`).find('.value.form_error').addClass('prototype');
      }
    }
  };

  pro.checkForm = function () {
    let row = 0;
    ClozeSettings.gaps_php[0].forEach((entry) => {
      let input_failed = 0;
      if (entry.type === 'numeric') {
        input_failed += pro.checkInputElementNotEmpty($(`.gap_${row}_numeric`), entry.values[0].answer);
        input_failed += pro.checkInputElementNotEmpty($(`.gap_${row}_numeric_upper`), entry.values[0].upper);
        input_failed += pro.checkInputElementNotEmpty($(`.gap_${row}_numeric_lower`), entry.values[0].lower);
        if (entry.values[0].error !== false) {
          const obj = entry.values[0].error;
          if (obj) {
            Object.keys(obj).forEach((key) => {
              if (obj[key] === true) {
                pro.highlightRed($(`#gap_${row}_numeric_${key}`));
                pro.showHidePrototypes(row, 'formula', true);
              } else {
                pro.removeHighlight($(`#gap_${row}_numeric_${key}`));
              }
            });
          }
        }
        if (pro.checkFormula(entry.values[0].lower)) {
          pro.removeHighlight($(`#gap_${row}_numeric_lower`));
        } else {
          pro.highlightRed($(`#gap_${row}_numeric_lower`));
        }
        if (pro.checkFormula(entry.values[0].upper)) {
          pro.removeHighlight($(`#gap_${row}_numeric_upper`));
        } else {
          pro.highlightRed($(`#gap_${row}_numeric_upper`));
        }
        input_failed += pro.checkInputIsNumeric(entry.values[0].points, row, '_points');
        if (input_failed !== 0) {
          pro.showHidePrototypes(row, 'number', true);
        } else {
          pro.showHidePrototypes(row, 'number', false);
        }
        if (entry.values[0].points === '0') {
          pro.highlightRed($(`#gap_${row}_numeric_points`));
          pro.showHidePrototypes(row, 'points', true);
        } else {
          pro.showHidePrototypes(row, 'points', false);
        }
      } else {
        let points = 0;
        let counter = 0;
        let number = true;
        let select_at_least_on_positive = false;
        entry.values.forEach((values) => {
          let points_value = values.points;
          points_value = points_value.toString().replace(',', '.');
          points += parseFloat(points_value);
          if (parseFloat(points_value) > 0) {
            select_at_least_on_positive = true;
          }
          if (isNaN(points_value) || points_value === '') {
            pro.highlightRed($(`#gap_${row}\\[points\\]\\[${counter}\\]`));
            number = false;
          } else {
            pro.removeHighlight($(`#gap_${row}\\[points\\]\\[${counter}\\]`));
          }
          const failed = pro.checkInputElementNotEmpty($(`#gap_${row}\\[answer\\]\\[${counter}\\]`), values.answer);
          input_failed += failed;
          counter++;
        });
        if (input_failed > 0) {
          pro.showHidePrototypes(row, 'value', true);
        } else {
          pro.showHidePrototypes(row, 'value', false);
        }
        if (number === false) {
          pro.showHidePrototypes(row, 'number', true);
        } else {
          pro.showHidePrototypes(row, 'number', false);
        }
        if (parseFloat(points) <= 0) {
          if (ClozeSettings.unused_gaps_comb[row] === true) {
            pro.removeHighlight($(`.gap_points_${row}`));
            pro.showHidePrototypes(row, 'points', false);
          } else if (entry.type === 'select' && select_at_least_on_positive === true) {
            pro.removeHighlight($(`.gap_points_${row}`));
            pro.showHidePrototypes(row, 'points', false);
          } else {
            pro.highlightRed($(`.gap_points_${row}`));
            pro.showHidePrototypes(row, 'points', true);
          }
        } else if (number === true) {
          pro.removeHighlight($(`.gap_points_${row}`));
          pro.showHidePrototypes(row, 'points', false);
        }
      }
      row++;
    });
    $('#gap_json_post').val(JSON.stringify(ClozeSettings.gaps_php));
    $('#gap_json_combination_post').val(JSON.stringify(ClozeSettings.gaps_combination));
  };

  pro.checkInputIsNumeric = function (number, row, field) {
    if (isNaN(number) || number === '') {
      pro.highlightRed($(`.gap_${row}_numeric${field}`));
      return 1;
    }

    pro.removeHighlight($(`.gap_${row}_numeric${field}`));

    return 0;
  };

  pro.showHidePrototypes = function (row, type, show) {
    if (show) {
      $(`.error_answer_${row}`).find(`.${type}`).removeClass('prototype');
    } else {
      $(`.error_answer_${row}`).find(`.${type}`).addClass('prototype');
    }
  };

  pro.checkFormula = function (val) {
    const regex = /^-?(\d*)(,|\.|\/){0,1}(\d*)$/;
    return regex.exec(val);
  };

  pro.highlightRed = function (selector) {
    if (ClozeGlobals.jour_fixe_incompatible) {
      selector.addClass(ClozeGlobals.form_error);
    }
  };

  pro.removeHighlight = function (selector) {
    if (ClozeGlobals.jour_fixe_incompatible) {
      selector.removeClass(ClozeGlobals.form_error);
    }
  };

  pro.highlightYellow = function (selector) {
    if (ClozeGlobals.jour_fixe_incompatible) {
      selector.addClass(ClozeGlobals.form_warning);
    }
  };

  pro.removeHighlightYellow = function (selector) {
    if (ClozeGlobals.jour_fixe_incompatible) {
      selector.removeClass(ClozeGlobals.form_warning);
    }
  };

  pro.checkInputElementNotEmpty = function (selector, value) {
    if (value === '' || value === null) {
      pro.highlightRed(selector);
      return 1;
    }

    pro.removeHighlight(selector);
    return 0;
  };

  pro.focusOnFormular = function (pos) {
    pro.cloneFormPart(pos[0]);
    // ToDo: fix fokus
    $('#ilGapModal').modal('show');
    ClozeGlobals.gap_restore = true;
    const gap = parseInt(pos[0], 10) - 1;
    const lightBoxInner = $('#ilGapModal');
    $('#cloze_text').focus();
    lightBoxInner.find(`#gap_${gap}\\[answer\\]\\[0\\]`).focus();
    lightBoxInner.find(`#gap_${gap}_numeric`).focus();

    $('#ilGapModal').off('hidden.bs.modal');
    $('#ilGapModal').on('hidden.bs.modal', () => {
      pro.restoreSavedGap();
      pro.checkForm();
    });
  };

  pro.cloneFormPart = function (pos) {
    ClozeGlobals.clone_active = pos;
    pos = parseInt(pos, 10) - 1;

    if (ClozeSettings.gaps_php[0][pos]) {
      $('.modal-body').html('');
      if (ClozeGlobals.jour_fixe_incompatible === false) {
        ClozeSettings.gap_backup = JSON.parse(JSON.stringify({
          id: pos,
          values: ClozeSettings.gaps_php[0][pos],
        }));
      }

      let clone_type = ClozeSettings.gaps_php[0][pos].type;
      if (clone_type === '') {
        clone_type = 'text';
      }
      if (clone_type == 'text') {
        $(`#text_row_${pos}_0`).clone(true).removeAttr('id').appendTo('.modal-body');
      } else if (clone_type == 'select') {
        $(`#text_row_${pos}_0`).clone(true).removeAttr('id').appendTo('.modal-body');
      } else if (clone_type == 'numeric') {
        $(`#numeric_answers_${pos}`).clone(true).removeAttr('id').appendTo('.modal-body');
        $(`#numeric_answers_lower_${pos}`).clone(true).removeAttr('id').appendTo('.modal-body');
        $(`#numeric_answers_upper_${pos}`).clone(true).removeAttr('id').appendTo('.modal-body');
        $(`#numeric_answers_points_${pos}`).clone(true).removeAttr('id').appendTo('.modal-body');
        $(`#remove_gap_container_${pos}`).clone(true).appendTo('.modal-body');
      }
      $(`.error_answer_${pos}`).clone(true).removeAttr('id').appendTo('.modal-body');
      const gapName = parseInt(pos, 10) + 1;
      $('.modal-title').html(`Gap ${gapName}`);
      pro.addModalFakeFooter();
    }
  };

  pro.removeSelectOption = (e) => {
    let getPosition; let pos; let
      value;
    const target = e.currentTarget.parentNode;
    if ($(target).attr('class') !== 'clone_fields_remove combination btn btn-link') {
      value = $(target).parent().parent().find('.text_field')
        .val();
      $(`[data-answer="${value}"]`).show();
      getPosition = $(target).prev().attr('name');
      pos = getPosition.split('_');
      ClozeSettings.gaps_php[0][pos[2]].values.splice(pos[3], 1);
      pro.editTextarea(pos[2]);
      if (ClozeSettings.gaps_php[0][pos[2]].values.length === 0) {
        ClozeSettings.gaps_php[0].splice(pos[2], 1);
        pro.removeFromTextarea(pos[2]);
      }
    } else {
      getPosition = $(target).parent().attr('name');
      pos = getPosition.split('_');
      ClozeSettings.gaps_combination[pos[2]][0].splice(parseInt(pos[3], 10), 1);
      ClozeSettings.gaps_combination[pos[2]][1].forEach((answers) => {
        answers.splice(parseInt(pos[3], 10), 1);
      });
      if (ClozeSettings.gaps_combination[pos[2]][0].length < 2) {
        ClozeSettings.gaps_combination.splice(parseInt(pos[2], 10), 1);
      }
    }
    pub.paintGaps();
    return false;
  },

  pro.appendEventListenerToBeRefactored = () => {
    $('.clone_fields_add .glyph').off('click');
    $('.clone_fields_add .glyph').on('click', (e) => {
      let getPosition; let pos; let
        insert;
      const target = e.currentTarget.parentNode;
      if ($(target).attr('class') != 'clone_fields_add combination btn btn-link') {
        getPosition = $(target).attr('name');
        pos = getPosition.split('_');
        insert = new Object({
          points: '0',
          answer: $(this).data('answer'),
        });
        if ($(target).data('answer') != '') {
          $(target).hide();
        }
        ClozeSettings.gaps_php[0][pos[2]].values.splice(parseInt(pos[3], 10) + 1, 0, insert);
        pro.editTextarea(pos[2]);
      } else {
        getPosition = $(target).parent().attr('name');
        pos = getPosition.split('_');
        ClozeSettings.gaps_combination[pos[2]][0].splice(parseInt(pos[3], 10) + 1, 0, -1);
        ClozeSettings.gaps_combination[pos[2]][1].forEach((answers) => {
          answers.splice(parseInt(pos[3], 10) + 1, 0, -1);
        });
      }
      pub.paintGaps();
      return false;
    });

    $('.clone_fields_add_value .glyph').off('click');
    $('.clone_fields_add_value .glyph').on('click', (e) => {
      let getPosition; let
        pos;
      const target = e.currentTarget.parentNode;
      getPosition = $(target).parent().attr('name');
      pos = getPosition.split('_');

      const dummy_array = [];
      const { length } = ClozeSettings.gaps_combination[pos[2]][1][0];
      for (let i = 0; i < length; i++) {
        dummy_array.push(null);
      }
      ClozeSettings.gaps_combination[pos[2]][1].splice(parseInt(pos[3], 10) + 1, 0, dummy_array);
      ClozeSettings.gaps_combination[pos[2]][2].splice(parseInt(pos[3], 10) + 1, 0, 0);
      pub.paintGaps();
      return false;
    });

    $('.clone_fields_remove_value .glyph').off('click');
    $('.clone_fields_remove_value .glyph').on('click', (e) => {
      const target = e.currentTarget.parentNode;
      const getPosition = $(target).parent().attr('name');
      const pos = getPosition.split('_');

      if (ClozeSettings.gaps_combination[pos[2]][1].length === 1) {
        ClozeSettings.gaps_combination.splice(parseInt(pos[2], 10), 1);
      } else {
        ClozeSettings.gaps_combination[pos[2]][1].splice(parseInt(pos[3], 10), 1);
        ClozeSettings.gaps_combination[pos[2]][2].splice(parseInt(pos[3], 10), 1);
      }
      pub.paintGaps();
      return false;
    });

    $('.clone_fields_remove .glyph').off('click', pro.removeSelectOption);
    $('.clone_fields_remove .glyph').on('click', pro.removeSelectOption);

    $('.remove_gap_button').off('click');
    $('.remove_gap_button').on('click', (e) => {
      const target = e.currentTarget;
      const position = $(target).attr('id').split('_');
      const whereAmI = $(target).parents().eq(4).attr('class');
      if (confirm($('#delete_gap_question').text())) {
        ClozeSettings.gaps_php[0].splice(position[2], 1);
        pro.removeFromTextarea(position[2]);
        pub.paintGaps();
        if (whereAmI == 'modal-body') {
          $('#ilGapModal').modal('hide');
        }
      }
    });
  };

  pub.Init = function () {
    ClozeSettings.gaps_combination = jQuery().ensureNoArrayIsAnObjectRecursive(ClozeSettings.gaps_combination);
    ClozeSettings.gaps_php = $.map(ClozeSettings.gaps_php, (value) => [value]);

    if ($(ClozeGlobals.form_class).length === 0 && $(ClozeGlobals.form_class_adjustment).length === 1) {
      ClozeGlobals.form_class = ClozeGlobals.form_class_adjustment;
    }

    pro.checkJSONArraysOnEntry();
    pro.bindTextareaHandler();
    pub.paintGaps();
    pro.createGapListener();
    pro.appendEventListenerToBeRefactored();
  };

  pub.addGapClickFunction = (gaptype) => {
    $('#cloze_text').insertGapCodeAtCaret();
    pro.createNewGapCode(gaptype);
    return false;
  };

  pub.appendFormHeaderClasses = function (selector) {
    selector.children().first().attr('class', ClozeGlobals.form_header);
    selector.children().first().next().attr('class', ClozeGlobals.form_header_value);
  };

  pub.appendFormClasses = function (selector) {
    selector.children().first().attr('class', ClozeGlobals.form_options);
    selector.children().first().next().attr('class', ClozeGlobals.form_value);
  };

  pub.paintGaps = function () {
    const last_position = $(window).scrollTop();
    $('.interactive').remove();
    let c = 0;
    ClozeSettings.gaps_php.forEach((obj, counter) => {
      obj.forEach(() => {
        const { type } = obj[c];
        const { values } = obj[c];
        const { text_field_length } = obj[c];
        let shuffle = 0;
        let upper = '';
        let lower = '';
        if (type === 'select') {
          shuffle = obj[c].shuffle;
        }
        if (type === 'numeric') {
          upper = obj[c].upper;
          lower = obj[c].lower;
        }
        // var gap_combination = obj[c].used_in_gap_combination;
        pro.buildFormObject(type, c, values, text_field_length, shuffle, upper, lower);
        c++;
      });
    });
    ClozeGlobals.gap_count = c;
    ClozeGapCombinationBuilder.refreshUnusedGapsForCombination();
    if (ClozeSettings.gaps_combination.length > 0) {
      ClozeGapCombinationBuilder.appendGapCombinationForm();
    }
    pro.moveFooterBelow();
    pro.bindSelectHandler();
    pro.bindInputHandler();
    pro.appendEventListenerToBeRefactored();
    pro.checkForm();
    if (ClozeGlobals.clone_active != -1) {
      pro.cloneFormPart(ClozeGlobals.clone_active);
    }
    if (typeof (tinyMCE) !== 'undefined') {
      ilTinyMceInitCallbackRegistry.addCallback(pro.bindTextareaHandlerTiny);
    }
    $(window).scrollTop(last_position);
  };

  pub.showHidePointsFieldForGaps = function (gap, show) {
    $(`#numeric_answers_points_${gap}`).css('display', show);
    $(`.gap_points_${gap}`).css('display', show);
    $(`#text_row_${gap}_0`).find('th').eq(1).css('display', show);
  };

  pub.protect = pro;
  return pub;
}());

var ClozeGapCombinationBuilder = (function () {
  const pub = {}; const
    pro = {};

  pro.buildCombinationHeader = function (combinationCounter, i) {
    $('#gap_combination_header').clone().attr({
      class: `${ClozeGlobals.form_row} interactive`,
      id: `gap_combination_header_${combinationCounter}`,
    }).appendTo(ClozeGlobals.form_class);
    const gapCombinationHeader = $(`#gap_combination_header_${combinationCounter}`);
    ClozeQuestionGapBuilder.appendFormHeaderClasses(gapCombinationHeader);
    gapCombinationHeader.find('.ilHeader').html(`${ClozeSettings.combination_text} ${combinationCounter}`);
    gapCombinationHeader.attr('copy', `<h3>${ClozeSettings.combination_text} ${combinationCounter}</h3>`);

    $('#gap_combination').clone().attr({
      id: `gap_combination_${i}`,
      class: `${ClozeGlobals.form_row} interactive row clear_before_use`,
    }).appendTo(ClozeGlobals.form_class);
    $(`#gap_combination_${i}`).find('.form-group').attr({
      id: `gap_id_select_append_${i}_0`,
    });
    ClozeQuestionGapBuilder.appendFormClasses($(`#gap_combination_${i}`));
  };

  pro.fillCombinationSelectWithGapOptions = function (gaps, i, g) {
    let buildOptionsSelect = $('#select_option_placeholder').html();
    const pos = parseInt(gaps, 10) + 1;
    $.each(ClozeSettings.gaps_php[0], (k) => {
      const value = parseInt(k, 10) + 1;
      if (pos === value) {
        buildOptionsSelect += `<option selected value="${k}">${ClozeSettings.gap_text} ${value}</option>`;
      } else if (ClozeSettings.unused_gaps_comb[k] === false) {
        buildOptionsSelect += `<option value="${k}">${ClozeSettings.gap_text} ${value}</option>`;
      }
    });
    $(`#gap_id_select_${i}_${g}`).html(buildOptionsSelect);
  };

  pro.multiplyCombinationAnswers = function (i, object) {
    let text = '';
    $(`.value_container_${i}_0`).find('select').each(function (index) {
      // Todo: replace this with a proper header function and not a workaround
      text = '';
      if ($(`#gap_id_select_${i}_${index} option:selected`).val() != 'none_selected_minus_one') {
        text = $(`#gap_id_select_${i}_${index} option:selected`).text();
      }
      $(`#gap_id_value_append_${i}_0`).find('.stretch_row').append(`<td class=dummy_${index}></td>`);
      $(`#gap_id_value_append_${i}_0`).find(`.dummy_${index}`).append($(this).clone().attr({
        id: '',
        name: '',
      }).addClass('small_hidden'));
      $(`#gap_id_value_append_${i}_0`).find('.first_row').append(`<td>${text} </td>`);
    });
    for (var j = 1; j < object.length; j++) {
      $('.gap_combination_spacer').clone().attr({
        class: 'gap_combination_spacer_applied',
      }).appendTo(`#gap_id_value_append_${i}_0`);

      $(`.value_container_${i}_0`).clone().attr({
        class: `value_container_${i}_${j} form-inline`,
      }).appendTo(`#gap_id_value_append_${i}_0`);

      $(`.value_container_${i}_${j}`).find('select').each(function (index) {
        $(this).attr({
          id: `gap_id_value_${i}_${j}_${index}`,
          name: `gap_combination[${i}][${j}][${index}][value]`,
        });
      });

      $(`.value_container_${i}_${j}`).find('.add_remove_buttons_gap').each(function (index) {
        $(this).attr({
          name: `gap_combination_${i}_${j}_${index}`,
        });
      });
    }
  };

  pro.setValuesForCombinationAnswers = function (i, object) {
    let default_value = 'none_selected_minus_one';
    for (var j = 0; j < object.length; j++) {
      $(`.value_container_${i}_${j}`).find('select').each(function (index) {
        if (object[j][index] !== -1) {
          default_value = object[j][index];
        } else {
          default_value = 'none_selected_minus_one';
        }
        $(this).attr({
          id: `gap_id_value_${i}_${j}_${index}`,
          name: `gap_combination_values[${i}][${j}][${index}]`,
          class: 'form-control gap_combination gap_comb_values',
        });
        $(this).val(default_value);
        if ($(this).val() === null || $(this).val() === '') {
          $(this).val('none_selected_minus_one');
        }
      });
    }
  };

  pro.addCloneButtonsForCombinations = function (i, j) {
    $('.add_remove_buttons').clone().attr({
      class: 'add_remove_buttons_gap',
      name: `gap_combination_${i}_${j}`,
    }).insertAfter(`#gap_id_select_${i}_${j}`);
    let counter = 0;
    $.each(ClozeSettings.gaps_combination, (index, value) => {
      counter += value[0].length;
    });
    if (counter === ClozeSettings.gaps_php[0].length) {
      $(`#gap_combination_${i}`).find('.clone_fields_add').remove();
    }
  };

  pro.addCloneButtonsForCombinationValues = function (i, j, k) {
    $('.add_remove_buttons').clone().attr({
      class: 'add_remove_buttons_gap',
      name: `gap_combination_${i}_${j}_${k}`,
    }).appendTo(`.value_container_${i}_${j}`);
    $(`.value_container_${i}_${j}`).find('.clone_fields_add.combination.btn.btn-link').attr({
      class: 'clone_fields_add_value combination btn btn-link',
    });
    $(`.value_container_${i}_${j}`).find('.clone_fields_remove.combination.btn.btn-link').attr({
      class: 'clone_fields_remove_value combination btn btn-link',
    });
  };

  pub.refreshUnusedGapsForCombination = function () {
    ClozeSettings.gaps_php[0].forEach((unused, key) => {
      ClozeSettings.unused_gaps_comb[key] = false;
    });
    ClozeSettings.gaps_combination.forEach((gaps) => {
      gaps[0].forEach((gap) => {
        ClozeSettings.unused_gaps_comb[gap] = true;
        ClozeQuestionGapBuilder.showHidePointsFieldForGaps(gap, 'none');
      });
    });
  };

  pub.appendGapCombinationForm = function () {
    $.each(ClozeSettings.gaps_combination, (i, combination) => {
      const combinationCounter = parseInt(i) + 1;
      pro.buildCombinationHeader(combinationCounter, i);
      const gapCombinationSelector = $(`#gap_combination_${i}`);
      let first_row = true;

      $.each(combination[0], (g, gaps) => {
        if (first_row) {
          gapCombinationSelector.find('#gap_id_select').attr({
            id: `gap_id_select_${i}_0`,
            name: `gap_combination[select][${i}][0]`,
          });
          first_row = false;
        } else {
          $('.gap_combination_spacer').clone().attr({
            class: 'gap_combination_spacer_applied',
          }).appendTo(`#gap_id_select_append_${i}_0`);
          gapCombinationSelector.find(`#gap_id_select_${i}_0`).clone().attr({
            id: `gap_id_select_${i}_${g}`,
            name: `gap_combination[select][${i}][${g}]`,
          }).appendTo(`#gap_id_select_append_${i}_0`);
          $(`#gap_id_select_${i}_${g}`).html('');
        }
        pro.addCloneButtonsForCombinations(i, g);
        pro.fillCombinationSelectWithGapOptions(gaps, i, g);
      });

      $('#gap_combination_value').clone().attr({
        id: `gap_combination_values_${i}`,
        class: `${ClozeGlobals.form_row} interactive row clear_before_use`,
      }).appendTo($(`#gap_combination_${i}`).parent());
      $(`#gap_combination_values_${i}`).find('.form-group').attr({
        id: `gap_id_value_append_${i}_0`,
      });
      $(`#gap_combination_values_${i}`).find('.value_container').attr({
        class: `value_container_${i}_0 form-inline`,
      });
      const gapCombinationValues = $(`#gap_combination_values_${i}`);
      ClozeQuestionGapBuilder.appendFormClasses(gapCombinationValues);
      first_row = true;

      $.each(combination[1][0], (a, answers) => {
        if (first_row) {
          gapCombinationValues.find('#gap_id_value').attr({
            id: `gap_id_value_${i}_0_0`,
            name: `gap_combination[${i}][0][0][value]`,
          });
          first_row = false;
        } else {
          gapCombinationValues.find(`#gap_id_value_${i}_0_0`).clone().attr({
            id: `gap_id_value_${i}_0_${a}`,
            name: `gap_combination[${i}][0][${a}][value]`,
          }).appendTo(`.value_container_${i}_0`);
        }
        const buildOptionsSelect = $('#select_option_placeholder').html();
        let buildOptionsValue = buildOptionsSelect;
        const pos = parseInt(ClozeSettings.gaps_combination[i][0][a], 10) + 1;
        $.each(ClozeSettings.gaps_php[0], (k, obj_inner_values) => {
          const value = parseInt(k, 10) + 1;
          if (pos === value) {
            $.each(obj_inner_values.values, (l, value) => {
              const cleaned_answer_value = value.answer.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
              buildOptionsValue += `<option value="${value.answer}">${cleaned_answer_value}</option>`;
            });
            if (obj_inner_values.type == 'numeric') {
              buildOptionsValue += `<option value="out_of_bound">${ClozeSettings.outofbound_text}</option>`;
            }
          }
          $(`#gap_id_value_${i}_0_${a}`).html(buildOptionsValue);
        });
      });
      pro.addCloneButtonsForCombinationValues(i, 0, 0);
      pro.multiplyCombinationAnswers(i, combination[1]);
      pro.setValuesForCombinationAnswers(i, combination[1]);

      $.each(combination[2], (p, points) => {
        $('#gap_combination_points').clone().attr({
          id: `gap_combination_points_${i}_${p}`,
          class: 'gap_combination_points form-control',
          name: `gap_combination[points][${i}][${p}]`,
          value: points,
        }).prependTo(`.value_container_${i}_${p}`);
      });
    });
  };

  pub.appendGapCombinationButton = function () {
    if (!$('#create_gap_combination_in_form').length) {
      $('#create_gap_combination').clone().attr({
        id: 'create_gap_combination_in_form',
        name: 'create_gap_combination_in_form',
        class: 'btn btn-default btn-sm',
      }).prependTo(ClozeGlobals.form_footer_buttons);
      $('#create_gap_combination_in_form').on('click', () => {
        const position = ClozeSettings.gaps_combination.length;
        const gaps = new Array(null, null);
        const answers = new Array(new Array(null, null));
        const points = new Array(1);
        const insert = [gaps, answers, points];
        ClozeSettings.gaps_combination.splice(position, 0, insert);
        ClozeQuestionGapBuilder.paintGaps();
      });
    }
  };

  pub.protect = pro;
  return pub;
}());

il.ClozeHelper = (function () {
  const pub = {}; const
    pro = {};

  pub.internetExplorerTinyMCECursorFix = function (ed) {
    const ua = window.navigator.userAgent;
    if (ua.match(/MSIE|Trident/)) {
      pro.correctCursorPosition(ed);
    }
  };

  pro.correctCursorPosition = function (ed) {
    const content = ed.getContent({ format: 'html' });
    const part1 = content.substr(0, ClozeGlobals.cursor_pos);
    const part2 = content.substr(ClozeGlobals.cursor_pos);
    const bookmark = ed.selection.getBookmark(0);
    const positionString = `<span id="${bookmark.id}_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>`;
    const contentWithString = part1 + positionString + part2;
    ed.setContent(contentWithString, ({ format: 'raw' }));
    ed.selection.moveToBookmark(bookmark);
  };
  return pub;
}());

(function ($) {
  $.fn.ensureNoArrayIsAnObjectRecursive = function (obj) {
    if ($.type(obj) === 'object' || $.type(obj) === 'array') {
      Object.keys(obj).forEach((key) => {
        obj[key] = jQuery().ensureNoArrayIsAnObjectRecursive(obj[key]);
      });
      obj = $.map(obj, (value) => [value]);
    }
    return obj;
  };
}(jQuery));
