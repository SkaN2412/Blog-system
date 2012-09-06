$(document).ready(function(){
    loadRating();
    
    $("div.voting img").click(function(){
        voice = $(this).attr("id");
        
        $.ajax({
            url: "?id=golosovatj&action=vote&article="+$("div.text").attr("id"),
            type: "post",
            data: {'voice': voice},
            dataType: "json",
            success: function(data){
                if (data.success == true){
                    $("#rating").text(data.rating);
                } else {
                    errorPopup(data.message);
                }
            }
        });
        return false;
    })
});

function loadRating(){
    $.ajax({
        url: "?id=golosovatj&action=load&article="+$("div.text").attr("id"),
        dataType: "json",
        success: function(data){
            $("#rating").text(data.rating);
        }
    });
}