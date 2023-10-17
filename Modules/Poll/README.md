# Poll

## Business Rules

### General and Question Settings

- Polls can only be set to 'online' when a question has been set.
- The default setting for the number of answers a participants can
give is 'unlimited'.
- The setting 'Display Results After Voting Period' can not be chosen
when no voting period is set.

### What Is Shown in the Poll Block?

- When offline (or equivalently outside of the availability period),
the block is only shown to users with write-access. The content shown
in the block is not affected at all: it is still shown to users with
write-access even if the poll is offline.
- Title and description of the poll, an anchor with its id, whether
it is offline (or unavailable), and comments (if enabled) are always
rendered in the block.
- When no question is set yet, a corresponding message
is shown in the block.
- When a question is set, but a limited voting period is set and has not
started yet, a corresponding message is shown but the question is not.
- When a question is set, and there is either no limited voting period
or it has started:
  - Question text and image are always shown.
  - When the user has not voted yet, and the voting period is not set or
  has not ended, they are shown the answer form and can vote. Alongside
  it, information is given about whether the user votes anonymously,
  how many answers they may give (if not unlimited or limited to 1),
  and when the voting period ends (if it is limited). For anonymous users,
  the answer form is shown but disabled. 
  - Results are shown in the form of a chart according to the settings:
  either always, never, directly after the user has voted, or after the
  voting period has ended. In the latter case, the results are always shown
  if no voting period is set. The results are never shown when nobody has
  cast a vote, regardless of the setting.
  - When neither the results nor the answer form is shown, a message is
  shown informing the user either that they have already voted or that
  the voting period has passed. If the results are set to appear after
  the end of the voting period, that date is also given.
  - Information about how many participants have cast a vote is shown
  when the results should be shown according to the settings, regardless
  of whether anybody has voted or not.

### Notifications

- Non-anonymous users can subscribe and unsubscribe to a poll when they have read access
and it is online and within its availability period. Anonymous users can
never subscribe or unsubscribe.