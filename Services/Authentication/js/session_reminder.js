(function ($) {
  "use strict";

  class Cookies {
    /**
     *
     * @type {{secure: bool, SameSite: string, expires: number|Date, path: string, domain: string}}
     */
    static #defaultParameters = {
      secure: window.location.protocol === "https:",
      SameSite: "Lax"
    };

    /**
     *
     * @param {string} value
     * @returns {string}
     */
    static #readValue(value) {
      if (value[0] === '"') {
        value = value.slice(1, -1)
      }

      return value.replace(/(%[\dA-F]{2})+/gi, decodeURIComponent);
    };

    /**
     *
     * @param {string} value
     * @returns {string}
     */
    static #writeValue(value) {
      return encodeURIComponent(value).replace(
        /%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g,
        decodeURIComponent
      );
    };

    /**
     *
     * @param {string} name
     * @returns {string}
     */
    static #encodeName(name) {
      name = encodeURIComponent(name)
      .replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent)
      .replace(/[()]/g, escape)//https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/escape

      return name;
    };

    /**
     *
     * @param {{secure: bool, SameSite: string, expires: number|Date, path: string, domain: string}} attributes
     * @returns {string}
     */
    static #attributesToString(attributes) {
      let text = "";

      for (const attributeName in attributes) {
        if (!attributes.hasOwnProperty(attributeName) || !attributes[attributeName]) {
          continue;
        }

        if (attributeName === "domain") {
          text += "; domain=" + attributes[attributeName].split(";")[0];
        }

        if (attributeName === "path") {
          text += "; path=" + attributes[attributeName].split(";")[0];
        }

        if (attributeName === "expires" && attributes[attributeName] instanceof Date) {
          text += "; expires=" + attributes[attributeName].toUTCString();
        }

        if (attributeName === "SameSite") {
          text += "; SameSite=" + attributes[attributeName].split(";")[0];
        }

        if (attributeName === "secure" && attributes[attributeName] === true) {
          text += "; secure";
        }
      }

      return text;
    };

    /**
     *
     * @param {string} name
     * @param {string} value
     * @param {{secure: bool, SameSite: string, expires: number|Date, path: string, domain: string}} attributes
     */
    static set(name, value, attributes) {
      attributes = Object.assign({}, Cookies.#defaultParameters, attributes);

      if (typeof attributes.expires === "number") {
        attributes.expires = new Date(Date.now() + attributes.expires * 864e5)
      }

      document.cookie = name + "=" + Cookies.#writeValue(value) + Cookies.#attributesToString(
        attributes);
    }

    /**
     *
     * @param {string} name
     * @returns {string|undefined}
     */
    static get(name) {
      const cookies = document.cookie ? document.cookie.split("; ") : [];
      let cookieJar = {};

      for (let i = 0; i < cookies.length; i++) {
        const parts = cookies[i].split("="),
          cookie = parts.slice(1).join("=");

        try {
          cookieJar[decodeURIComponent(parts[0])] = Cookies.#readValue(
            cookie,
            decodeURIComponent(parts[0])
          );
        }
        catch (e) {
        }
      }

      return cookieJar[name];
    }

    /**
     *
     * @param {string} name
     */
    remove(name) {
      Cookies.set(
        name,
        '',
        { expires: -1 }
      );
    }
  }

  class PeriodicalExecuter {
    #callback;
    #frequency;
    #timer;
    #currentlyExecuting;

    /**
     * @param {function} callback
     * @param {number} frequency
     */
    constructor(callback, frequency) {
      this.#callback = callback;
      this.#frequency = frequency;
      this.#currentlyExecuting = false;
      this.#registerCallback();
    }

    #registerCallback() {
      this.#timer = setInterval(() => {
        this.#onTimerEvent()
      }, this.#frequency);
    }

    #execute() {
      this.#callback(this);
    }

    stop() {
      if (!this.#timer) return;
      clearInterval(this.#timer);
      this.#timer = null;
    }

    #onTimerEvent() {
      if (!this.#currentlyExecuting) {
        try {
          this.#currentlyExecuting = true;
          this.#execute();
        } finally {
          this.#currentlyExecuting = false;
        }
      }
    }
  }

  $.fn.ilSessionReminder = function (method) {
    let session_reminder_executer = null,
      session_reminder_locked = false;

    const internals = {
      log: function (message) {
        if (this.properties.debug) {
          console.log(message);
        }
      },
      properties: {}
    };

    const ilSessionReminderCallback = function () {
      const properties = internals.properties,
        cookie_prefix = "il_sr_" + properties.client_id + "_";

      if (Cookies.get(cookie_prefix + "activation") == "disabled" ||
        Cookies.get(cookie_prefix + "status") == "locked") {
        internals.log("Session reminder disabled or locked for current user session");
        return;
      }

      Cookies.set(cookie_prefix + "status", "locked");
      session_reminder_locked = true;
      internals.log("Session reminder locked");
      $.ajax({
        url: properties.url,
        dataType: 'json',
        type: 'POST',
        data: {
          hash: properties.hash
        },
        success: function (response) {
          if (response.message && typeof response.message == "string") {
            internals.log(response.message);
          }

          if (response.remind) {
            session_reminder_executer.stop();

            const extend = confirm(unescape(response.txt));

            if (extend == true) {
              $.ajax({
                url: response.extend_url,
                type: 'GET',
                success: function () {
                  session_reminder_executer = new PeriodicalExecuter(
                    ilSessionReminderCallback,
                    properties.frequency * 1000
                  );
                  Cookies.set(cookie_prefix + "status", "unlocked");
                  session_reminder_locked = false;
                  internals.log("User extends session: Session reminder unlocked");
                }
              }).fail(function () {
                session_reminder_executer = new PeriodicalExecuter(
                  ilSessionReminderCallback,
                  properties.frequency * 1000
                );
                Cookies.set(cookie_prefix + "status", "unlocked");
                session_reminder_locked = false;
                internals.log("XHR Failure: Session reminder unlocked");
              });
            } else {
              Cookies.set(cookie_prefix + "activation", "disabled");
              Cookies.set(cookie_prefix + "status", "unlocked");
              session_reminder_locked = false;
              internals.log(
                "User disabled reminder for current session: Session reminder disabled but unlocked");
              session_reminder_executer = new PeriodicalExecuter(
                ilSessionReminderCallback,
                properties.frequency * 1000
              );
            }
          } else {
            Cookies.set(cookie_prefix + "status", "unlocked");
            session_reminder_locked = false;
            internals.log("Reminder of session expiration not necessary: Session reminder unlocked");
          }
        }
      }).fail(function () {
        Cookies.set(cookie_prefix + "status", "unlocked");
        session_reminder_locked = false;
        internals.log("XHR Failure: Session reminder unlocked");
      });
    };

    const methods = {
      init: function (params) {
        return this.each(function () {
          const $this = $(this);

          if ($this.data('sessionreminder')) {
            return;
          }

          const data = {
            properties: $.extend(
              true, {},
              {
                url: "",
                client_id: "",
                hash: "",
                frequency: 60,
                debug: 0
              },
              params
            )
          };

          $this.data("sessionreminder", data);
          internals.properties = data.properties;

          const properties = internals.properties,
            cookie_prefix = "il_sr_" + properties.client_id + "_";

          $(window).on('beforeunload', function () {
            if (session_reminder_locked) {
              Cookies.set(cookie_prefix + "status", "unlocked");
              internals.log("Unlocked session reminder on unload event");
            }
          });

          internals.log("Session reminder started");
          if (Cookies.get(cookie_prefix + "session_id_hash") !== properties.hash) {
            Cookies.set(cookie_prefix + "activation", "enabled");
            Cookies.set(cookie_prefix + "status", "unlocked");
            Cookies.set(cookie_prefix + "session_id_hash", properties.hash);
            internals.log(
              "Session cookie changed after new login or session reminder initially started " +
              "for current session: Release lock and enabled reminder"
            );
          }

          session_reminder_executer = new PeriodicalExecuter(
            ilSessionReminderCallback, properties.frequency * 1000
          );
        });
      }
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === "object" || !method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error("Method " + method + " does not exist on jQuery.ilSessionReminder");
    }
  };
})(jQuery);
