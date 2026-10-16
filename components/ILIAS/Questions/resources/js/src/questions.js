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
 */

import il from 'ilias';
import longmenuAutocomplete from './Cloze/longmenuAutocomplete.js';
import clozeSpecificTextFeedback from './Cloze/feedback.js';
import textFeedbackRetrieve from './TextFeedback/retrieve.js';
import suggestedLearningContentRetrieve from './SuggestedLearningContent/retrieve.js';
import asyncViewShowFeedback from './AsyncView/showFeedback.js';

il.questions = il.questions || {};
il.questions.cloze = il.questions.cloze || {};
il.questions.cloze.initLongmenuGap = longmenuAutocomplete;
il.questions.cloze.specificTextFeedback = il.questions.specificTextFeedback || {};
il.questions.cloze.specificTextFeedback = clozeSpecificTextFeedback;
il.questions.textFeedback = il.questions.textFeedback || {};
il.questions.textFeedback.retrieve = textFeedbackRetrieve;
il.questions.suggestedLearningContent = il.questions.suggestedLearningContent || {};
il.questions.suggestedLearningContent.retrieve = suggestedLearningContentRetrieve;
il.questions.asyncView = il.questions.asyncView || {};
il.questions.asyncView.showFeedback = asyncViewShowFeedback;
