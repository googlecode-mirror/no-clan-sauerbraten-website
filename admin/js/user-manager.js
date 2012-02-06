function StartUp() {
	<!-- Create AJAX handle, response routine & call the function to show the users -->
	<!-- Every function updates the "users" div, so just 1 handle and 1 response routine -->

	//establish connection
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		UserHandle=new XMLHttpRequest()
	} else {// code for IE6, IE5
		UserHandle=new ActiveXObject("Microsoft.XMLHTTP");
	}
	// When we get the responce, update "users" div
	UserHandle.onreadystatechange=function() {
		if (UserHandle.readyState==4 && UserHandle.status==200)
		{
			document.getElementById("users").innerHTML=UserHandle.responseText
		}
	}

	UserFilter('All')
}

function UpdateUser(formid) {
	<!-- When one user type value changes, it submits the form for that value alone. -->
	<!-- but first get confirmation -->
	
	var username=formid.name
	var idUser=formid.id
	var type=formid.value
	//get (user) filter value to pass
	var filter=document.getElementById("type").value
    var answer = confirm("Update " + username + "'s type to " + type +"?")

    if (answer){
		//send request
		UserHandle.open("GET","/admin/users.php?idUser=" + idUser + "&type=" + type +"&filter="+filter,true)
	} else {
		//else just refresh (or type would be now displayed wrong)
		UserHandle.open("GET","/admin/users.php?filter="+filter,true)
	}
		UserHandle.send()
}

function outlimbo(idUser, username) {
	<!-- Gets the user out of penalty box (Limbo). They can login now. -->
	<!-- but first get confirmation -->
	
	var user=username
	var filter = document.getElementById('type').value
    var answer = confirm("Release " + user + " from Limbo?")
    if (answer){
		
		//get (user) filter value to pass
		filter=document.getElementById("type").value;
		//send request
		UserHandle.open("GET","users.php?filter="+filter+"&outlimbo="+idUser,true)
		UserHandle.send()
	}
}

function inlimbo(idUser, username) {
	<!-- Places the user into penalty box (Limbo). They canNOT login now. -->
	<!-- but first get confirmation -->
    
	//get (user) filter value to pass
	var filter=document.getElementById("type").value
    var answer = confirm("Place " + username + " into Limbo?");

    if (answer){
		var reason=prompt("You must enter a reason")
		//send request
		UserHandle.open("GET","users.php?filter="+filter+"&inlimbo="+idUser+"&reason="+reason,true)
		UserHandle.send()
	}
}

function UserInfo(selectid) {
	<!-- Changes info displayed in users table -->

	//get (user) filter value to pass
	var filter=document.getElementById("type").value
	
	//send request
	UserHandle.open("GET","users.php?filter="+filter+"&info="+selectid.value,true)
	UserHandle.send()
}

function outEditors(idEditor, username) {
	<!-- Gets the user out of editors table. -->
	<!-- but first get confirmation -->
	
	user=username;
    var answer = confirm("Remove " + user + " from Editors?")

    if (answer){
		//get (user) filter value to pass
		var filter=document.getElementById("type").value
		//send request
		UserHandle.open("GET","users.php?filter="+filter+"&outEditor="+idEditor,true)
		UserHandle.send()
	}
}

function inEditors(idUser, username) {
	<!-- Places the user into editors. -->
	<!-- but first get confirmation -->
    
    var answer = confirm("Place " + username + " into Editors?")
    if (answer){
		//get (user) filter value to pass
		var filter=document.getElementById("type").value
		//send request
		UserHandle.open("GET","users.php?filter="+filter+"&inEditor="+idUser,true)
		UserHandle.send()
	}
}

function UserFilter(UserType) {
	<!-- AJAX. Show registered users with filter by user type (admin, member, friend, user). -->
    
    var filter=UserType
    
	//send request
	UserHandle.open("GET","users.php?filter="+filter,true)
	UserHandle.send()
}
