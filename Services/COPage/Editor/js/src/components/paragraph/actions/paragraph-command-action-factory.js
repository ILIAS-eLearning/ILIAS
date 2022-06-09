/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import ACTIONS from "./paragraph-action-types.js";

/**
 * COPage command actions being sent to the server
 */
export default class ParagraphCommandActionFactory {

  /**
   * @type {ClientActionFactory}
   */
  //clientActionFactory;

  //COMPONENT = "Paragraph";

  /**
   * @param {ClientActionFactory} clientActionFactory
   */
  constructor(clientActionFactory) {
    this.COMPONENT = "Paragraph";
    this.clientActionFactory = clientActionFactory;
  }

  /**
   * @param after_pcid
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  insert(after_pcid, pcid, content, characteristic, fromPlaceholder) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.INSERT, {
      after_pcid: after_pcid,
      pcid: pcid,
      content: content,
      characteristic: characteristic,
      fromPlaceholder: fromPlaceholder
    }, true);
  }

  /**
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  update(pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE, {
      pcid: pcid,
      content: content,
      characteristic: characteristic
    }, true);
  }

  /**
   * @param pcid
   * @param content
   * @param characteristic
   * @return {CommandAction}
   */
  autoSave(pcid, content, characteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.UPDATE_AUTO, {
      pcid: pcid,
      content: content,
      characteristic: characteristic
    }, true);
  }

  /**
   * @param after_pcid
   * @param pcid
   * @param content
   * @param characteristic
   * @param fromPlaceholder
   * @return {CommandAction}
   */
  autoInsert(after_pcid, pcid, content, characteristic, fromPlaceholder) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.INSERT_AUTO, {
      after_pcid: after_pcid,
      pcid: pcid,
      content: content,
      characteristic: characteristic,
      fromPlaceholder: fromPlaceholder
    }, true);
  }

  /**
   *
   * @param insertMode
   * @param after_pcid
   * @param pcid
   * @param text
   * @param characteristic
   * @param newParagraphs
   * @return {CommandAction}
   */
  split(insertMode, after_pcid, pcid, text, characteristic, newParagraphs, fromPlaceholder) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.SPLIT, {
      insert_mode: insertMode,
      after_pcid: after_pcid,
      pcid: pcid,
      text: text,
      characteristic: characteristic,
      new_paragraphs: newParagraphs,
      fromPlaceholder: fromPlaceholder
    }, true);
  }

  /**
   *
   * @param pcid
   * @param after_pcid
   * @param model
   * @param is_insert
   * @param oldCharacteristic
   * @param newCharacteristic
   * @return {CommandAction}
   */
  sectionClass(pcid, after_pcid, model, is_insert, oldCharacteristic, newCharacteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CMD_SECTION_CLASS, {
      insert_mode: is_insert,
      after_pcid: after_pcid,
      pcid: pcid,
      text: model.text,
      characteristic: model.characteristic,
      old_section_characteristic: oldCharacteristic,
      new_section_characteristic: newCharacteristic
    }, true);
  }

  /**
   *
   * @param pcid
   * @param previousPcid
   * @param newPreviousContent
   * @param previousCharacteristic
   * @return {CommandAction}
   */
  mergePrevious(pcid, previousPcid, newPreviousContent, previousCharacteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CMD_MERGE_PREVIOUS, {
      pcid: pcid,
      previousPcid: previousPcid,
      newPreviousContent: newPreviousContent,
      previousCharacteristic: previousCharacteristic
    }, true);
  }

  /**
   *
   * @param removeSectionFromPcid
   * @return {CommandAction}
   */
  cancel(removeSectionFromPcid, paragraphText, paragraphCharacteristic) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.CMD_CANCEL, {
      removeSectionFromPcid: removeSectionFromPcid,
      paragraphText: paragraphText,
      paragraphCharacteristic: paragraphCharacteristic
    });
  }

  /**
   * @param pcid
   * @return {CommandAction}
   */
  delete(pcid) {
    return this.clientActionFactory.command(this.COMPONENT, ACTIONS.DELETE, {
      pcid: pcid
    }, true);
  }

}