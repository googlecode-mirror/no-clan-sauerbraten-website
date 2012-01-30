

function StartUp()
{

    //establish connection for # of new messages display
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		MessageHandle=new XMLHttpRequest();
	} else {// code for IE6, IE5
		MessageHandle=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	// When we get the responce, update "messages" div
	MessageHandle.onreadystatechange=function() {
		if (MessageHandle.readyState==4 && MessageHandle.status==200)
		{
			document.getElementById("messages").innerHTML=MessageHandle.responseText;
			//We're finished, call the timer again.
			messages=setTimeout("UpdateMsgStatus()",10000);
		}
	}
	
	// This gets called in <body onload="StartUp()">
	// ONLY when user is logged in.
	// So we can load lots of timers here.
	messages=setTimeout("UpdateMsgStatus()",10000);
	
	// EXAMPLE:
	// var comments=SetTimeout("UpdateLatestComments()", 10000); 
} 



function UpdateMsgStatus()
{
	<!-- AJAX. Show incoming messages #. -->

	//send request
	MessageHandle.open("GET","/includes/check-messages.php",true);
	MessageHandle.send();
	//reset the timer
}
