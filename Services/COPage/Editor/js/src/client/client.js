/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ActionFactory from '../actions/action-factory.js';
import ResponseFactory from './response/response-factory.js';
import FetchWrapper from './fetch-wrapper.js';
import FormWrapper from './form-wrapper.js';

export default class Client {

  /**
   * @type {string}
   */
  query_endpoint;

  /**
   * @type {string}
   */
  command_endpoint;

  /**
   * @type {ResponseFactory}
   */
  response_factory;

  /**
   * @type {string}
   */
  form_action;

  /**
   * Constructor
   * @param {string} query_endpoint
   * @param {string} command_endpoint
   * @param {string} form_action
   * @param {ResponseFactory} response_factory
   */
  constructor(query_endpoint, command_endpoint, form_action, response_factory) {
    this.query_endpoint = query_endpoint;
    this.command_endpoint = command_endpoint;
    this.form_action = form_action;
    this.response_factory = response_factory || new ResponseFactory();
  }

  /**
   * Send query action
   * @param {QueryAction} query_action
   * @returns {Promise}
   */
  sendQuery(query_action) {

    return new Promise((resolve, reject) => {
      let params = {
        action_id: query_action.getId(),
        component: query_action.getComponent(),
        action: query_action.getType()
      };
      params = Object.assign(params, query_action.getParams());
      FetchWrapper.getJson(this.query_endpoint, params)
      .then(response => {
        // note that fetch.json() returns yet another promise
        response.json().then(json =>
          resolve(this.response_factory.response(query_action, json))
        ).catch(err => reject(err));
      }).catch(err => reject(err));
    });
  }

  /**
   * Send command action
   * @param {CommandAction} command_action
   * @returns {Promise}
   */
  sendCommmand(command_action) {

    return new Promise((resolve, reject) => {

      FetchWrapper.postJson(this.command_endpoint, {
        action_id: command_action.getId(),
        component: command_action.getComponent(),
        action: command_action.getType(),
        data: command_action.getData()
      }).then(response => resolve(
        this.response_factory.response(command_action, response.json())
      ))
      .catch(err => reject(err));

    });
  }

  /**
   * Send form (includes redirect, use sendCommand to do ajax!)
   * @param {CommandAction} command_action
   */
  sendForm(command_action) {

    const data = command_action.getData();

    if (data['cmd']) {
      data["cmd[" + data['cmd'] + "]"] = "-";
    }

    FormWrapper.postForm(this.form_action, data);
  }


}