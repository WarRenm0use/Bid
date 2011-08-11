var $user = $("#user-info");
var res;
var $login;
var usuario;
FB.init({
    appId   : 111254775631703,
    status  : true, // check login status
    cookie  : false, // enable cookies to allow the server to access the session
    xfbml   : true // parse XFBML
});
FB.getLoginStatus(function(response) {
    var res = response;
    if (response.session) {
        //TODO guardar en la base de datos y setear session
        FB.api('/me', function(response) {
            response.session = res.session;
//            console.log(response);
            $.ajax({
                url: '?do=login',
                type: 'post',
                data: response,
                dataType: 'json',
                success: function(data) {
                    $user.html("<img src='https://graph.facebook.com/"+response.id+"/picture' width=25 border=0/> <div id='bid-info'>Bienvenido "+response.name+" <span>Tienes <span id='nBid'>3</span> Bid disponibles</span></div>");
//                    console.log(data);
                }
            });
//            console.log(response);
//            usuario = response;
        });
    } else {
        $login = $("<a id='#login'><img src='http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif' border=0/></a>").bind("click", function() {
            conecta();
        });
        $user.html($login);
    }
});
//console.log(FB);
function conecta() {
    console.log("conecta");
    FB.login(function(response) {
        if (response.session) {
            if (response.perms) {
                console.log("login ok");
                console.log(response);
                $.ajax({
                    url: '?do=login',
                    type: 'post',
                    data: response,
                    dataType: 'json',
                    success: function(data) {
//                        console.log(data);
                    }
                });
            } else {
                console.log("login maso");
                // user is logged in, but did not grant any permissions
//                alert("ok maso");
            }
        } else {
            console.log("login fail");
            // user is not logged in
//            alert("fail");
        }
    }, {
        perms:'email,publish_stream,offline_access'
    });
}

function handleSessionResponse(response) {
    // if we dont have a session, just hide the user info
    if (!response.session) {
        alert("no logeado");
        return;
    } else {
        console.log("loged");
    }
}