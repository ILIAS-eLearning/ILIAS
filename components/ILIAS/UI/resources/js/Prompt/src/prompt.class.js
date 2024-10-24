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

export default class Prompt {
  /**
   * @type {DOMParser}
   */
  #DOMParser;

  /**
   * @type {HTMLDivElement}
   */
  #component;

  /**
   * @type {HTMLPromptElement}
   */
  #prompt;

  /**
   * @param {DOMParser} DOMParser
   * @param {string} componentId
   * @throws {Error} if DOM element is missing
   */
  constructor(DOMParser, componentId) {
    this.#DOMParser = DOMParser;
    this.#component = document.getElementById(componentId);
    if (this.#component === null) {
      throw new Error(`Could not find a Prompt for id '${componentId}'.`);
    }
    this.#prompt = this.#component.getElementsByTagName('dialog').item(0);
  }

  /**
    * @param {string} url
    * @return {void}
    */
  show(url) {
    this.load(url);
    this.#prompt.showModal();
  }

  /**
    * @return {void}
    */
  close() {
    this.#prompt.close();
  }

  async load(url, par = {}) {
    await fetch(url, par)
      .then((resp) => resp.text())
      .then((html) => {
        const parser = new this.#DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const title = doc.querySelector('section[data-section="il-prompt-state__title"]');
        const contents = doc.querySelector('section[data-section="il-prompt-state__contents"]');
        const buttons = doc.querySelector('section[data-section="il-prompt-state__buttons"]');
        const command = doc.querySelector('section[data-section="il-prompt-state__command"]');
        const parameters = doc.querySelector('section[data-section="il-prompt-state__parameters"]');
        const scripts = doc.querySelector('script');
        const dialogTitle = this.#prompt.querySelector('span.il-prompt__title');
        const dialogContents = this.#prompt.querySelector('div.il-prompt__contents');
        const dialogButtons = this.#prompt.querySelector('div.il-prompt__buttons');
        dialogTitle.innerHTML = title.innerHTML;
        dialogContents.innerHTML = contents.innerHTML;
        dialogButtons.innerHTML = buttons.innerHTML;

        this.#captureForms(dialogContents);
        this.#captureLinks(dialogContents);
        if (scripts) {
          this.#appendScript(scripts.text);
        }

        const params = [];
        parameters.querySelectorAll('data').forEach(
          (data) => {
            params[data.innerHTML.trim()] = data.getAttribute('value');
          },
        );

        return {
          cmd: command.innerHTML.trim(),
          params,
        };
      })
      .then(
        (script) => {
          const command = script.cmd;
          const { params } = script;
          if (command === 'close') {
            this.close();
          }
          if (command === 'redirect') {
            window.location.replace(params.redirect);
          }
        },
      );
  }

  /**
   * @param {string} js
   * @return {void}
   */
  #appendScript(js) {
    const dialogScript = this.#component.querySelector('section.il-prompt__scripts');
    const script = document.createElement('script');
    script.text = js;
    dialogScript.innerHTML = '';
    dialogScript.appendChild(script);
  }

  /**
   * @param {HTMLDivElement} doc
   * @return {void}
   */
  #captureForms(doc) {
    const forms = doc.getElementsByTagName('form');
    forms.forEach(
      (form) => {
        form.addEventListener('submit', (e) => {
          e.preventDefault();
          this.load(form.action, {
            method: form.method,
            body: new FormData(form),
          });
        });
      },
    );
  }

  /**
   * @param {HTMLDivElement} doc
   * @return {void}
   */
  #captureLinks(doc) {
    const links = doc.getElementsByTagName('a');
    links.forEach(
      (lnk) => {
        const { target } = lnk;
        if (target !== '_blank') {
          const url = lnk.href;
          lnk.addEventListener('click', () => this.load(url));
          lnk.removeAttribute('href');
        }
      },
    );
  }
}
