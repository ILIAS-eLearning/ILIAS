
---

# assignAdminsToGroup

This activity allows the assignment of multiple group administrators.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesGroupTasks

## Method

assignAdminsToGroup

## Inputs

* grpRefId

	Group Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="AssignAdminsToGroup">
		<bpmn2:extensionElements>
		<ilias:properties>
			<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
			api="ilModulesCourseTasks" method="assignAdminsToGroup"/>
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

# assignMembersToGroup

This activity allows the assignment of multiple group members.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesGroupTasks

## Method

assignMembersToGroup

## Inputs

* grpRefId

	Group Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="AssignMembersToGroup">
		<bpmn2:extensionElements>
		<ilias:properties>
			<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
			api="ilModulesCourseTasks" method="assignMembersToGroup"/>
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

# readAdminsFromGroup

This activity reads group administrators.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesGroupTasks

## Method

readAdminsFromGroup

## Inputs

* grpRefId

	Group Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

## Outputs

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="ReadAdminsFromGroup">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" 
				api="ilModulesGroupTasks" method="readAdminsFromGroup"/>
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

# readMembersFromGroup

This activity reads group Members.

## Location

Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilModulesGroupTasks

## Method

readMembersFromGroup

## Inputs

* grpRefId

	Group Reference ID as seen in the URL of ILIAS (ref_id - Parameter)

## Outputs

* usrIdList

	A list ( an array ) of integer values which are supposed to be user-object-ids. 

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_5" name="ReadMembersFromGroup">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" 
				api="ilModulesGroupTasks" method="readMembersFromGroup"/>
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
