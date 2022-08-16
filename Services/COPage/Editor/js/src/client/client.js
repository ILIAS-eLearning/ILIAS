/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ResponseFactory from './response/response-factory.js';
import FetchWrapper from './fetch-wrapper.js';
import FormWrapper from './form-wrapper.js';
import FormCommandAction from './actions/form-command-action.js';
import CommandQueue from './actions/command-queue.js';

export default class Client {

  /**
   * @type {boolean}
   */
  //debug = true;

  /**
   * @type {string}
   */
  //query_endpoint;

  /**
   * @type {string}
   */
  //command_endpoint;

  /**
   * @type {ResponseFactory}
   */
  //response_factory;

  /**
   * @type {string}
   */
  //form_action;

  /**
   * Constructor
   * @param {string} query_endpoint
   * @param {string} command_endpoint
   * @param {string} form_action
   * @param {ResponseFactory} response_factory
   */
  constructor(query_endpoint, command_endpoint, form_action, response_factory) {
    this.debug = true;
    this.query_endpoint = query_endpoint;
    this.command_endpoint = command_endpoint;
    this.form_action = form_action;
    this.response_factory = response_factory || new ResponseFactory();
    this.defErrorHandler = null;
    this.queue = new CommandQueue();
  }

  setDefaultErrorHandler(handler) {
    this.defErrorHandler = handler;
  }

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   * Send query action
   * @param {QueryAction} query_action
   * @returns {Promise}
   */
  sendQuery(query_action) {
    this.log("client.sendQuery");
    this.log(query_action);

    const errorHandler = (err) => {
      this.errorHandler(err);
    }

    return new Promise((resolve, reject) => {
      let params = {
        action_id: query_action.getId(),
        component: query_action.getComponent(),
        action: query_action.getType()
      };
      params = Object.assign(params, query_action.getParams());
      FetchWrapper.getJson(this.query_endpoint, params)
      .then(response => {
        this.log("client.sendQuery, response:");
        this.log(response);

        if (!response.ok) {
          const statusText = response.statusText;
          response.text().then(text =>
              errorHandler(statusText + " " + text)
          ).catch(errorHandler);
        } else {
          // note that fetch.json() returns yet another promise
          response.json().then(json =>
              resolve(this.response_factory.response(query_action, json))
          ).catch(errorHandler);
        }
      }).catch(errorHandler);
    });
  }

  errorHandler(err) {
    if (this.defErrorHandler) {
      console.log(err);
      this.defErrorHandler(err);
    }
  }

  /**
   * Send command action
   * @param {CommandAction} command_action
   * @returns {Promise}
   */
  sendCommand(command_action) {
    if (command_action.getQueueable()) {
      const t = this;
      console.log("### Put command in queue:");
      console.log(command_action);
      return this.queue.push(() => {return t._sendCommand(command_action);});
    } else {
      console.log("### Sending command directly:");
      console.log(command_action);
      return this._sendCommand(command_action);
    }
  }

  /**
   * Send command action
   * @param {CommandAction} command_action
   * @returns {Promise}
   */
  _sendCommand(command_action) {

    this.log("...sending Command " + command_action.getId());

    const errorHandler = (err) => {
      this.errorHandler(err);
    }

    // POST FORM
    if (command_action instanceof FormCommandAction) {

      return new Promise((resolve, reject) => {

        const formData = command_action.getParams();
        formData.append("action_id", command_action.getId());
        formData.append("component", command_action.getComponent());
        formData.append("action", command_action.getType());

        FetchWrapper.postForm(this.command_endpoint, formData).then(response => {
          this.log("client.sendCommand, response:");
          this.log(response);

          let getAsJSON = false;
          const contentType = response.headers.get("content-type");
          if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
            getAsJSON = true;
          }
          if (!getAsJSON) {
            const statusText = response.statusText;
            response.text().then(text =>
                errorHandler(statusText + " " + text)
            ).catch(errorHandler);
          } else {
            // note that fetch.json() returns yet another promise
            response.json().then(json =>
                resolve(this.response_factory.response(command_action, json))
            ).catch(errorHandler);
          }
          this.log("...left in Queue: " + this.queue.count());
        }).catch(errorHandler);
      });

    } else {      // POST JSON

      return new Promise((resolve, reject) => {

        FetchWrapper.postJson(this.command_endpoint, {
          action_id: command_action.getId(),
          component: command_action.getComponent(),
          action: command_action.getType(),
          data: command_action.getParams()
        }).then(response => {
          this.log("client.sendCommand, response:");
          this.log(response);

          let getAsJSON = false;
          const contentType = response.headers.get("content-type");
          if (response.ok && contentType && contentType.indexOf("application/json") !== -1) {
            getAsJSON = true;
          }
          if (!getAsJSON) {
            const statusText = response.statusText;
            response.text().then(text =>
                errorHandler(statusText + " " + text)
            ).catch(errorHandler);
          } else {
              // note that fetch.json() returns yet another promise
            response.json().then(json =>
                resolve(this.response_factory.response(command_action, json))
            ).catch(errorHandler);
          }
          this.log("...left in Queue: " + this.queue.count());
        }).catch(errorHandler);
      });
    }
  }

  /**
   * Send form (includes redirect, use sendCommand to do ajax!)
   * @param {CommandAction} command_action
   */
  sendForm(command_action) {

    const data = command_action.getParams();
    if (data['cmd']) {
      data["cmd[" + data['cmd'] + "]"] = "-";
    }

    this.log("client.sendForm " + this.form_action);
    this.log(data);

    FormWrapper.postForm(this.form_action, data);
  }


}