// JavaScript Document

function offMsg(){ document.getElementsByClassName('xmsg').item('p').style.display = 'none'}
var t=setTimeout("offMsg()",4000)





$(document).ready(function(){
	
	function getRows(){
		i=0
		$("#listagem tr").each(function () {i++;})
		return i
	}
	function removeCampo() {
		$(".bt_del").unbind("click")
		$(".bt_del").bind("click", function () {			
			if (getRows()) {
				$(this).parent().parent().remove()
			}
		})
	}	
	removeCampo()

	$("#add").click(function(e) {
		var row = getRows()
		$('<tr>'+                   
					'<td><input name="file'+row+'[o]" type="text" value="/origem"></td>'+
					'<td><input name="file'+row+'[d]" type="text" value="/destino/file.phar"></td>'+
					'<td><input name="file'+row+'[i]" type="text" value="stub.php"></td>'+
					'<td title="Compactar o arquivo: On/Off"><input name="file'+row+'[z]" type="checkbox" value="checked" checked></td>'+
					'<td><input name="del" class="bt_del" type="button" value="" title="Excluir este recurso." /></td>'+					
				'</tr>').insertAfter("#listagem tr:last")		
		removeCampo()
	})



})