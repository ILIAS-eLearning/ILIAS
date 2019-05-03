# Reviewer

A review is given by a reviewer on code contributions (see: [contributing.md](contributing.md)) of 
a reviewee. There are no guidelines on how review has to be performed in ILIAS, however here we propose a pattern
inspired by an article of Dan Munckton in [How to be a kinder more effective code reviewer](https://cultivatehq.com/posts/how-to-be-a-kinder-more-effective-code-reviewer/).

In our experience many conflicts arise due to feedback written in a fashion which makes it hard to understand,
what exactly is asked of a reviewee. The pattern adapted from suggestions by Munckton is aimed
to make reviews easier to read and to leave less room for misunderstandings and communicational deadlocks.

 

## General Pattern
We try to lay a strong focus on making feedback **actionable**, meaning the reviewee should have 
a clear picture on how to react. Inspired by Munckton (see below for more details) we propose
to use the following structure to reply to PRs:

```
Hi @[name_of_reviewee]

Thank you a lot for contributing to ILIAS.

[reaction_not_actionable]

Please answer the following questions:
- [ ] [question1]? [optional: reasoning behind the question]
- [ ] [question2]? [optional: reasoning behind the question]
- ...

Please consider the following suggestions. You do not need to follow those, but but please indicate shortly
why you prefer to do otherwise:
- [ ] [suggestion1] [optional: reasoning behind the suggestion]
- [ ] [suggestion2] [optional: reasoning behind the suggestion]
- ...

Please implement the following changes:
- [ ] [change_request_1] [optional: reasoning behind the suggestion]
- [ ] [change_request_2] [optional: reasoning behind the suggestion]
- ...

[optional_comments_on_how_to_proceed]

kindly,
@[your_username]

```
This might look as follows:

```
Hi @klees

Thank you a lot for contributing to ILIAS.

We highly apreciate the effort you took to improve the JS in the KS component. I especially
like the new proposed pattern to tackle the scoping issue.

Please answer the following questions:
- [ ] Why did you not use ECMAScript 6? Note that Bootstrap 4 makes use of it.

Please consider the following suggestions. You do not need to follow those, but but please indicate shortly
why you prefer to do otherwise:
- [ ] Change XY to pattern XZ. We believe that pattern XZ might be superior. However, I am not completely sure.


Please implement the following changes:
- [ ] Reword the function addProperty to withProperty. withProperty is in line with our naming scheme used
  in othe methods of the framework.


Please give a feedback until the end of the week indicating how long it will take to answer
the questions given, react to the suggestions and implement our change requests.


kindly,
@amstutz

```

In short, we have the following suggestions for you when writting reviews:
- [ ] Use the above template. Reasoning: See next chapter.

## Reasoning
Munckton introduces the term **actionable** for feedbacks. An **actionable** feedback is one that
can directly be reacted upon. He claims Questions, Suggestions and Change Requests 
to be the most relevant types of such statements. He gives the following examples of those three statements:
                                                  
- Question: could you explain why this ended up having to be here?
- Suggestion: you may have come across this already, but we might be able to use XYZ for this.
- Change request: sorry, I appreciate the effort it took to get it this far. But the team 
already agreed to solve this using XYZ. So I'm going to have to ask you to rework this. Let me know if I can help.


A fourth type he sometimes uses is the reaction, which is not actionable, but might help to express emotions such as thanks.

He further suggests to always keep things as clear as possible by using the following pattern:

>[type]: [actionable request] 

>[rationale or discussion]

Whereas:
- type: one of Question, Suggestion or Changes Request.
- actionable request: is a short clear question or statement, which makes clear how the reviewee
should respond.
- rational or discussion: Possible further explanations of the reasoning behind the request.

An example could be: 
>Suggestion: use XYZ. 

>You may have come across this already, but we might be able to use XYZ for this because blah blah blah etc.

We adapted this slightly by bundling questions, suggestions and change requests in grouped listings and placing
the reactions up front.

## Inline Comments
Github makes it very easy to leave inline comments. However, in the past, we observed, that large
numbers of inline comments make a review hard to read and understand. We therefore propose
only the leave inline comments for trivial directly actionable suggestions and change request, that
do not need an rational (E.g. *Change Request: typo in word actionalbe*).

Do not leave any reactions in inline comments. They tend to crowd the discussion without giving
the possibility of being resolved (seens not actionable). Move them to the summary.




