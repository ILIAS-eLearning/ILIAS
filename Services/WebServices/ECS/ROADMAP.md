# Roadmap

## Short Term
Fix leftovers from Review:
- Restructure the current UI. The Participant Screen tries to fit way to much unrelated information into one screen.
- No usage of $_GET / $_POST / $_REQUEST / $_SESSION: The review  found 69 usages of $_REQUEST, 18 usages of $_GET, 4 usages of $_SESSION and 38 usages of $_POST.
- Fix broken Advanced Metadata-Handling. This Code is broken since the Reworking of the AdvMetadata in 2014.

## Mid Term
- Rework the current way, messages are processed. Remove the need to leave already processed messages on the ECS server

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
- Replace manual handling of the ECS Json Messages with Binding Code generated from the corresponding JSON Schema for Campus Connect
