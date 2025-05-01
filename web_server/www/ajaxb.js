function sedtypeswitch(mysedtype){
	//alert(mytastype);
	if(mysedtype=='author'){
		document.getElementById('sedauthor').style.display='';
		document.getElementById('sedech').style.display='none';
	}else if(mysedtype=='ech'){
		document.getElementById('sedech').style.display='';
		document.getElementById('sedauthor').style.display='none';
	}
}

function changetas(mytastype){
	//alert(mytastype);
	if(mytastype=='volcanic'){
		document.getElementById('volcanictasdiv').style.display='';
		document.getElementById('plutonictasdiv').style.display='none';
	}else if(mytastype=='plutonic'){
		document.getElementById('plutonictasdiv').style.display='';
		document.getElementById('volcanictasdiv').style.display='none';
	}
}

function changerock(myrocktype){
	
	var myrocks=new Array("igneous",
							"metamorphic",
							"alteration",
							"vein",
							"ore",
							"sedimentary",
							"xenolith");
	for(i = 0; i < myrocks.length; i++){
		//alert(myrocks[i]);
		if(myrocks[i]==myrocktype){
			document.getElementById(myrocks[i]).style.display='';
		}else{
			document.getElementById(myrocks[i]).style.display='none';
		}
	}
}

function removeSpaces(string) {
	var tstring = "";
	string = '' + string;
	splitstring = string.split(" ");
	for(i = 0; i < splitstring.length; i++)
	tstring += splitstring[i];
	return tstring;
}


function removedashes(string) {
	var tstring = "";
	string = '' + string;
	splitstring = string.split("-");
	for(i = 0; i < splitstring.length; i++)
	tstring += splitstring[i];
	return tstring;
}


	//Event.observe(window, 'load', init, false);




	function DeselectAllList(CONTROL){
	for(var i = 0;i < document.getElementById(CONTROL).length;i++){
	document.getElementById(CONTROL).options[i].selected = false;
	}
	}



	function addrocks()
	{
	  var rocknamesel = document.getElementById('rocknamegroup');
	  var rockchosensel = document.getElementById('rockchosengroup');
	  var i;
	  for (i = rocknamesel.length - 1; i>=0; i--) {
		if (rocknamesel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rocknamesel.options[i].value;
			rockoptnew.value = rocknamesel.options[i].text;

			  try {
				rockchosensel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rockchosensel.add(rockoptnew); // IE only
			  }

			rocknamesel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById('hiddenrocknames');
	  mylist.value=rocknamelist;
	  
	}


	function removerocks()
	{
	  var rocknamesel = document.getElementById('rocknamegroup');
	  var rockchosensel = document.getElementById('rockchosengroup');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {
		if (rockchosensel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById('hiddenrocknames');
	  mylist.value=rocknamelist;
	
	}

	function clearrocks()
	{
	  var rocknamesel = document.getElementById('rocknamegroup');
	  var rockchosensel = document.getElementById('rockchosengroup');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
	  }

	  mylist = document.getElementById('hiddenrocknames');
	  mylist.value='';
	  
	}
	
	function showrocks()
	{
	
		mylist = document.getElementById('hiddenrocknames').value;
		alert(mylist);
	
	}



















	function addseds()
	{
	  var rocknamesel = document.getElementById('sednamegroup');
	  var rockchosensel = document.getElementById('sedchosengroup');
	  var i;
	  for (i = rocknamesel.length - 1; i>=0; i--) {
		if (rocknamesel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rocknamesel.options[i].value;
			rockoptnew.value = rocknamesel.options[i].text;

			  try {
				rockchosensel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rockchosensel.add(rockoptnew); // IE only
			  }

			rocknamesel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById('hiddensednames');
	  mylist.value=rocknamelist;
	  
	}


	function removeseds()
	{
	  var rocknamesel = document.getElementById('sednamegroup');
	  var rockchosensel = document.getElementById('sedchosengroup');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {
		if (rockchosensel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById('hiddensednames');
	  mylist.value=rocknamelist;
	
	}

	function clearseds()
	{
	  var rocknamesel = document.getElementById('sednamegroup');
	  var rockchosensel = document.getElementById('sedchosengroup');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
	  }

	  mylist = document.getElementById('hiddensednames');
	  mylist.value='';
	  
	}
















































	function addnames(myname)
	{
	  var rocknamesel = document.getElementById(myname+'namegrp');
	  var rockchosensel = document.getElementById(myname+'chosengrp');
	  var i;
	  for (i = rocknamesel.length - 1; i>=0; i--) {
		if (rocknamesel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rocknamesel.options[i].value;
			rockoptnew.value = rocknamesel.options[i].text;

			  try {
				rockchosensel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rockchosensel.add(rockoptnew); // IE only
			  }

			rocknamesel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById(myname+'hidden');
	  mylist.value=rocknamelist;
	  
	}


	function removenames(myname)
	{
	  var rocknamesel = document.getElementById(myname+'namegrp');
	  var rockchosensel = document.getElementById(myname+'chosengrp');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {
		if (rockchosensel.options[i].selected) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
		}
	  }
	  
	  var rocknamelist='';
	  var delim='';
	  for (i = 0; i<=rockchosensel.length - 1; i++) {
	    rocknamelist=rocknamelist+delim+rockchosensel.options[i].value;
	    delim=',';
	  }
	  mylist = document.getElementById(myname+'hidden');
	  mylist.value=rocknamelist;
	
	}

	function clearnames(myname)
	{
	  var rocknamesel = document.getElementById(myname+'namegrp');
	  var rockchosensel = document.getElementById(myname+'chosengrp');
	  var i;
	  for (i = rockchosensel.length - 1; i>=0; i--) {

			var rockoptnew = document.createElement('option');
			rockoptnew.text = rockchosensel.options[i].value;
			rockoptnew.value = rockchosensel.options[i].text;

			  try {
				rocknamesel.add(rockoptnew, null); // standards compliant; doesn't work in IE
			  }
			  catch(ex) {
				rocknamesel.add(rockoptnew); // IE only
			  }

			rockchosensel.remove(i);
	  }

	  mylist = document.getElementById(myname+'hidden');
	  mylist.value='';
	  
	}



















