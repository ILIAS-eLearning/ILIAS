
---

# assignAdminsToCourse

This activity allows the assignment of multiple course administrators.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

assignAdminsToCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 
	Such may be gathered from methods reading participants, e.g. readLearnersFromCourse or readAdminsFromCourse.

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="AssignAdminsToCourse">
		<bpmn2:extensionElements>
		<ilias:properties>
			<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
			api="ilModulesCourseTasks" method="assignAdminsToCourse"/>
		</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# assignTutorToCourse

This activity allows the assignment of multiple course tutors.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

assignTutorsToCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 
	Such may be gathered from methods reading participants, e.g. readLearnersFromCourse or readAdminsFromCourse.

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="AssignTutorsToCourse">
		<bpmn2:extensionElements>
		<ilias:properties>
			<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
			api="ilModulesCourseTasks" method="assignTutorsToCourse"/>
		</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# assignLearnersToCourse

This activity allows the assignment of multiple learners to a course.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

assignLearnersToCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 
	Such may be gathered from methods reading participants, e.g. readLearnersFromCourse or readAdminsFromCourse.

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="AssignTutorsToCourse">
		<bpmn2:extensionElements>
		<ilias:properties>
			<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
			api="ilModulesCourseTasks" method="assignLearnersToCourse"/>
		</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# createCourse

This activity allows the creation of a course.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

createCourse

## Inputs

* crsTitle

	String with the name of the course to be created.

* destRefId

	Ref-ID where the new course is to be created.

## Outputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref-id - Parameter)

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="CreateCourse">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
				api="ilModulesCourseTasks" method="createCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# readAdminsFromCourse

This activity reads users assigned as course administrators.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

readAdminsFromCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

## Outputs

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="ReadAdminsFromCourse">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
				api="ilModulesCourseTasks" method="readAdminsFromCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# readTutorsFromCourse

This activity reads users assigned as course tutors.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

readTutorsFromCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

## Outputs

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="ReadTutorsFromCourse">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
				api="ilModulesCourseTasks" method="readTutorsFromCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---

# readLearnersFromCourse

This activity reads users assigned as learners.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesCourseTasks

## Method

readLearnersFromCourse

## Inputs

* crsRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

## Outputs

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="ReadLearnersFromCourse">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
				api="ilModulesCourseTasks" method="readLearnersFromCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_5</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_6</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_6">
			<bpmn2:sourceRef>DataObjectReference_4</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---