$(document).ready(function(){
    $("input[type=text], input[type=password], textarea").setHints().focusin(function(){
        $(this).setHints();
    }).focusout(function(){
        $(this).setHints();
    });
    
    $("button#reg").click(function(){
        document.location.href = "?id=registratsija";
    });
    
    $("#top10").click(function(){
        if ($("#tops").is(":hidden")) {
            $("#tops").slideDown();
        } else {
            $("#tops").slideUp();
        }
    });
});

function errorPopup(t) {
    $("#errorPopup").text(t).fadeIn();
    setTimeout("$(\"#errorPopup\").fadeOut().empty()", 5000);
}