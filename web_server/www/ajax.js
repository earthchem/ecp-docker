	function greet(updatebox, pkey){ //document.write(document.getElementById('boxy').length); exit;
		tb_remove();

		var mystring = '';
		var mydelim = '';

		for (var i = 0;i < document.getElementById('boxy').length;i++){
			if(document.getElementById('boxy').options[i].selected == true)
			{
				mystring=mystring+mydelim+document.getElementById('boxy').options[i].value;
				mydelim=',';
			}
		}

		AjaxRequest.get(
			{
				'url':'greeting.php'
				,'parameters':{ 'boxy':escape(mystring), 'item':updatebox, 'pkey':pkey }
				//,'onSuccess':function(req){ updatebox.value=req.responseText; }
				//,'onSuccess':function(req){ alert('Success!'); }
				//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
				,'onSuccess':function(req) { 
				//document.getElementById(updatebox).innerHTML = req.responseText; // This puts the output from the request into a hidden form field named something like SIO2
 
				theText = req.responseText; // the text that was output from the request 
				document.chemistry_form.elements[updatebox + '_methods'].value = theText; // This updates the hidden form field with the output from the request
				document.getElementById(updatebox + '_methods_div').innerHTML = theText; // This puts the output from the request into a visible element
			
				}
				,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
			}
		);
	}

	
	function greetclear(updatebox, pkey){
		AjaxRequest.get(
			{
				'url':'greetingclear.php'
				,'parameters':{ 'item':updatebox, 'pkey':pkey }
				//,'onSuccess':function(req){ updatebox.value=req.responseText; }
				//,'onSuccess':function(req){ alert('Success!'); }
				//,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = req.url; }
				,'onSuccess':function(req) { document.getElementById(updatebox).innerHTML = '&nbsp;'; }
				,'onError':function(req){ alert('Error!\nStatusText='+req.statusText+'\nContents='+req.responseText);}
			}
		);

	}
	
	function clearmethods(updatebox){
		document.chemistry_form.elements[updatebox + '_methods'].value = ''; // This puts a null value in the hidden form field 
		document.getElementById(updatebox + '_methods_div').innerHTML = ''; // This puts a null value in the display div 
	}
	

