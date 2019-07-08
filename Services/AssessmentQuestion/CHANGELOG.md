# Changelog

## 03.07.2019
- MST: globale Struktur src/Data/Domain verschoben nach Servicec/AssessmentQuestion/src/Common - Muss nun zusätzlich geprüft und fertiggestellt werden. Readme (Services/AssessmentQuestion/src/Common/DomainModel/Aggregate/docs/documentation/Readme.md) muss fertiggestellt werden.
- MST: Noch einige zusätzliche Klassen bei Event hinzugefügt

## 04.07.2017
- MST QuestionId als eigenes Objekt eingeführt. Common soll nur Abstrakte Klassen und Interfaces haben.
- Interface und Abstrakte Klasse für DomainObjectId eingeführt.
- Example-Implementierung angepasst.
- RevisionId neu Abstrakt
- Variablen sollen expliziter benannt werden. z.B. creator -> creator_id
- Interface Aggregation Repository eingeführt
- AbstractAggregateRoot eingeführt für die nicht EventSourced Beispiele / Anwendungen. Brauchen wir für TB Besprechung / Allenfalls für andere Entwicklungen

## 08.07.2019
Moddeling Answer-Entity
Usage see
-> Services/AssessmentQuestion/src/Authoring/DomainModel/Question/Answer/Option/AnswerOptionFactory.php
