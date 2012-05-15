var id_request = 0,
    $registroBtn,
    $registroBtn2,
    $invitarBtn,
    $comprarBtn,
    $btn_login,
    confirmacion,
    subasta,
    $notificacion,
    $flechas,
    $modal,
    modal,
    $askNick = true,
    $title,

showNotificacion = function(msg) {
    $notificacion.html("<p>"+msg+"</p>");
    $notificacion.fadeIn();
    setTimeout("$notificacion.fadeOut()", 3000);
};
ko.bindingHandlers.dot = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel) {
        $(element).attr("data-original-title", allBindingsAccessor().dot());
    },
    update: function(element, valueAccessor, allBindingsAccessor, viewModel) {
        $(element).attr("data-original-title", allBindingsAccessor().dot());
    }
};

var baseViewModel = function(nick, nBid, nProdCa, montoCa) {
    this.nick = ko.observable(nick);
    this.nBid = ko.observable(nBid);
    this.nProdCa = ko.observable(nProdCa);
    this.montoCa = ko.observable(montoCa);
    
    this.nBidHtml = ko.computed(function(){
        return this.nBid() + " Bids";
    }, this);
    
    this.nBidTitulo = ko.computed(function(){
        return this.nick()+" tienes "+this.nBid() + " Bids para usar en las subastas";
    }, this);
    
    this.carroHtml = ko.computed(function(){
        return "Carro de compra ("+this.nProdCa()+")";
    }, this);
    
    this.carroTitulo = ko.computed(function(){
        return "Tienes "+this.nProdCa()+" en tu carro de compra por un total de $"+this.montoCa();
    }, this);
}

$(document).ready(function(){
    $btn_login = $("#registro");
    $registroBtn = $("#registro");
    $registroBtn2 = $("#registro2");
    $invitarBtn = $("#invitar");
    $comprarBtn = $("#compraBid");
    $carroBtn = $("#carroCompra");
    $flechas = $("#flechas");
    $notificacion = $("#notificacion");
    $(".tp").tooltip({
        'live':true,
        'offset': 5
    });
    $('.flexslider').flexslider({
        animation: "fade", 
        directionNav: false,
        controlNav: false,
        keyboardNav: true,
        prevText: "Previous", 
        nextText: "Next"
    });
});