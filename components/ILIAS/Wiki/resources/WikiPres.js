if (!il.Wiki) {
  il.Wiki = {};
}

il.Wiki.Pres = {
  url: '',
  with_comments: 0,

  init(url) {
    const t = il.Wiki.Pres;

    t.url = url;
    $('#il_wiki_user_export').on('click', (e) => {
      e.preventDefault();
      t.with_comments = 0;
      t.performHTMLExport();
    });
  },

  performHTMLExportWithComments: function () {
    const t = il.Wiki.Pres;
    t.performHTMLExport(1);
  },

  performHTMLExport: function (with_comments = 0) {
    const t = il.Wiki.Pres;
    t.with_comments = with_comments;
    console.log("performHTMLExport" + with_comments);
    if (document.getElementById('il_wiki_user_export')) {
      $("<div id='il_wiki_export_progress'></div>").insertAfter("#il_wiki_user_export");
    } else {
      $("<div id='il_wiki_export_progress'></div>").insertAfter("#il_wiki_user_export2");
    }
    t.startHTMLExport();
  },

  getDownloadCommand: () => {
    const t = il.Wiki.Pres;
    if (t.with_comments) {
      return 'downloadUserHTMLExportWithComments';
    }
    return 'downloadUserHTMLExport';
  },

  startHTMLExport() {
    const t = il.Wiki.Pres;
    const par = {
      with_comments: t.with_comments,
    };

    il.repository.core.fetchUrl(`${t.url}&cmd=initUserHTMLExport`, par, {}, (o) => {
      var t = il.Wiki.Pres;
      console.log(o.text);
      if (o.text == 2) {
        window.location.href = `${t.url}&cmd=${t.getDownloadCommand()}`;
      } else {
        il.repository.core.fetchUrl(`${t.url}&cmd=startUserHTMLExport`, par, {}, () => {
        });
        var t = il.Wiki.Pres;
        t.updateProgress();
      }
    });
  },

  updateProgress() {
    const t = il.Wiki.Pres;
    const par = {
      with_comments: t.with_comments,
    };

    il.repository.core.fetchUrl(`${t.url}&cmd=getUserHTMLExportProgress`, par, {}, t.ajaxProgressSuccess);
  },

  ajaxProgressSuccess(o) {
    const t = il.Wiki.Pres;

    if (o.text !== undefined) {
      const s = JSON.parse(o.text);
      $('#il_wiki_export_progress').html(s.progressBar);
      if (s.status != 0) {
        window.setTimeout(t.updateProgress, 1000);
      } else {
        window.location.href = `${t.url}&cmd=${t.getDownloadCommand()}`;
        $('#il_wiki_export_progress').remove();
      }
    }
  },
};
