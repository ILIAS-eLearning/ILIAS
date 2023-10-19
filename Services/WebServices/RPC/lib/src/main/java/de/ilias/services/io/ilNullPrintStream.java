package de.ilias.services.io;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.PrintStream;

public class ilNullPrintStream extends PrintStream {
	
	public ilNullPrintStream(File file) throws FileNotFoundException {
		super(file);
	}
	public void write(byte[] buf, int off, int len) {}
	public void write(int b) {}
	public void write(byte [] b) {}
}
