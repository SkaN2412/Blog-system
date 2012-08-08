$(document).ready(function() {
	$("img.remove").click(function() {
		var filename = $(this).parent("td").prev().prev().text();
		if (confirm("Вы точно хотите удалить файл "+filename+"?") == false) return false;
		$.ajax({
			url: "?wd=admin&id=uploader&action=remove",
			type: "post",
			data: {filename: filename},
			dataType: "html",
			success: function (html) {
				$("div#scroll").html(html);
			}
		});
		return false;
	});
});