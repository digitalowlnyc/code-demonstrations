package com.waffleapps.tnine;
import java.util.ArrayList;


public class TNineFunctions {
	
	// Returns the T9 encoding for a string
	public static ArrayList<Integer> encodeString(String str){
		
		ArrayList<Integer> encoding = new ArrayList<Integer>();
		
		for(char c: str.toCharArray())
		{
			TNineEncodings translation = TNineEncodings.value(Character.toString(c).toLowerCase());
			encoding.add(translation.keypadNumber);
		}
		
		return encoding;
	}
}
