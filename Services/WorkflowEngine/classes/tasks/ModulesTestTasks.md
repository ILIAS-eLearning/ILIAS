
---

# createTestInCourse

This activity allows the creation of Tests in a Course.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesTestTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesTestTasks

## Method

createTestInCourse

## Inputs

* destRefId

	Course Reference ID as seen in the URL of ILIAS (ref_id - Parameter), location where the test shall be created.

* crsTitle

	String with the name 

## Outputs

* tstRefId

	Reference ID of the newly created test.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_8" name="CreateTest">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesTestTasks.php"
				api="ilModulesTestTasks" method="createTestInCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_13</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_9</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_4">
			<bpmn2:sourceRef>DataObjectReference_7</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataOutputAssociation id="DataOutputAssociation_5">
			<bpmn2:targetRef>DataObjectReference_8</bpmn2:targetRef>
		</bpmn2:dataOutputAssociation>
	</bpmn2:callActivity>

---

# assignTestParticipants

This activity allows the addition of users to a fixed participants based test.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesTestTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesTestTasks

## Method

assignTestParticipants

## Inputs

* anonUserList / usrIdList

	List of users to be added to the test.

* tstRefId

	Reference ID of the test

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_9" name="AssignTestParticipants">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesTestTasks.php" 
				api="ilModulesTestTasks" method="assignUsersToTest"/>
			</ilias:properties>
		</bpmn2:extensionElements>
		<bpmn2:incoming>SequenceFlow_9</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_10</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_13">
			<bpmn2:sourceRef>DataObjectReference_8</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataInputAssociation id="DataInputAssociation_16">
			<bpmn2:sourceRef>DataObjectReference_9</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
	</bpmn2:callActivity>

---