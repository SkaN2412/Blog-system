$(document).ready(function(){
    $("#register").submit(function(){
        $(this).children().each(function(){
            $(this).attr("disabled", "disabled").next("span").remove();
        });
        
        if ( $(this['password']).val() != $(this['pass_confirm']).val() ) {
            errorPopup("Пароли не совпадают!");
            $(this).children().each(function(){
                $(this).removeAttr("disabled");
            });
            return false;
        }
        
        var $this = this,
            $email = $(this['email']).val(),
            $password = $(this['password']).val(),
            $nickname = $(this['nickname']).val();
        
        if ($email == "" || $password == "" || $nickname == "" || $email == $(this['email']).attr("title") || $password == $(this['password']).attr("title") || $nickname == $(this['nickname']).attr("title")) {
            errorPopup("Не все поля заполнены");
            $(this).children().each(function(){
                $(this).removeAttr("disabled");
            });
            return false;
        }
        
        $.ajax({
            url: "?id=registratsija&action=reg",
            type: "post",
            data: {'email': $email, 'password': $password, 'nickname': $nickname},
            dataType: "json",
            success: function(data){
                if (data.success == false) {
                    if (data.email == false) {
                        $($this['email']).focus().after("<span style=\"color: red\">Такой email уже зарегистрирован</span>");
                    }
                    if (data.nickname == false) {
                        $($this['nickname']).focus().after("<span style=\"color: red\">Такой никнейм уже занят</span>");
                    }
                    if (data.email != false && data.nickname != false) { alert(data.error);
                        errorPopup("Произошла неизвестная ошибка, попробуйте еще раз позже");
                    }
                    $($this).children().each(function(){
                        $(this).removeAttr("disabled");
                    });
                } else {
                    document.location.href = "./";
                }
            }
        });
        
        return false;
    });
});