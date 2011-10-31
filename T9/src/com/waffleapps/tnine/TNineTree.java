package com.waffleapps.tnine;
import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.io.StringWriter;
import java.util.ArrayList;

// Represents a T9 "tree" (or trie). Each level represents
// a possible character position for words in the T9
// dictionary.
public class TNineTree {
	private TNineNode Root;
	
	public TNineTree()
	{
		this.Root = new TNineNode();
	}
	
	// Inserts a new word into the T9 dictionary
	public void insertDictionaryEntry(String entry)
	{
		ArrayList<Integer> TNineEncoding = TNineFunctions.encodeString(entry);
		
		TNineNode currentLevel = Root;
		
		for(Integer i : TNineEncoding)
		{
			if(currentLevel.childNodes[i] == null)
			{
				currentLevel.childNodes[i] = new TNineNode();
			}
						
			currentLevel = currentLevel.childNodes[i];
		}
		
		currentLevel.dictionaryValues.add(entry);
	}
	
	// For a sequence of input integers, returns the
	// matching T9 dictionary values
	public ArrayList<String> getValuesforEncoding(ArrayList<Integer> encoding)
	{
		TNineNode currentLevel = Root;
		
		for(Integer i : encoding)
		{
			if(currentLevel.childNodes[i] == null)
				return new ArrayList<String>();
			
			currentLevel = currentLevel.childNodes[i];
		}
		
		return currentLevel.dictionaryValues;
	}
	
	// Takes an input string, representing a T9 sentence,
	// and returns a result string which represent the 
	// possible words for each block of numbers.
	// Results depend on the number of matching dictionary words;
	// 0 matches : return the original input word
	// 1 match - return the literal string for that match
	// 2+ matches - return a bracket enclosed, comma delimited string of all matches
	public String parseInputString(String inputString)
	{
		StringWriter s = new StringWriter();
		
		for(String word : inputString.split(" "))
		{
			ArrayList<Integer> input = new ArrayList<Integer>();
			
			for(Character c : word.toCharArray())
				input.add(Integer.parseInt(c.toString()));
			
			ArrayList<String> values = this.getValuesforEncoding(input);
			
			if(values.size() == 0)
			{
				s.append(word); // return original input if no decoding available
			}
			else if(values.size() == 1)
			{
				s.append(values.get(0));
			}
			else // multiple dictionary entries found
			{
				s.append("[");
				
				int counter = 0;
				for(String value : values)
				{
					s.append(value);
					
					counter++;
					
					if(counter != values.size())
						s.append(",");
				}
				s.append("]");
			}
			s.append(" ");
		}
		
		return s.toString();
	}
	
	// Seeds the T9 tree from a file where each word is on it's own line,
	// and the values are followed by commas
	public static TNineTree fromFile(String file) throws IOException
	{
		TNineTree t = new TNineTree();

		BufferedReader reader = new BufferedReader(new FileReader(file));
		String text = null;
		
		while ((text = reader.readLine()) != null) {
			String filteredEntry = text.replace(",", "");
			
			t.insertDictionaryEntry(filteredEntry);
		}

		return t;
	}
	
	// A node in the T9 tree holds an array of child nodes. The position
	// of the child nodes in the array map to the corresponding T9 encoded values
	// For example, if a child exists in childNodes[2], it represents a possible
	// a/b/c character in that position (level) of the dictionary values
	private class TNineNode{
		public TNineNode[] childNodes = new TNineNode[10];
		public ArrayList<String> dictionaryValues = new ArrayList<String>();			
	}
}