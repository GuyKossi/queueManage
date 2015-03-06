$(function () {
    $("#loginForm").find("form").submit( function (e){
        var message = "";
        if( $("input[name=\"code\"]").val() == "") {
            message += "Il campo codice è obbligatorio\n";
        }
        if( $("input[name=\"password\"]").val() == "") {
            message += "Il campo password è obbligatorio\n";
        }
        if ( message !== "" ) {
            alert(message);
            e.preventDefault();
        } 
    })
});