# Accessibility Guidelines

If you are a programmer and just want to check your code. Please look at the [Accessibility Checklist](#accessibility-checklist) at the bottom of this document.

## PREAMBLE
ILIAS aims to provide, a digital learning environment that can be used by as many people as possible, regardless of individual abilities, limitations, or technical requirements. The guiding principle “Usable for Everyone” forms a central foundation for the further development of ILIAS.

Digital accessibility is an essential prerequisite for equal participation in education, communication, and collaboration. It particularly supports people with disabilities, but at the same time improves the usability of digital systems for all users.

ILIAS views accessibility not as an afterthought to individual features, but as an integral part of the software’s conception, design, development, quality assurance, and operation.

This guideline describes the principles and requirements for implementing digital accessibility in ILIAS. It serves as a guide for developers, designers, operators, content authors, and all other members of the ILIAS community.

The accessibility guidelines are based on the currently valid version of [EN 301 549 Accessibility requirements for ICT products and services](https://www.etsi.org/deliver/etsi_en/301500_301599/301549/03.02.01_60/en_301549v030201p.pdf), which currently refers to [WCAG 2.1, conformance levels A and AA](https://www.w3.org/TR/WCAG21/). A future update of EN 301 549 is expected to refer to WCAG 2.2; the references in this document may therefore need to be updated accordingly. 

In addition, the applicable national and regional legal regulations regarding digital accessibility must be observed.

## OBJECTIVES
The objective of this guideline is to
* permanently establish digital accessibility as a quality feature of ILIAS,
* transparently describe the requirements for the development and further development of ILIAS,
* clarify responsibilities within the ILIAS community,
* promote the creation and use of accessible content,
* and lay the foundation for uniform quality assurance in the area of accessibility.

## SCOPE
This guideline applies to:
* the ILIAS core system,
* official components and services,
* extensions and plugins,
* user interfaces and interaction concepts,
* documentation and help systems,
* as well as the provision of functions for creating and managing content.

## PRINCIPLES
Accessibility is a shared responsibility of all those involved in the development, operation, and use of ILIAS. It concerns technical functions as well as content, processes, and organizational frameworks. It must be taken into account from the outset in conception, design, development, and quality assurance. Accessibility requirements should be integrated into development processes as early as possible.

The development of ILIAS is guided by the requirements of EN 301 549 as well as the WCAG criteria for Conformity Levels A and AA referenced therein. 
* Information and functions must be designed so that they can be perceived by users with different sensory abilities.
* All essential functions must be operable using different input methods. This includes, in particular, keyboard navigation and support for assistive technologies.
* User interfaces, processes, and information should be designed to be understandable, transparent, and consistent.
* ILIAS should function reliably with current browsers, assistive technologies, and various end devices. To this end, established technical standards must be adhered to.
* The use of assistive technologies must not lead to any restriction of data protection, privacy, or informational self-determination.

Accessibility is an ongoing development process. User feedback, audit results, and technical advancements should be taken into account on a regular basis.

## GENERAL REQUIREMENTS FOR SOFTWARE 
New features, components, and user interfaces should meet the requirements of this guideline.
The following aspects, in particular, must be taken into account during development:
* Support for keyboard navigation,
* Compatibility with assistive technologies,
* Adequate color and contrast design,
* Alternative presentation formats for non-textual content,
* Comprehensible error messages and input prompts,
* Consistent navigation and interaction patterns,
* Avoidance of unnecessary cognitive load,
* Compliance with semantic and technical web standards.

## ACCESSIBILITY CHECKLIST

The following checklist specifies the requirements of this guideline and serves as a tool for development, quality assurance, review processes, and accessibility audits. It is based on the relevant requirements of EN 301 549 as well as the success criteria of the Web Content Accessibility Guidelines (WCAG) referenced therein.

The checklist is structured in accordance with EN 301 549 and covers, in particular, the functional performance requirements (Chapter 4), the general requirements (Chapter 5), requirements for communication and video functions (Chapters 6 and 7), requirements for web content (Chapter 9), non-web documents (Chapter 10), software (Chapter 11), and documentation and support (Chapter 12).

The test entry contains the following elements:

1.	Requirement: Name of the requirement to be tested.
2.	Reference: Reference to the corresponding requirement in EN 301 549 and — where applicable — to the associated WCAG success criterion.
3.	Description: A brief description of the requirement and its objective.
4.	Success Criterion: A description of the condition that must be met for the requirement to be considered implemented.
5.	Test Questions: Guiding questions to support manual or automated testing.
6.	Additional Information (where applicable):  References to the respective W3C documents  [Understanding WCAG](https://w3c.github.io/wcag21/understanding/) and [How to Meet WCAG](https://www.w3.org/WAI/WCAG22/quickref/) to support the interpretation and implementation of the requirement. Where appropriate, additional ILIAS-specific notes, examples, implementation guidelines, known limitations, and references to related ILIAS features or components are provided.

The checklist serves as a guide and for quality assurance. The requirements of the currently valid version of EN 301 549 and the standards referenced therein are authoritative. 

<details>
<summary> 1. GENERAL REQUIREMENTS</summary>
<details>
<summary>1.1 Activation of Accessibility Features</summary>

**Reference:**
EN 301 549 → 5.2 Activation of accessibility features Description 

**Description:**
If documented features are provided that serve or support accessibility, they must not be deactivated, impaired, or made unnecessarily difficult to use. The activation and use of such features must be accessible to all users.

**Success Criterion:**
Documented Accessibility features can be activated, configured, and used by users regardless of their individual abilities. Existing accessibility features of browsers, operating systems, or assistive technologies are supported and not impaired.

**Check Questions:**
* Are accessibility features provided by browsers or operating systems supported?
* Are zoom functions, contrast adjustments, or font enlargements not restricted?
* Are screen readers, voice controls, or alternative input devices not hindered?
* Are accessibility settings themselves accessible and operable without barriers?
* Are accessibility features not compromised by new components or extensions?

**Further Information:**
Since this is a general requirement of EN 301 549, there is no direct correspondence to a single WCAG success criterion. Implementation is specified in particular by the requirements of Chapters 9 (Web) and 11 (Software).

**Understanding WCAG:**
This requirement has no direct equivalent in the WCAG success criteria. It concerns the transmission behaviour and responsiveness of real-time text communication under EN 301 549.

**How to Meet WCAG:** No specific „How to Meet WCAG“ resource applies directly to this requirement.


</details>

<details>
<summary>1.2 Preservation of Accessibility Information</summary>

**Reference:**
EN 301 549 → 5.4 Preservation of accessibility information during conversion

**Description:**
Accessibility-related information must be preserved during the creation, editing, storage, transmission, conversion, import, or export of content. This applies in particular to semantic structures and information used by assistive technologies.

**Success Criterion:**
Accessibility information is not lost during content processing or, to the extent technically feasible, is fully retained and passed on.

**Test Questions**
* Are alternative texts for images preserved when saving, importing, or exporting?
* Are heading hierarchies preserved?
* Are table structures, including table headers, retained?
* Are lists, markup, and semantic structures processed correctly?
* Are form labels and associated information preserved?
* Is accessibility metadata retained during export and conversion processes?
* Are accessible document properties retained for supported file formats?

**Further Information:**
For ILIAS, as a learning management and authoring system, preserving accessibility information is of particular importance. Functions for importing, exporting, copying, moving, reusing, or converting content should preserve existing accessibility information as completely as possible.

**This requirement specifically supports the implementation of the following WCAG success criteria:**
* WCAG 2.1 → 1.1.1 Non-text Content
* WCAG 2.1 → 1.3.1 Info and Relationships
* WCAG 2.1 → 4.1.2 Name, Role, Value

**Understanding WCAG:**
* https://www.w3.org/WAI/WCAG21/Understanding/non-text-content.html
* https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
* https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html

**How to Meet WCAG:**
* https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
* https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
* https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>2. TWO-WAY-VOICE-COMMUNICATION</summary>

This section applies to all features that enable synchronous communication between users. These include, in particular, video conferencing systems, audio conferences, chats, Etherpads, EtherCalc sessions, and similar collaborative tools.

<details>
<summary>2.1 Voice Communication: Audio Bandwidth for Speech</summary>

**Reference:** EN 301 549 → 6.1 Wide band speech

**Description:** Voice communication must be provided at a quality that enables reliable understanding. To ensure this in two-way voice communication, the application should use a frequency range with an upper limit of at least 7,000 Hz when encoding and decoding audio.

**Success Criterion:** Spoken content can be understood without avoidable technical limitations.

**Test Questions:**
*	Is sufficient audio quality supported?
*	Are modern audio codecs used?
*	Is speech intelligibility guaranteed even during longer conferences?
*	Are connection issues communicated appropriately?

**Further Information:** In particular, this requirement supports:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing

**Understanding WCAG:**
This requirement has no direct equivalent in the WCAG success criteria. It concerns the transmission behaviour and responsiveness of real-time text communication under EN 301 549.

**How to Meet WCAG:** No specific „How to Meet WCAG“ resource applies directly to this requirement.
</details>
<details>
<summary> 2.2 Real-Time Text Communication</summary>

<details>
<summary>2.2.1 Real-Time Text Communication</summary>

**Reference**: EN 301 549 → 6.2.1.1 RTT communication

**Description:** If two-way communication (a connection that allows communication in both directions, such as voice and video calls) is supported, the system must also provide real-time text (RTT) communication. In this context, „real time“ means that even individual characters are transmitted and displayed to the recipient as they are typed. Users do not have to actively send the text they have entered.

**Success Criterion:** Users can communicate through text in real time during a two-way communication session. Text entered by one participant is transmitted and displayed to other participants with minimal delay and without requiring manual submission.

**Test Questions:**
*	Does the communication system provide a text-based communication channel during voice or video sessions?
*	Is text transmitted while being typed, without requiring the user to press a "Send" button?
*	Are incoming text updates displayed immediately to recipients?
*	Can users participate in the communication session using text only?
*	Does the RTT functionality remain available throughout the communication session?
*	Can RTT be used simultaneously with voice communication?
*	Is RTT accessible through keyboard-only operation?
*	Is RTT compatible with assistive technologies such as screen readers?

**Further Information:** When assessing compliance with this requirement, it is important to distinguish between Real-Time Text (RTT) as defined by EN 301 549 and other forms of synchronous text-based communication. According to EN 301 549, RTT refers to text that is transmitted and displayed during entry, without requiring the sender to explicitly submit the message. Other communication mechanisms, such as chat systems or collaborative editing environments, may also support accessible communication, but do not necessarily meet the definition of RTT. For communication features provided within ILIAS or through integrated third-party services, the applicable communication model should therefore be identified and assessed against the corresponding requirements of the standard.

In particular, this requirement supports:
* EN 301 549 → 4.2.4 Usage without hearing
* EN 301 549 → 4.2.5 Usage with limited hearing
* EN 301 549 → 4.2.6 Usage with no or limited vocal capability

**Understanding WCAG:**
This requirement has no direct equivalent in the WCAG success criteria. It concerns the transmission behaviour and responsiveness of real-time text communication under EN 301 549.

**How to Meet WCAG:** No specific „How to Meet WCAG“ resource applies directly to this requirement.
</details>

<details>
<summary>2.2.2 Concurrent Speech and Text</summary>

**Reference:** EN 301 549 → 6.2.1.2 Concurrent voice and text

**Description:** If the web application supports two-way communication and real-time text (RTT), it should be possible to communicate concurrently using both voice and real-time text.

**Success Criterion:** Users can send, receive, and access real-time text while simultaneously participating in voice communication. Neither communication channel requires the other to be disabled, paused, or interrupted.

**Test Questions:**
*	Can users participate in voice communication and RTT simultaneously?
*	Can users receive incoming RTT messages while speaking or listening?
*	Can users send RTT messages without interrupting voice communication?
*	Are both communication channels available throughout the session?
*	Are notifications of incoming RTT messages accessible while voice communication is active?
*	Can users independently control voice and RTT functions?
*	Are both communication channels accessible using assistive technologies?

**Further Information:** When assessing compliance with this requirement, it is important to distinguish between Real-Time Text (RTT) as defined by EN 301 549 and other forms of synchronous text-based communication. According to EN 301 549, RTT refers to text that is transmitted and displayed during entry, without requiring the sender to explicitly submit the message. Other communication mechanisms, such as chat systems or collaborative editing environments, may also support accessible communication, but do not necessarily meet the definition of RTT. For communication features provided within ILIAS or through integrated third-party services, the applicable communication model should therefore be identified and assessed against the corresponding requirements of the standard.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability

Related Requirements:
*	EN 301 549 → 6.2.1.1 Real-time text communication
*	EN 301 549 → 6.4 Alternatives to voice-based services

**Understanding WCAG:**
This requirement has no direct equivalent in the WCAG success criteria. It concerns the transmission behaviour and responsiveness of real-time text communication under EN 301 549.

**How to Meet WCAG:** No specific „How to Meet WCAG“ resource applies directly to this requirement.
</details>

<details>
<summary>2.2.3 Visually Distinguishable Display of Text Messages</summary>

**Reference:** EN 301 549 → 6.2.2.1 Visually distinguishable display

**Description:** If the application supports sending and receiving real-time text (RTT), incoming and outgoing text messages shall be visually distinguishable from one another. Users must be able to identify the direction and origin of messages without ambiguity. Visual distinctions may be achieved through layout, positioning, labelling, styling, or other visual cues. Colour alone shall not be the sole means of distinguishing messages.

**Success Criterion:** Users can easily distinguish between sent and received text messages. The distinction remains understandable regardless of colour perception and is maintained throughout the communication session.

**Test Questions:**
*	Are incoming and outgoing text messages visually distinguishable?
*	Is the distinction between sent and received messages clear and consistent?
*	Is colour the only mechanism used to differentiate messages?
*	Are additional visual indicators provided (e.g., labels, alignment, icons, borders, message grouping)?
*	Does the distinction remain understandable for users with colour vision deficiencies?
*	Is the message display still understandable when custom styles or browser settings are applied?
*	Does the distinction remain visible when the interface is magnified?

**Further Information:** For communication features provided within ILIAS or through integrated third-party services, visual differentiation may be achieved through message alignment, sender labels, avatars, message grouping, icons, or comparable interface elements. The distinction should remain perceivable regardless of individual colour perception or display settings. Section 6.2.2.1 deals exclusively with visual distinguishability.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.2 Usage with limited vision
*	EN 301 549 → 4.2.3 Usage without perception of colour
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 1.4.11 Non-text Contrast

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/use-of-color.html
*	https://www.w3.org/WAI/WCAG21/Understanding/non-text-contrast.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-contrast
</details>

<details>
<summary>2.2.4 Programmatically Distinguishable Display of Text Messages</summary>

**Reference:** EN 301 549 → 6.2.2.2 Programmatically determinable send and receive direction

**Description:** If the web application offers a real-time text (RTT) communication feature, it should be possible to programmatically determine the direction of transmission and reception. Specifically, this means that users of assistive technologies should be able to distinguish between text they have sent and text they have received when reading the text communication. The distinction must not rely solely on visual presentation such as alignment, colour, spacing, or position. Instead, the send/receive direction should be conveyed through accessible names, labels, roles, relationships, metadata, or other programmatically determinable information.

**Success Criterion:** Assistive technologies can identify whether a text message was sent by the current user or received from another participant. The send/receive direction is available programmatically and remains understandable when the visual layout is not perceived.

**Test Questions:**
*	Can screen reader users distinguish between sent and received text messages?
*	Is the send/receive direction exposed programmatically and not only visually?
*	Are messages labelled in a way that identifies whether they were sent or received?
*	Are sender names or roles available to assistive technologies?
*	Is the message order understandable when accessed with a screen reader?
*	Are new incoming messages announced in a meaningful way?
*	Does the distinction remain available when CSS or visual layout information is not available?
*	Is the same information available during live communication and when reviewing the communication history?

**Further Information:** For communication features provided within ILIAS or through integrated third-party services, the distinction between sent and received messages should be available not only visually but also programmatically. This may be achieved through accessible labels such as „sent message“, „received message“, sender names, ARIA attributes, structured markup, or comparable mechanisms. This requirement should be assessed together with EN 301 549 → 6.2.2.1, which addresses the visual distinguishability of sent and received messages.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.1 Usage without vision
*	EN 301 549 → 4.2.2 Usage with limited vision
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html
*	https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>2.2.5 Speaker Identification</summary>

**Reference:** EN 301 549 → 6.2.2.3 Speaker Identification

**Description:** If the web application supports real-time text (RTT) communication and identifies active speakers based on their voice, speakers who use real-time text input should also be equally identifiable. This means that participants using RTT must not be less identifiable than participants using voice. Their contributions should be associated with their name, role, or another clear identifier in a way that is available visually and, where relevant, programmatically.

**Success Criterion:** Participants using real-time text can be identified in a manner equivalent to participants using voice communication. Text contributions are clearly associated with the respective user and can be understood in the context of the ongoing communication.

**Test Questions:**
*	Are participants using RTT clearly identified?
*	Is each text contribution associated with the person who entered it?
*	Is the identification of RTT users equivalent to the identification of active voice speakers?
*	Are names, roles, or other identifiers available visually?
*	Are names, roles, or other identifiers available to assistive technologies?
*	Can users distinguish between contributions from different participants?
*	Is the identification maintained when reviewing the communication history?
*	Is the identification still understandable when several users communicate at the same time?

**Further Information:** For communication features provided within ILIAS or through integrated third-party services, this requirement is relevant where participants are identified during synchronous communication, for example in video conferences, chats, or collaborative environments. If active voice speakers are highlighted or otherwise identified, participants using text-based communication should be identifiable in a comparable way.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.1 Usage without vision
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html
*	https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>2.2.6  Real-Time Indication of Voice Communication</summary>

**Reference:** EN 301 549 → 6.2.2.4 Visual indicator of Audio with RTT

**Description**: If the web application supports two-way voice communication and provides real-time text (RTT) features, the activity of speakers shall be indicated in real time. Users must be able to identify that audio input is currently taking place, even if they cannot hear the audio. The indicator of audio activity should be visually available and programmatically determinable, so that assistive technologies, including screen readers or Braille displays, can inform users about ongoing audio communication. This requirement concerns the indication that audio input is occurring; it does not require the audio content itself to be transcribed or converted into text.

**Success Criterion:** Users can determine in real time when a participant is producing audio input. The audio activity indicator is available visually and programmatically, and can be perceived by users who are deaf, hard of hearing, deafblind, or using assistive technologies.

**Test Questions:**
*	Is audio activity indicated in real time when a participant is speaking or producing audio input?
*	Is the visual indicator clearly associated with the active participant?
*	Is the audio activity indicator available without relying on sound?
*	Is the audio activity indicator programmatically determinable?
*	Can screen readers or other assistive technologies detect and convey the audio activity state?
*	Can users relying on Braille displays be informed that audio input is currently taking place?
*	Does the indicator update when the active speaker changes?
*	Does the indicator disappear or change state when audio input stops?
*	Is the indicator distinguishable without relying on colour alone?
*	Does the indicator remain available when the interface is magnified or customised?

**Further Information:** For communication features provided within ILIAS or through integrated third-party services, this requirement is particularly relevant for video conferencing or audio conferencing scenarios. If an active speaker is visually highlighted, the same information should also be available programmatically, for example through accessible labels, state changes, live regions, or comparable mechanisms. This requirement does not require automatic speech-to-text transcription. It only concerns the real-time indication that audio input is currently occurring.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.1 Usage without vision
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/use-of-color.html
*	https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html
*	https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>2.2.7  Interoperability of Real-Time Text Communication</summary>

**Reference:** EN 301 549 → 6.2.3 Interoperability

**Description:** If the web application enables real-time text (RTT) communication with other applications or communication services, the RTT functionality shall support interoperability. This means that relevant and applicable standards for real-time text communication, such as ITU, IETF, or ETSI standards, should be supported where the application exchanges RTT with external systems. The purpose of this requirement is to ensure that users can communicate through RTT across different systems, platforms, or services without losing accessibility-related functionality.

**Success Criterion:** Real-time text communication can be exchanged with compatible external systems or services using relevant recognised standards. RTT functionality remains usable, reliable, and accessible when communication takes place across system boundaries.

**Test Questions:**
*	Does the application exchange RTT communication with external systems, services, or applications?
*	If external RTT communication is supported, are recognised RTT standards used?
*	Is RTT communication preserved when exchanged between different systems?
*	Can users send and receive RTT across compatible external communication services?
*	Are RTT-specific properties, such as real-time transmission and message direction, preserved during exchange?
*	Does interoperability work without requiring users to switch to a less accessible communication mode?
*	Are limitations of RTT interoperability documented?
*	If RTT interoperability is not supported, is this clearly documented as not applicable or as a known limitation?

**Further Information:** For ILIAS and integrated third-party services, this requirement is primarily relevant where real-time text communication is exchanged with external communication systems or services. It is less relevant for communication features that operate only within a single application context, such as an internal chat or collaborative editing environment, unless these features explicitly claim or provide RTT interoperability. When assessing this requirement, it should first be determined whether the communication feature provides RTT in the normative sense and whether RTT is exchanged with other applications or services. If this is not the case, the requirement may be documented as not applicable.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability

Related Requirements:
*	EN 301 549 → 6.2.1.1 Real-time text communication
*	EN 301 549 → 6.2.1.2 Concurrent voice and text
*	EN 301 549 → 6.2.4 Real-time text responsiveness
*	EN 301 549 → 6.4 Alternatives to voice-based services

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns interoperability of RTT communication between systems and is therefore primarily addressed by the technical requirements of EN 301 549.

**How to Meet WCAG:** No specific „How to Meet WCAG“ resource applies directly to this requirement.
</details>

<details>
<summary>2.2.8 Response Speed of Real-Time Text Communication</summary>

**Reference:** EN 301 549 → 6.2.4 RTT responsiveness

**Description:** If the web application supports real-time text (RTT) and accepts text input, the smallest unit of text input shall be transmitted within a maximum of 500 milliseconds. This applies to systems that forward entered characters, words, or comparable text units to the recipient immediately after they are entered. This requirement does not refer to conventional chat systems in which a complete message is composed first and then sent by pressing Enter or a „Send“ button. It applies only where text is transmitted during entry in the normative sense of real-time text.

**Success Criterion:** Text entered by the user is transmitted to the recipient within 500 milliseconds per smallest supported text unit, such as an individual character, word, or comparable unit.
Success Criterion

**Test Questions:**
*	Is text transmitted during entry rather than only after pressing Enter or “Send”?
*	Is the smallest supported text unit transmitted within 500 milliseconds?
*	Does RTT responsiveness remain stable during an active communication session?
*	Are any limitations of RTT responsiveness documented?
*	Further Information

**Further Information:** For ILIAS and integrated third-party services, this requirement should only be applied where a communication feature provides RTT in the normative sense. Chat systems or collaborative editing tools may support accessible communication, but they do not necessarily meet the RTT definition if text is only transmitted after a message is submitted or through non-RTT collaboration mechanisms.

Related Functional Performance Statements:
•	EN 301 549 → 4.2.4 Usage without hearing
•	EN 301 549 → 4.2.5 Usage with limited hearing
•	EN 301 549 → 4.2.6 Usage with no or limited vocal capability

Related Requirements:
•	EN 301 549 → 6.2.1.1 Real-time text communication
•	EN 301 549 → 6.2.1.2 Concurrent voice and text
•	EN 301 549 → 6.2.3 Interoperability

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns the transmission behaviour and responsiveness of real-time text communication under EN 301 549.

**How to Meet WCAG:** No specific “How to Meet WCAG” resource applies directly to this requirement.
</details>
</details>
<details>
<summary>2.3 Identification: Caller/Participant Identification</summary>

**Reference:** EN 301 549 → 6.3 Caller ID

**Description:** If the web application provides telecommunications functions that include caller or participant identification, the identification information shall also be available in text form and programmatically determinable. This means that users who cannot perceive visual or auditory caller information must still be able to identify who is calling, speaking, joining, or participating through assistive technologies such as screen readers or Braille displays.

**Success Criterion:** Caller or participant identification is available in a text-based and programmatically determinable form. Assistive technologies can retrieve and communicate the relevant identification information to users.
Test Questions

**Test Questions:**
*	Is caller or participant identification available in text form?
*	Can assistive technologies access the identification information?
*	Is the identification updated when participants join, leave, or change status?
*	Is the identification understandable without relying on audio, images, colour, or position alone?

**Further Information:** For ILIAS and integrated third-party services, this requirement is relevant where communication features display participant information, for example in video conferences, audio conferences, chats, or comparable synchronous communication tools. In such cases, participant names, roles, or status information should be available not only visually but also as text and through programmatically determinable information.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.1 Usage without vision
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/name-role-value.html
*	https://www.w3.org/WAI/WCAG21/Understanding/status-messages.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>2.4 Video Conferencing</summary>

Clause 2.4 addresses video communication features that are relevant for users who communicate through sign language or rely on lip-reading. For these users, video quality, frame rate, audio-video synchronisation and visual indicators are essential for accessible communication. As a baseline, video communication should support a resolution of at least Quarter Video Graphics Array (QVGA, 320 × 240), a frame rate of at least 20 frames per second, and an audio-video time difference that does not exceed 100 milliseconds. Higher resolution and higher frame rates can further improve the usability of sign language communication, especially finger spelling, and lip-reading. In practice, frame rate is often more important than resolution. Audio-video asynchronicity can significantly affect lip-reading. In particular, video that lags behind audio may have a negative impact on comprehension. End-to-end latency may also affect video-based sign language communication. Overall delays below 400 milliseconds are preferred, with lower delays, especially closer to 100 milliseconds, improving usability. Since overall delay depends on several factors, including network conditions and video processing, it should be documented and optimised where possible, but is not treated here as a directly testable minimum requirement.

<details>
<summary>2.4.1 Resolution for Video Calls</summary>

**Reference:** EN 301 549 → 6.5.2 Resolution

**Description:** Web applications with video calling functionality should support at least QVGA resolution for video transmission. This typically corresponds to 320x240 pixels. For other aspect ratios, 76,800 pixels must be provided accordingly. This requirement supports users who rely on visual communication, including sign language users and users who depend on lip-reading. Higher resolutions may further improve accessibility, especially for finger spelling, facial expressions, and fine hand movements.

**Success Criterion:** The video communication feature supports a minimum video resolution of 320 × 240 pixels or an equivalent resolution of at least 76,800 pixels for other aspect ratios.

**Test Questions:**
*	Does the video communication feature support at least QVGA resolution or an equivalent pixel count?
*	Is the minimum resolution maintained during normal use?
*	Are users able to select or maintain a video quality suitable for sign language or lip-reading?
*	Are limitations of video resolution documented?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems and virtual classroom scenarios. The assessment should consider whether the video quality is sufficient for visual communication, including sign language, lip-reading, facial expressions, and gestures.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related Requirements:
*	EN 301 549 → 6.5.3 Frame rate
*	EN 301 549 → 6.5.4 Synchronization between audio and video
*	EN 301 549 → 6.5.6 Speaker indicator for sign language communication

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns technical performance requirements for accessible video communication under EN 301 549.

**How to Meet WCAG:** No specific “How to Meet WCAG” resource applies directly to this requirement.
</details>

<details>
<summary>2.4.2  Frame Rate for Video Calls</summary>

**Reference:** EN 301 549 → 6.5.3 Frame rate

**Description:** If the website supports video calls, it should support a frame rate of at least 20 frames per second. A frame rate of 30 frames per second is preferable. This requirement supports users who rely on visual communication, including sign language users and users who depend on lip-reading. A sufficient frame rate is particularly important for perceiving hand movements, finger spelling, facial expressions, and mouth movements.

**Success Criterion:** The video communication feature supports a frame rate of at least 20 frames per second during video calls. Where possible, 30 frames per second or higher should be supported to improve usability for sign language communication and lip-reading.

**Test Questions:**
*	Does the video communication feature support at least 20 frames per second?
*	Can a frame rate of 30 frames per second or higher be achieved under suitable conditions?
*	Is the frame rate sufficient for sign language, finger spelling, facial expressions, and lip-reading?
*	Are limitations of frame rate or video quality documented?
*	Further Information

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems and virtual classroom scenarios. The assessment should consider whether the supported frame rate is sufficient for visual communication, especially sign language, finger spelling and lip-reading.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related Requirements:
*	EN 301 549 → 6.5.2 Resolution
*	EN 301 549 → 6.5.4 Synchronization between audio and video
*	EN 301 549 → 6.5.6 Speaker indicator for sign language communication

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns technical performance requirements for accessible video communication under EN 301 549.

**How to Meet WCAG:** No specific “How to Meet WCAG” resource applies directly to this requirement.
</details>

<details>
<summary>2.4.3 Synchronization for Video Calls</summary>

**Reference:** EN 301 549 → 6.5.4 Synchronization between audio and video

**Description:** If the website supports video calls, the time difference between audio and video should not exceed 100 milliseconds. This requirement supports users who rely on lip-reading or on the simultaneous perception of speech, facial expressions, mouth movements, gestures, and other visual cues. Audio-video asynchronicity can significantly affect comprehension, especially when the video lags behind the audio.

**Success Criterion:** Audio and video are synchronized so that the time difference between both streams does not exceed 100 milliseconds under supported operating conditions.

**Test Questions:**
*	Does the time difference between audio and video remain within 100 milliseconds?
*	Is synchronization maintained during normal video call usage?
*	Is the synchronization sufficient for lip-reading and visual communication?
*	Are known limitations of audio-video synchronization documented?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems and virtual classroom scenarios. The assessment should consider whether audio and video remain sufficiently synchronized for lip-reading, sign language communication, facial expressions, and other visual communication cues. Overall end-to-end latency may also affect video-based communication. Since total delay depends on several factors, including network conditions and video processing, it should be optimized and documented where possible.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related Requirements:
*	EN 301 549 → 6.5.2 Resolution
*	EN 301 549 → 6.5.3 Frame rate
*	EN 301 549 → 6.5.6 Speaker indicator for sign language communication

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns technical performance requirements for accessible video communication under EN 301 549.

**How to Meet WCAG:** No specific “How to Meet WCAG” resource applies directly to this requirement.

</details>

<details>
<summary>2.4.4 Visual Indication of Audio Activity</summary>

**Reference:** EN 301 549 → 6.5.5 Visual indicator of audio with video

**Description:** If the web application supports video calls, audio activity shall be indicated visually. Users must be able to recognize when a participant is currently producing audio input, even if they cannot hear the audio. The visual indication should be clearly associated with the respective participant and should update in real time when audio activity starts, stops, or changes between participants.

**Success Criterion:** Users can visually identify when audio activity is taking place during a video call and which participant is producing audio input. The indication does not rely on sound alone and remains understandable throughout the communication session.

**Test Questions:**
*	Is audio activity displayed visually during video calls?
*	Is the visual indicator clearly associated with the active participant?
*	Does the indicator update when audio activity starts, stops, or changes between participants?
*	Is the indicator distinguishable without relying on colour alone?
*	Are known limitations of audio activity indication documented?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems and virtual classroom scenarios. Visual indicators may include highlighting the active speaker, displaying an audio activity icon, showing microphone activity, or using comparable mechanisms. The indication should not rely on colour alone.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related WCAG Success Criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 1.4.11 Non-text Contrast

**Understanding WCAG:**
*	https://www.w3.org/WAI/WCAG21/Understanding/info-and-relationships.html
*	https://www.w3.org/WAI/WCAG21/Understanding/use-of-color.html
*	https://www.w3.org/WAI/WCAG21/Understanding/non-text-contrast.html

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-contrast
</details>

<details>
<summary>2.4.5 Speaker Indication for Sign Language Communication</summary>

**Reference:** EN 301 549 → 6.5.6 Speaker identification with video (sign language) communication

**Description:** If the web application supports video calls and provides an indication of speaker activity, equivalent indication shall also be provided for users communicating through sign language. This means that participants using sign language should be identifiable as active communicators in a way comparable to participants using spoken audio. The system should not treat audio activity as the only form of active communication.

**Success Criterion:** Users can identify active participants communicating through sign language in a comparable way to active speakers using audio. The indication is available visually and remains understandable during the video call.

**Test Questions:**
*	Are participants using sign language identifiable as active communicators?
*	Is speaker or communicator activity not limited to audio input only?
*	Can sign language users be highlighted, pinned, spotlighted, or otherwise indicated during communication?
*	Is the indication clear and consistent for all participants?
*	Are known limitations for sign language speaker indication documented?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems and virtual classroom scenarios. It should be possible to make sign language users or interpreters visible and identifiable during communication, for example through pinning, spotlighting, persistent video display, or comparable mechanisms. Where a system highlights active speakers based on audio input, comparable mechanisms should be considered for sign language communication, since sign language activity may not produce audio input.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related Requirements:
*	EN 301 549 → 6.5.2 Resolution
*	EN 301 549 → 6.5.3 Frame rate
*	EN 301 549 → 6.5.4 Synchronization between audio and video
*	EN 301 549 → 6.5.5 Visual indicator of audio with video

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. It concerns functional support for sign language communication in video communication systems under EN 301 549.

**How to Meet WCAG:** No specific “How to Meet WCAG” resource applies directly to this requirement.
</details>
</details>

<details>
<summary>2.5 Alternatives to Video-Based Services</summary>

**Reference:** EN 301 549 → 6.6 Alternatives to video-based services

**Description:** If the web application provides video-based communication services, alternative communication channels shall be available where video communication is not accessible or cannot be used by a participant. Alternative communication channels may include text chat, real-time text, audio-only participation, shared notes, collaborative documents, captions, transcripts, or comparable mechanisms, depending on the communication context and user needs. This requirement supports users who cannot use or perceive video communication, including users with visual impairments, users with limited bandwidth or technical constraints, users who cannot activate a camera, and users who require text-based or audio-based alternatives.

**Success Criterion:** Users can participate in the essential communication and interaction without being required to use video. Relevant information, contributions, and interaction options are available through at least one suitable alternative communication channel.

**Test Questions:**
*	Is participation possible without using video?
*	Are essential information and contributions available through an alternative channel?
*	Are alternative communication channels accessible with keyboard and assistive technologies?
*	Are limitations of video alternatives documented?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for video conferencing systems, virtual classrooms, and collaborative teaching scenarios. Alternative communication channels may include chat, shared notes, Etherpad, EtherCalc, captions, transcripts, audio-only participation, or downloadable materials. The requirement should not be understood as replacing the accessibility requirements for video communication itself. Instead, it ensures that users are not excluded when video communication is not usable for them or when video-based participation is not possible.

Related Functional Performance Statements:
*	EN 301 549 → 4.2.1 Usage without vision
*	EN 301 549 → 4.2.2 Usage with limited vision
*	EN 301 549 → 4.2.4 Usage without hearing
*	EN 301 549 → 4.2.5 Usage with limited hearing
*	EN 301 549 → 4.2.6 Usage with no or limited vocal capability
*	EN 301 549 → 4.2.7 Usage with limited manipulation or strength
*	EN 301 549 → 4.2.9 Usage with limited cognition

Related Requirements:
*	EN 301 549 → 6.2.1.1 Real-time text communication
*	EN 301 549 → 6.2.1.2 Concurrent voice and text
*	EN 301 549 → 6.3 Caller ID
*	EN 301 549 → 6.5.2 Resolution
*	EN 301 549 → 6.5.3 Frame rate
*	EN 301 549 → 6.5.4 Synchronization between audio and video
*	EN 301 549 → 6.5.5 Visual indicator of audio with video
	EN 301 549 → 6.5.6 Speaker identification with video (sign language) communication

**Understanding WCAG:** This requirement has no direct equivalent in the WCAG success criteria. Depending on the implementation, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>
</details>

<details>
<summary>3. VIDEO SKILLS</summary>

This checklist focuses on the accessibility requirements for ICT with video capabilities as defined in Chapter 7 of EN 301 549. The items listed in this section address the technical capabilities of video players, platforms, or systems to support accessibility features such as captions, audio description, spoken subtitles, and accessible controls. It is important to note that these requirements do not assess whether a specific video content item actually provides accessible media alternatives. Instead, they examine whether the ICT system is capable of displaying, synchronising, preserving, customising, and controlling such features when they are available.

For a complete accessibility assessment of video content, additional requirements from the relevant content-related chapters of EN 301 549 should also be considered, depending on the context. For example, web-based video content may require checks against Chapter 9, non-web documents against Chapter 10, and software-based content against Chapter 11. These additional requirements address whether captions, live captions, audio description, or equivalent media alternatives are actually provided for the content itself.

Therefore, this checklist should be understood as a technical assessment of video functionality under Chapter 7, complemented where necessary by separate content-level checks.

<details>
<summary>3.1 Caption processing technology</summary>

<details>
<summary>3.1.1 Captioning playback</summary>

**Reference:** EN 301 549 → 7.1.1 Captioning playback

**Description:** If displays video with synchronized audio, it shall provide a mode of operation to display available captions. Where closed captions are provided as part of the content, users shall be able to choose to display them. This requirement applies to ICT products, services, platforms, or video players that display video with synchronized audio. It does not require the ICT itself to create captions. Instead, it requires that available captions can be rendered and made visible to users. Captions are particularly important for users who are deaf or hard of hearing, users who cannot listen to audio in a given situation, users in noisy environments, and users who benefit from reading spoken content and relevant sound information. This requirement concerns the technical capability of the video player or platform. Whether captions must be provided for a specific video content item is assessed under the relevant content-related requirements, for example in EN 301 549 Chapter 9 for web content, Chapter 10 for non-web documents, or Chapter 11 for software.

**Success Criterion:** Users can activate and view available captions when video with synchronized audio is played. If closed captions are included in the video content or provided as an associated caption track, it allows users to select and display those captions.

**Test Questions:**
*	Does the video player or platform provide a visible option to turn captions on and off?
*	Are available captions displayed during playback?
*	Can users select closed captions when they are provided as part of the content?
*	Are captions displayed without blocking essential video content, controls, or interaction elements?
*	Can the caption function be operated with a keyboard?
*	Is the caption control accessible to assistive technologies, including screen readers?
*	Is the selected caption state preserved during playback, for example after pausing, seeking, or switching to full-screen mode?
*	Are captions still available when the video is embedded in the web application or learning platform?
*	Are limitations documented if the platform supports captions only for certain video formats, players, browsers, or integrations?

**Further Information:**
For ILIAS and integrated third-party services, this requirement is particularly relevant for embedded video players, media objects, learning modules, video-based course materials, lecture recordings, and external video platforms integrated into the learning environment. The requirement should not be understood as requiring ILIAS or the player to automatically generate captions. It requires that captions, where available, can be displayed. If the video content itself does not include captions or an associated caption file, this may indicate an issue with the accessibility of the content, but not necessarily with the technical capability of the player under EN 301 549 clause 7.1.1. Examples of caption sources may include integrated caption tracks, WebVTT files, subtitle files uploaded with the video, captions provided by an external video platform, or captions embedded in the media file. 

The assessment should distinguish between:
*	the availability of captions as part of the video content; and
*	the ability of the ICT, platform, or player to display those captions.

Related Requirements:
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.1.3 Preservation of captioning
*	EN 301 549 → 7.1.4 Captions characteristics
*	EN 301 549 → 7.1.5 Spoken subtitles
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 9.1.2.4 Captions (Live)
*	EN 301 549 → 10.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 10.1.2.4 Captions (Live)
*	EN 301 549 → 11.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 11.1.2.4 Captions (Live)

**Understanding WCAG:** This requirement is related to, but not identical with, WCAG requirements for captions. WCAG focuses primarily on whether captions are provided for audio content in synchronized media. EN 301 549 clause 7.1.1 focuses on whether the ICT can display available captions. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.4.3 Contrast (Minimum)
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>3.1.2 Captioning synchronization</summary>

**Reference:** EN 301 549 → 7.1.2 Captioning synchronization

**Description:** Where ICT displays captions, the captions shall be synchronized with the corresponding audio and video content. This requirement applies to ICT that plays video with synchronized audio and displays captions. Captions must appear at the correct time so that users can understand which spoken content, sound, or speaker the captions refer to. Synchronized captions are essential for users who rely on captions to follow spoken language, sound effects, speaker changes, or other relevant audio information. Poor synchronization can make video content difficult or impossible to understand, especially in educational, instructional, or interactive contexts. This requirement concerns the technical capability of the ICT to maintain synchronization during normal playback, including pausing, seeking, changing playback speed where supported, and switching display modes.

**Success Criterion:** Displayed captions remain synchronized with the corresponding audio and video content during playback. Captions appear at the appropriate time and remain understandable in relation to the spoken or relevant audio content.

**Test Questions:**
*	Do captions appear at the correct time in relation to the spoken audio?
*	Do captions remain synchronized after pausing and resuming playback?
*	Do captions remain synchronized after seeking forward or backward in the video?
*	Do captions remain synchronized in full-screen mode?
*	If playback speed can be changed, do captions remain usable and synchronized?
*	Are speaker changes, sound effects, and relevant audio events presented at the appropriate time
*	 Does synchronization work across supported browsers, devices, and embedded player contexts?
*	Are limitations documented if caption synchronization depends on a specific video format, caption format, browser, or third-party service?

**Further Information:** For ILIAS and integrated third-party services, this requirement is particularly relevant for lecture recordings, learning videos, embedded media objects, video assignments, and externally hosted videos integrated into course pages. The requirement should not be confused with the editorial quality of the captions themselves. If caption timing is incorrect because the caption file was authored incorrectly, this may be a content-level issue. If the timing is correct in the source file but the platform or player displays captions out of sync, this indicates a technical issue under EN 301 549 clause 7.1.2. Testing should therefore include at least one known-good test video with correctly timed captions. This helps distinguish between problems caused by the video content and problems caused by the player or platform.

Related Requirements
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.3 Preservation of captioning
*	EN 301 549 → 7.1.4 Captions characteristics
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 9.1.2.4 Captions (Live)
*	EN 301 549 → 10.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 10.1.2.4 Captions (Live)
*	EN 301 549 → 11.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 11.1.2.4 Captions (Live)

**Understanding WCAG:** WCAG requires captions for certain types of synchronized media but does not define a separate success criterion only for caption synchronization. Synchronization is nevertheless essential for meeting the purpose of caption-related WCAG requirements. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>3.1.3 Preservation of captioning</summary>

**Reference:** EN 301 549 → 7.1.3 Preservation of captioning

**Description:** Where ICT transmits, converts, or records video with synchronized audio, it shall preserve caption data so that the captions can still be displayed and synchronized after the transmission, conversion, or recording process. This requirement applies to ICT systems that process video, for example by uploading, transcoding, embedding, streaming, recording, exporting, converting, or distributing video files. Caption data must not be removed, corrupted, disconnected, or made unusable by these processes. This requirement is particularly important in learning environments where videos may be uploaded, converted into different formats, embedded in learning modules, recorded in virtual classrooms, or exported for later use.

**Success Criterion:** Caption data is preserved when video with synchronized audio is transmitted, converted, recorded, embedded, exported, or otherwise processed by the ICT. After processing, captions can still be selected, displayed, and synchronized with the corresponding audio and video.

**Test Questions:**
*	Are caption tracks preserved when a video is uploaded to the platform?
*	Are captions preserved after the video is converted or transcoded?
*	Are captions preserved when a video is embedded in a course page, learning module, or media object?
*	Are captions preserved when a recorded session is saved or exported?
*	Are captions preserved when videos are downloaded, copied, moved, or reused in another course context?
*	Are caption files still correctly associated with the corresponding video after processing?
*	Do captions remain selectable and visible after transmission or conversion?
*	Do captions remain synchronized after processing?
*	Are limitations documented if caption preservation only works for certain formats, workflows, or third-party integrations?

**Further Information:** For ILIAS and integrated services, this requirement is particularly relevant for media upload workflows, lecture recording systems, virtual classroom recordings, video conversion services, media pools, learning modules, and exports. A common accessibility risk is that captions are available in the original video or external platform, but are lost during upload, conversion, embedding, download, or recording. This can make an otherwise accessible video inaccessible after it has been processed by the platform. Testing should include typical workflows used by teachers, students, and administrators, for example: upload a captioned video, embed it in a course, play it back, export or reuse it, and check whether captions are still available and synchronized. The requirement should not be understood as requiring the platform to create missing captions. It requires the platform to preserve caption information that already exists.

Related Requirements:
*	EN 301 549 → 5.4 Preservation of accessibility information during conversion
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.1.4 Captions characteristics
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 11.8.3 Preservation of accessibility information in transformations
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 10.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 11.1.2.2 Captions (Prerecorded)

**Understanding WCAG:** This requirement has no direct one-to-one equivalent in WCAG. WCAG addresses whether captions are available for synchronized media, while EN 301 549 clause 7.1.3 addresses whether caption data is preserved when ICT processes video.

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>3.1.4 Captions characteristics</summary>

**Reference:** EN 301 549 → 7.1.4 Captions characteristics

**Description:** Where ICT displays captions, users shall be able to adapt relevant caption characteristics to meet their needs, except where the caption characters are unmodifiable. Caption characteristics may include presentation aspects such as font size, font type, text colour, background colour, opacity, contrast, positioning, and related visual settings, depending on the technology and implementation. This requirement supports users with limited vision, users with cognitive impairments, users with reading difficulties, and users who need captions to be displayed in a way that is perceivable and readable in their specific context. The requirement concerns the ability of the ICT or player to allow caption presentation to be adapted where technically possible. It does not require modification of captions that are part of the video image itself, such as open captions burned into the video.

**Success Criterion:** Users can adjust available caption display characteristics where the caption technology allows such adjustment. Captions remain readable, visible, and usable after customization, and the customization does not prevent users from accessing the video content or controls.

**Test Questions:**
*	Can users adjust caption size?
*	Can users adjust caption colours or contrast?
*	Can users adjust the caption background or opacity where supported?
*	Can users choose between available caption styles or display settings?
*	Do customized captions remain readable and synchronized during playback?
*	Do caption settings remain effective after pausing, seeking, or switching to full-screen mode?
*	Are caption settings accessible with keyboard operation?
*	Are caption settings accessible to assistive technologies?
*	Are captions displayed without covering essential content or controls after customization?
*	Are limitations documented where captions are unmodifiable, for example because they are burned into the video or provided as bitmap images?

**Further Information:** For ILIAS and integrated third-party services, this requirement is relevant where the platform or embedded player displays caption tracks and offers caption settings. This may depend on the specific player, browser, video format, caption format, or third-party video service. The requirement should not be understood as requiring customization of open captions that are permanently embedded in the video image. However, where closed captions or text-based subtitle tracks are used, the player should support appropriate display customization. Testing should include captions in a text-based format, such as WebVTT, where presentation customization is technically possible. It should also check whether user-defined settings are preserved during normal playback and whether the settings remain available in embedded contexts. Caption customization is especially important in higher education contexts because videos may be used in different environments, including lecture halls, mobile devices, low-light situations, and individual study contexts.

Related Requirements:
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.1.3 Preservation of captioning
*	EN 301 549 → 7.1.5 Spoken subtitles
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.4.3 Contrast (Minimum)
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.1.4.12 Text spacing
*	EN 301 549 → 11.7 User preferences

**Understanding WCAG:** WCAG does not contain a specific success criterion for caption customization in the same way as EN 301 549 clause 7.1.4. However, several WCAG criteria are relevant when captions are displayed as text or when caption controls and settings are part of the user interface. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.4.3 Contrast (Minimum)
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 1.4.12 Text Spacing
*	WCAG 2.1 → 1.4.13 Content on Hover or Focus
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#text-spacing
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#content-on-hover-or-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>3.1.5 Spoken Subtitles</summary>

**Reference:** EN 301 549 → 7.1.5 Spoken subtitles

**Description:** Where ICT displays video with synchronized audio, it shall provide a mode of operation to output available captions as speech, except where the content of the displayed captions is not programmatically determinable. This requirement is especially relevant for users who cannot read captions visually, including blind users, users with limited vision, users with reading difficulties, and users who need captions to be presented audibly. Spoken subtitles are not the same as audio description. Audio description provides information about relevant visual content. Spoken subtitles provide speech output of the caption or subtitle text. This can be particularly important when the original audio is in a language the user does not understand and subtitles are provided in a language they can understand. This requirement depends on whether the caption text is programmatically determinable. If subtitles are burned into the video image or provided as bitmap images, the ICT may not be able to output them as speech.

**Success Criterion:** Users can activate a mode of operation that provides speech output of available captions when the caption text is programmatically determinable. The spoken subtitles are available during video playback and correspond to the displayed caption content.

**Test Questions:**
*	Can available captions or subtitles be output as speech?
*	 Is the spoken subtitle function available through the video player or platform?
*	Can the spoken subtitle function be operated with a keyboard?
*	Is the spoken subtitle control accessible to assistive technologies?
*	Does the speech output correspond to the displayed caption or subtitle text?
*	Does speech output remain synchronized and understandable during playback?
*	Can users distinguish spoken subtitles from the original audio, for example through volume control, voice settings, or assistive technology settings where available?
*	Does the function work with text-based subtitle formats such as WebVTT?
*	Are limitations documented where spoken subtitles are not available because subtitles are not programmatically determinable?

**Further Information:** For ILIAS and integrated third-party services, this requirement may be relevant for embedded video players, lecture recordings, multilingual videos, subtitle-based learning materials, and external video platforms integrated into course environments. This requirement should not be confused with captions for deaf or hard-of-hearing users. Captions are primarily a visual text alternative for audio content. Spoken subtitles are an auditory rendering of subtitle or caption text, which may support users who cannot visually read the captions. The requirement also differs from audio description. A video may need both spoken subtitles and audio description, depending on the content and the user need. Testing should use subtitle or caption files that are programmatically determinable. If captions are burned into the image or provided only as graphical text, spoken output may not be technically possible unless additional OCR or alternative text mechanisms are provided. Where spoken subtitles are supported by assistive technology rather than by the video player itself, the interaction between the player, caption format, browser, operating system, and assistive technology should be tested.

Related Requirements:
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.1.3 Preservation of captioning
*	EN 301 549 → 7.1.4 Captions characteristics
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 7.2.2 Audio description synchronization
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 9.1.2.4 Captions (Live)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 10.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 11.1.2.2 Captions (Prerecorded)

**Understanding WCAG:** This requirement has no direct equivalent in WCAG. WCAG requires captions for certain audio content and audio description for certain video content, but it does not generally require captions or subtitles to be spoken aloud. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>
</details>

<details>
<summary>3.2 Audio description technology</summary>

<details>
<summary>3.2.1 Audio Description Playback</summary>

**Reference:** EN 301 549 → 7.2.1 Audio description playback

**Description:** If ICT displays video with synchronized audio, it shall provide a mechanism to select and play available audio description. Audio description is an additional audio track or audio function that describes relevant visual information which is necessary to understand the video content. This may include visual actions, scene changes, gestures, on-screen text, diagrams, or other visual information that is not available through the main audio track. This requirement applies to ICT products, services, platforms, or video players that display video with synchronized audio. It does not require the ICT itself to create audio description. Instead, it requires that available audio description can be selected and played by users.

**Success Criterion:** Users can select and play available audio description when video with synchronized audio is played. The audio description can be accessed through the ICT, platform, or video player without requiring an inaccessible workaround.

**Test Questions:** Can users select and play available audio description?
*	Is the audio description control accessible with keyboard and assistive technologies?
*	Does audio description remain available in normal playback and full-screen mode?
*	Are limitations documented if audio description is only supported for certain formats, browsers, or third-party players?

**Further Information:** This requirement concerns the technical capability of the ICT or player to play available audio description. Whether audio description must be provided for a specific video content item is assessed under the relevant content-related requirements, for example in EN 301 549 Chapter 9 for web content, Chapter 10 for non-web documents, or Chapter 11 for software. For ILIAS, this requirement is especially relevant when videos are embedded via external services or media players. In such cases, the accessibility of audio description playback may depend on the integrated player or third-party platform rather than on ILIAS itself.

Related Requirements:
*	EN 301 549 → 7.2.2 Audio description synchronization
*	EN 301 549 → 7.2.3 Preservation of audio description
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 10.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 10.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 11.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 11.1.2.5 Audio Description (Prerecorded)

**Understanding WCAG:** This requirement is related to WCAG requirements for audio description, but it has a different focus. WCAG addresses whether audio description or a suitable media alternative is provided for video content. EN 301 549 clause 7.2.1 addresses whether the ICT can select and play available audio description. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>3.2.2 Audio description synchronization</summary>

**Reference:** EN 301 549 → 7.2.2 Audio description synchronization

**Description:** Where ICT has a mechanism to play audio description, it shall preserve synchronization between the audio/visual content and the corresponding audio description.This requirement ensures that audio description is presented at the correct time in relation to the video content. Users must be able to understand which visual action, scene, object, or event the audio description refers to. Synchronization is particularly important in educational and instructional videos, where diagrams, demonstrations, slides, gestures, or visual examples may be essential for understanding the content.

**Success Criterion:** Audio description remains synchronized with the corresponding audio and video content during playback. The description is presented at the appropriate time and supports understanding of the relevant visual information.

**Test Questions:**
*	Does audio description play at the correct time in relation to the video?
*	Does synchronization remain stable after pausing, resuming, seeking, or switching to full-screen mode?
*	If playback speed can be changed, does audio description remain usable and understandable?
*	Are synchronization limitations documented?

**Further Information:** This requirement focuses on the player or ICT mechanism that plays audio description. If the audio description track itself is incorrectly produced or timed, this may be a content-level issue. If a correctly timed audio description track becomes asynchronous in the platform or player, this indicates a technical issue under EN 301 549 clause 7.2.2. For testing, use a video with a known correctly timed audio description track. This makes it easier to distinguish between content-authoring problems and technical playback problems.

Related Requirements:
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 7.2.3 Preservation of audio description
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	 EN 301 549 → 10.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 10.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 11.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 11.1.2.5 Audio Description (Prerecorded)

**Understanding WCAG:** WCAG requires audio description or a media alternative for certain types of prerecorded synchronized media. It does not define a separate success criterion only for audio description synchronization. However, synchronization is necessary for audio description to fulfil its purpose. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
</details>

<details>
<summary>3.2.3 Preservation of audio description</summary>

**Reference:** EN 301 549 → 7.2.3 Preservation of audio description

**Description:** Where ICT transmits, converts, or records video with synchronized audio, it shall preserve audio description data so that it can still be played and synchronized after the transmission, conversion, or recording process.
This requirement applies to ICT systems that process video, for example by uploading, transcoding, embedding, streaming, recording, exporting, converting, or distributing video files. Audio description data must not be removed, corrupted, disconnected, or made unusable by these processes.

**Success Criterion:** Audio description data is preserved when video with synchronized audio is transmitted, converted, recorded, embedded, exported, or otherwise processed by the ICT. After processing, audio description can still be selected, played, and synchronized with the corresponding audio and video content.

**Test Questions:**
*	Is audio description preserved after upload, conversion, embedding, recording, or export?
*	Is the audio description track still correctly associated with the video?
*	 Can users still select and play audio description after processing?
*	Are limitations documented for formats, workflows, or third-party integrations?

**Further Information:** This requirement does not require the platform to create missing audio description. It requires the platform to preserve audio description information that already exists. A common risk is that an accessible original video loses its audio description track during upload, transcoding, export, or recording. This may occur when media workflows support only a single audio track or when metadata is not preserved. For ILIAS, this point is relevant when videos are uploaded, embedded, copied between courses, exported, or processed through external media services. Testing should follow the actual workflows used in the institution.

Related Requirements:
*	EN 301 549 → 5.4 Preservation of accessibility information during conversion
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 7.2.2 Audio description synchronization
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 11.8.3 Preservation of accessibility information in transformations
*	EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 10.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 11.1.2.3 Audio Description or Media Alternative (Prerecorded)

**Understanding WCAG:** This requirement has no direct one-to-one equivalent in WCAG. WCAG addresses whether audio description or a media alternative is available for certain video content. EN 301 549 clause 7.2.3 addresses whether audio description data is preserved when ICT processes video. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
</details>
</details>

<details>
<summary>3.3 Controls for captions and audio description</summary>

**Reference:** EN 301 549 → 7.3 User controls for captions and audio description

**Description:** Where ICT displays video with synchronized audio, user controls for captions and audio description shall be available at the same level of interaction as the primary media controls. This means that users should not have to navigate through complex menus, settings pages, or inaccessible workflows to activate captions or audio description when the main video controls, such as play, pause, volume, or full-screen, are directly available. This requirement supports users who need captions or audio description to access the video content and ensures that accessibility-related media controls are not hidden or more difficult to reach than other essential playback controls.

**Success Criterion:** Controls for captions and audio description are available at the same level of interaction as the primary media controls. Users can find, operate, and understand these controls using keyboard and assistive technologies.

**Test Questions:**
*	Are controls for captions and audio description available close to the primary media controls?
*	Can the controls be operated with keyboard and assistive technologies?
*	Are the controls clearly named and understandable?
*	Do the controls remain available in embedded and full-screen playback?

**Further Information:** The requirement does not necessarily mean that caption and audio description controls must always be visually identical to play or pause buttons. However, they should be comparably easy to find and operate. If users must open several menus while other media controls are immediately available, this may not meet the intended level of access. For ILIAS, this is especially relevant when videos are embedded through external players. The assessment should check the actual user interface shown to learners, not only the configuration options available to administrators or course authors.

Related Requirements:
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.4 Captions characteristics
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus Order
*	EN 301 549 → 9.2.4.6 Headings and Labels
*	EN 301 549 → 9.2.4.7 Focus Visible
*	EN 301 549 → 9.4.1.2 Name, Role, Value
*	EN 301 549 → 11.7 User preferences

**Understanding WCAG:** This requirement has no direct one-to-one equivalent in WCAG. WCAG contains requirements for captions and audio description as content alternatives, and for accessible controls as part of the user interface. EN 301 549 clause 7.3 adds the specific requirement that caption and audio description controls must be available at the same level of interaction as primary media controls. 

Depending on the implementation and context, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4. WEB CONTENT ACCESSIBILITY</summary>

<details>
<summary>4.1 Perceiveable of web content</summary>

<details>
<summary>4.1.1 Text Alternatives</summary>

<details>
<summary>4.1.1.1 Text Alternatives for Controls</summary>

**Reference:** EN 301 549 → 9.1.1.1 Non-text content (WCAG 2.1 → 1.1.1 Non-text Content)

**Description:** If non-text content is used as a control or accepts user input, it shall have an accessible name that describes its purpose. This applies to image buttons, icon buttons, graphical links, controls represented only by symbols, and other interactive non-text elements. Users of screen readers, voice control, keyboard navigation, or other assistive technologies must be able to understand what the control does before activating it. The text alternative should describe the function of the control, not necessarily the visual appearance of the icon. For example, an icon button showing a magnifying glass should usually be named „Search“, not „Magnifying glass“.

**Success Criterion:** All non-text controls have an accessible name that clearly describes their function or purpose. The name is available to assistive technologies and is consistent with the visible label or function of the control.

**Test Questions:**
*	Do all image buttons, icon buttons, and graphical links have an accessible name?
*	Does the accessible name describe the action or purpose of the control?
*	Is the control name available to screen readers and voice control?
*	Is the accessible name consistent with the visible label or icon meaning?

**Further Information:** This requirement is especially relevant for toolbars, media controls, navigation icons, action menus, upload buttons, delete buttons, edit buttons, and other icon-based interface elements. For ILIAS, this may be relevant in areas where actions are represented by icons, for example edit, delete, move, preview, download, or settings controls. The important question is whether the purpose of the control is programmatically available, not only visually understandable.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.4 Link purpose in context
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.1.1 requires that non-text content which acts as a control or accepts user input has a name that describes its purpose. For controls, the alternative text should communicate the action or result, not merely describe the graphic.

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.4 Link Purpose (In Context)
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.1.2 Text Alternatives for Images and Objects</summary>

**Reference:** EN 301 549 → 9.1.1.1 Non-text content (WCAG 2.1 → 1.1.1 Non-text Content)

**Description:** All meaningful non-text content shall have a text alternative that serves the equivalent purpose. This applies to informative images, graphics, diagrams, charts, icons, illustrations, screenshots, embedded objects, and other visual content that conveys information. The text alternative must provide the information or function that the non-text content communicates. The appropriate alternative depends on the purpose of the image in context. A simple image may need a short alternative text. A complex diagram or chart may require a short alternative plus a longer description in the surrounding text or on a linked page.

**Success Criterion:** Meaningful images and non-text objects have text alternatives that communicate the same purpose or essential information as the visual content. Complex visual information is described sufficiently so that users who cannot perceive the visual content can still understand the relevant information.

**Test Questions:**
*	Do all meaningful images, graphics, icons, and objects have suitable text alternatives?
*	Does the alternative text reflect the purpose of the image in its specific context?
*	Are complex graphics, charts, or diagrams explained in nearby text or through a longer description?
*	Are redundant or misleading text alternatives avoided?

**Further Information:** Alternative text should be concise but meaningful. It should not usually begin with phrases such as „image of“ or „graphic of“, unless the fact that it is an image is important for understanding. For complex content such as charts, process diagrams, infographics, or screenshots used for instruction, a short alt text alone is often not sufficient. The relevant information should also be available in the surrounding text, a data table, a transcript, or another accessible format. For ILIAS, this may be relevant in course pages, learning modules, test questions, media objects, glossaries, wikis, and uploaded teaching materials where images are used to convey learning content. A corresponding mechanism must also be implemented here to set alternative text appropriately.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.5 Images of text
*	EN 301 549 → 9.2.4.4 Link purpose in context
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.4.1.2 Name, role, value
*	EN 301 549 → 10.1.1.1 Non-text content
*	EN 301 549 → 11.1.1.1 Non-text content

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.1.1 requires text alternatives for non-text content so that the information can be presented in other forms, such as speech, braille, large print, symbols, or simpler language. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.5 Images of Text
*	WCAG 2.1 → 2.4.4 Link Purpose (In Context)
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#images-of-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.1.3 Empty Alt Attributes for Decorative Images</summary>

**Reference:** EN 301 549 → 9.1.1.1 Non-text content (WCAG 2.1 → 1.1.1 Non-text Content)

**Description:** Images or graphics that are purely decorative, used only for visual formatting, or do not convey information shall be implemented so that they can be ignored by assistive technologies. For HTML images, this is commonly done by using an empty alt attribute, for example alt="". Decorative images should not be announced by screen readers, because this can create unnecessary noise and make the page harder to understand. This requirement applies only to content that is genuinely decorative. If an image conveys information, supports understanding, functions as a link or button, or identifies content, it is not decorative and needs a meaningful text alternative.

**Success Criterion:** Purely decorative images are hidden from assistive technologies or provided with empty alternative text. Meaningful images are not incorrectly marked as decorative.

**Test Questions:**
*	Are purely decorative images ignored by screen readers?
*	Do decorative HTML images have empty alt attributes?
*	Are informative images, icons, and linked images not incorrectly treated as decorative?
*	Does removing the decorative image from perception leave the meaning of the page unchanged?

**Further Information:** Decorative images include visual spacers, borders, background flourishes, and images that repeat information already provided in nearby text. If the same information is already available in adjacent text, an empty alt attribute may be appropriate to avoid repetition. For ILIAS, this may be relevant in templates, skins, page layouts, decorative icons, and learning module designs. Care is needed where icons look decorative but actually communicate status, type, warning, progress, or action.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.5 Images of text
*	EN 301 549 → 9.4.1.2 Name, role, value
*	EN 301 549 → 10.1.1.1 Non-text content
*	EN 301 549 → 11.1.1.1 Non-text content

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.1.1 allows decorative non-text content to be implemented in a way that assistive technologies can ignore it. This prevents unnecessary or confusing announcements and helps users focus on meaningful content. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.1.4 Alternatives for CAPTCHAs</summary>

**Reference:** EN 301 549 → 9.1.1.1 Non-text content (WCAG 2.1 → 1.1.1 Non-text Content)

**Description:** If a CAPTCHA is used, text alternatives shall identify and describe the purpose of the CAPTCHA. In addition, alternative forms of CAPTCHA shall be provided for different types of sensory perception. CAPTCHAs can create barriers for users with visual, auditory, cognitive, motor, or language-related impairments. A visual CAPTCHA alone is not sufficient, because users who cannot see or interpret the image may be excluded. An audio CAPTCHA alone may also exclude users who are deaf or hard of hearing. Where possible, accessible alternatives to traditional CAPTCHA should be used, such as non-interactive bot detection, email verification, time-based checks, server-side spam protection, or other methods that do not require users to solve inaccessible tasks.

**Success Criterion:** CAPTCHAs are not the only way to complete an essential process. If a CAPTCHA is used, its purpose is described, and accessible alternatives are available for users with different sensory abilities.

**Test Questions:**
*	Is the purpose of the CAPTCHA described in text?
*	Are alternatives available for users who cannot see, hear, or solve the CAPTCHA?
*	Can the CAPTCHA or alternative verification process be completed with keyboard and assistive technologies?
*	Is there an accessible fallback or support option if the CAPTCHA cannot be completed?

**Further Information:** CAPTCHAs should be avoided where possible because they often create accessibility barriers. If they are necessary, they should not rely on only one sensory ability, such as vision or hearing. For ILIAS, this may be relevant in login, registration, contact, self-registration, password reset, or public form workflows. If CAPTCHA functionality is provided by a third-party service, the accessibility of that service must be included in the assessment.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.2.1 Timing adjustable
*	EN 301 549 → 9.3.3.1 Error identification
*	 EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.3 Error suggestion
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.1.1 treats CAPTCHAs as a special case. The text alternative must identify and describe the purpose of the CAPTCHA, and alternative forms must be provided for different modes of sensory perception. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.2.1 Timing Adjustable
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.3 Error Suggestion
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#timing-adjustable
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.1.2 Time-based Media</summary>
<details>
<summary>4.1.2.1 Audio-only and Video-only Prerecorded</summary>

**Reference:** EN 301 549 → 9.1.2.1 Audio-only and Video-only (Prerecorded) (WCAG 2.1 → 1.2.1 Audio-only and Video-only (Prerecorded))

**Description:** For prerecorded audio-only and prerecorded video-only content, an alternative shall be provided that presents equivalent information. For audio-only content, such as podcasts, audio recordings, interviews, or spoken instructions, a text transcript should provide the same information as the audio. For video-only content without audio, such as silent demonstrations, animations, or visual instructions, either an audio track or a text alternative shall describe the relevant visual information. This requirement ensures that users who cannot hear audio or cannot perceive visual content can still access the information conveyed by the media.

**Success Criterion:** Prerecorded audio-only and video-only content has an accessible alternative that provides equivalent information. Users can understand the essential content without relying solely on hearing or vision.

**Test Questions:**
*	Is a transcript available for prerecorded audio-only content?
*	Is a text or audio alternative available for prerecorded video-only content?
*	Does the alternative include all essential information conveyed by the original media?
*	Is the alternative easy to find near the media?

**Further Information:** This requirement applies only to prerecorded audio-only or video-only media. It does not apply to video with synchronized audio; those are covered by the following requirements, especially captions and audio description. Examples include audio interviews, recorded lectures without video, silent instructional videos, animations without sound, or visual-only demonstrations. For ILIAS, this may be relevant for uploaded audio files, podcasts, silent demonstration videos, media objects, learning modules, and course materials that include time-based media.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 10.1.2.1 Audio-only and Video-only (Prerecorded)
*	EN 301 549 → 11.1.2.1 Audio-only and Video-only (Prerecorded)

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.2.1 requires alternatives for prerecorded audio-only and video-only content. For audio-only content, a transcript is usually sufficient. For video-only content, the alternative must communicate the relevant visual information. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.1.1 Non-text Content
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-only-and-video-only-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
</details>

<details>
<summary>4.1.2.2 Captions Prerecorded</summary>

**Reference:** EN 301 549 → 9.1.2.2 Captions (Prerecorded) (WCAG 2.1 → 1.2.2 Captions (Prerecorded))

**Description:** Captions shall be provided for all prerecorded audio content in synchronized media, except where the media is itself a media alternative for text and is clearly labelled as such. This applies to prerecorded videos with sound, such as lecture recordings, instructional videos, interviews, tutorials, presentations, demonstrations, or other audiovisual learning materials. Captions should include spoken dialogue and relevant non-speech audio information, such as speaker identification, important sounds, music, or sound effects where they are necessary for understanding. Captions support users who are deaf or hard of hearing, users who cannot use audio in their current environment, users who are not fluent in the spoken language, and users who benefit from reading while listening.

**Success Criterion:** All prerecorded videos with synchronized audio provide captions that convey spoken content and relevant audio information. The captions are available, accurate enough to support understanding, and synchronized with the media.

**Test Questions:**
*	Are captions available for prerecorded videos with sound?
*	Do captions include relevant spoken content and important sound information?
*	Are captions synchronized with the audio?
*	Can users activate and read the captions in the player?

**Further Information:** This requirement concerns the video content itself. It is different from EN 301 549 clause 7.1.1, which checks whether the player can display available captions. Automatic captions may be helpful as a starting point, but they should be reviewed for accuracy, punctuation, speaker changes, technical terms, and learning-relevant terminology. For ILIAS, this requirement is relevant wherever prerecorded videos are provided as learning content, including media objects, course pages, learning modules, lecture recordings, and embedded third-party videos.

Related Requirements
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.1.3 Preservation of captioning
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.1 Audio-only and Video-only (Prerecorded)
*	EN 301 549 → 9.1.2.4 Captions (Live)
*	EN 301 549 → 10.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 11.1.2.2 Captions (Prerecorded)

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.2.2 requires captions for prerecorded audio content in synchronized media. Captions are not merely subtitles for speech; they should also include relevant audio information needed to understand the content. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.2.1 Audio-only and Video-only (Prerecorded)
*	WCAG 2.1 → 1.2.4 Captions (Live)
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-only-and-video-only-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.2.3 Audio Description or Media Alternative Prerecorded</summary>

**Reference:** EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded) (WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded))

**Description:** For prerecorded video content with synchronized audio, either audio description or a full media alternative shall be provided, unless the media is itself a media alternative for text and is clearly labelled as such.
Audio description provides spoken information about relevant visual content that is not available through the original audio. A full media alternative is a text-based alternative that includes both the spoken content and the relevant visual information needed to understand the video. This requirement ensures that users who cannot see or fully perceive the visual content can access information that is conveyed only visually.

**Success Criterion:** Prerecorded videos with synchronized audio provide either audio description or a full media alternative that communicates all essential visual and auditory information necessary to understand the content.

**Test Questions:**
*	Is audio description or a full text alternative available for prerecorded videos?
*	Does the alternative include essential visual information not available in the audio?
*	Is the alternative easy to find near the video?
*	Is it clear whether the video itself is already a media alternative for text?

**Further Information:** This requirement is fulfilled if either audio description or a full media alternative is provided. A transcript that only contains spoken dialogue is not sufficient if important visual information is missing. Examples of relevant visual information include on-screen text, diagrams, demonstrations, gestures, visual instructions, scene changes, or actions that are necessary for understanding the content. For ILIAS, this may be relevant for lecture recordings, screen recordings, tutorial videos, laboratory demonstrations, explanatory videos, and other learning materials where visual information is central.

A brief technical note: EN 301 549 9.1.2.3 and 9.1.2.5 overlap, but are not identical. EN 301 549 9.1.2.3 still permits „audio description or media/full-text alternatives”, whereas EN 301 549 9.1.2.5 explicitly requires audio description at the AA level, provided that relevant visual information is not already included in the normal audio.

Related Requirements:
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 7.2.2 Audio description synchronization
*	EN 301 549 → 7.2.3 Preservation of audio description
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.1 Audio-only and Video-only (Prerecorded)
*	EN 301 549 → 9.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 10.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 11.1.2.3 Audio Description or Media Alternative (Prerecorded)

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.2.3 requires either audio description or a media alternative for prerecorded synchronized media. This is a Level A requirement. It allows a full media alternative instead of audio description. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.2.1 Audio-only and Video-only (Prerecorded)
*	WCAG 2.1 → 1.2.5 Audio Description (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
</details>

<details>
<summary>4.1.2.4 Captions Live</summary>

**Reference:** EN 301 549 → 9.1.2.4 Captions (Live) (WCAG 2.1 → 1.2.4 Captions (Live))

**Description:** Captions shall be provided for all live audio content in synchronized media. This applies to live video streams, webinars, virtual classrooms, live lectures, conferences, meetings, or other real-time audiovisual events where audio is presented together with video. Live captions should communicate spoken content and relevant audio information in real time. This requirement supports users who are deaf or hard of hearing, users who cannot listen to audio, and users who need text support to follow live communication.

**Success Criterion:** Live video with synchronized audio provides live captions. The captions are available during the live event, sufficiently timely, and suitable for following the essential spoken content and relevant audio information.

**Test Questions:**
*	Are live captions available for live video with audio?
*	Are the captions timely enough to follow the live content?
*	Do captions include the essential spoken content?
*	Can users access the captions without disrupting participation?

**Further Information:** Live captions may be provided by professional captioners, speech-to-text interpreters, automated captioning systems, or integrated platform functions. The chosen method should be appropriate for the importance, complexity, and context of the event. Automatic live captions should be checked carefully, especially for names, technical terms, specialist vocabulary, multilingual content, and poor audio quality. For ILIAS, this requirement may be relevant when live teaching scenarios, virtual classrooms, webinars, or integrated conferencing tools are used. If the live video is provided by a third-party service, the captioning functionality of that service must be included in the assessment.

Related Requirements:
*	EN 301 549 → 6.2.1.1 Real-time text communication
*	EN 301 549 → 6.2.1.2 Concurrent voice and text
*	EN 301 549 → 7.1.1 Captioning playback
*	EN 301 549 → 7.1.2 Captioning synchronization
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.2 Captions (Prerecorded)
*	EN 301 549 → 10.1.2.4 Captions (Live)
*	EN 301 549 → 11.1.2.4 Captions (Live)

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.2.4 requires captions for live audio content in synchronized media. Unlike prerecorded captions, live captions are produced in real time and may have some delay, but they must still allow users to follow the essential content. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.2.2 Captions (Prerecorded)
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-live
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>4.1.2.5 Audio Description Prerecorded</summary>

**Reference:** EN 301 549 → 9.1.2.5 Audio Description (Prerecorded) (WCAG 2.1 → 1.2.5 Audio Description (Prerecorded))

**Description:** Audio description shall be provided for all prerecorded video content in synchronized media where visual information is necessary to understand the content and is not already available through the audio. Audio description describes relevant visual information, such as actions, gestures, scene changes, diagrams, demonstrations, visual instructions, or on-screen text. It is usually provided as an additional audio track or integrated into pauses in the original audio. This requirement ensures that users who cannot see or fully perceive the video can access essential visual information.

**Success Criterion:** Prerecorded videos with synchronized audio provide audio description for relevant visual information that is not conveyed by the original audio. The audio description is available, understandable, and synchronized with the video.

**Test Questions:**
*	Is audio description available for prerecorded videos where visual information is essential?
*	Does the audio description cover important visual content not available in the original audio?
*	Can users select and play the audio description?
*	Is the audio description synchronized with the video?

**Further Information:** This requirement is more specific than EN 301 549  9.1.2.3. While EN 301 549  9.1.2.3 allows either audio description or a full media alternative, EN 301 549  9.1.2.5 specifically requires audio description for prerecorded synchronized media. Audio description may not be necessary if all relevant visual information is already described in the main audio track. For example, if a speaker fully explains what is shown on slides or in a demonstration, additional audio description may not be required. For ILIAS, this requirement may be relevant for instructional videos, screen recordings, demonstrations, recorded lectures with visual material, and embedded videos from external platforms.

Related Requirements:
*	EN 301 549 → 7.2.1 Audio description playback
*	EN 301 549 → 7.2.2 Audio description synchronization
*	EN 301 549 → 7.2.3 Preservation of audio description
*	EN 301 549 → 7.3 User controls for captions and audio description
*	EN 301 549 → 9.1.2.3 Audio Description or Media Alternative (Prerecorded)
*	EN 301 549 → 10.1.2.5 Audio Description (Prerecorded)
*	EN 301 549 → 11.1.2.5 Audio Description (Prerecorded)

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.2.5 requires audio description for prerecorded video content in synchronized media. This is a Level AA requirement. Unlike WCAG 1.2.3, this criterion cannot generally be met by providing only a full text alternative; audio description is required where visual information is needed for understanding. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative (Prerecorded)
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.1.3 Adaptable</summary>

<details>
<summary>4.1.3.1 Info and relationships </summary>

<details>
<summary>4.1.3.1.1 HTML Structural Elements for Headings</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Headings shall be marked up programmatically using appropriate heading elements or equivalent semantic structures. Headings visually divide content into sections and communicate the structure of a page. This structure must also be available to assistive technologies so that users can understand the organization of the content and navigate efficiently. Headings should not be created only by visual formatting such as bold text, larger font size, or spacing. Conversely, heading markup should not be used only for visual styling when the text is not actually a heading.

**Success Criterion:** Visible headings are programmatically identifiable as headings, and the heading structure reflects the logical structure of the content.

**Test Questions:**
*	Are visible headings marked up as headings?
*	Does the heading hierarchy reflect the content structure?
*	Are headings not used only for visual styling?
*	Can screen reader users navigate the content by headings?

**Further Information:** A correct heading structure helps users scan pages, understand content sections, and navigate directly to relevant information.

Related Requirements:
*	EN 301 549 → 9.2.4.1 Bypass blocks
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.2.4.10 Section headings
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires that structure and relationships conveyed through presentation can be programmatically determined or are available in text. Headings are a central example of such structure. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.4.1 Bypass Blocks
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 2.4.10 Section Headings
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#section-headings
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.2 HTML Structural Elements for Lists</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Lists shall be marked up programmatically using appropriate list structures when items form a list.
This applies to unordered lists, ordered lists, navigation lists, step-by-step instructions, collections of related items, and other content where the list relationship is meaningful. Lists should not be created only through visual formatting, such as line breaks, hyphens, asterisks, indentation, or manually typed numbers. The list relationship must be available to assistive technologies.

**Success Criterion:** Content that visually or logically forms a list is programmatically marked up as a list. The list type and item relationships are available to assistive technologies.

**Test Questions:**
*	Are visual lists marked up as lists?
*	Are ordered lists used where sequence or numbering is meaningful?
*	Are list items programmatically associated with their list?
*	Are lists not simulated only with line breaks or symbols?

**Further Information:** Related Requirements:
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires relationships conveyed visually to be programmatically determinable. List markup communicates the relationship between a group of items and the list as a whole. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.3 HTML Structural Elements for Quotations</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Quotations shall be marked up programmatically where the quoted nature of the content is meaningful. This applies to block quotations, cited passages, excerpts from sources, quoted statements, or other content where it is important to distinguish between the author’s own text and quoted material. Quotations should not be indicated only by visual formatting such as indentation, italics, quotation marks, or different font styles if the quotation relationship is important for understanding.

**Success Criterion:** Quoted content is programmatically identifiable as a quotation or is otherwise clearly identified in text. Users can distinguish quoted material from surrounding content.

**Test Questions:**
*	Are longer quotations marked up with appropriate quotation structures?
*	Is the quoted nature of the content clear to assistive technologies or available in text?
*	Are sources or attributions provided where necessary?
*	Is quotation markup not used only for visual indentation?

**Further Information:** Related Requirements
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.1.2 Language of parts

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires that relationships and structural information conveyed by presentation are also available programmatically or in text. Quotations are structural relationships when they distinguish cited or external content from the surrounding text. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.1.2 Language of Parts

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-parts
</details>

<details>
<summary>4.1.3.1.4 Content Is Structured</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Content shall be structured so that information, relationships, and grouping conveyed visually are also programmatically determinable or available in text. This includes headings, paragraphs, sections, groups of related content, form groups, navigation areas, regions, labels, instructions, and other structural relationships that help users understand the page. Structure should not rely only on visual presentation such as spacing, position, colour, font size, or indentation.

**Success Criterion:** The structure of the content is programmatically available or clearly provided in text. Users of assistive technologies can understand the same organization and relationships that are visible on the page.

**Test Questions:**
*	Are sections, groups, and relationships programmatically identifiable or explained in text?
*	Is the content understandable when CSS or visual styling is removed?
*	Are visual groupings supported by semantic markup or text?
*	Is the structure consistent and meaningful?

**Further Information:** This requirement is broad and applies wherever visual formatting communicates meaning. It is especially important for complex pages, dashboards, forms, learning content, settings pages, and administrative interfaces. For ILIAS, this is relevant across course pages, repository views, forms, learning modules, test settings, object properties, and administrative pages.

Related Requirements:
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.2.4.1 Bypass blocks
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 ensures that information and relationships are not lost when the visual presentation changes, for example when content is read by a screen reader or displayed with user-defined styles. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 2.4.1 Bypass Blocks
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.5 Data Tables Are Correctly Structured</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Data tables shall be structured so that rows, columns, headers, and data relationships are programmatically determinable. This applies to tables that present data, comparisons, schedules, grades, statistics, results, lists of users, or other information where the table structure is necessary for understanding. Tables should include appropriate table markup. Header cells should be marked as headers, and captions or summaries should be provided where they help users understand the purpose or structure of the table.

**Success Criterion:** Data tables are implemented using appropriate table structures. Users of assistive technologies can identify table headers, data cells, and the relationship between them.

**Test Questions:**
*	Are data tables implemented as data tables, not as plain text or layout blocks?
*	Are column and row headers marked up correctly?
*	Is the table purpose clear from a caption, heading, or surrounding text?
*	Can the table be understood when read by a screen reader?

**Further Information:** Simple data tables usually need correctly marked header cells. Complex tables may require additional associations between header and data cells. For ILIAS, this is relevant for overview tables, participant lists, test results, grade tables, booking tables, learning progress tables, repository lists, and administrative tables.

Related Requirements:
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires relationships conveyed by table layout to be programmatically determinable. In data tables, the relationship between headers and data cells is essential. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.6 Association of Table Cells</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** In data tables, data cells shall be programmatically associated with their relevant header cells.
This is necessary so that users of screen readers can understand what each data cell refers to when navigating the table. The association may involve column headers, row headers, or more complex header relationships. The more complex the table, the more important it is to ensure that the correct header associations are available programmatically.

**Success Criterion:** Each data cell in a table can be correctly interpreted in relation to its row and/or column headers. Header associations are programmatically determinable.

**Test Questions:**
*	Are data cells associated with the correct header cells?
*	Are row headers and column headers identified where needed?
*	Are complex header relationships implemented correctly?
*	Is the table still understandable when navigated cell by cell with a screen reader?

**Further Information:** For simple tables, correct use of table header cells may be sufficient. Complex tables with multiple header levels may need additional markup or should be simplified where possible.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires that table relationships conveyed visually are also available programmatically. This includes the relationship between data cells and their corresponding headers. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.7 No Structural Markup for Layout Tables</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Tables used only for visual layout shall not use structural table markup that communicates data relationships. If tables are used for layout, they must not falsely identify headers, captions, or data relationships that do not actually exist. Otherwise, assistive technologies may announce misleading table structures. Where possible, layout should be implemented with CSS rather than layout tables.

**Success Criterion:** Layout tables do not expose misleading data table structures to assistive technologies. They do not use table headers or associations unless they represent real data relationships.

**Test Questions:**
*	Are layout tables avoided where possible?
*	If layout tables are used, are they not announced as meaningful data tables?
*	Are table headers not used for purely visual layout?
*	Does the reading order remain meaningful?

**Further Information:** The main risk with layout tables is that users of assistive technologies may perceive a visual layout as a data table and try to interpret non-existing row and column relationships.

Related Requirements:
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires meaningful structure to be programmatically determinable. It also implies that non-meaningful visual layout should not be exposed as meaningful structure. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.1.8 Labels of Form Elements Are Programmatically Determinable</summary>

**Reference:** EN 301 549 → 9.1.3.1 Info and relationships (WCAG 2.1 → 1.3.1 Info and Relationships)

**Description:** Form elements shall have labels or instructions that are programmatically associated with the corresponding input fields, controls, or selection elements. This applies to text fields, checkboxes, radio buttons, select fields, search fields, upload fields, date fields, switches, sliders, and other form controls. Users of assistive technologies must be able to identify what information is expected or what the control does. Visual proximity alone is not sufficient. A label that appears next to a field must also be programmatically connected to that field.

**Success Criterion:** Each form control has a programmatically determinable label or accessible name that identifies its purpose. The relationship between the label and the control is available to assistive technologies.

**Test Questions:**
*	Does every form field have an accessible label or name?
*	Is the visible label programmatically associated with the correct field?
*	Are groups of radio buttons or checkboxes labelled as groups where necessary?
*	Are required fields and instructions available to assistive technologies?

**Further Information:** This requirement is closely related to form usability and error prevention. Labels should be clear, persistent, and not rely only on placeholder text.

Related Requirements:
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.3 Error suggestion
*	EN 301 549 → 9.4.1.2 Name, role, value
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.1 requires form relationships conveyed visually to be programmatically determinable. This includes the relationship between labels, instructions, form groups, and input fields. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.3 Error Suggestion
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>
</details>

<details>
<summary>4.1.3.2 Meaningful Sequence</summary>

**Reference:** EN 301 549 → 9.1.3.2 Meaningful sequence (WCAG 2.1 → 1.3.2 Meaningful Sequence)

**Description:** When the sequence in which content is presented affects its meaning, a correct reading sequence shall be programmatically determinable. This applies to content where the order of headings, text, images, form fields, instructions, table content, messages, or controls is necessary for understanding or use. Users who rely on screen readers, keyboard navigation, simplified layouts, or custom styles must receive the content in a meaningful order. The visual order and the programmatic reading order do not need to be identical in every case, but the programmatic order must preserve the meaning.

**Success Criterion:** Content is presented in a meaningful programmatic sequence when order affects understanding or operation. Users can understand and use the content when it is read or navigated in the programmatic order.

**Test Questions:**
*	Is the reading order meaningful when using a screen reader or keyboard?
*	Are instructions presented before the controls or content they refer to?
*	Does the order remain meaningful when CSS or visual layout changes?
*	Are columns, cards, dialogs, and dynamic content read in a logical sequence?

**Further Information:** This requirement is especially important for multi-column layouts, card layouts, forms, modal dialogs, drag-and-drop interfaces, dashboards, and responsive designs.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.2 requires that a correct reading sequence can be programmatically determined where sequence affects meaning. Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.3 Sensory Characteristics</summary>

**Reference:** EN 301 549 → 9.1.3.3 Sensory characteristics (WCAG 2.1 → 1.3.3 Sensory Characteristics)

**Description:** Instructions for understanding and operating content shall not rely solely on sensory characteristics such as shape, colour, size, visual location, orientation, or sound. Users must be able to understand instructions even if they cannot perceive the referenced sensory characteristic. For example, instructions such as “click the green button”, “use the menu on the right”, or “select the round icon” are insufficient if no additional text or programmatic information identifies the target. Sensory characteristics may be used, but they must not be the only way to identify content or controls.

**Success Criterion:** Instructions and references are understandable without relying only on sensory characteristics. Users can identify the relevant content or control through text, labels, accessible names, or other non-sensory information.

**Test Questions:**
*	Do instructions avoid relying only on colour, shape, position, size, sound, or orientation?
*	Are controls and content identified by clear names or labels?
*	Is sensory information supplemented by text or programmatic information?
*	Are instructions still understandable for users who cannot see or hear the referenced feature?

**Further Information:** Acceptable instructions may combine sensory and non-sensory information, for example “Select the green Submit button” if the button is also labelled “Submit”.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.1 Use of colour
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.3 requires that instructions do not depend solely on sensory characteristics such as shape, colour, size, visual location, orientation, or sound. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.1.1 Non-text Content
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#sensory-characteristics
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
</details>

<details>
<summary>4.1.3.4 Orientation</summary>

**Reference:** EN 301 549 → 9.1.3.4 Orientation (WCAG 2.1 → 1.3.4 Orientation)

**Description:** Content shall not restrict its view and operation to a single display orientation, such as portrait or landscape, unless a specific display orientation is essential. Users must be able to access and operate content in both portrait and landscape orientation where the device supports orientation changes. This is important for users who have their device mounted in a fixed orientation, use assistive technology, or cannot easily rotate the device. An orientation restriction is only acceptable when the specific orientation is essential for the function, for example for certain measurement, camera, or specialized application scenarios.

**Success Criterion:** Content and functionality are available and usable in both portrait and landscape orientation unless a specific orientation is essential.

**Test Questions:**
*	Can the content be used in both portrait and landscape orientation?
*	Is functionality not lost when orientation changes?
*	Is any orientation restriction technically or functionally essential?
*	Are messages about orientation restrictions accessible?

**Further Information:** This requirement is particularly relevant for mobile and tablet use. Responsive design should adapt layout without forcing users to rotate the device.

Related Requirements:
*	EN 301 549 → 9.1.4.10 Reflow
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.3.4 requires that content does not restrict view and operation to a single display orientation unless that orientation is essential. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.10 Reflow
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#orientation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.3.5 Identify Input Purpose</summary>

Reference
EN 301 549 → 9.1.3.5 Identify input purpose (WCAG 2.1 → 1.3.5 Identify Input Purpose)

**Description:** The purpose of input fields that collect information about the user shall be programmatically determinable when the input purpose matches one of the input purposes defined by WCAG and the field is implemented using technologies that support this. This applies to common personal data fields such as name, email address, username, password, organization, address, telephone number, birth date, and similar user-related information. The requirement supports users by enabling autocomplete, personalization, assistive technologies, and simplified input. It is especially helpful for users with cognitive disabilities, motor impairments, or users who rely on assistive input tools.

**Success Criterion:** Input fields that collect recognized user information expose their purpose programmatically, for example through appropriate autocomplete attributes or equivalent mechanisms.

**Test Questions:**
*	Do fields collecting personal user data expose their input purpose programmatically?
*	Are appropriate autocomplete values used where applicable?
*	Are labels and input purposes consistent with each other?
*	Does autocomplete or assistive input support work without creating confusion?

**Further Information:** This requirement does not apply to every input field. It applies where the input field collects information about the user and the purpose is included in the defined set of input purposes supported by the technology.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG: WCAG 2.1 Success Criterion 1.3.5 requires that the purpose of input fields collecting information about the user can be programmatically determined where the input purpose is known and supported by the technology. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#identify-input-purpose
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.1.4 Distinguishable</summary>

<details>
<summary>4.1.4.1 Use of Colour</summary>

**Reference:** EN 301 549 → 9.1.4.1 Use of colour (WCAG 2.1 → 1.4.1 Use of Color)

**Description:** Colour shall not be used as the only visual means of conveying information, indicating an action, prompting a response, or distinguishing a visual element. Users who cannot perceive colour differences, including users with colour vision deficiencies, limited vision, or monochrome displays, must still be able to understand and operate the content. Colour may be used, but it must be supplemented by additional cues such as text, icons, patterns, labels, shapes, underlining, or programmatically determinable information.

**Success Criterion:** All information and functionality conveyed by colour is also available through another visual or programmatic means.

**Test Questions**
*	Is information still understandable without colour perception?
*	Are required fields, errors, status indicators, links, and selections identified by more than colour alone?
*	Are charts, diagrams, and legends usable without relying only on colour?
*	Are additional cues available visually or programmatically?

**Further Information:** Examples of failures include marking errors only in red, showing active states only by colour, or using colour-only chart legends. For ILIAS, this may be relevant in forms, test feedback, learning progress indicators, status icons, repository object states, dashboards, calendars, and charts.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.3 Contrast (Minimum)
*	EN 301 549 → 9.1.4.11 Non-text contrast
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.1 requires that colour is not the only means of conveying information, indicating an action, prompting a response, or distinguishing a visual element. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.1.1 Non-text Content
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.3 Contrast (Minimum)
*	WCAG 2.1 → 1.4.11 Non-text Contrast
*	WCAG 2.1 → 3.3.1 Error Identification

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-contrast
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
</details>

<details>
<summary>4.1.4.2 Audio Control</summary>

**Reference:** EN 301 549 → 9.1.4.2 Audio control (WCAG 2.1 → 1.4.2 Audio Control)

**Description:** If audio plays automatically on a web page for more than three seconds, a mechanism shall be available to pause or stop the audio, or to control the audio volume independently from the overall system volume.
Automatically playing audio can interfere with screen reader output, concentration, speech recognition, and general usability. This requirement applies to audio that starts automatically when a page loads, when an element receives focus, or when a user enters a part of the page without intentionally starting the audio.

**Success Criterion:** Automatically playing audio lasting more than three seconds can be paused, stopped, or controlled independently by the user.

**Test Questions:**
*	Does any audio start automatically?
*	If it lasts more than three seconds, can users pause, stop, or reduce it?
*	Is the audio control easy to find and operate with keyboard and assistive technologies?
*	Does the audio interfere with screen reader output?

**Further Information:** The safest approach is to avoid automatically playing audio unless it is essential.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.2.2 Pause, stop, hide
*	EN 301 549 → 9.4.1.2 Name, role, value
*	EN 301 549 → 7.3 User controls for captions and audio description

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.2 requires a mechanism to pause, stop, or independently control automatically playing audio that lasts more than three seconds. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.2.2 Pause, Stop, Hide
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-control
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pause-stop-hide
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.4.3 Contrast Minimum</summary>

**Reference:** EN 301 549 → 9.1.4.3 Contrast (Minimum) (WCAG 2.1 → 1.4.3 Contrast (Minimum))

**Description:** Text and images of text shall have sufficient contrast against their background. For normal text, the contrast ratio shall be at least 4.5:1. For large-scale text, the contrast ratio shall be at least 3:1. Exceptions apply to incidental text, inactive user interface components, logos, and purely decorative text. Sufficient contrast supports users with low vision, colour vision deficiencies, ageing-related vision changes, and users working in difficult lighting conditions.

**Success Criterion:** Text and images of text meet the minimum contrast requirements against their background, except where a defined exception applies.

**Test Questions:**
*	Does normal text meet a contrast ratio of at least 4.5:1?
*	Does large text meet a contrast ratio of at least 3:1?
*	Are hover, focus, selected, disabled, and error states checked?
*	Are text images and text over images sufficiently contrasted?

**Further Information:** Contrast should be tested for all relevant states and themes, including default, hover, focus, active, selected, disabled, and error states.

Related Requirements:
*	EN 301 549 → 9.1.4.1 Use of colour
*	EN 301 549 → 9.1.4.5 Images of text
*	EN 301 549 → 9.1.4.11 Non-text contrast
*	EN 301 549 → 9.2.4.7 Focus visible

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.3 defines minimum contrast requirements for text and images of text. The usual thresholds are 4.5:1 for normal text and 3:1 for large-scale text. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 1.4.5 Images of Text
*	WCAG 2.1 → 1.4.11 Non-text Contrast
*	WCAG 2.1 → 2.4.7 Focus Visible

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#images-of-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-contrast
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
</details>

<details>
<summary>4.1.4.4 Resize Text</summary>

**Reference:** EN 301 549 → 9.1.4.4 Resize text (WCAG 2.1 → 1.4.4 Resize Text)

**Description:** Text shall be resizable up to 200 percent without loss of content or functionality, except for captions and images of text. Users must be able to enlarge text in the browser or user agent without content being clipped, overlapping, hidden, or becoming unusable. This requirement supports users with low vision, users with reading difficulties, and users who need larger text for comfortable reading.

**Success Criterion:** Text can be resized up to 200 percent without loss of information, content, or functionality.

**Test Questions:**
*	Can text be enlarged up to 200 percent?
*	Does content remain readable without clipping or overlap?
*	Are controls, menus, dialogs, and forms still usable?
*	Is horizontal scrolling avoided where it is not expected?

**Further Information:** Testing should include common pages, forms, navigation menus, modal dialogs, tables, and responsive layouts.

Related Requirements:
*	EN 301 549 → 9.1.4.10 Reflow
*	EN 301 549 → 9.1.4.12 Text spacing
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.4 requires text to be resizable up to 200 percent without loss of content or functionality, except for captions and images of text. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.10 Reflow
*	WCAG 2.1 → 1.4.12 Text Spacing
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#text-spacing
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.4.5 Images of Text</summary>

**Reference:** EN 301 549 → 9.1.4.5 Images of text (WCAG 2.1 → 1.4.5 Images of Text)

**Description:** Images of text shall not be used to convey information if the same visual presentation can be achieved using real text. Real text can be resized, reflowed, styled, searched, translated, copied, and read by assistive technologies. Images of text often become unreadable when enlarged and may not adapt to user preferences. Exceptions apply where the particular presentation of the text is essential, such as logos, brand names, or cases where the text image is necessary for the information being conveyed.

**Success Criterion:** Text is provided as real text rather than as an image of text, unless a defined exception applies.

**Test Questions:**
*	Is visible text implemented as real text wherever possible?
*	Are images of text avoided in buttons, banners, instructions, and navigation?
*	If images of text are used, is the visual presentation essential?
*	Is equivalent text available where needed?

**Further Information:** Graphic fonts used in logos or within photos are generally not considered negative. The use of the SVG `text` element in inline SVGs is also not considered negative. For graphic fonts that also convey the informational content as text within the context, this text can be considered a conforming alternative version of the graphic font. This is the case, for example, when images of brochures, posters, or similar documents that contain text within the image are used as teaser images, and the title of the brochure is also visible as text immediately below, above, or next to the image.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.4.3 Contrast (Minimum)
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.1.4.10 Reflow

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.5 requires that images of text are only used where the visual presentation is essential or where the image can be visually customized to the user’s requirements.Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.1.1 Non-text Content
*	WCAG 2.1 → 1.4.3 Contrast (Minimum)
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 1.4.10 Reflow

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#images-of-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
</details>

<details>
<summary>4.1.4.6 Reflow </summary>

**Reference:** EN 301 549 → 9.1.4.10 Reflow (WCAG 2.1 → 1.4.10 Reflow)

**Description:** Content shall be presented without loss of information or functionality and without requiring scrolling in two dimensions at a viewport width equivalent to 320 CSS pixels, or a viewport height equivalent to 256 CSS pixels, except for parts of content where two-dimensional layout is essential. This requirement ensures that users can enlarge content or use small screens without needing to scroll both vertically and horizontally. Two-dimensional scrolling may be acceptable for content such as large data tables, maps, diagrams, complex images, or interfaces where the spatial relationship is essential.

**Success Criterion:** Content reflows into a single direction of scrolling without loss of information or functionality at the required viewport size, except where two-dimensional layout is essential.

**Test Questions:**
*	Does content reflow at 320 CSS pixels width without loss of content or function?
*	Is horizontal scrolling avoided except where essential?
*	Are menus, forms, dialogs, tables, and controls still usable?
*	Is the reading and focus order still meaningful after reflow?

**Further Information:** Testing should include responsive views, browser zoom, mobile viewports, modal dialogs, navigation menus, tables, and embedded content.

Related Requirements:
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.1.3.4 Orientation
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.1.4.12 Text spacing
*	 EN 301 549 → 9.2.4.3 Focus order

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.10 requires content to reflow without loss of information or functionality and without two-dimensional scrolling at the specified viewport sizes, except where two-dimensional layout is essential. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 1.3.4 Orientation
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 1.4.12 Text Spacing
*	WCAG 2.1 → 2.4.3 Focus Order

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#orientation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#text-spacing
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
</details>

<details>
<summary>4.1.4.7 Non-text Contrast</summary>

**Reference:** EN 301 549 → 9.1.4.11 Non-text contrast (WCAG 2.1 → 1.4.11 Non-text Contrast)

**Description:** Visual information required to identify user interface components, states, and meaningful graphical objects shall have a contrast ratio of at least 3:1 against adjacent colours. This applies to graphical controls, icons, focus indicators, input boundaries, selected states, checkboxes, radio buttons, switches, charts, diagrams, and other non-text visual information needed to understand or operate the content. Exceptions apply where the visual presentation is determined by the user agent, where the component is inactive, or where the graphical information is not required for understanding or operation.

**Success Criterion:** Essential non-text visual information and graphical user interface components have sufficient contrast of at least 3:1 against adjacent colours.

**Test Questions:**
*	Do icons, controls, input borders, and focus indicators meet 3:1 contrast?
*	Are selected, checked, error, hover, and focus states sufficiently visible?
*	Are charts, diagrams, and graphical information distinguishable?
*	Are inactive or decorative elements correctly treated as exceptions?

**Further Information:** Non-text contrast is especially important where icons or borders are the only visible indicators of controls or states.

Related Requirements:
*	EN 301 549 → 9.1.4.1 Use of colour
*	EN 301 549 → 9.1.4.3 Contrast (Minimum)
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.11 requires sufficient contrast for visual information needed to identify user interface components and graphical objects. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 1.4.3 Contrast (Minimum)
*	WCAG 2.1 → 2.4.7 Focus Visible
*	 WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-contrast
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.4.8 Text Spacing</summary>

Reference
EN 301 549 → 9.1.4.12 Text spacing (WCAG 2.1 → 1.4.12 Text Spacing)

**Description: Content implemented using markup languages shall not lose content or functionality when users override text spacing settings within defined limits. The relevant text spacing settings are: line height of at least 1.5 times the font size, spacing after paragraphs of at least 2 times the font size, letter spacing of at least 0.12 times the font size, and word spacing of at least 0.16 times the font size. This supports users with low vision, dyslexia, cognitive disabilities, and other reading-related needs who adjust text spacing for readability.

**Success Criterion:** When users apply the defined text spacing settings, no content or functionality is lost, and text remains readable and usable.

**Test Questions:**
*	Does content remain readable when text spacing is increased?
*	Are text, buttons, menus, dialogs, and form fields not clipped or overlapped?
*	Is functionality still available after spacing adjustments?
*	Are responsive and embedded views also tested?

**Further Information:** The requirement does not force authors to use these spacing values by default. It requires that users can apply them without breaking content.

Related Requirements:
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.1.4.10 Reflow
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.12 requires that no loss of content or functionality occurs when users override text spacing according to the defined values. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 1.4.10 Reflow
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#text-spacing
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.1.4.9 Content on Hover or Focus</summary>

**Reference:** EN 301 549 → 9.1.4.13 Content on hover or focus (WCAG 2.1 → 1.4.13 Content on Hover or Focus)

**Description:** When additional content appears on hover or keyboard focus and then disappears when hover or focus is removed, the content shall be dismissible, hoverable, and persistent, unless an exception applies. This applies to tooltips, popovers, help bubbles, dropdown previews, validation hints, expanded labels, or other content that appears temporarily. The requirement helps users who magnify content, use a keyboard, rely on screen readers, or need more time to read and interact with additional information.

**Success Criterion:** Additional content triggered by hover or focus can be dismissed without moving pointer or focus, can be hovered without disappearing, and remains visible until the trigger is removed, the user dismisses it, or it is no longer valid.

**Test Questions:**
*	Can the additional content be dismissed, for example with Escape?
*	Does the content remain visible when the pointer moves over it?
*	Does the content stay visible long enough to read and use?
*	Is the content accessible with keyboard and assistive technologies?

**Further Information:** This requirement does not apply to browser-native tooltips or situations where the visual presentation is controlled by the user agent and cannot be modified by the author. This requirement does not apply to displayed content whose behavior is determined by the user agent (such as native title attributes).

Related Requirements:
*	EN 301 549 → 9.1.4.3 Contrast (Minimum)
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 1.4.13 applies to additional content that appears and disappears in response to pointer hover or keyboard focus. It requires the content to be dismissible, hoverable, and persistent, except where specific exceptions apply. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#content-on-hover-or-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>
</details>

<details>
<summary>4.2 Operable</summary>

<details>
<summary>4.2.1 Keyboard Accessible</summary>

<details>
<summary>4.2.1.1 Keyboarde</summary>

**Reference:** EN 301 549 → 9.2.1.1 Keyboard (WCAG 2.1 → 2.1.1 Keyboard)

**Description:** All functionality of the content shall be operable through a keyboard interface without requiring specific timing for individual keystrokes. This includes links, buttons, menus, dialogs, forms, media controls, custom widgets, and embedded components. The requirement supports users who cannot use a mouse, including blind users, users with motor impairments, and users of alternative input devices.

**Success Criterion:** All functionality can be reached, operated, and completed using a keyboard or keyboard interface, without requiring a mouse, touch gesture, or other pointing device.

**Test Questions:**
* Can all interactive elements be reached and operated using the keyboard?
* Can links, buttons, menus, dialogs, forms, and embedded components be used without a mouse?
* Are there any functions that require hover, drag-and-drop, or complex pointer gestures?
* Are keyboard interactions predictable and consistent?
* Can the functionality also be used with assistive technologies?
* Can course objects, tabs, accordions, action menus and settings menus be operated without a mouse?
* Can files be uploaded, edited, moved, submitted or deleted using only the keyboard?
* Are test questions and exercise submissions fully operable by keyboard?
* Are embedded tools, plugins, H5P or SCORM elements also keyboard accessible?
* Are TinyMCE/editor functions usable without a mouse?

**Further Information:** This requirement does not mean that every function must be operated with the Tab key alone. Other standard keyboard interactions, such as arrow keys, Enter, Space, or Escape, may be appropriate depending on the component.

Related Requirements:
*	EN 301 549 → 9.2.1.2 No keyboard trap
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.1.1 requires that all functionality is available from a keyboard. Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.2 No Keyboard Trap
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#no-keyboard-trap
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.1.2 No Keyboard Trap</summary>

**Reference:** EN 301 549 → 9.2.1.2 No keyboard trap (WCAG 2.1 → 2.1.2 No Keyboard Trap)

**Description:** If keyboard focus can be moved to a component, users must also be able to move focus away from that component using the keyboard. This applies to modal dialogs, menus, embedded content, media players, calendar widgets, chat windows, and other complex components. The requirement prevents keyboard users from becoming trapped in a part of the interface.

**Success Criterion:** Keyboard focus can be moved away from every focusable component using the keyboard. If more than standard navigation keys are required, the method for moving focus away is clearly provided.

**Test Questions:**
*	Can every focusable area be exited using the keyboard?
*	Do Tab and Shift+Tab work as expected?
*	Can modal dialogs, menus, and embedded content be closed or exited with the keyboard?
*	Is a non-standard exit method clearly explained and accessible?
*	Is focus returned to a meaningful position after leaving a component?
*	Can users enter and leave embedded objects, media players, SCORM packages and plugins by keyboard?
*	Can modal dialogs be closed with keyboard controls?
*	Is focus returned to a meaningful position after closing overlays or dialogs?
*	Are there keyboard traps in test questions, file upload dialogs or rich-text editors?

**Further Information:** A temporary focus restriction, for example inside a modal dialog, may be acceptable if users can clearly and reliably exit the dialog and focus is managed appropriately.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.1.2 requires that keyboard users are not trapped within content. Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#no-keyboard-trap
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.1.3 Character Key Shortcuts</summary>

**Reference:** EN 301 549 → 9.2.1.4 Character key shortcuts (WCAG 2.1 → 2.1.4 Character Key Shortcuts)

**Description:** If a keyboard shortcut uses only a single character key, such as a letter, number, punctuation mark, or symbol, users must be able to turn it off, remap it, or ensure that it is active only when the relevant component has focus. This helps users of speech input, users with motor impairments, and users who may accidentally trigger single-key shortcuts.

**Success Criterion:** Single-character keyboard shortcuts can be disabled, remapped to include non-printable keys such as Ctrl, Alt, or Cmd, or are only active when the related component has focus.

**Test Questions:**
*	Are there shortcuts that use only a single character key?
*	Can these shortcuts be turned off?
*	Can they be remapped to safer key combinations?
*	Are shortcuts only active when the relevant component has focus?
*	Are shortcut settings easy to find and use?
*	Do embedded tools, editors, H5P objects or media players use single-character shortcuts?
*	Can these shortcuts be disabled or changed?
*	Are shortcuts only active when the respective component has focus?

**Further Information:** This requirement does not apply to browser, operating system, or assistive technology shortcuts. It applies to shortcuts provided by the digital content or application itself. ILIAS itself generally relies on traditional keyboard interactions. However, rich-text editors, learning modules, H5P content, video players, conferencing tools, code editors, or externally embedded applications can pose problems. 

ILIAS-Specific Note: This check should not be hastily marked as “not applicable.” In ILIAS courses, instructors can integrate external content or interactive elements that have their own shortcuts.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.1.2 No keyboard trap
*	EN 301 549 → 9.3.2.1 On focus
*	EN 301 549 → 9.3.2.2 On input

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.1.4 addresses shortcuts that are triggered by single printable character keys. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.1.2 No Keyboard Trap
*	WCAG 2.1 → 3.2.1 On Focus
*	WCAG 2.1 → 3.2.2 On Input

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#character-key-shortcuts
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#no-keyboard-trap
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-input
</details>
</details>

<details>
<summary>4.2.2 Enough Time</summary>

<details>
<summary>4.2.2.1 Timing Adjustable</summary>

**Reference:** EN 301 549 → 9.2.2.1 Timing adjustable (WCAG 2.1 → 2.2.1 Timing Adjustable)

**Description:** If content or functionality has a time limit, users must be able to turn off, adjust, or extend the time limit unless an exception applies. This includes session timeouts, timed forms, time-limited tasks, auto-logout warnings, booking processes, quizzes, and other interactions where time affects usability. The requirement supports users who need more time to read, understand, navigate, type, or interact with content.

**Success Criterion:** Users can disable, adjust, or extend time limits before they expire, unless the time limit is essential, longer than 20 hours, or part of a real-time event where no alternative is possible.

**Test Questions:**
*	Are there any time limits in the content or application?
*	Can users turn off the time limit before it starts?
*	Can users adjust or extend the time limit before it expires?
*	Is a timeout warning provided in time for users to react?
*	Does extending the time limit work with keyboard and assistive technologies?
*	Are test time limits configurable for individual users or groups?
*	Are users warned before a session timeout?
*	Can users extend the session before losing work?
*	Are autosave mechanisms available in tests, exercises or forms?
*	Can time accommodations be implemented for students with disabilities?

**Further Information:** This requirement does not prohibit time limits in general. It requires that users are given control where time limits are not essential. ILIAS features typical time-related scenarios: tests with time limits, exams, exercises with submission deadlines, session timeouts, availability periods, booking pools, or time-controlled learning paths. Here, a distinction must be made between:
*	didactic/organizational deadlines such as submission deadlines,
*	technical time limits such as session timeouts,
*	and time limits set by examination regulations for tests or online exams.
Not every time limit is automatically impermissible. However, users must, to the extent possible, be able to adjust or extend time limits, or be granted individually tailored time limits as part of accommodations for students with disabilities.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.3.2.1 On focus
*	EN 301 549 → 9.3.2.2 On input
*	EN 301 549 → 9.3.3.1 Error identification

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.2.1 requires that users can control time limits that are set by content. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 3.2.1 On Focus
*	WCAG 2.1 → 3.2.2 On Input
*	WCAG 2.1 → 3.3.1 Error Identification

**How to Meet WCAG:
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#timing-adjustable
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-input
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
</details>

<details>
<summary>4.2.2.2 Pause, Stop, Hide</summary>

**Reference:** EN 301 549 → 9.2.2.2 Pause, stop, hide (WCAG 2.1 → 2.2.2 Pause, Stop, Hide)

**Description:** Moving, blinking, scrolling, or auto-updating content that starts automatically, lasts more than five seconds, and is presented in parallel with other content must be pausable, stoppable, or hideable. This includes carousels, tickers, animated banners, auto-playing content, live updates, and moving backgrounds. The requirement supports users with attention-related, cognitive, vestibular, visual, and reading-related needs.

**Success Criterion:** Users can pause, stop, or hide moving, blinking, scrolling, or auto-updating content unless the movement or update is essential to the activity.

**Test Questions:**
*	Does any moving, blinking, scrolling, or auto-updating content start automatically?
*	Does it last longer than five seconds?
*	Is there a keyboard-accessible control to pause, stop, or hide it?
*	Does the control work reliably and remain available?
*	Does auto-updating content avoid disrupting reading or interaction?
*	Do course pages, learning modules or embedded tools contain auto-playing or moving content?
*	Can animations, videos, sliders or carousels be paused, stopped or hidden?
*	Are animated GIFs or auto-updating content used in content pages?
*	Are controls keyboard accessible?

**Further Information:** This requirement does not apply when the movement, blinking, scrolling, or auto-updating is essential to the functionality, for example in some real-time monitoring contexts. The problem here often arises not from ILIAS itself, but from content embedded by users. For this reason, a clear distinction should be made between the platform and the course content.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.3.1 Three flashes or below threshold
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.7 Focus visible

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.2.2 requires controls for content that moves, blinks, scrolls, or updates automatically. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.3.1 Three Flashes or Below Threshold
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.7 Focus Visible

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pause-stop-hide
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#three-flashes-or-below-threshold
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
</details>
</details>

<details>
<summary>4.2.3 Seizures and Physical Reactions: Three Flashes or Below Threshold</summary>

**Reference:** EN 301 549 → 9.2.3.1 Three flashes or below threshold (WCAG 2.1 → 2.3.1 Three Flashes or Below Threshold)

**Description:** Web pages and digital content shall not contain anything that flashes more than three times in any one-second period, unless the flashing is below the general flash and red flash thresholds. This applies to animations, videos, advertisements, alerts, transitions, loading effects, and other visual content. The requirement helps reduce the risk of seizures and other physical reactions.

**Success Criterion:** Content does not flash more than three times in any one-second period, or the flash is below the defined general flash and red flash thresholds.

**Test Questions:**
*	Does any content flash, flicker, blink, or rapidly change brightness?
*	Does the flashing occur more than three times in one second?
*	Are red flashes or high-contrast flashes present?
*	Has video or animation content been checked for flashing risk?
*	Can risky flashing effects be removed or replaced?
*	Do uploaded videos, GIFs, animations or learning modules contain flashing content?
*	Are externally embedded materials checked for flashing or flickering?
*	Are warning signs or alternative materials provided where needed?

**Further Information:** This requirement is especially important for videos, animations, advertisements, alerts, loading indicators, and visual effects. Automated tools may help identify potential issues, but visual inspection is often necessary. This point should be taken into account in ILIAS exams as part of content responsibility: Even though the ILIAS Core does not cause flickering, course content can be problematic.

Related Requirements:
*	EN 301 549 → 9.2.2.2 Pause, stop, hide
*	EN 301 549 → 9.1.4.3 Contrast
*	EN 301 549 → 9.2.1.1 Keyboard

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.3.1 limits flashing content to reduce the risk of seizures. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.2.2 Pause, Stop, Hide
*	WCAG 2.1 → 1.4.3 Contrast
*	WCAG 2.1 → 2.1.1 Keyboard

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#three-flashes-or-below-threshold
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pause-stop-hide
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
</details>

<details>
<summary>4.2.4 Navigable</summary>

<details>
<summary>4.2.4.1 Bypass Blocks</summary>

**Reference:** EN 301 549 → 9.2.4.1 Bypass blocks (WCAG 2.1 → 2.4.1 Bypass Blocks)

**Description:** Users shall be able to bypass blocks of content that are repeated on multiple pages or views. This includes navigation menus, headers, sidebars, search areas, cookie banners, and other repeated interface regions. The requirement supports keyboard users, screen reader users, and users who navigate sequentially through content.

**Success Criterion:** A mechanism is available to bypass repeated blocks of content and move directly to the main content or other important areas.

**Test Questions:**
*	Is there a keyboard-accessible skip link or equivalent mechanism?
*	Does the skip mechanism become visible when focused?
*	Does it move focus to the correct target, such as the main content?
*	Are repeated navigation and header areas avoidable?
*	Are landmarks or headings used to support efficient navigation?
*	Is there a skip link to the main content area?
*	Can repeated course navigation, breadcrumbs and menus be bypassed?
*	Are landmarks and headings used consistently in course and object views?

**Further Information:** Bypass mechanisms may include skip links, meaningful headings, ARIA landmarks, or other structural navigation mechanisms. A visible skip link is often the most direct solution for keyboard users. ILIAS pages contain recurring sections: header, breadcrumb trail, main navigation, magazine/course navigation, side menus, toolbars, object information, and footer. For long course pages or learning modules, it is important that users do not have to tab through all navigation elements every time.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.2.4.7 Focus visible

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.1 requires a way to bypass repeated content blocks. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 2.4.7 Focus Visible

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
</details>

<details>
<summary>4.2.4.2 Page Titled</summary>

**Reference:** EN 301 549 → 9.2.4.2 Page titled (WCAG 2.1 → 2.4.2 Page Titled)

**Description:** Web pages, documents, and views shall have titles that describe their topic or purpose. Meaningful titles help users understand where they are, distinguish between multiple open pages or tabs, and navigate efficiently. This is particularly important for screen reader users and users with cognitive disabilities.

**Success Criterion:** Each page, document, or view has a descriptive title that identifies its topic or purpose.

**Test Questions:**
*	Does each page or view have a meaningful title?
*	Does the title describe the current topic or purpose?
*	Is the title unique enough to distinguish the page from other pages?
*	Is the title updated when the user moves to a new view in a single-page application?
*	Is the title available to assistive technologies?
*	Does the browser title identify the current ILIAS object or page?
*	Are test steps, exercise pages, forums and learning modules clearly titled?
*	Is the title updated when navigating within dynamic views?

**Further Information:** The title should not be generic, such as “Home”, “Untitled”, or “Page 1”, unless that title is genuinely descriptive in context. In web pages, the title is usually provided in the HTML title element. For ILIAS: The page title should not simply be “ILIAS” or “Course,” but should reflect the specific context, such as the course name, object name, or current step.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.2.4.6 Headings and labels

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.2 requires that pages have titles describing topic or purpose. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.4 Link Purpose
*	WCAG 2.1 → 2.4.6 Headings and Labels

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#page-titled
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
</details>

<details>
<summary>4.2.4.3 Focus Order</summary>

**Reference:** EN 301 549 → 9.2.4.3 Focus order (WCAG 2.1 → 2.4.3 Focus Order)

**Description:** If a page or application can be navigated sequentially and the navigation sequence affects meaning or operation, focusable components shall receive focus in an order that preserves meaning and operability. This applies to forms, menus, dialogs, cards, accordions, carousels, and other interactive components. The requirement supports users who navigate with a keyboard, screen reader, switch control, or other sequential input method.

**Success Criterion:** Keyboard focus moves through content in a logical and meaningful order that matches the visual and structural order of the interface.

**Test Questions:**
*	Does keyboard focus follow a logical order?
*	Does the focus order match the visual reading and interaction order?
*	Are dialogs, menus, and dynamic content inserted into the focus order appropriately?
*	Does focus move to newly opened content when necessary?
*	Is focus returned to a meaningful position after closing dialogs or menus?

**Further Information:** A focus order does not always need to match the visual order exactly, but it must preserve meaning and operability. Unexpected focus jumps, skipped controls, or focus moving behind modal dialogs can create barriers.

**Related Requirements:**
*	EN 301 549 → 9.1.3.2 Meaningful sequence
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.1.2 No keyboard trap
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.3 requires that focus order preserves meaning and operability. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.2 Meaningful Sequence
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.1.2 No Keyboard Trap
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#meaningful-sequence
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#no-keyboard-trap
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.4.4 Link Purpose</summary>

**Reference:** EN 301 549 → 9.2.4.4 Link purpose (WCAG 2.1 → 2.4.4 Link Purpose (In Context))

**Description:** The purpose of each link shall be clear from the link text alone or from the link text together with its programmatically determined context. This supports users who navigate by links, screen reader users who open link lists, and users who need clear orientation.

**Success Criterion:** Each link has a meaningful purpose that can be determined from its text or its programmatically associated context.

**Test Questions:**
*	Is the link text meaningful and specific?
*	Can users understand where the link leads or what it does?
*	Are vague link texts such as “click here”, “more”, or “read more” avoided or clarified by context?
*	Is the relevant context programmatically available to assistive technologies?
*	Are repeated links with different destinations distinguishable?
*	Are course links and file links meaningful without relying only on visual context?
*	Do file names describe the content, not just “Document.pdf” or “Download”?
*	Are repeated links distinguishable, especially in lists, tables and learning modules?
*	Are links created by teachers meaningful and specific?

**Further Information:** The link text does not always need to contain all information by itself if the surrounding context is programmatically associated. However, clear standalone link text is usually preferable. ILIAS contains many automatically generated links, as well as course content created by instructors. Link texts such as “here,” “more,” “Download,” “File,” and “Next” can be problematic if the context is not clear from the code.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.2 Page titled
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.4 requires that the purpose of links can be determined from link text or context. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.2 Page Titled
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#page-titled
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.4.5 Multiple Ways</summary>

**Reference:** EN 301 549 → 9.2.4.5 Multiple ways (WCAG 2.1 → 2.4.5 Multiple Ways)

**Description:** More than one way shall be available to locate a web page within a set of web pages, unless the page is the result of, or a step in, a process. Alternative access routes may include navigation menus, search, sitemap, table of contents, breadcrumbs, filters, or related links. The requirement supports users with different navigation strategies and cognitive needs.

**Success Criterion:** Users have at least two ways to find pages within a set of web pages, unless the page is part of a process or an exception applies.

**Test Questions:**
*	Are there multiple ways to find important pages or views?
*	Is there a search function, navigation menu, sitemap, index, or table of contents?
*	Can users reach content without knowing the exact path?
*	Are alternative access routes available with keyboard and assistive technologies?
*	Are process steps exempt only where appropriate? 
*	Can users find course objects through more than one route, such as course navigation, search, breadcrumbs or dashboard?
*	Are important materials not only accessible through one visually specific path?
*	Are linear processes, such as tests or guided learning paths, clearly identified as such?

**Further Information:** This requirement applies to sets of web pages, not necessarily to isolated pages or individual steps in a linear process such as checkout, registration, or a multi-step form.

Related Requirements:
*	EN 301 549 → 9.2.4.1 Bypass blocks
*	EN 301 549 → 9.2.4.2 Page titled
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.2.4.6 Headings and labels

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.5 requires multiple ways to locate pages in a set of pages. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.4.1 Bypass Blocks
*	WCAG 2.1 → 2.4.2 Page Titled
*	WCAG 2.1 → 2.4.4 Link Purpose
*	WCAG 2.1 → 2.4.6 Headings and Labels

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#multiple-ways
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#page-titled
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
</details>

<details>
<summary>4.2.4.6 Headings and Labels</summary>

**Reference:** EN 301 549 → 9.2.4.6 Headings and labels (WCAG 2.1 → 2.4.6 Headings and Labels)

**Description:** Headings and labels shall describe the topic or purpose of the content or component they identify. This includes page headings, section headings, form labels, button labels, fieldset legends, menu labels, and labels for interactive controls. The requirement helps users understand structure, navigate efficiently, and identify the purpose of controls.

**Success Criterion:** Headings and labels are descriptive, meaningful, and appropriate for the content or functionality they introduce or identify.

**Test Questions:**
*	Do headings describe the topic of the following section?
*	Do form labels clearly identify the expected input?
*	Are button and control labels meaningful?
*	Are headings and labels consistent across similar content?
*	Do labels remain understandable when read out of visual context?
*	Are course sections and learning module pages structured with meaningful headings?
*	Do object titles describe the material or activity clearly?
*	Are test questions, form fields and upload fields clearly labelled?
*	Are labels understandable when read by a screen reader?

**Further Information:** This requirement focuses on whether headings and labels are descriptive, not on whether every possible section must have a heading. However, meaningful headings often improve navigation and understanding.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.1 Bypass blocks
*	EN 301 549 → 9.2.4.2 Page titled
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.6 requires headings and labels to describe topic or purpose. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.1 Bypass Blocks
*	WCAG 2.1 → 2.4.2 Page Titled
*	WCAG 2.1 → 2.4.4 Link Purpose
*	WCAG 2.1 → 3.3.2 Labels or Instructions

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#page-titled
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
</details>

<details>
<summary>4.2.4.7 Focus Visible</summary>

**Reference:** EN 301 549 → 9.2.4.7 Focus visible (WCAG 2.1 → 2.4.7 Focus Visible)

**Description:** Any keyboard-operable user interface component shall have a visible indication when it receives keyboard focus. This applies to links, buttons, form fields, menus, custom controls, dialogs, and embedded components. The requirement supports keyboard users by showing where the current point of interaction is.

**Success Criterion:** The keyboard focus indicator is visible when any interactive component receives focus.

**Test Questions:**
*	Is the keyboard focus visible on every interactive element?
*	Is the focus indicator sufficiently clear and easy to distinguish?
*	Is the focus indicator not removed or hidden by CSS?
*	Is focus visible in dialogs, menus, carousels, and custom components?
*	Does focus remain visible across responsive and embedded views?

**Further Information:** The default browser focus indicator may be sufficient if it is visible and not suppressed. Custom focus styles must provide a clear visual indication of the focused element.

Related Requirements:
*	EN 301 549 → 9.1.4.3 Contrast
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.4.7 requires a visible keyboard focus indicator. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.4.3 Contrast
*	WCAG 2.1 → 2.1.1 Keyboard
*	 WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.2.5 Input Modalities</summary>

<details>
<summary>4.2.5.1 Pointer Gestures</summary>

**Reference:** EN 301 549 → 9.2.5.1 Pointer gestures (WCAG 2.1 → 2.5.1 Pointer Gestures)

**Description:** Functionality that uses multipoint or path-based gestures shall also be operable with a single pointer without a path-based gesture, unless such a gesture is essential. This applies to pinch-to-zoom, swipe, drag paths, sliders, maps, drawing areas, carousels, and other gesture-based interactions. The requirement supports users with motor impairments, users of assistive technologies, and users who cannot perform complex gestures.

**Success Criterion:** All functionality that uses multipoint or path-based gestures can also be operated with a single pointer without requiring a path-based gesture, unless the gesture is essential.

**Test Questions:**
*	Are any functions triggered by multipoint gestures, such as pinch or two-finger gestures?
*	Are any functions triggered by path-based gestures, such as swiping or dragging along a path?
*	Is there a single-pointer alternative, such as buttons or controls?
*	Can the alternative be used with keyboard and assistive technologies?
*	Is the gesture truly essential, or can it be replaced?
*	Are drag-and-drop tasks also operable by keyboard or simple controls?
*	Do sorting, matching or ordering tasks provide alternatives to pointer gestures?
*	Are H5P, SCORM or plugin-based interactions checked separately?
*	Are map, slider or whiteboard interactions usable without complex gestures?

**Further Information:** A gesture may be considered essential when the path itself is part of the input, for example in freehand drawing or signature capture. For most interface controls, an alternative should be provided. Critical elements include drag-and-drop tasks, sorting questions, maps, sliders, interactive H5P content, whiteboards, drawing tasks, and external tools.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.5.2 Pointer cancellation
*	EN 301 549 → 9.2.5.4 Motion actuation
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.5.1 requires alternatives for multipoint or path-based gestures. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.5.2 Pointer Cancellation
*	WCAG 2.1 → 2.5.4 Motion Actuation
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-gestures
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-cancellation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#motion-actuation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.5.2 Pointer Cancellation</summary>

**Reference:** EN 301 549 → 9.2.5.2 Pointer cancellation (WCAG 2.1 → 2.5.2 Pointer Cancellation)

**Description:** Functionality operated by a single pointer shall avoid triggering actions on the down-event alone, or provide a way to abort or undo the action. This applies to buttons, links, drag-and-drop, sliders, touch controls, custom widgets, and other pointer-operated components. The requirement helps users avoid accidental activation and supports users with motor impairments or tremors.

**Success Criterion:** For single-pointer operation, actions are not triggered on the down-event alone, or users can abort, undo, or reverse the action, unless completion on the down-event is essential.

**Test Questions:**
*	Are actions triggered immediately on mouse-down or touch-start?
*	Can users cancel the action by moving the pointer away before release?
*	Is there an undo or reversal mechanism for accidental activation?
*	Are drag-and-drop interactions cancellable?
*	Is down-event activation only used when truly essential?
*	Are critical actions triggered only on release, not on pointer down?
*	Can accidental drag-and-drop actions be cancelled or undone?
*	Are delete, submit, finish test or booking actions confirmable or reversible?
*	Are touch interactions on mobile devices forgiving?

**Further Information:** The safest pattern is usually to trigger actions on the up-event, such as mouse-up or touch-end, allowing users to move away before activation. This is relevant for buttons, drag-and-drop, mobile views, and interactive tasks. Actions should not be triggered definitively as soon as the mouse button is pressed or a touch is initiated.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.5.1 Pointer gestures
*	EN 301 549 → 9.3.3.4 Error prevention
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.5.2 requires that pointer actions can be cancelled, undone, or are not triggered on the down-event unless essential. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.5.1 Pointer Gestures
*	WCAG 2.1 → 3.3.4 Error Prevention
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-cancellation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-gestures
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-prevention-legal-financial-data
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.5.3 Label in Name</summary>

**Reference:** EN 301 549 → 9.2.5.3 Label in name (WCAG 2.1 → 2.5.3 Label in Name)

**Description:** For user interface components with visible text labels, the accessible name shall contain the visible label text. This ensures that people using speech input can activate controls by saying the visible label and that screen reader users receive names that match the visual interface. The requirement applies to buttons, links, form fields, menu items, tabs, and other controls with visible labels.

**Success Criterion:** The accessible name of a component contains the visible text label, preferably at the beginning of the accessible name.

**Test Questions:**
*	Does each control with visible text have an accessible name?
*	Does the accessible name include the visible label text?
*	Is the visible label text placed at the beginning of the accessible name where possible?
*	Do speech input commands match the visible labels?
*	Are icon buttons with visible text named consistently?

**Further Information:** The accessible name may contain additional information, but it should not omit or contradict the visible label. For example, a visible “Search” button should not have the accessible name “Submit query” without including “Search”. ILIAS uses many buttons, icon buttons, action menus, and combined labels. If the visible text and the accessible name do not match, this can be particularly problematic for voice input.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.5.3 requires that the accessible name contains the visible label for controls with text labels. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#label-in-name
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.2.5.4 Motion Actuation</summary>

**Reference:** EN 301 549 → 9.2.5.4 Motion actuation (WCAG 2.1 → 2.5.4 Motion Actuation)

**Description:** Functionality that can be operated by device motion or user motion shall also be operable by user interface components. Motion activation shall be able to be disabled to prevent accidental activation, unless motion is essential or supported through an accessibility feature. This applies to shaking, tilting, rotating, gestures detected by a camera, and other movement-based interactions.

**Success Criterion:** Motion-based functionality has a non-motion alternative, and motion activation can be disabled unless motion is essential or part of an accessibility-supported mechanism.

**Test Questions:**
*	Is any function triggered by shaking, tilting, rotating, or moving the device?
*	Is any function triggered by user movement detected by sensors or a camera?
*	Is there an alternative control, such as a button or menu option?
*	Can motion activation be disabled?
*	Is motion activation only required when truly essential?

**Further Information:** This requirement does not prohibit motion-based interaction. It requires an accessible alternative and a way to prevent accidental triggering where motion is not essential.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.5.1 Pointer gestures
*	EN 301 549 → 9.2.5.2 Pointer cancellation
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 2.5.4 requires alternatives for functionality triggered by device or user motion and allows motion activation to be disabled. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.5.1 Pointer Gestures
*	WCAG 2.1 → 2.5.2 Pointer Cancellation
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#motion-actuation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-gestures
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-cancellation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>
</details>

<details>
<summary>4.3 Understandable</summary>

<details>
<summary>4.3.1 Readable</summary>

<details>
<summary>4.3.1.1 Language of Page</summary>

**Reference:** EN 301 549 → 9.3.1.1 Language of page (WCAG 2.1 → 3.1.1 Language of Page)

**Description:** The default human language of each web page, document, or view shall be programmatically determinable. This allows assistive technologies, browsers, translation tools, and other user agents to apply the correct pronunciation rules, hyphenation, spelling, character rendering, and language-specific processing. The requirement supports screen reader users, users with reading difficulties, multilingual users, and users relying on language-specific assistive technologies.

**Success Criterion:** The main language of the page or view is correctly specified in a way that can be programmatically determined by browsers and assistive technologies.

**Test Questions:**
*	Is the main language of the page specified correctly?
*	Does the HTML lang attribute match the actual main language of the page?
*	Is the language still correct after changing the ILIAS user interface language?
*	Are documents, learning modules, tests, forums, and wiki pages checked for the correct main language?
*	Do screen readers use the expected pronunciation for the page language?

**Further Information:** The main language does not have to match every individual word or phrase on the page. Short foreign-language terms may be covered by the page language unless they need a different pronunciation or interpretation.In ILIAS, the main page language may depend on the installation language, the user’s selected interface language, and the language of the specific course or learning content. This should be checked not only on the dashboard or repository pages, but also in courses, groups, learning modules, tests, exercises, forums, wikis, surveys, and administration views. Special attention is needed when a German ILIAS interface contains English course content, or when English-language courses are hosted in a German-language installation. Locally customized skins, templates, or plugins should not override or remove the correct language information.

Related Requirements:
*	EN 301 549 → 9.3.1.2 Language of parts
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.1.1 requires that the default human language of each page can be programmatically determined. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 3.1.2 Language of Parts
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-page
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-parts
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value

</details>

<details>
<summary>4.3.1.2 Language of Parts</summary>

**Reference:** EN 301 549 → 9.3.1.2 Language of parts (WCAG 2.1 → 3.1.2 Language of Parts)

**Description:** The human language of each passage or phrase in the content shall be programmatically determinable when it differs from the default language of the page, except for proper names, technical terms, words of indeterminate language, or words that have become part of the surrounding language. This enables assistive technologies to switch pronunciation and processing correctly.

**Success Criterion:** Foreign-language passages and relevant phrases are marked with the correct language so that assistive technologies can identify and process them correctly.

**Test Questions:**
*	Are longer foreign-language passages marked with the correct language?
*	Are quotations, instructions, test items, glossary entries, and learning materials checked for language changes?
*	Are foreign-language form labels, buttons, or navigation elements correctly marked where needed?
*	Are proper names, technical terms, or commonly adopted words treated appropriately?
*	Does a screen reader switch pronunciation when reaching marked foreign-language passages?

**Further Information:** Not every individual foreign word requires separate language markup. Exceptions may apply to names, technical terms, or words that are commonly used in the surrounding language. However, longer passages, quotations, task instructions, and learning materials in another language should be marked correctly. This requirement is especially relevant for multilingual courses, language-learning courses, international study programmes, and bilingual learning modules. In ILIAS, content created with the page editor, learning modules, test questions, survey questions, forum posts, wiki pages, glossaries, and object descriptions may contain language changes. Teachers may need guidance because the ILIAS core can provide technical possibilities, but language markup in self-created content often depends on editorial practice. Imported HTML, SCORM packages, H5P elements, and external tools must be checked separately.

Related Requirements:
*	EN 301 549 → 9.3.1.1 Language of page
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.1.2 requires that the language of passages or phrases can be programmatically determined when it differs from the default page language. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 3.1.1 Language of Page
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-parts
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-page
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.3.2 Predictable</summary>

<details>
<summary>4.3.2.1 On Focus</summary>

**Reference:** EN 301 549 → 9.3.2.1 On focus (WCAG 2.1 → 3.2.1 On Focus)

**Description:** When any user interface component receives focus, it shall not initiate an unexpected change of context. This means that moving keyboard focus to a link, button, form field, tab, menu item, or other control must not automatically submit data, open a new window, move the user to another page, change the current view, or significantly rearrange content unless the user has intentionally activated the component.

**Success Criterion:** Receiving focus alone does not cause an unexpected change of context.

**Test Questions:**
*	Does moving focus to an element avoid triggering navigation, submission, or major layout changes?
*	Do links, buttons, tabs, menus, form fields, and custom controls wait for explicit activation?
*	Are dropdowns, accordions, and menus predictable when they receive keyboard focus?
*	Does focus movement avoid opening new windows, dialogs, or pages unexpectedly?
*	Are screen reader and keyboard users able to explore the interface without triggering actions unintentionally?

**Further Information:** Visual changes such as highlighting, displaying a focus indicator, or showing additional help text may be acceptable if they do not change the context unexpectedly. A change of context should normally require explicit user action, such as pressing Enter, Space, or selecting a control. In ILIAS, this should be tested on object lists, course pages, tabs, action menus, dropdown menus, settings forms, filters, repository navigation, learning modules, test pages, and administration screens. Focus should not automatically open object actions, submit forms, change tabs, start tests, finish attempts, or navigate to another page. Locally developed plugins, custom templates, and JavaScript enhancements are particularly important because they may introduce focus-triggered behaviour that is not present in the ILIAS core.

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.7 Focus visible
*	EN 301 549 → 9.3.2.2 On input
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.2.1 requires that components do not initiate a change of context when they receive focus. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.7 Focus Visible
*	WCAG 2.1 → 3.2.2 On Input
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-visible
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-input
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.3.2.2 On Input</summary>

**Reference:** EN 301 549 → 9.3.2.2 On input (WCAG 2.1 → 3.2.2 On Input)

**Description:** Changing the setting of a user interface component shall not automatically cause an unexpected change of context unless the user has been advised of the behaviour before using the component. This applies to form fields, dropdowns, radio buttons, checkboxes, filters, search controls, date pickers, tab controls, and other input elements.

**Success Criterion:** Changing an input value does not unexpectedly change the context unless users are clearly informed before interacting with the component.

**Test Questions:**
*	Does selecting an option avoid automatically submitting a form or navigating away unexpectedly?
*	Are users warned before input causes a page reload, navigation, or major content change?
*	Do filters, sorting options, and search fields behave predictably?
*	Are changes triggered only after explicit confirmation where appropriate?
*	Is automatic updating announced or communicated accessibly?

**Further Information:** Automatic updates are not always prohibited. They may be acceptable when the behaviour is predictable, limited in scope, or clearly explained before interaction. However, users should not lose focus, entered data, orientation, or control unexpectedly. This is highly relevant for ILIAS forms, settings pages, availability settings, course filters, object lists, test configuration, member administration, booking pools, survey settings, and learning module navigation. Dropdowns or filters should not unexpectedly submit, reload, or change context without prior notice. In tests and exercises, changing an answer should not unexpectedly move to the next question, submit the attempt, or remove entered data unless this behaviour is clearly communicated and appropriate. 

Related Requirements:
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.3.2.1 On focus
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.2.2 requires that changing the setting of a component does not automatically cause an unexpected change of context unless users are advised before use. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 3.2.1 On Focus
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-input
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#on-focus
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
</details>

<details>
<summary>4.3.2.3 Consistent Navigation</summary>

**Reference:** EN 301 549 → 9.3.2.3 Consistent navigation (WCAG 2.1 → 3.2.3 Consistent Navigation)

**Description:** Navigational mechanisms that are repeated on multiple pages within a set of pages shall occur in the same relative order each time they are repeated, unless a change is initiated by the user. This helps users build a reliable mental model of the interface and supports users with cognitive disabilities, low vision, keyboard users, and screen reader users.

**Success Criterion:** Repeated navigation mechanisms appear in a consistent relative order across pages and views.

**Test Questions:**
*	Are repeated navigation elements presented in a consistent order?
*	Are menus, tabs, breadcrumbs, side navigation, and footer links consistent across comparable pages?
*	Does the order remain stable unless users intentionally change it?
*	Are responsive views consistent with the desktop navigation concept?
*	Are dynamically generated navigation areas predictable?

**Further Information:** Navigation does not need to be identical on every page if the context changes. However, repeated mechanisms should remain in a consistent relative order when they appear across pages within the same set.

Related Requirements:
*	EN 301 549 → 9.2.4.1 Bypass blocks
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.2.4.5 Multiple ways
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.2.4 Consistent identification

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.2.3 requires repeated navigation mechanisms to occur in the same relative order. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.4.1 Bypass Blocks
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 2.4.5 Multiple Ways
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.2.4 Consistent Identification

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-navigation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#bypass-blocks
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#multiple-ways
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-identification
</details>

<details>
<summary>4.3.2.4 Consistent Identification</summary>

**Reference:** EN 301 549 → 9.3.2.4 Consistent identification (WCAG 2.1 → 3.2.4 Consistent Identification)

**Description:** Components that have the same functionality within a set of pages shall be identified consistently. This includes buttons, links, icons, menus, form controls, status messages, navigation elements, and recurring actions. Consistent identification supports users who rely on predictable naming, screen reader output, speech input, and learned interaction patterns.

**Success Criterion:** Components with the same function are labelled, named, and presented consistently across pages and views.

**Test Questions:**
* Are identical functions labelled consistently across the application?
* Do buttons, links, icons, and menu entries use the same name for the same action?
* Are visible labels and accessible names consistent?
* Are recurring icons or symbols used with the same meaning?
* Are translations, local customizations, and plugin labels consistent with the ILIAS interface?

**Further Information:** The same function should not be labelled in different ways without a clear reason. For example, “Submit”, “Send”, and “Save” should not be used interchangeably for the same action if this creates confusion.

ILIAS-specific notes: Check that icons with tooltips or ARIA labels use the same accessible name as the visible label or expected ILIAS terminology.

Related Requirements:
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.2.5.3 Label in name
*	EN 301 549 → 9.3.2.3 Consistent navigation
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.2.4 requires consistent identification of components with the same functionality. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.4.4 Link Purpose
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 2.5.3 Label in Name
*	WCAG 2.1 → 3.2.3 Consistent Navigation
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#label-in-name
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-navigation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>

<details>
<summary>4.3.3 Input Assistance</summary>

<details>
<summary>4.3.3.1 Error Identification</summary>

**Reference:** EN 301 549 → 9.3.3.1 Error identification (WCAG 2.1 → 3.3.1 Error Identification)

**Description:** If an input error is automatically detected, the item that is in error shall be identified, and the error shall be described to the user in text. This applies to forms, login screens, registration processes, test submissions, uploads, settings pages, search filters, and other input-based interactions. The requirement helps users understand what went wrong and where correction is needed.

**Success Criterion:** Detected input errors are clearly identified and described in text, and the affected fields or controls are indicated accessibly.

**Test Questions:**
*	Are input errors clearly identified in text?
*	Is the field or control with the error clearly indicated?
*	Are error messages available to screen readers?
*	Are errors not communicated by colour alone?
*	Does keyboard focus move to or clearly reference the error where appropriate?

**Further Information:** An error message should explain what is wrong, not only state that an error occurred. Visual highlighting may be helpful, but it must not be the only way the error is communicated. For example: In tests and exams, error identification must be handled carefully so that users understand missing or invalid answers without being disoriented or losing their work. For upload errors, ILIAS should clearly communicate file type, file size, missing file, or upload failure information.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.1 Use of colour
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.3 Error suggestion

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.3.1 requires automatically detected input errors to be identified and described in text. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.3 Error Suggestion

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
</details>

<details>
<summary>4.3.3.2 Labels or Instructions</summary>

**Reference:** EN 301 549 → 9.3.3.2 Labels or instructions (WCAG 2.1 → 3.3.2 Labels or Instructions)

**Description:** Labels or instructions shall be provided when content requires user input. This includes form fields, checkboxes, radio buttons, dropdowns, upload fields, date fields, search fields, test answers, survey questions, and other input components. Labels and instructions help users understand what information is expected and how to complete the task successfully.

**Success Criterion:** User input fields have clear labels or instructions that identify the expected input and are available to assistive technologies.

**Test Questions:**
*	Does every input field have a clear label or instruction?
*	Are required fields clearly indicated in text, not only by colour or symbols?
*	Are format requirements explained before input, for example date format or file type?
*	Are labels programmatically associated with their input fields?
*	Are instructions available to screen readers and keyboard users?

**Further Information:** Labels should remain visible or otherwise easily available while users enter information. Placeholder text alone is usually not sufficient as a label because it disappears during input and may not be reliably available to all users.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.2.5.3 Label in name
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.3.2 requires labels or instructions when content requires user input. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 2.5.3 Label in Name
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#label-in-name
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>4.3.3.3 Error Suggestion</summary>

**Reference:** EN 301 549 → 9.3.3.3 Error suggestion (WCAG 2.1 → 3.3.3 Error Suggestion)

**Description:** If an input error is automatically detected and suggestions for correction are known, the suggestions shall be provided to the user unless this would jeopardize security or the purpose of the content. This helps users correct mistakes efficiently and reduces frustration, especially for users with cognitive disabilities, dyslexia, low vision, or motor impairments.

**Success Criterion:** When correction suggestions are available, users receive clear and accessible guidance on how to fix the detected error.

**Test Questions:**
*	Are users told how to correct detected errors?
*	Are examples or expected formats provided where helpful?
*	Are invalid dates, missing required fields, wrong file types, or incorrect formats explained clearly?
*	Are correction suggestions available to screen readers?
*	Are security-sensitive cases handled appropriately without revealing unsafe information?

**Further Information:** Error suggestions should be specific enough to help users correct the problem. For example, “Enter the date in the format DD.MM.YYYY” is more helpful than “Invalid input”. Suggestions should not expose sensitive information or weaken security.

Related Requirements:
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.4 Error prevention
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.3.3 requires that correction suggestions are provided when input errors are detected and suggestions are known, unless doing so would compromise security or purpose. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.4 Error Prevention
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-prevention-legal-financial-data
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>4.3.3.4 Error Prevention</summary>

**Reference:** EN 301 549 → 9.3.3.4 Error prevention (WCAG 2.1 → 3.3.4 Error Prevention (Legal, Financial, Data))

**Description:** For pages or processes that cause legal commitments, financial transactions, data changes, deletion of user-controllable data, or submission of test responses, users shall be supported in avoiding serious errors. At least one of the following mechanisms shall be available: submissions are reversible, data can be checked and corrected before final submission, or user input is checked and users are given an opportunity to correct it.

**Success Criterion:** For legal, financial, data-changing, or test-related submissions, users can review, correct, or reverse their input before or after final submission.

**Test Questions:**
*	Can users review important information before final submission?
*	Can users correct errors before committing changes?
*	Are delete, submit, finish, enrol, booking, or payment-like actions confirmed?
*	Can destructive actions be undone or restored where appropriate?
*	Are test or exam submissions clearly confirmed before they become final?

**Further Information:** This requirement applies to high-impact actions, such as legal commitments, financial transactions, changes or deletion of stored data, and test submissions. It does not require every minor form submission to be reversible, but serious or irreversible actions need additional safeguards. Particular attention is needed for actions such as “Submit”, “Finish Test”, “Delete”, “Remove Member”, “Cancel Booking”, “Submit Assignment”, “Save Grade”, or “Publish Result”. Users should be able to review and confirm final submissions. Where reversal is not possible, ILIAS should provide clear confirmation steps and opportunities to check the data before final commitment. 

Related Requirements:
*	EN 301 549 → 9.2.5.2 Pointer cancellation
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.3 Error suggestion
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** WCAG 2.1 Success Criterion 3.3.4 requires error prevention mechanisms for legal, financial, data-changing, and test-related submissions. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 2.5.2 Pointer Cancellation
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.3 Error Suggestion
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-prevention-legal-financial-data
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#pointer-cancellation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>
</details>
</details>
<details>
<summary>4.4. Compatible</summary>

<details>
<summary>4.4.1. Correct Syntax</summary>

**Reference:** EN 301 549 → 9.4.1.1 Parsing / Correct syntax (WCAG 2.1 → 4.1.1 Parsing)

**Description:** Web content shall be implemented using correct syntax so that browsers, assistive technologies, and other user agents can reliably interpret the structure and functionality of the interface. Markup shall avoid serious parsing errors that affect accessibility, such as incorrectly nested elements, duplicate IDs, missing required relationships, or broken ARIA references.

**Success Criterion:** Markup and code used for user interface components can be parsed reliably by user agents and assistive technologies, without accessibility-relevant syntax errors.

**Test Questions:**
*	Are HTML elements correctly nested and closed?
*	Are IDs unique within the page or view?
*	Are form labels, ARIA references, headings, tables, dialogs, and widgets technically valid?
*	Are custom ILIAS components implemented without broken ARIA attributes or invalid relationships?
*	Do automated accessibility tools report parsing or syntax errors that affect accessibility?
*	Do assistive technologies correctly recognize the page structure and controls?

**Further Information:** This requirement is especially relevant for complex ILIAS pages with forms, tables, modal dialogs, accordions, tabs, tree structures, menus, overlays, and dynamically generated content. Invalid markup may not be visible to sighted users but can prevent screen readers and other assistive technologies from interpreting the interface correctly. 

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.4.1.2 Name, role, value
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** WCAG 2.1 Success Criterion 4.1.1 requires that content implemented using markup languages has complete start and end tags, is nested according to specification, does not contain duplicate attributes, and uses unique IDs where required. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#parsing
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>4.4.2 Name, Role, Value Available</summary>

**Reference:** EN 301 549 → 9.4.1.2 Name, role, value (WCAG 2.1 → 4.1.2 Name, Role, Value)

**Description:** For all user interface components, the name and role shall be programmatically determinable. States, properties, and values that can be set by the user shall also be programmatically determinable and updated when they change. This allows assistive technologies to identify controls, communicate their purpose, and operate them correctly.

**Success Criterion:** Each interactive component exposes a correct accessible name, role, state, and value, and changes are communicated to assistive technologies.

**Test Questions:**
*	Does each button, link, form field, checkbox, radio button, tab, menu item, and dialog have a correct accessible name?
*	Is the role of each component programmatically determinable?
*	Are states such as expanded, collapsed, selected, checked, disabled, required, invalid, or current correctly exposed?
*	Are changing values, for example in sliders, date pickers, filters, or progress indicators, available to assistive technologies?
*	Are custom ILIAS widgets implemented with native HTML where possible or with correct ARIA where necessary?
*	Do screen readers announce controls in a way that matches their visual purpose?

**Further Information:** 

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.1 Correct syntax
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** WCAG 2.1 Success Criterion 4.1.2 requires that user interface components expose their name and role, and that states, properties, and values are available to assistive technologies. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>4.4.3 Status Messages Programmatically Available</summary>

**Reference:** EN 301 549 → 9.4.1.3 Status messages (WCAG 2.1 → 4.1.3 Status Messages)

**Description:** Status messages shall be programmatically determinable so that assistive technologies can present them to users without moving focus unnecessarily. This applies to messages that inform users about results, changes, errors, success confirmations, loading states, progress, or updates in the interface.

**Success Criterion:** Status messages are available to assistive technologies and are announced appropriately without requiring keyboard focus to move to the message.

**Test Questions:**
*	Are dynamic updates, such as saved changes, loaded results, or validation messages, programmatically available?
*	Are live regions or equivalent mechanisms used where appropriate?
*	Does focus remain in a logical position when a status message appears?
*	Are loading indicators, progress updates, and search results communicated accessibly?
*	Are messages in tests, exercises, forums, forms, and administration views announced reliably?

**Further Information:** Status messages may occur after saving settings, submitting forms, uploading files, joining courses, completing tests, sending forum posts, filtering tables, or changing page editor content. These messages should not rely only on visual placement, color, or animation. Important messages should be associated with the relevant context and announced in a way that does not interrupt the user unnecessarily.

Related Requirements:
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.1 Use of colour
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** WCAG 2.1 Success Criterion 4.1.3 requires that status messages can be programmatically determined through role or properties, so that assistive technologies can present them without receiving focus. 

Depending on the implementation and context, the following WCAG criteria may also be relevant:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.1 Use of Color
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.3 Error Suggestion
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#use-of-color
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>
</details>

<details>
<summary>5. USER-DEFINED SETTINGS AND AUTHORING FUNCTIONS INTERFACE </summary>

<details>
<summary>5.1 User-defined Settings</summary>

**Reference:** EN 301 549 → 11.7 User preferences / User-defined settings

**Description:** Where software provides user-defined settings, those settings shall not prevent accessibility features from working. User preferences should be respected where possible, especially settings related to accessibility, display, language, input, contrast, text size, motion, captions, and assistive technology compatibility.

**Success Criterion:** User-defined settings in ILIAS can be adjusted, saved, restored, and used without reducing accessibility or preventing assistive technologies from operating correctly.

**Test Questions:**
*	Can users adjust relevant preferences without barriers?
*	Are preferences reachable and operable by keyboard?
*	Are preference controls correctly labelled and understandable?
*	Are saved settings retained after logout, page changes, or session changes?
*	Do user preferences work with screen readers, magnification, keyboard navigation, and browser zoom?
*	Do settings such as language, layout, notification preferences, editor preferences, or accessibility-related options create accessibility problems?
*	Are default settings accessible before any personalization is applied?

**Further Information:** User-defined settings may include language, personal profile settings, notification settings, mail settings, calendar settings, table views, repository views, editor settings, learning progress views, and display-related options. These settings should not introduce inaccessible layouts or controls. If ILIAS provides accessibility-related preferences, they should be easy to find, clearly labelled, and reversible.

Related Requirements:
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.1.4.10 Reflow
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.3 Focus order
*	EN 301 549 → 9.3.2.3 Consistent navigation
*	EN 301 549 → 9.3.2.4 Consistent identification
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. 

However, depending on the setting and its effect on the interface, several WCAG criteria may be relevant:
*	WCAG 2.1 → 1.4.4 Resize Text
*	WCAG 2.1 → 1.4.10 Reflow
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.4.3 Focus Order
*	WCAG 2.1 → 3.2.3 Consistent Navigation
*	WCAG 2.1 → 3.2.4 Consistent Identification
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#reflow
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-navigation
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#consistent-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>5.2  Authoring Tools</summary>

<details>
<summary>5.2.1 Accessible Content Creation</summary>

**Reference:** EN 301 549 → 11.8.2 Accessible content creation

**Description:** Authoring tools shall support users in creating accessible content. Where ILIAS provides tools for creating, editing, uploading, structuring, or publishing content, these tools should enable authors to include accessibility information such as text alternatives, headings, labels, table headers, language information, captions, transcripts, and accessible document structures.

**Success Criterion:** Authoring functions provide the necessary features for creating accessible learning content, documents, media, tests, exercises, pages, forums, and other user-generated content.

**Test Questions:**
*	Can authors add text alternatives for images and graphical content?
*	Can authors create semantic headings and structured content?
*	Can authors create accessible tables with headers where needed?
*	Can authors specify language changes where relevant?
*	Can authors add captions, transcripts, or alternatives for audio and video content?
*	Can authors create accessible links, buttons, forms, questions, and instructions?
*	Do page editor, learning module, test, wiki, forum, glossary, exercise, and media tools support accessible content creation?
*	Are accessibility-related input fields labelled and explained clearly?

**Further Information:** This requirement is highly relevant for ILIAS because many users create content directly in the system. Course administrators, tutors, teachers, and learners may create pages, learning modules, test questions, exercise instructions, forum posts, wiki pages, glossary entries, media objects, portfolios, and uploaded materials. The authoring interface should not force inaccessible content structures and should provide accessible options by default.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.2.2 Captions
*	EN 301 549 → 9.1.2.3 Audio description or media alternative
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.3.1.1 Language of page
*	EN 301 549 → 9.3.1.2 Language of parts
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** This EN requirement is not itself a WCAG success criterion for web content, because it concerns the authoring tool. 

However, the content produced with the authoring tool should support conformance with relevant WCAG criteria, including:
*	WCAG 2.1 → 1.1.1 Non-text Content
*	WCAG 2.1 → 1.2.2 Captions
*	WCAG 2.1 → 1.2.3 Audio Description or Media Alternative
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 2.4.4 Link Purpose
*	WCAG 2.1 → 3.1.1 Language of Page
*	WCAG 2.1 → 3.1.2 Language of Parts
*	WCAG 2.1 → 3.3.2 Labels or Instructions

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#audio-description-or-media-alternative-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-page
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-parts
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
</details>

<details>
<summary>5.2.2 Preservation of Accessibility Information in Transformation</summary>

**Reference:** EN 301 549 → 11.8.3 Preservation of accessibility information in transformation

**Description:** When content is transformed, converted, imported, exported, copied, moved, or saved in another format, accessibility information shall be preserved where supported by the target format. This includes structural information, text alternatives, headings, labels, table headers, language information, captions, transcripts, metadata, and reading order.

**Success Criterion:** Accessibility information is retained during transformations performed by ILIAS, unless the target format cannot support the information.

**Test Questions:**
*	Are headings, lists, tables, links, labels, and reading order preserved when content is copied or transformed?
*	Are image text alternatives retained during import, export, duplication, or conversion?
*	Are captions, transcripts, and media alternatives preserved when media content is moved or reused?
*	Is accessibility information retained when learning modules, pages, tests, or content objects are copied?
*	Are accessibility properties preserved when content is exported from or imported into ILIAS?
*	Does the target format support the accessibility information, and if not, is the limitation communicated?

**Further Information:**

Related Requirements:
*	EN 301 549 → 11.8.2 Accessible content creation
*	EN 301 549 → 11.8.4 Repair assistance
*	EN 301 549 → 11.8.5 Templates
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.3.1.1 Language of page
*	EN 301 549 → 9.3.1.2 Language of parts

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. It supports the creation and maintenance of content that can conform to WCAG, especially where accessibility information is required for perceivable, operable, understandable, and robust content.

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-page
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-parts
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>

<details>
<summary>5.2.3 Repair Assistance</summary>

**Reference:** EN 301 549 → 11.8.4 Repair assistance

**Description:** Where an authoring tool checks, identifies, or reports accessibility problems, it shall assist authors in repairing them. Repair assistance may include warnings, explanations, suggestions, guidance, prompts, validation messages, or workflows that help authors add missing accessibility information or correct inaccessible content.

**Success Criterion:** Easy-to-understand guidance is provided to help identify and resolve accessibility issues in content created by authors, provided that appropriate checking or correction features are available.

**Test Questions:**
*	Does ILIAS warn authors when required accessibility information is missing, such as alternative text for images?
*	Are repair suggestions clear, specific, and actionable?
*	Are accessibility warnings accessible to keyboard and screen reader users?
*	Are error messages linked to the relevant fields or content areas?
*	Can authors correct accessibility issues without losing content?
*	Are authors guided to create accessible headings, tables, links, language markings, captions, and media alternatives?
*	Are accessibility-related validation messages understandable for non-expert users?

**Further Information:** The goal is not only to detect problems, but also to help authors fix them effectively. Repair assistance should avoid technical jargon where possible and should explain why the issue matters.

Related Requirements:
*	EN 301 549 → 11.8.2 Accessible content creation
*	EN 301 549 → 11.8.3 Preservation of accessibility information in transformation
*	EN 301 549 → 11.8.5 Templates
*	EN 301 549 → 9.3.3.1 Error identification
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.3.3.3 Error suggestion
*	EN 301 549 → 9.4.1.3 Status messages

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion for web content. 

However, the repair process itself should be accessible and may relate to the following WCAG criteria:
*	WCAG 2.1 → 3.3.1 Error Identification
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 3.3.3 Error Suggestion
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-identification
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#error-suggestion
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>5.2.4 Templates</summary>

**Reference:** EN 301 549 → 11.8.5 Templates

**Description:** Where authoring tools provide templates, at least one template that supports the creation of accessible content shall be available. Templates should not introduce accessibility barriers and should encourage correct use of structure, headings, labels, instructions, reading order, contrast, and other accessibility-relevant features.

**Success Criterion:** Templates and layout presets support accessible content creation and do not require authors to start from inaccessible structures.

**Test Questions:**
*	Are accessible templates available for pages, learning modules, courses, exercises, tests, and other authoring contexts?
*	Do templates use semantic headings, lists, tables, labels, and regions correctly?
*	 Do templates avoid inaccessible color combinations, layout-only tables, unclear links, and missing labels?
*	Are template components operable by keyboard and understandable for screen reader users?
*	Do templates include guidance for alternative text, captions, transcripts, and accessible media use where relevant?
*	Are custom templates, skins, and locally adapted layouts checked for accessibility?
*	Can authors use templates without needing advanced accessibility knowledge?

**Further Information:** Templates can strongly influence the accessibility of ILIAS content. If templates are accessible, they help authors produce accessible learning materials more consistently. If templates contain poor heading structures, insufficient contrast, unclear placeholders, inaccessible tables, or unlabeled components, these problems may be copied into many courses and learning objects. Locally created templates should therefore be included in accessibility testing.

Related Requirements:
*	EN 301 549 → 11.8.2 Accessible content creation
*	EN 301 549 → 11.8.3 Preservation of accessibility information in transformation
*	EN 301 549 → 11.8.4 Repair assistance
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.3 Contrast
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. 

However, templates should support content that can conform to WCAG, especially the following criteria:
*	WCAG 2.1 → 1.3.1 Info and Relationships
*	WCAG 2.1 → 1.4.3 Contrast
*	WCAG 2.1 → 2.4.6 Headings and Labels
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
</details>
</details>
</details>

<details>
<summary>6. DOKUMNETATION AND SUPPORT</summary>

<details>
<summary>6.1 Documentation of Compatibility and Accessibility Features</summary>

**Reference:** EN 301 549 → 12.1.1 Accessibility and compatibility features

**Description:** Product documentation shall list and explain the accessibility and compatibility features of the product. Users, administrators, support staff, and procurement teams should be able to understand which accessibility features are available, how they work, and which assistive technologies, browsers, settings, or configurations are supported.

**Success Criterion:** The documentation describes relevant accessibility and compatibility features clearly and accurately.

**Test Questions:**
*	Does the documentation describe accessibility features available in ILIAS?
*	Does it explain keyboard operation, screen reader compatibility, language settings, captions, alternative text, templates, and accessible authoring options where relevant?
*	Are limitations or known accessibility issues documented transparently?
*	Does the documentation explain supported browsers, assistive technologies, and configurations?
*	Is information available for learners, authors, tutors, administrators, and support staff?
*	Is accessibility information easy to find from the main documentation or help areas?

**Further Information:** For ILIAS, this may include user documentation, administrator documentation, release notes, accessibility statements, plugin documentation, help pages, onboarding materials, and institutional guidance. Accessibility documentation should not be limited to technical administrators; it should also help everyday users and content authors understand how to use ILIAS accessibly.

Related Requirements:
*	EN 301 549 → 12.1.2 Accessible documentation
*	EN 301 549 → 12.2.3 Effective communication
*	EN 301 549 → 12.2.4 Documentation provided by support
*	EN 301 549 → 11.8.2 Accessible content creation

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. However, if documentation is provided as web content, the documentation itself should meet the applicable WCAG requirements for web content.

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#language-of-page
</details>

<details>
<summary>6.2 Accessible Documentation</summary>

**Reference:** EN 301 549 → 12.1.2 Accessible documentation

**Description:** Documentation that supports the use shall itself be accessible. This applies to online documentation, help pages, PDFs, manuals, tutorials, videos, training material, release notes, and other support content. Users with disabilities must be able to perceive, navigate, understand, and use the documentation.

**Success Criterion:** Documentationis available in accessible formats and conforms to the applicable accessibility requirements for the format used.

**Test Questions:**
*	Is online documentation accessible by keyboard and screen reader?
*	Are headings, lists, links, tables, and navigation structures used correctly?
*	Do images in the documentation have appropriate text alternatives?
*	Do videos include captions and, where necessary, transcripts or audio descriptions?
*	Are PDF documents tagged and structured accessibly?
*	Is the documentation usable with browser zoom, reflow, and high contrast settings?
*	Are instructions understandable and not dependent only on color, shape, position, or sensory characteristics?

**Further Information:** The Documentation should avoid instructions such as “click the green button on the right” without naming the control.

Related Requirements:
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.2.2 Captions
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.1.4.3 Contrast
*	EN 301 549 → 9.1.4.4 Resize text
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.4.6 Headings and labels
*	EN 301 549 → 10 Non-web documents

**Understanding WCAG:** This EN requirement concerns documentation, but documentation delivered as web content should meet the applicable WCAG requirements. Documentation delivered as non-web documents should meet the relevant EN requirements for documents.

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#contrast-minimum
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#resize-text
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
</details>

<details>
<summary>6.3 Technical Support</summary>

**Reference:** EN 301 549 → 12.2.2 Information on accessibility and compatibility features

**Description:** Technical support shall provide information about accessibility and compatibility features that are included in the product documentation. Support staff should be able to help users understand and use accessibility features, supported assistive technologies, accessible workflows, and known limitations.

**Success Criterion:** The support channels can provide accurate information about accessibility and compatibility features when users request help.

**Test Questions:**
*	Can support staff explain the accessibility features of ILIAS?
*	Can support staff help users with keyboard operation, screen reader use, language settings, captions, alternative text, and accessible authoring?
*	Can support staff provide information about supported browsers and assistive technologies?
*	Are known accessibility limitations or workarounds documented for support staff?
*	Are support responses consistent with the official documentation?
*	Can users with disabilities contact support without accessibility barriers?

**Further Information:** Technical support for ILIAS may be provided by a central helpdesk, e-learning support team, IT department, accessibility office, vendor, hosting provider, or local administrators. Support staff do not need to be accessibility experts in every case, but they should have access to reliable information and escalation paths for accessibility-related issues.

Related Requirements:
*	EN 301 549 → 12.1.1 Documentation of compatibility and accessibility
*	EN 301 549 → 12.1.2 Accessible documentation
*	EN 301 549 → 12.2.3 Effective communication
*	EN 301 549 → 12.2.4 Documentation provided by support
*	EN 301 549 → 11.7 User preferences
*	EN 301 549 → 11.8.2 Accessible content creation

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. However, support portals, contact forms, chat systems, ticket systems, and knowledge bases used for technical support should meet the applicable WCAG requirements.

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#focus-order
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#headings-and-labels
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages
</details>

<details>
<summary>6.4  Effective Communication</summary>

**Reference:** EN 301 549 → 12.2.3 Effective communication

**Description:** Support services shall accommodate the communication needs of users with disabilities. Users shall be able to communicate effectively with support through accessible channels and receive information in a form they can perceive and use.

**Success Criterion:** The support is available through communication methods that are accessible and effective for users with different disabilities.

**Test Questions:**
*	Can users contact support through accessible channels, such as accessible web forms, email, phone, chat, or ticket systems?
*	Are support channels usable with keyboard, screen readers, magnification, speech input, and other assistive technologies?
*	Can users request information in an alternative accessible format?
*	Are time limits, CAPTCHAs, uploads, forms, or authentication steps in support processes accessible?
*	Are urgent accessibility issues handled through an appropriate escalation process?

**Further Information:** Effective communication is not limited to the content of support answers. It also includes the accessibility of the support process itself. In an environment, users may need help with login, course access, content, media, or assistive technology compatibility. Support channels should not exclude users who cannot use a telephone, cannot complete an inaccessible form, or need written communication.

Related Requirements:
*	EN 301 549 → 12.1.2 Accessible documentation
*	EN 301 549 → 12.2.2 Technical support
*	EN 301 549 → 12.2.4 Documentation provided by support
*	EN 301 549 → 9.2.1.1 Keyboard
*	EN 301 549 → 9.2.2.1 Timing adjustable
*	EN 301 549 → 9.3.3.2 Labels or instructions
*	EN 301 549 → 9.4.1.2 Name, role, value

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. However, digital support channels and communication tools should meet the applicable WCAG requirements. 

Depending on the support channel, the following WCAG criteria may be relevant:
*	WCAG 2.1 → 2.1.1 Keyboard
*	WCAG 2.1 → 2.2.1 Timing Adjustable
*	WCAG 2.1 → 3.3.2 Labels or Instructions
*	WCAG 2.1 → 4.1.2 Name, Role, Value
*	WCAG 2.1 → 4.1.3 Status Messages

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#keyboard
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#timing-adjustable
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#name-role-value
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#status-messages

</details>

<details>
<summary>6.5 Documentation Provided by Support</summary>

**Reference:** EN 301 549 → 12.2.4 Accessible documentation provided by support services

**Description:** Documentation provided by support services shall be accessible. This includes instructions, troubleshooting guides, screenshots, PDFs, email responses, ticket responses, knowledge base articles, links, videos, and other materials that support staff provide to users.

**Success Criterion:** Support-provided documentation is available in accessible formats and can be used by people with disabilities.

**Test Questions:**
*	Are support emails and ticket responses written in a clear and accessible way?
*	Are attached PDFs, guides, screenshots, or documents accessible?
*	Do screenshots include text explanations for relevant information?
*	Are video instructions captioned and, where needed, accompanied by transcripts?
*	Are links descriptive and understandable out of context where possible?
*	Are instructions independent of color, shape, visual position, or mouse-only actions?
*	Can users request support documentation in an alternative accessible format?

**Further Information:**

Related Requirements:
*	EN 301 549 → 12.1.1 Documentation of compatibility and accessibility
*	EN 301 549 → 12.1.2 Accessible documentation
*	EN 301 549 → 12.2.2 Technical support
*	EN 301 549 → 12.2.3 Effective communication
*	EN 301 549 → 9.1.1.1 Non-text content
*	EN 301 549 → 9.1.3.1 Info and relationships
*	EN 301 549 → 9.2.4.4 Link purpose
*	EN 301 549 → 9.3.3.2 Labels or instructions

**Understanding WCAG:** This EN requirement is not a direct WCAG success criterion. However, documentation provided through web pages, emails, documents, or media should follow the applicable accessibility requirements for the format used.

**How to Meet WCAG:**
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#non-text-content
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#info-and-relationships
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#link-purpose-in-context
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#labels-or-instructions
*	https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1#captions-prerecorded
</details>
</details>
