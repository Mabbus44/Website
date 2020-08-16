//Change language
function changeLanguage(langID){
	try {
		$.ajax({
			type: "POST",
			url: "../Functions/changeLanguage.php",
			data: {langID: langID},
			success: function(obj, textstatus){
				location.reload();
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}
