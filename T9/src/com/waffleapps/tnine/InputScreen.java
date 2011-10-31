package com.waffleapps.tnine;
import javax.swing.JFrame;
import javax.swing.JOptionPane;
import javax.swing.JPanel;

// Generic input screen
public class InputScreen extends JPanel{

	InputScreen(){
	}
	
	String getInput()
	{
		JFrame frame = new JFrame();
		
		String s = (String)JOptionPane.showInputDialog(
		                    frame,
		                    "Enter numeric input to see T9 output",
		                    "Customized Dialog",
		                    JOptionPane.PLAIN_MESSAGE,
		                    null,
		                    null,
		                    "");
		
		s = (s == null) ? "" : s;
		
		for(Character c : s.toCharArray())
		{
			try
			{
				if(!Character.isSpaceChar(c))
					Integer.parseInt(c.toString());
			} catch( NumberFormatException e)
			{
				throw( new IllegalArgumentException( "T9 input for decoding must be integer string") );
			}
		}
		
		return(s);
	}
}
