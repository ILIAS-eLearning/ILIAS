(function() {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        Lang = YAHOO.lang,
        myConfig = {
            height: '700px',
            width: '700px',
            animate: false,
            dompath: false,
            focusAtStart: true
        },
        //Borrowed this from CodePress: http://codepress.sourceforge.net
        cc = '\u2009', // carret char
        keywords = [
            { code: /(&lt;DOCTYPE.*?--&gt.)/g, tag: '<ins>$1</ins>' }, // comments
            { code: /(&lt;[^!]*?&gt;)/g, tag: '<b>$1</b>'	}, // all tags
            { code: /(&lt;!--.*?--&gt.)/g, tag: '<ins>$1</ins>' }, // comments
            { code: /\b(YAHOO|widget|util|Dom|Event|lang)\b/g, tag: '<cite>$1</cite>' }, // reserved words
            { code: /\b(break|continue|do|for|new|this|void|case|default|else|function|return|typeof|while|if|label|switch|var|with|catch|boolean|int|try|false|throws|null|true|goto)\b/g, tag: '<b>$1</b>' }, // reserved words
            { code: /\"(.*?)(\"|<br>|<\/P>)/g, tag: '<s>"$1$2</s>' }, // strings double quote
            { code: /\'(.*?)(\'|<br>|<\/P>)/g, tag: '<s>\'$1$2</s>' }, // strings single quote
            { code: /\b(alert|isNaN|parent|Array|parseFloat|parseInt|blur|clearTimeout|prompt|prototype|close|confirm|length|Date|location|Math|document|element|name|self|elements|setTimeout|navigator|status|String|escape|Number|submit|eval|Object|event|onblur|focus|onerror|onfocus|onclick|top|onload|toString|onunload|unescape|open|valueOf|window|onmouseover|innerHTML)\b/g, tag: '<u>$1</u>' }, // special words
            { code: /([^:]|^)\/\/(.*?)(<br|<\/P)/g, tag: '$1<i>//$2</i>$3' }, // comments //
            { code: /\/\*(.*?)\*\//g, tag: '<i>/*$1* /</i>' } // comments / * */
        ];
        //End Borrowed Content

    YAHOO.widget.SimpleEditor.prototype._defaultToolbar.titlebar = 'Javascript Editor';
    YAHOO.widget.SimpleEditor.prototype._defaultToolbar.collapse = false;

    YAHOO.widget.SimpleEditor.prototype._setInitialContent = function() {
        YAHOO.log('Populating editor body with contents of the text area', 'info', 'SimpleEditor');
        var html = Lang.substitute(this.get('html'), {
            TITLE: this.STR_TITLE,
            CONTENT: this._cleanIncomingHTML(this.get('element').value),
            CSS: this.get('css'),
            HIDDEN_CSS: this.get('hiddencss')
        }),
        
        check = true;
        this.get('iframe').get('element').src = 'blank.htm';

        if (check) {
            this._checkLoaded();
        }
    };
    YAHOO.widget.SimpleEditor.prototype.focusCaret = function() {
        if (this.browser.gecko) {
            if (this._getWindow().find(cc)) {
                this._getSelection().getRangeAt(0).deleteContents();
            }
        } else if (this.browser.ie) {
            var range = this._getDoc().body.createTextRange();
            if(range.findText(cc)){
                range.select();
                range.text = '';  
            }
        } else if (this.browser.opera) {
            var sel = this._getWindow().getSelection();
            var range = this._getDoc().createRange();
            var span = this._getDoc().getElementsByTagName('span')[0];
                
            range.selectNode(span);
            sel.removeAllRanges();
            sel.addRange(range);
            span.parentNode.removeChild(span);
        } else if (this.browser.webkit) {
            this._selectNode(this.currentElement[0]);
        }
    };
    YAHOO.widget.SimpleEditor.prototype.highlight = function(focus) {
        if (!focus) {
            if (this.browser.gecko) {
                this._getSelection().getRangeAt(0).insertNode(this._getDoc().createTextNode(cc));
            } else if (this.browser.opera) {
			    var span = this._getDoc().createElement('span');
			    this._getWindow().getSelection().getRangeAt(0).insertNode(span);
            } else if (this.browser.webkit) {
                this._createCurrentElement('span');
            }
        }
        var html = '';
        html = this._getDoc().body.innerHTML;
        if (this.browser.opera) {
		    html = html.replace(/<(?!span|\/span|br).*?>/gi,'');
        } else if (this.browser.webkit) {
            //YAHOO.log('1: ' + html);
            html = html.replace(/<\/div>/ig, '');
            html = html.replace(/<br><div>/ig, '<br>');
            html = html.replace(/<div>/ig, '<br>');
            html = html.replace(/<br>/ig,'\n');
            html = html.replace(/<.*?>/g,'');
            html = html.replace(/\n/g,'<br>');
            //YAHOO.log('2: ' + html);
        } else {
            YAHOO.log(html);
            html = html.replace(/<br>/g,'\n');
            html = html.replace(/<.*?>/g,'');
            html = html.replace(/\n/g,'<br>');
            YAHOO.log(html);
        }
        for (var i = 0; i < keywords.length; i++) {
            html = html.replace(keywords[i].code, keywords[i].tag);
        }
        if (this.browser.ie) {
            YAHOO.log(html);
            html = '<pre>' + html + '</pre>';
        }
        this._getDoc().body.innerHTML = html;
        if (!focus) {
            this.focusCaret();
        }
    };

    myEditor = new YAHOO.widget.SimpleEditor('editor', myConfig);
    myEditor.on('editorContentLoaded', function() {
        if (this.browser.ie) {
            var pre = this._getDoc().createElement('pre');
            this._getDoc().body.appendChild(pre);
        }
        this.highlight(true);
    }, myEditor, true);
    myEditor.on('editorKeyPress', function(ev) {
        var self = this;
        //YAHOO.log(ev.ev.charCode);
        if (ev.ev.charCode == 40) {
            if (!this.browser.webkit) {
                this._createCurrentElement('span');
                var node = this._getDoc().createTextNode(")");
                this.currentElement[0].parentNode.replaceChild(node, this.currentElement[0]);
            }
        }
        if (ev.ev.charCode == 123) {
            if (!this.browser.webkit) {
                //Left Paran
                this._createCurrentElement('span');
                var node = this._getDoc().createTextNode("  \n}");
                this.currentElement[0].parentNode.replaceChild(node, this.currentElement[0]);
                var br = this._getDoc().createElement('br');
                node.parentNode.insertBefore(br, node);
                setTimeout(function() {
                    self.highlight.call(self, false, '}');
                }, 100);
            }

        }
        if ((ev.ev.keyCode == 32) || (ev.ev.charCode == 59) || (ev.ev.charCode == 32) || (ev.ev.keyCode == 13)) {
            var run = true;
            if (ev.ev.keyCode == 13) {
                if (this.browser.webkit) {
                    run = false;
                }
            }
            if (run) {
                setTimeout(function() {
                    self.highlight.call(self);
                }, 100);
            }
        }
    }, myEditor, true);
    myEditor.render();
})();
