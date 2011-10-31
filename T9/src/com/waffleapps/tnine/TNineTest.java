package com.waffleapps.tnine;

import java.io.IOException;

// Program to test T9 functionality
// Input: T9 encoded digit string
// Output: possible T9 decoded dictionary entries
public class TNineTest {

	public static void main(String[] args) throws IOException {
			String filename;
			
			if(args.length == 1)
				filename = args[0];
			else
				throw(new IllegalArgumentException("Class takes one file name argument"));
			
			TNineTree testTree = TNineTree.fromFile(filename);
			
			InputScreen IS = new InputScreen();
			String inputString = IS.getInput();

			String parsedString = testTree.parseInputString(inputString);
			
			System.out.println(parsedString);
	}
}
