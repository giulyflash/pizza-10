// JavaScript Document

function offMsg(){ document.getElementsByClassName('xmsg').item('p').style.display = 'none'}
var t=setTimeout("offMsg()",4000)





$(document).ready(function(){
	
	function removeCampo() {
		$(".bt_del").unbind("click")
		$(".bt_del").bind("click", function () {
			i=0
			$("#listagem tr").each(function () {i++;})
			if (i>2) {
				$(this).parent().parent().remove()
			}
		})
	}	
	removeCampo()
	
	

	$("#add").click(function(e) {
		$("#listagem tr:last").clone().insertAfter("#listagem tr:last")
		removeCampo()
	})



})