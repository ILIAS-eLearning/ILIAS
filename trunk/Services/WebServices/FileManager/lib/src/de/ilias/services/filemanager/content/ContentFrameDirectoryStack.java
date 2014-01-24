/*
 * Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
 */
package de.ilias.services.filemanager.content;

import java.util.Stack;

/**
 * Class ContentFrameDirectoryStack
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class ContentFrameDirectoryStack {
	
	private static ContentFrameDirectoryStack instance = null;
	
	private Stack<DirectoryStackItem> localStack = new Stack<DirectoryStackItem>();
	private Stack<DirectoryStackItem> remoteStack = new Stack<DirectoryStackItem>();
	
	
	/**
	 * Get instance
	 * @return 
	 */
	public static ContentFrameDirectoryStack getInstance() {
		
		if(instance != null) {
			return instance;
		}
		return instance = new ContentFrameDirectoryStack();
	}
	
	/**
	 * Get stack by type
	 * @param type
	 * @return 
	 */
	public Stack<DirectoryStackItem> getStack(int type) {
		if(type == ContentFrame.FRAME_LEFT) {
			return getLocalStack();
		}
		else {
			return getRemoteStack();
		}
	}
	
	
	/**
	 * Get remote stack
	 */
	public Stack<DirectoryStackItem> getRemoteStack() {
		return localStack;
	}
	
	/**
	 * Get local stack
	 */
	public Stack<DirectoryStackItem> getLocalStack() {
		return localStack;
	}
	
	
}
