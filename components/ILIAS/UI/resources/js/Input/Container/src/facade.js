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

import $ from 'jquery';
import il from 'ilias';
import document from 'document';
import filter from './filter.main.js';
import ContainerFactory from './ContainerFactory.js';

il.UI = il.UI || {};
il.UI.filter = filter($);
il.UI.Input = il.UI.Input || {};

/**
 * This provides the client side interface to access rudamentary representations
 * of Input Field's inside an Input Container.
 *
 * @example basic usage:
 *    // retrieve an Input Container instance (entry point to the subsystem):
 *    const inputContainer = il.UI.Input.Container.get('some-container-id');
 *    // retrieve a rudamentary representation of all Input Field's:
 *    const inputFields = inputContainer.getFields();
 *    // search for a specific Field inside the Container:
 *    const someField = inputContainer.getFieldByName('form/input_4/input_6');
 *    // retrieve the flat substructure of a specific Input Field:
 *    const flatInputFieldsOf = inputContainer.getFieldsFlat(someField);
 *    // ... or all of them:
 *    const flatInputFields = inputContainer.getFieldsFlat();
 *
 * In addition to the rudamentary representation of the Input Container, which
 * can be used to perform various calculations, the Container also serves as an
 * entry point to the catamorphism of a Field instance.
 *
 * @example catamorphism:
 *    // reduce all Input Text Field's into an array of strings:
 *   const inputContainer = il.UI.Input.Container.get('some-container-id');
 *   const textFieldReducer = (field) => field.getInputs()[0]?.value ?? null;
 *   inputContainer.addFieldReducer(FieldType.TEXT, textFieldReducer);
 *   const textFieldValues = inputContainer.reduceFields();
 *   // do something more fun with the reduced values:
 *   console.log(textFieldValues);
 *
 * The Container by default will provide
 */
il.UI.Input.Container = new ContainerFactory(document);
