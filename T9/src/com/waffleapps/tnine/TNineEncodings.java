package com.waffleapps.tnine;

import java.util.HashMap;

public enum TNineEncodings {
	a("a", 2),
	b("b", 2),
	c("c", 2),
	d("d", 3),
	e("e", 3),
	f("f", 3),
	g("g", 4),
	h("h", 4),
	i("i", 4),
	j("j", 5),
	k("k", 5),
	l("l", 5),
	m("m", 6),
	n("n", 6),
	o("o", 6),
	p("p", 7),
	q("q", 7),
	r("r", 7),
	s("s", 7),
	t("t", 8),
	u("u", 8),
	v("v", 8),
	w("w", 9),
	x("x", 9),
	y("y", 9),
	z("z", 9),
	comma("," , 1),
	apostrophe("'" , 1);
	
	public static HashMap<String,Integer> mappings = new HashMap<String,Integer>();
	
	public int keypadNumber;
	private String character;
	
    TNineEncodings(String character, int keypadNumber) {
        this.keypadNumber = keypadNumber;
        this.character = character;
    }
    
    public static TNineEncodings value(String c){
    	TNineEncodings translation = null;
    	
    	
		for( TNineEncodings enc : TNineEncodings.values() )
		{
			if(enc.character.equalsIgnoreCase(c))
				translation = enc;
		}
		
		if(translation == null)
			throw(new IllegalArgumentException("Character is not part of defined T9 encodings"));
    	
		return translation;
    }
}
