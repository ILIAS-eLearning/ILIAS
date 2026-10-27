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

import il from 'ilias';
import document from 'document';
import AsyncRenderer from '../../Core/src/AsyncRenderer.js';
import TemplateRenderer from '../../Core/src/TemplateRenderer.js';
import PromptRegistry from './PromptRegistry.js';
import PromptStateLoader from './PromptStateLoader.js';
import createPrompt from './createPrompt.js';

const promptStateLoader = new PromptStateLoader(new AsyncRenderer(document));
const templateRenderer = new TemplateRenderer(document);
const promptRegistry = new PromptRegistry();

il.UI = il.UI || {};

il.UI.Prompt = {
  createPrompt: (element) => {
    promptRegistry.set(element.id, createPrompt(promptStateLoader, templateRenderer, element));
    return promptRegistry.get(element.id);
  },
  get: (id) => promptRegistry.get(id),
};
