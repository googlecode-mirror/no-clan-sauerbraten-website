// Loads sections like Latest Comments and updates them
// IsMember=1 loads additional member only features like Messages
function StartUp(IsMember)
{

	var member=IsMember

    //establish connection for # of new messages display
	if (window.XMLHttpRequest) {// code for new browsers
		MessageHandle=new XMLHttpRequest()
		CommentHandle=new XMLHttpRequest()
		LoginsHandle =new XMLHttpRequest()
	} else {// code for IE6, IE5
		MessageHandle=new ActiveXObject("Microsoft.XMLHTTP")
		CommentHandle=new ActiveXObject("Microsoft.XMLHTTP")
		LoginsHandle =new ActiveXObject("Microsoft.XMLHTTP")
	}
	
    if (member==1) {
		// When we get the responce, update "messages" div
		MessageHandle.onreadystatechange=function() {
			if (MessageHandle.readyState==4 && MessageHandle.status==200)
			{
				document.getElementById("messages").innerHTML=MessageHandle.responseText
				//We're finished, set the timer.
				messages=setTimeout("UpdateMsgStatus()",10000)
			}
		}

		UpdateMsgStatus()
	} // end if (member==1)
	
	// Update Comments div
	CommentHandle.onreadystatechange=function() {
		if (CommentHandle.readyState==4 && CommentHandle.status==200)
		{
			document.getElementById("last_comments").innerHTML=CommentHandle.responseText
			//We're finished, set the timer again.
			comments=setTimeout("UpdateComments()",10000)
		}
	}

	// Update last_seen div (latest logins).
	// This one is updated once every minute.
	LoginsHandle.onreadystatechange=function() {
		if (LoginsHandle.readyState==4 && LoginsHandle.status==200)
		{
			document.getElementById("last_seen").innerHTML=LoginsHandle.responseText
			//We're finished, set the timer again.
			logins=setTimeout("UpdateLogins()",60000)
		}
	}
	// This gets called in <body onload="StartUp()">
	// Calling the functions sets the timers too, once they reply.
    UpdateComments()
    UpdateLogins()
	
} 

function UpdateMsgStatus()
{
	<!-- AJAX. Show incoming messages #. -->
	//send request
	MessageHandle.open("GET","/includes/check-messages.php",true)
	MessageHandle.send()
}

function UpdateComments()
{
	<!-- AJAX. Show latest comments. -->
	//send request
	CommentHandle.open("GET","/includes/check-comments.php",true)
	CommentHandle.send()
}

function UpdateLogins()
{
	<!-- AJAX. Show latest logins. -->
	//send request
	LoginsHandle.open("GET","/includes/check-logins.php",true)
	LoginsHandle.send()
}
