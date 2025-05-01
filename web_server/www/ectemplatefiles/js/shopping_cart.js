
	function additems(divname)
	{
	  var itemnamesel = document.getElementById('itemnamegroup');
	  var itemchosensel = document.getElementById('itemchosengroup');
	  var i;
	  for (i = itemnamesel.length - 1; i>=0; i--) {
		if (itemnamesel.options[i].selected) {

			var itemoptnew = document.createElement('option');
			itemoptnew.text = itemnamesel.options[i].text;
			itemoptnew.value = itemnamesel.options[i].value;

			  try {
				itemchosensel.add(itemoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				itemchosensel.add(itemoptnew); // IE only
			  }

			itemnamesel.remove(i);
		}
	  }
	  
	  var itemnamelist='';
	  var delim='';
	  for (i = 0; i<=itemchosensel.length - 1; i++) {
	    itemnamelist=itemnamelist+delim+itemchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById(divname);
	  mylist.value=itemnamelist;
	  
	}


	function removeitems(divname)
	{
	  var itemnamesel = document.getElementById('itemnamegroup');
	  var itemchosensel = document.getElementById('itemchosengroup');
	  var i;
	  for (i = itemchosensel.length - 1; i>=0; i--) {
		if (itemchosensel.options[i].selected) {

			var itemoptnew = document.createElement('option');
			itemoptnew.text = itemchosensel.options[i].text;
			itemoptnew.value = itemchosensel.options[i].value;

			  try {
				itemnamesel.add(itemoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				itemnamesel.add(itemoptnew); // IE only
			  }

			itemchosensel.remove(i);
		}
	  }
	  
	  var itemnamelist='';
	  var delim='';
	  for (i = 0; i<=itemchosensel.length - 1; i++) {
	    itemnamelist=itemnamelist+delim+itemchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById(divname);
	  mylist.value=itemnamelist;
	
	}

	function clearitems(divname,allitems)
	{
	  
		//console.log(allitems);

		var itemnamesel = document.getElementById('itemnamegroup');
		for (var i = itemnamesel.length - 1; i>=0; i--) {
			itemnamesel.remove(i);
		}

		var itemchosensel = document.getElementById('itemchosengroup');
		for (var i = itemchosensel.length - 1; i>=0; i--) {
			itemchosensel.remove(i);
		}

		for (var i = 0; i < allitems.length; i++) {
			var itemoptnew = document.createElement('option');
			itemoptnew.text = allitems[i].name;
			itemoptnew.value = allitems[i].gid;

			  try {
				itemnamesel.add(itemoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				itemnamesel.add(itemoptnew); // IE only
			  }
		}

	  mylist = document.getElementById(divname);
	  mylist.value='';
	  
	}
	

