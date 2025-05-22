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

il.COPagePres = {
  /**
	 * Basic init function
	 */
  init() {
    this.initToc();
    this.updateQuestionOverviews();
    this.initMapAreas();
    this.initAdvancedContent();
    this.initAudioVideo();
    this.initAccordions();
  },

  //
  // Toc (as used in Wikis)
  //

  /**
	 * Init the table of content
	 */
  initToc() {
    // init toc
    const cookiePos = document.cookie.indexOf('pg_hidetoc=');
    if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1) {
      this.toggleToc();
    }
  },

  initAccordions() {
    if (typeof ilAccordionsInits !== 'undefined') {
      for (let i = 0; i < ilAccordionsInits.length; i++) {
        il.Accordion.add(ilAccordionsInits[i]);
      }
    }
  },

  /**
	 * Toggle the table of content
	 */
  toggleToc() {
    let toc_on; let toc_off; const
      toc = document.getElementById('ilPageTocContent');

    if (!toc) {
      return;
    }

    toc_on = document.getElementById('ilPageTocOn');
    toc_off = document.getElementById('ilPageTocOff');

    if (toc && toc.style.display == 'none') {
      toc.style.display = 'block';
      toc_on.style.display = 'none';
      toc_off.style.display = '';
      document.cookie = 'pg_hidetoc=0';
    } else {
      toc_on.style.display = '';
      toc_off.style.display = 'none';
      toc.style.display = 'none';
      document.cookie = 'pg_hidetoc=1';
    }
  },

  //
  // Question Overviews
  //

  qover: {},
  ganswer_data: {},

  addQuestionOverview(conf) {
    this.qover[conf.id] = conf;
  },

  updateQuestionOverviews() {
    const correct = {};
    const incorrect = {};
    let correct_cnt = 0;
    let incorrect_cnt = 0;
    let answered_correctly; let index; let k; let i; let ov_el; let ul; let j; let
      qtext;

    if (typeof questions === 'undefined') {
      // #17532 - question overview does not work in copage editor / preview
      for (i in this.qover) {
        ov_el = $(`div#${this.qover[i].div_id}`);
        $(ov_el).addClass('ilBox');
        $(ov_el).css('margin', '5px');
        ov_el.empty();
        ov_el.append(`<div class="il_Description_no_margin">${ilias.questions.txt.ov_preview}</div>`);
      }

      return;
    }

    for (k in questions) {
      answered_correctly = true;
      index = parseInt(k, 10);
      if (!isNaN(index)) {
        if (!answers[index]) {
          answered_correctly = false;
        } else if (answers[index].passed != true) {
          answered_correctly = false;
        }
        if (!answered_correctly) {
          incorrect[k] = k;
          incorrect_cnt++;
        } else {
          correct[k] = k;
          correct_cnt++;
        }
      }
    }

    // iterate all question overview elements
    for (i in this.qover) {
      ov_el = $(`div#${this.qover[i].div_id}`);

      // remove all children
      ov_el.empty();

      // show success message, if all questions have been answered
      if (incorrect_cnt == 0) {
        ov_el.attr('class', 'ilc_qover_Correct');
        ov_el.append(
          ilias.questions.txt.ov_all_correct,
        );
      } else {
        ov_el.attr('class', 'ilc_qover_Incorrect');
        // show message including of number of not
        // correctly answered questions
        if (this.qover[i].short_message == 'y') {
          ov_el.append(`<div class="ilc_qover_StatusMessage">${
            ilias.questions.txt.ov_some_correct.split('[x]').join(String(correct_cnt))
              .split('[y]').join(String(incorrect_cnt + correct_cnt))
          }</div>`);
        }

        if (this.qover[i].list_wrong_questions == 'y') {
          ov_el.append(
            `<div class="ilc_qover_WrongAnswersMessage">${
						 ilias.questions.txt.ov_wrong_answered}:` + '</div>',
          );

          // list all incorrect answered questions
          ov_el.append('<ul class="ilc_list_u_BulletedList"></ul>');
          ul = $(`div#${this.qover[i].div_id} > ul`);
          for (j in incorrect) {
            qtext = questions[j].question;

            if (questions[j].type == 'assClozeTest') {
              qtext = questions[j].title;
            }

            ul.append(
              '<li class="ilc_list_item_StandardListItem">'
							+ `<a href="#" onclick="return il.COPagePres.jumpToQuestion('${j}');" class="ilc_qoverl_WrongAnswerLink">${qtext}</a>`
							+ '</li>',
            );
          }
        }
      }
    }
  },

  // jump to a question
  jumpToQuestion(qid) {
    if (typeof pager !== 'undefined') {
      pager.jumpToElement(`container${qid}`);
    }
    return false;
  },

  setGivenAnswerData(data) {
    ilCOPagePres.ganswer_data = data;
  },

  //
  // Map area functions
  //

  // init map areas
  initMapAreas() {
    // $('img[usemap^="#map_il_"][class!="ilIim"]').maphilight({ neverOn: true });
  },

  /// /
  /// / Handle advanced content
  /// /
  showadvcont: true,
  initAdvancedContent() {
    const c = $('div.ilc_section_AdvancedKnowledge');
    const b = $('#ilPageShowAdvContent'); let
      cookiePos;
    if (c.length > 0 && b.length > 0) {
      cookiePos = document.cookie.indexOf('pg_hideadv=');
      if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1) {
        this.showadvcont = false;
      }

      $('#ilPageShowAdvContent').css('display', 'block');
      if (il.COPagePres.showadvcont) {
        $('div.ilc_section_AdvancedKnowledge').css('display', '');
        $('#ilPageShowAdvContent > span:nth-child(1)').css('display', 'none');
      } else {
        $('div.ilc_section_AdvancedKnowledge').css('display', 'none');
        $('#ilPageShowAdvContent > span:nth-child(2)').css('display', 'none');
      }
      $('#ilPageShowAdvContent').click(() => {
        if (il.COPagePres.showadvcont) {
          $('div.ilc_section_AdvancedKnowledge').css('display', 'none');
          $('#ilPageShowAdvContent > span:nth-child(1)').css('display', '');
          $('#ilPageShowAdvContent > span:nth-child(2)').css('display', 'none');
          il.COPagePres.showadvcont = false;
          document.cookie = 'pg_hideadv=1';
        } else {
          $('div.ilc_section_AdvancedKnowledge').css('display', '');
          $('#ilPageShowAdvContent > span:nth-child(1)').css('display', 'none');
          $('#ilPageShowAdvContent > span:nth-child(2)').css('display', '');
          il.COPagePres.showadvcont = true;
          document.cookie = 'pg_hideadv=0';
        }
        return false;
      });
    }
  },

  /// /
  /// / Audio/Video
  /// /

  initAudioVideo(acc_el) {
    let $elements;
    if (acc_el) {
      $elements = $(acc_el).find('video.ilPageVideo,audio.ilPageAudio');
    } else {
      $elements = $('video.ilPageVideo,audio.ilPageAudio');
    }

    if ($elements.mediaelementplayer) {
      $elements.each((i, el) => {
        let def; let
          cfg;

        def = $(el).find("track[default='default']").first().attr('srclang');
        cfg = {};
        if (def != '') {
          cfg.startLanguage = def;
        }
        $(el).mediaelementplayer(cfg);
      });
    }
  },

  setFullscreenModalShowSignal(signal, suffix) {
    il.COPagePres.fullscreen_signal = signal;
    il.COPagePres.fullscreen_suffix = suffix;
    $(`#il-copg-mob-fullscreen${suffix}`).closest('.modal').on('shown.bs.modal', () => {
      il.COPagePres.resizeFullScreenModal(suffix);
    }).on('hidden.bs.modal', () => {
      $(`#il-copg-mob-fullscreen${suffix}`).attr('src', '');
    });
  },

  inIframe() {
    try {
      return window.self !== window.top;
    } catch (e) {
      return true;
    }
  },

  openFullScreenModal(target) {
    // see 32198
    if (il.COPagePres.inIframe()) {
      window.parent.il.COPagePres.openFullScreenModal(target);
      return;
    }
    $(`#il-copg-mob-fullscreen${il.COPagePres.fullscreen_suffix}`).attr('src', target);
    // workaround for media pool full screen view
    $('#ilMepPreviewContent').attr('src', target);
    if (il.COPagePres.fullscreen_signal) {
      $(document).trigger(il.COPagePres.fullscreen_signal, {
        id: il.COPagePres.fullscreen_signal,
        event: 'click',
        triggerer: $(document),
        options: JSON.parse('[]'),
      });
    }
  },

  resizeFullScreenModal(suffix) {
    const vp = il.Util.getViewportRegion();
    const ifr = il.Util.getRegion(`#il-copg-mob-fullscreen${suffix}`);
    $('.il-copg-mob-fullscreen').css('height', `${vp.height - ifr.top + vp.top - 120}px`);
  },

};
il.Util.addOnLoad(() => { il.COPagePres.init(); });
