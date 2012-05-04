

function StartUp()
{

    //establish connection for # of new messages display
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		MessageHandle=new XMLHttpRequest()
		CommentHandle=new XMLHttpRequest()
	} else {// code for IE6, IE5
		MessageHandle=new ActiveXObject("Microsoft.XMLHTTP")
		CommentHandle=new ActiveXObject("Microsoft.XMLHTTP")
	}
	
	// When we get the responce, update "messages" div
	MessageHandle.onreadystatechange=function() {
		if (MessageHandle.readyState==4 && MessageHandle.status==200)
		{
			document.getElementById("messages").innerHTML=MessageHandle.responseText
			//We're finished, set the timer again.
			messages=setTimeout("UpdateMsgStatus()",10000)
		}
	}
	
	// Update Comments div
	CommentHandle.onreadystatechange=function() {
		if (CommentHandle.readyState==4 && CommentHandle.status==200)
		{
			document.getElementById("last_comments").innerHTML=CommentHandle.responseText
			//We're finished, set the timer again.
			comments=setTimeout("UpdateComments()",10000)
		}
	}
	
	// This gets called in <body onload="StartUp()">
	// ONLY when user is logged in.
	// So we can load lots of timers here.
    UpdateMsgStatus()
    UpdateComments()
    messages=setTimeout("UpdateMsgStatus()",10000)
    comments=setTimeout("UpdateComments()",10000)	
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
