var $user = $("#registro");
var res;
var $login;
var usuario = null;
$(document).ready(function() {
//    FB.init({
//        appId : 264213770284841,
//        status : true, // check login status
//        cookie : true, // enable cookies to allow the server to access the session
//        xfbml : true, // parse XFBML
//        oauth: true
//    });
});

function checkLogin() {
//    FB.getLoginStatus(function(response) {
//        var res = response;
//        console.log(response);
//        if (response.authResponse) {
//            setSession(response);
//        } else {
//            $registroBtn.click(conecta);
//        }
//    });
}

//FB.Event.subscribe('auth.login', function(response) {
////    console.log("login event");
////    console.log(response);
//    setSession(response);
//});
//FB.Event.subscribe('auth.logout', function(response) {
//    console.log("logout event");
//});
//FB.Event.subscribe('auth.authResponseChange', function(response) {
//    console.log("authResponseChange");
//    console.log(response);
//});

function setSession(res) {
    FB.api('/me', function(response) {
        response.session = (res)?res.authResponse:null;
        $.ajax({
            url: '/?sec=log&do=in',
            type: 'post',
            data: response,
            dataType: 'json',
            success: function(data) {
//                console.log(data);
                usuario = data;
                if(data.RELOAD == 1) window.location.reload();
                if(data.IS_NEW == 1) {
//                    window.location = "/";
                    if($askNick) askNick(data.NICK_USUARIO);
                } else if(data.NICK_USUARIO=="") {
                    if($askNick) askNick("");
                }
//                $invitarBtn.fadeIn();
//                $comprarBtn.fadeIn();
//                $carroBtn.fadeIn();
//                $carroBtn.html("<a href='/carro'>Carro de compra ("+data.N_PRODUCTOS+")</a>").attr("data-original-title", "Tienes "+data.N_PRODUCTOS+"  productos en tu carro de compra por un total de $"+data.MONTO_CARRO_H);
//                $user.html("<a href='/micuenta'><span id='nBid'>"+data.BID_DISPONIBLE+"</span> Bids<img src='https://graph.facebook.com/"+response.id+"/picture' height=32 border=0/></a>").attr("data-original-title", data.NICK_USUARIO+" tienes "+data.BID_DISPONIBLE+" Bids para usar en las subastas");
            }
        });
    });
}

function askNick(nick) {
    nick = (nick)?nick:"";
    var modal = $("#askNick");
    $modal = modal.modal({
        'show': true,
        'backdrop': 'static'
    });
    $("#username", modal).val(nick);
    var $btn = $("#btnGuardar", modal);
    var validator = $("#nickForm", modal).bind("invalid-form.validate",
        function() {
            $("#msg", modal).html("Debes ingresar un nombresss");
        }).validate({
        errorPlacement: function(error, element) {},
        submitHandler: function(form) {
            if(!$btn.hasClass("disabled")) {
                $.ajax({
                    url: '/?sec=log&do=setNick',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'username': form.username.value
                    },
                    beforeSend: function() {
                        $btn.val("Guardando").addClass("disabled");
                        $("#msg", modal).html("");
                    },
                    success: function(data) {
//                        console.log(data);
                        $("#msg", modal).html(data.MENSAJE);
                        if(data.ERROR == 0) {
                            $user.html("<a href='/micuenta'><span id='nBid'>"+data.BID_DISPONIBLE+"</span> Bids<img src='https://graph.facebook.com/"+data.ID_FB+"/picture' height=32 border=0/></a>").attr("data-original-title", data.NICK_USUARIO+" tienes "+data.BID_DISPONIBLE+" Bids para usar en las subastasss");
                            setTimeout("$modal.modal('hide')", 2000);
                            $btn.val("Guardado!");
                        } else {
                            $btn.val("Guardar").removeClass("disabled");
                        }
                    }
                });
            }
            return false;
        },
        success: function(label) {
        }
    });
    
//    console.log(validator);
}

function checkReserva(id, fb_id) {
    $.ajax({
        url: '/?sec=svip&do=checkReserva&id='+id+"&fb_id="+fb_id,
        type: 'get',
        dataType: 'json',
        success: function(data) {
//            console.log(data);
            var btn = $("#s_"+data.ID_SVIP);
            if(data.IN == 1) {
                if(data.ESTADO_SUBASTA == 0) {
                    btn.html("Reservado!").attr("data-original-title", "Ya eres parte de la subasta, solo debes esperar a que comience!");
                    btn.parent().find("#anular").fadeIn();
//                    btn.parent().append("<a id=\"anular\" class=\"btn-side negativo\">Anular reserva</a>");
                } else if(data.ESTADO_SUBASTA == 1){
                    btn.html("ENTRA!").attr("data-original-title", "Ya comenzo, entra ahora!");
//                    btn.parent().find("#go").fadeIn();
//                    btn.parent().append("<a id=\"go\" class=\"btn-side positivo\">Entra a la subasta!</a>");
                }
            }
        }
    });
}


//console.log(FB);
function conecta() {
//    console.log("conecta");
    FB.login(function(response) {
//         FB.getLoginStatus();
        setSession(response);
    }, {
        scope:'email,publish_stream'
    });
}

function handleSessionResponse(response) {
    // if we dont have a session, just hide the user info
    if (!response.session) {
//        alert("no logeado");
        return;
    } else {
//        console.log("loged");
    }
}

var enviarInvitaciones = function(response) {
    console.log("enviarInvitacion");
    console.log(response);
    if(response) {
        $.ajax({
            url: '/?sec=invitacion&do=invitar',
            type: 'post',
            data: response,
            dataType: 'json',
            success: function(data) {
//                console.log("listo");
                console.log(data);
//                cp._invitacion.update(data.MODELO);
//                cp._invitaciones.add(data.INVITACIONES);
            }
        });
    }
}

//var invitar = function() {
//    $.ajax({
//        url: '/?sec=invitacion&get=invitacion',
//        dataType: 'json',
//        data: {},
//        success: function(data) {
////            console.log(data);
//            if(data.INVITACION_DISP>0) {
//                FB.ui({
//                    method: 'apprequests', 
//                    title: 'Invita a tus amigos y gana Bids!',
//                    filters: ['app_non_users'],
//                    message: 'Registrate gratis y subasta productos con hasta un 100% de descuento!', 
//                    exclude_ids: data.INVITADOS,
//                    max_recipients: data.INVITACION_DISP
//                }, enviarInvitaciones);
//            }
//        }
//    });
//    return false;
//}



function getInvitaciones() {
    FB.api('264213770284841', function(response) {
//        console.log(response);
    });
}

