/*
 * Don't forget to edit this file before using!
 */

$(function(){
	$("#showaddform").click(function(){
		$(this).hide();
		$("#add").slideDown("slow");
		$("#add > #text").redactor();
		
		$("#add input[type=reset]").click(function(){
			$(this).unbind("click");
			$("#add").slideUp("slow");
			$("#showaddform").show();
		});
	});
	
	$("#add").submit(function(){
		$(this).children().each(function(){
			$(this).attr("disabled", "disabled");
		});
		var $self = $(this),
			$name = $(this['name']).val(),
			$text = $(this['text']).val();
		if ($name == "" || $text == "") {
			$self.before("<div class=\"error\">Некоторые поля не заполнены!</div>");
			$(this).children().each(function(){
				$(this).removeAttr("disabled");
			});
			setTimeout("$(\"div.error\").remove()", 5000);
			return false;
		}
		$.ajax({
			url: $self.attr("action"),
			type: "post",
			data: {name: $name, text: $text},
			dataType: "html",
			success: function(html){
				$self.replaceWith(html);
				setTimeout("document.location.href = document.location.href", 3000);
			}
		});
		return false;
	});
	
	$("div.name").click(function(){
		if ($(this).parent().next("div.text").is(":hidden") == true)
		{
			var $id = $(this).parents("div.entry").attr("id");
			$(this).parent().next("div.text").load("?wd=admin&id=article&action=load&article="+$id).slideDown();
		} else {
			$(this).parent().next("div.text").slideUp("slow").empty();
		}
	});
	
	$("button.reduct").click(function(){
		$.ajaxSetup({
			async: false
		});
		$("div.text:visible").each(function(){
			$(this).slideUp("slow").empty();
		});
		$(this).parents("div.header").next("div.text").load("?wd=admin&id=article&action=loadforedit&article="+$(this).parents("div.entry").attr("id"));
		var $parent = $(this).parents("div.entry"),
			$name = $(this).parents("div.entry").children("div.header").children("div.name").text(),
			$text = $(this).parents("div.entry").children("div.text").text();
		$parent.wrap("<form id=\"edit\" name=\"edit\" action=\"?wd=admin&id=article&action=edit&article="+$parent.attr("id")+"\" />");
		$parent.children("div.header").children("div.name").html("<input type=\"text\" id=\"name\" name=\"name\" value=\""+$name+"\" />");
		$parent.children("div.text").empty().html("<textarea id=\"text\" name=\"text\">"+$text+"</textarea>").slideDown("slow");
		$("textarea#text").redactor();
		$(this).nextAll("button.remove").replaceWith("<input type=\"reset\" value=\"Отмена\" />");
		$(this).replaceWith("<input type=\"submit\" value=\"Отправить\" />");
		
		$("input[type=reset]").click(function(){
			$(this).unbind("click");
			document.location.href = document.location.href;
		});
		
		$("#edit").submit(function(){
			$(this).children().each(function(){
				$(this).attr("disabled", "disabled");
			});
			var $self = $(this),
				$name = $(this['name']).val(),
				$text = $(this['text']).val();
			if ($name == "" || $text == "") {
				$self.before("<div class=\"error\">Некоторые поля не заполнены!</div>");
				$(this).children().each(function(){
					$(this).removeAttr("disabled");
				});
				setTimeout("$(\"div.error\").remove()", 5000);
				return false;
			}
			$action = $self.attr("action");
			$.ajax({
				url: $action,
				type: "post",
				data: {name: $name, text: $text},
				dataType: "html",
				success: function(html){
					$self.replaceWith(html);
					setTimeout("document.location.href = document.location.href", 3000);
				}
			});
			return false;
		});
		return false;
	});
	
	$("button.remove").click(function(){
		$confirm = confirm("Вы точно хотите удалить статью \""+$(this).parent().prev().text()+"\"?");
		if ($confirm == false)
		{
			return false;
		}
		var $id = $(this).parents("div.entry").attr("id");
		$.ajax({
			url: "?wd=admin&id=article&action=remove&article="+$id,
			dataType: "html",
			success: function(html){
				$(this).parents("div.entry").html(html);
				setTimeout("$(this).parents(\"div.entry\").fadeOut(\"slow\").remove()", 3000);
			}
		});
	});
});
