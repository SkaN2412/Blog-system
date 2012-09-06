$(document).ready(function(){
    $("#auth").submit(function(){
        var $this = this;
        var $email = $(this['email']).val(),
            $password = $(this['password']).val();
        
        if ( $email == "" || $password == "" || $email == $(this['email']).attr("title") || $password == $(this['password']).attr("title") )
        {
            errorPopup("Не все поля заполнены!");
            return false;
        }
        
        $.ajax({
            url: "?id=avtorizatsija",
            type: "post",
            data: {'email': $($this['email']).val(), 'password': $($this['password']).val()},
            dataType: "json",
            success: function(data){
                if ( data.success == true ) {
                    document.location.href = document.location.href;
                } else {
                    errorPopup("Проверьте правильность данных, авторизация не произведена");
                }
            },
            error: function(xhr, ajaxOptions, thrownError){
                errorPopup("При отправке данных произошла ошибка. Проверьте наличие соединения с интернетом и попробуйте еще раз позже");
            }
        });
        
        return false;
    });
});