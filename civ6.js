function btnExecute(){
	var sndObj = {};
	sndObj["value2"] = document.getElementById("name").value;
	sndObj["value3"] = document.getElementById("turn").value;
	var encodedSndObj = JSON.stringify(sndObj);
	//document.getElementById("debug").innerHTML = encodedSndObj;
	try {
		$.ajax({
			type: "POST",
			url: "http://rasmus.today/civ6Save.php",
			data: encodedSndObj,
			success: function(obj, textstatus){
				/*var obj2 = JSON.parse(obj);
				if("error" in obj2){
					window.alert(obj2["error"]);
				}*/
					document.getElementById("debug").innerHTML = obj;
				
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}