var id_request = 0,
    $registroBtn,
    $invitarBtn,
    $comprarBtn,
    confirmacion,
    subasta,
    $notificacion,
    $flechas,
    $modal,
    modal,
    $askNick = true;
    
$(document).ready(function(){
    $registroBtn = $("#registro");
    $invitarBtn = $("#invitar");
    $comprarBtn = $("#compraBid");
    $carroBtn = $("#carroCompra");
    $flechas = $("#flechas");
    $(".tp").tooltip({
        'live': true,
        'offset': 5
    });
});