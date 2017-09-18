# Creating a task

---

## Introduction

Pretty much everything happening in applications is the automation and support for business operations. The exact order 
of events and actions following them is called a process and such is described as a workflow, regardless if a human or
a machine is reacting to events, most of the time even both. The workflow engine for ILIAS is a tool to execute such 
workflows within the LMS to allow for ideal customization of the software to find its place seamlessly in the hosting
organizations processes.

The workflow engine receives a workflow in the form of a diagram, essentially a drawing of what should happen and when.
To allow for everyone to learn how draw these diagrams, a well known standard is used, BPMN 2.0, the "Business Process 
Modeling Notation". There is a variety of tools available for the creation of works in this visual language, both open 
source and proprietary.

After creating the workflow diagram, which results in an XML file combining both the flowcontrol of the process as well
as the visual representation, a few simple modifications - additions - are required to close the gap between the generic 
workflow model representation and the specifics of the ILIAS workflow engine. A task may be named completely at the 
leisure of the workflow creator, the specific action that makes ILIAS "move" needs to be added so the runtime 
environment in LIAS - the workflow engine itself - knows what to do. These modifications are intended by the 
authors of the BPMN 2.0 standards, so the files created are still positively validated. A typical workflow goes through
stages of design, with a purely visual model at first and in later stages, detail and execution instruction are added.

Based on this, ILIAS supports tasks to manipulate the LMS. This document describes how a new task for ILIAS can be 
created in the sense that authors of process diagrams will be able to trigger certain parts of ILIAS by adding some 
information to a task-symbols markup in a workflow.

This document will not dive into details how to create a workflow or how to add markup elements, it will provide a 
sample, though, to make the document round.

## The task "readLearnersFromCourse"

Consider the following task, located in class ilModulesCourseTasks in file 
Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php :

	/**
	 * @param ilNode $context
	 * @param array  $params
	 *
	 * @return array
	 */
	public static function readLearnersFromCourse($context, $params)
	{
		require_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$input_params = $params[0];
		$output_params = $params[1];

		$participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
		$learners = $participants->getMembers();
		$retval = array($output_params[0] => $learners);

		return $retval;
	}

This is a complete and tested working task. Here is a list of requirements for creating a task:

1. The method MUST be static
2. The first parameter MUST accept an instance of a context, for a workflowengine enabled method this is an instance of ilNode.
3. The second parameter MUST accept an associative array
4. The method MAY return values, but MUST do so as an associative array.

## Data roles

These arrays need to be looked at. The BPMN2 does not support ordered inputs, so there is no way to plug in one data 
connection as first and another as second parameter. To resolve the problem of task with variadic inputs, the concept
of "data roles" was introduced. The data items that go into and out of a task need to have data roles so the process 
knows wherefrom and whereto data is directed.

The sample above reads learners from a course and so the course must be communicated. The first element of the input
parameters holds this:

	$params[0} = array('crsRefId' => 4711);

This is extracted and passed into the function of the task. A new task must know which kind of data it needs to operate
and expect these inputs to be given. It is the responsibility of a task to accept relevant roles as input, even if they
are multiple and a task may react flexible on the nature of data given to it.

Here is a sample of that concept from assignUsersToTest in class ilModulesTestTask, file
Services/WorkflowEngine/classes/tasks/class.ilModulesTestTasks.php :

		$input_params = $params[0};

		if(isset($input_params['usrIdList']))
		{
			$usr_id_list = $input_params['usrIdList'];
		}
		if(isset($input_params['discloseMap']))
		{
			foreach($input_params['discloseMap'] as $map_entry)
			{
				$usr_id_list[] = $map_entry['Anon User'];
			}
		}

This bit of code either takes a usrIdList role, which is an array of integers and assigns this to the internal
$usr_id_list and if it finds a discloseMap-role, it iterates over its contents, taking a part of that array into the
$usr_id_list.

## Context

Usually, the first parameter of the task - context - should be safely ignored. Using it means that not all necessary
parameters were given in the second paramater, $params. Still this context must be given to allow a task to inspect and
modify the surrounding execution context. 

