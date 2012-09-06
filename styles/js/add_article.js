$(document).ready(function(){
    bindCatFocus();
    
    bindClick();
    
    $("#list1, #list2").mouseenter(function(){
        switch ( $(this).attr("id") ) {
            case "list1":
                $("#category1-name").unbind("focusout");
                break;
            case "list2":
                $("#category2-name").unbind("focusout");
                break;
        }
    }).mouseleave(function(){
        bindCatFocus();
    });
    
    $("#list2 span").click(function(){
        $("#category2-name").focus();
        
        var $name = $(this).text(),
            $id = $(this).attr("id");
            
        $("#list2 span").removeClass("selected");
            
        $("#category2-name").val($name);
        $("#category2").val($id);
        
        $(this).addClass("selected");
    });
    
    $("#add_article").submit(function(){
        $(this).children().each(function(){
            $(this).attr("disabled", "disabled");
        });
        
        var $name = $(this['name']).val(),
            $text = $(this['text']).val(),
            $category1 = $(this['category1']).val(),
            $category2 = $(this['category2']).val(),
            $date = $(this['year']).val()+"-"+$(this['month']).val()+"-"+$(this['day']).val()+" "+$(this['hours']).val()+":"+$(this['minutes']).val();
            
        if ($name == "" || $text == "" || $category1 == "" || $category2 == "" || $name == $(this['name']).attr("title") || $text == $(this['text']).attr("title")) {
            errorPopup("Не все поля заполнены!");
            $(this).children().each(function(){
                $(this).removeAttr("disabled");
            });
            return false;
        }
        
        $.ajax({
            url: "?id=dobavitj-statju&action=add",
            type: "post",
            data: {'name': $name, 'text': $text, 'category1': $category1, 'category2': $category2, 'date': $date},
            dataType: "json",
            success: function(data){
                if (data.success == false){
                    errorPopup("Неизвестная ошибка, попробуйте обратиться к админинстратору.");
                    $(this).children().each(function(){
                        $(this).removeAttr("disabled");
                    });
//                    alert(data.message);
                } else {
                    document.location.href = "?id=spisok";
                }
            },
            error: function(xhr, ololo, error){
                errorPopup("Ошибка при отправке. Проверьте подключение к инернету и попробуйте позже.");
            }
        });
        
        return false;
    });
});

function bindCatFocus(){
    $("#category1-name").focus(function(){
        $("#list1").show();
    }).focusout(function(){
        $("#list1").hide();
    });
    
    $("#category2-name").focusin(function(){
        $("#list2").show();
    }).focusout(function(){
        $("#list2").hide();
    });
}

function loadCats(o){
    if ($(o).parent().children("ul").length>0) return false;
    $.ajax({
        url: "?id=dobavitj-statju&action=loadCategories&parent="+$(o).attr("id"),
        dataType: "json",
        success: function(data){
            if (data.length<1) return false;
            $ul = $("<ul></ul>").appendTo($(o).parent());
            
            for (i=0;i<data.length;i++){
                $li = $("<li></li>").appendTo($ul);
                $(o).clone(true).appendTo($li).removeClass("selected").attr("id", data[i]['id']).text(data[i]['name']);
            }
        }
    });
    $("#list1 span").unbind("click");
    bindClick();
}

function bindClick(){
    $("#list1 span").click(function(){
        $("#category1-name").focus();
        
        var $name = $(this).text(),
            $id = $(this).attr("id");
            
        $("#list1 span").removeClass("selected");
            
        $("#category1-name").val($name);
        $("#category1").val($id);
        
        $(this).addClass("selected");
        loadCats(this);
    });
}