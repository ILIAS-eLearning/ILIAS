/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/*
	JS port of ADL SeqActivityTree.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqActivityTree.java by ADL Co-Lab, which is licensed as:
	
	Advanced Distributed Learning Co-Laboratory (ADL Co-Lab) Hub grants you 
	("Licensee") a non-exclusive, royalty free, license to use, modify and 
	redistribute this software in source and binary code form, provided that 
	i) this copyright notice and license appear on all copies of the software; 
	and ii) Licensee does not utilize the software in a manner which is 
	disparaging to ADL Co-Lab Hub.

	This software is provided "AS IS," without a warranty of any kind.  ALL 
	EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING 
	ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE 
	OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS 
	SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF 
	USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO 
	EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, 
	PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, 
	INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE 
	THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE 
	SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH 
	DAMAGES.
*/

function SeqActivityTree(iCourseID, iLearnerID, iScopeID, iRoot)
{
	this.mCourseID = iCourseID;
	this.mLearnerID = iLearnerID;
	this.mScopeID = iScopeID;
	this.mRoot = iRoot;
	
	this.dsMap = new Object();
}

//this.SeqActivityTree = SeqActivityTree;
SeqActivityTree.prototype = 
{
	dataStoreLoc: null,
	mRoot: null,
	mValidReq: null,
	mLastLeaf: null,
	mScopeID: null,
	mCourseID: null,
	mLearnerID: null,
	mCurActivity: null,
	mFirstCandidate: null,
	mSuspendAll: null,
	mActivityMap: null,
	mObjSet: null,
	mObjMap: null,
	mObjScan: false,
	mDataScopedForAllAttempts: true,

	// trivial getter/setter
	getScopeID: function () { return this.mScopeID; },
	setRoot: function (iRoot) { this.mRoot = iRoot; },
	getRoot: function () { return this.mRoot; },
	setLastLeaf: function (iLastLeaf) { this.mLastLeaf = iLastLeaf; },
	getLastLeaf: function () { return this.mLastLeaf; },
	setValidRequests: function (iValidRequests) { this.mValidReq = iValidRequests; },
	getValidRequests: function () { return this.mValidReq; },
	getCurrentActivity: function () { return this.mCurActivity; },
	setCurrentActivity: function (iCurrent) {this.mCurActivity = iCurrent; },
	setFirstCandidate: function (iFirst) { this.mFirstCandidate = iFirst; },
	setSuspendAll: function (iSuspendTarget) { this.mSuspendAll = iSuspendTarget; },
	getSuspendAll: function () { return this.mSuspendAll; },
	getLearnerID: function () { return this.mLearnerID; },
	setCourseID: function (iCourseID) { this.mCourseID = iCourseID; },
	getCourseID: function () { return this.mCourseID; },

	setLearnerID: function (iLearnerID)
	{
		this.mLearnerID = iLearnerID;
	
		this.buildActivityMap();
		
		if (!(this.mActivityMap == null || iLearnerID == null))
		{
			for (var act in this.mActivityMap)
			{
				act.setLearnerID(iLearnerID);
			}
		}
	},
	
	setScopeID: function (iScopeID)
	{
		this.mScopeID = iScopeID;
		
		if (this.mScopeID != null)
		{
			this.buildActivityMap();
			
			if (this.mActivityMap != null)
			{
				for (var act in this.mActivityMap)
				{
					act.setScopeID(this.mScopeID);
				}
			}
		}
	},
	
	getFirstCandidate: function ()
	{
		if (this.mFirstCandidate == null)
		{
			return this.mCurActivity;
		}
		return this.mFirstCandidate;
	},
	
	getActivity: function (iActivityID)
	{
		// Make sure the Activity Map has been created
		if (this.mActivityMap == null)
		{
			this.buildActivityMap();
		}
		
		var temp = null;
		
		if (iActivityID != null)
		{
			temp = this.mActivityMap[iActivityID];
		}
		return temp;
	},

	getObjMap: function (iObjID)
	{
		var actSet = null;
		
		// If we haven't scanned the current tree for global objective IDs, do 
		// it now.
		if (!this.mObjScan)
		{
			this.scanObjectives();
			
			// Do not allow an empty set
			if (this.mObjMap != null)
			{
				if (this.mObjMap.length == 0)
				{
					this.mObjMap = null;
				}
			}
		}
		if (this.mObjMap != null)
		{
			actSet = this.mObjMap[iObjID];
		}
		return actSet;
	},

	getGlobalObjectives: function ()
	{
		// If we haven't scanned the current tree for global objective IDs, do 
		// it now.
		if (!this.mObjScan)
		{
			this.scanObjectives();
		}
		
		// Do not return an empty set
		if (this.mObjSet != null)
		{
			if (this.mObjSet.length == 0)
			{
				this.mObjSet = null;
			}
		}
		
		return this.mObjSet;
	},

	clearSessionState: function ()
	{
		this.mActivityMap = null;
	},

	setDepths: function ()
	{
		if (this.mRoot != null)
		{
			// Walk the activity tree, setting depths
			var walk =  this.mRoot;
			var depth = 0;
			
			var lookAt = new Array();
			var depths = new Array();
			
			while (walk != null)
			{
				// Check if the activity has children
				if (walk.hasChildren(true))
				{
					// Look at its children later
					lookAt[lookAt.length] = walk;
					depths[depths.length] = (depth + 1);
				}
				
				walk.setDepth(depth);
				
				// Walk the current level of the tree
				walk = walk.getNextSibling(true);
				
				// If there is not another sibling
				if (walk == null)
				{
					// Look one level deeper
					if (lookAt.length != 0)
					{
						// Remove the activity from the 'lookat' list
						walk = lookAt[0];
						//delete lookAt[0];
						lookAt.splice(0,1)
						// Remove the depth of the new activity from the 'depths' list
						depth = depths[0];
						//delete depths[0];
						depths.splice(0,1);
						// Start at the first child of the activity
						// todo: check
						temp = walk.getChildren(true);
						//rewrite using temp variable
						walk=temp[0];
					}
				}
			}
		}
	},

	setTreeCount: function ()
	{
		if (this.mRoot != null)
		{
			// Walk the activity tree, setting count
			var walk =  this.mRoot;
			var count = 0;
			
			var lookAt = new Array();
			
			while (walk != null)
			{
				count++;
				walk.setCount(count);
				
				// Save the activity for later
				if (walk.hasChildren(true))
				{
					lookAt[lookAt.length] = walk;
					walk = walk.getChildren(true)[0];
				}
				else
				{
					walk = walk.getNextSibling(true);
				}
				
				while (lookAt.length != 0 && walk == null)
				{
					// Remove the activity from the 'lookat' list
					walk = lookAt[0];
					//delete lookAt[0];
					lookAt.splice(0,1);
					walk = walk.getNextSibling(true);
				}
			}
		}
	},

	buildActivityMap: function ()
	{
		// Create or clear the activity map
		this.mActivityMap = new Object();
		if (this.mRoot != null)
		{
			this.addChildActivitiestoMap(this.mRoot);
		}
	},

	addChildActivitiestoMap: function (iNode)
	{
		// Make sure the node is not empty
		if (iNode != null)
		{
			var children = iNode.getChildren(true);
			var i = 0;
			
			// Add the current activity to the activity map
			this.mActivityMap[iNode.getID()] = iNode;
			
			// If the activity has children, add each child to the activity map
			if (children != null)
			{
				for (i = 0; i < children.length; i++)
				{
					this.addChildActivitiestoMap(children[i]);
				}
			}
		}
	},

	scanObjectives: function ()
	{
		// Walk the activity tree, recording all mapped global objectives
		var walk =  this.mRoot;
		var lookAt = new Array();
		
		while (walk != null)
		{
			// Check if the activity has children
			if (walk.hasChildren(true))
			{
				// Look at its children later
				lookAt[lookAt.length] = walk;
			}
			
			// Check if the activity references global objectives
			var objs = walk.getObjectives();
			
			if (objs != null)
			{
				for (var i = 0; i < objs.length; i++)
				{
					var obj = objs[i];
					
					if (obj.mMaps != null)
					{
						for (var j = 0; j < obj.mMaps.length; j++)
						{
							var map = obj.mMaps[j];
							var target = map.mGlobalObjID;
							
							// Make sure we haven't already added this objective
							if (this.mObjSet == null)
							{
								this.mObjSet = new Array();
								this.mObjSet[0] = target;
							}
							else
							{
								var found = false;
								
								for (var k = 0; k < this.mObjSet.length && !found; k++)
								{
									var id = this.mObjSet[k];
									found = (id == target);
								}
								if (!found)
								{
									this.mObjSet[this.mObjSet.length] = target;
								}
							}
							
							// If this is a 'read' objective add it to our obj map
							if ((map.mReadStatus || map.mReadMeasure || map.mReadCompletionStatus || map.mReadProgressMeasure)&& obj.mContributesToRollup)
							{
								if (this.mObjMap == null)
								{
									this.mObjMap = new Object();
								}
								
								var actList = this.mObjMap[target];
								
								if (actList == null)
								{
									actList = new Array();
								}
								
								actList[actList.length] = walk.getID();
								this.mObjMap[target] = actList;
							}
						}
					}
				}
			}
			
			// Walk the current level of the tree
			walk = walk.getNextSibling(true);
			
			// If there is not another sibling
			if (walk == null)
			{
				// Look one level deeper
				if (lookAt.length != 0)
				{
					// Remove the activity from the 'lookat' list
					walk = lookAt[0];
					//delete lookAt[0];
					lookAt.splice(0,1);
					// Start at the first child of the activity
					walk = walk.getChildren(true)[0];
				}
			}
		}
		this.mObjScan = true;
	},
	
	//Sets whether the data store collection persists for all attempts
	setDataScopedForAllAttempts: function (iAttributeValue) 
		{ 
			this.mDataScopedForAllAttempts = iAttributeValue; 
		},
	
	//Indicates if the data store collection is persisted for all attempts
	dataScopedForAllAttempts: function() 
		{ 
			return this.mDataScopedForAllAttempts; 
		},

	//Gets the activity map.  Returns Map of the activities
	getActivityMap: function()
	{
		if ( this.mActivityMap == null)
			{
				this.buildActivityMap();
			}
		return this.mActivityMap;
	}
};