This is already thought ahead for the service discovery: The task may have a context, but if the context needs 
inspection or modification, the context must be marked as supported (using docblock-markers) and within the task, it is
required that the actual context is checked. Consider this "fictional" example:

	if($context instanceof ilNode && $params[1]['seriouslyBroken'] == 1)
	{
		/** @var $context ilNode */
		$context->getContext()->stopWorkflow();
	}

In this example, a task stops the workflow from which it was called if some condition indicates this.
At the time of this writing, though, there is no service discovery yet and the tasks are solely used for purposes of the
workflow engine. 

## Doing the task

The task can now operate with the data and do its work. Tasks should be lean on this and not introduce significant new
behaviors. The average number of active lines other than parameter-juggling is seen at around four. There may be
exceptions, though.

In our example, this is what it looks like:

	$participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
	$learners = $participants->getMembers();

Two lines, add another one for the require above and if the call doesn't go to a static method, getting an instance 
justifies another line.

## Returning values

Finally, the task may return values. To do that, an associative array is to be created and filled with roles.
This can be as easy as assigning a result from a method call:

	$retval = array($output_params[0] => $learners); // $output_params is earlier made from $params[1].

	return $retval;

The output params are a list of roles in the second element of params array:

		$params[1] = array('usrIdList');

If there is more than one data role on the output, this may mean 

* there may be some more processing to find which output data goes where
* there may be a repetetive returning the same data into return values
* there may be a repetetive returning the same data in different forms into return values.

## Example

Now that we have a task, here is how it is used in a model.
At first, the drawing will look like this at the according task that was drawn:

	<bpmn2:callActivity id="CallActivity_1" name="I am the example">
		<bpmn2:incoming>SequenceFlow_1</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_2</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_1</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:dataOutputAssociation id="DataOutputAssociation_1">
			<bpmn2:targetRef>DataObjectReference_2</bpmn2:targetRef>
		</bpmn2:dataOutputAssociation>
	</bpmn2:callActivity>

The call activity has an id that is automatically given to it by the modelling tool, the name is a standard property
that is usually set by the creator of the diagram, it is what the icon in the graph is captioned with.
Then there are incoming and outgoing tags. These represent incoming and outgoing control flow, solid lines in the 
diagram.
Then there is a node dataInputAssociation and this association points to a data object reference. A data object exists
in the background of the process and is accessed through such references. This reference - where it is modelled - may 
look like this:

	<bpmn2:dataObjectReference id="DataObjectReference_1" name="Data Object 1 Reference" dataObjectRef="DataObject_1"/>

This reference points to a data object "DataObject_1" in the example anf that looks like this:

	<bpmn2:dataObject id="DataObject_1" name="Data Object 1">
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:dataobject role="crsRefId" type="integer"/>
			</ilias:properties>
		</bpmn2:extensionElements>
	</bpmn2:dataObject>

This data object includes properties specific to ILIAS and mark the data-role of the value in this data object a 
crsRefId. When a value is assigned to this data-object in that role, it will be forwarded to associated tasks as this.

The data output association works in a very similar way, just it is dealing with the return value of the task and stores
what is coming back into the data object that is referenced.

To now use our task, we need to add mark up for the ilias-specific parts so the task looks finalized like this:

	<bpmn2:callActivity id="CallActivity_1" name="I am the example">
		<bpmn2:incoming>SequenceFlow_1</bpmn2:incoming>
		<bpmn2:outgoing>SequenceFlow_2</bpmn2:outgoing>
		<bpmn2:dataInputAssociation id="DataInputAssociation_1">
			<bpmn2:sourceRef>DataObjectReference_1</bpmn2:sourceRef>
		</bpmn2:dataInputAssociation>
		<bpmn2:extensionElements>
			<ilias:properties>
				<ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" 
				api="ilModulesCourseTasks" method="readLearnersFromCourse"/>
			</ilias:properties>
		</bpmn2:extensionElements>
	</bpmn2:callActivity>

Within the task a new tag extensionElements is introduced and within there the properties of ilias live. In this case
it's a libraryCall tag that has attributes for 

* location

	the classes filepath

* api

	the classes name, and

* method

	the method name.

The rest of the mapping of data input associations and data output associations is done automatic.