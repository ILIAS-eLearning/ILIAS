---

# createAnonymousUsers

This activity allows the creation of anonymous users from a list of users, which may then later be repersonalized.

## Location

Services/WorkflowEngine/classes/tasks/class.ilServicesUserTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilServicesUserTasks

## Method

createAnonymousUsers

## Inputs

* usrIdList

	List of users for which anonymized users are to be created.

## Outputs

* discloseMap

	Associative array of this structure:


	$discloseMap[] = array(
		'Original User' => $user_id,
		'Original Login' => $login,
		'Original Firstname' => $firstname,
		'Original Lastname' => $lastname,
		'Original Matriculation' => $matriculation,
		'Original Gender' => $gender,
		'Original EMail' => $email,
		'Anon User' => $anon_id,
		'Anon Login' => $anon_login,
		'Anon Password' => $anon_password
	);

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_10" name="PseudonymizeUsers">
		<bpmn2:incoming>SequenceFlow_12</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_13</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_15">
			<bpmn2:sourceRef>DataObjectReference_6</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataOutputAssociation id="DataOutputAssociation_6">
			<bpmn2:targetRef>DataObjectReference_9</bpmn2:targetRef>
		</bpmn2:dataOutputAssociation>
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilServicesUserTasks.php" 
				api="ilServicesUserTasks" method="createAnonymousUsers"/>
			</ilias:properties>
		</bpmn2:extensionElements>
	</bpmn2:callActivity>

---

# repersonalizeUsers

This activity allows the repersonaliziation or anonymous users from a disclose-map.

## Location

Services/WorkflowEngine/classes/tasks/class.ilServicesUserTasks.php
Temporary Location: Will be moved to location under control of the relevant maintainer

## API

ilServicesUserTasks

## Method

repersonalizeUsers

## Inputs

* discloseMap

	Associative array of this structure:


	$discloseMap[] = array(
		'Original User' => $user_id,
		'Original Login' => $login,
		'Original Firstname' => $firstname,
		'Original Lastname' => $lastname,
		'Original Matriculation' => $matriculation,
		'Original Gender' => $gender,
		'Original EMail' => $email,
		'Anon User' => $anon_id,
		'Anon Login' => $anon_login,
		'Anon Password' => $anon_password
	);

## Outputs

None.

## Sample Modelling / Usage

	<bpmn2:callActivity id="CallActivity_11" name="RepersonalizeUsers">
		<bpmn2:incoming>SequenceFlow_15</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_16</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_17">
			<bpmn2:sourceRef>DataObjectReference_9</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilServicesUserTasks.php" 
				api="ilServicesUserTasks" method="repersonalizeUsers"/>
			</ilias:properties>
		</bpmn2:extensionElements>
	</bpmn2:callActivity>

---