var SVipViewModel = function(id, cod, in_sub, ru) {
    this.cod_subasta = cod;
    this.in_subasta = ko.observable(in_sub);
    this.resto_usuarios = ko.observable(ru);
    this.cargando = ko.observable(false);
    this.id_svip = id;

    this.ru_titulo = ko.computed(function(){
        if(this.resto_usuarios() > 1) {
            return "Faltan "+this.resto_usuarios()+" usuarios para que la subasta se active";
        } else if(this.resto_usuarios() == 1) {
            return "Un usuario más y la subasta podra realizarse!";
        } else {
            return "Se alcanzó el minimo de usuarios para que la subasta se realice!";
        }
    }, this);
    
    this.ru_texto = ko.computed(function(){
        if(this.resto_usuarios()>1)
            return "Faltan "+this.resto_usuarios()+" usuarios";
        else if(this.resto_usuarios()==1) 
            return "Solo falta uno, apurate!";
        else 
            return "Minimo alcanzado ;)!";
    }, this);
    
    this.res_texto = ko.computed(function(){
        if(this.in_subasta()) {
            return "Ya esta <b>Reservado</b>!";
        } else {
            return "<b>RESERVA</b> TU CUPO AHORA!";
        }
    }, this);
    
    this.res_titulo = ko.computed(function(){
        if(this.in_subasta()) {
            return "Ya eres parte de la subasta, solo debes esperar a que comience!";
        } else {
            return "Reserva tu cupo para ser parte de la subasta!";
        }
    }, this);
    
    this.is_disabled = ko.computed(function(){
        return (this.cargando() || this.in_subasta());
    }, this);
    
    this.anular = function() {
        var vm = this;
        if(!vm.cargando() && vm.in_subasta()) {
            $.ajax({
                url: '/?sec=svip&do=delReserva',
                type: 'post',
                data: {
                    'ID_SVIP': this.id_svip
                },
                dataType: 'json',
                beforeSend: function() {
                    vm.cargando(true);
                },
                success: function(data) {
                    vm.resto_usuarios(data.RESTO_USUARIOS);
                    vm.cargando(false);
                    if(data.ERROR == 0) {
                        $btn_login.html("<a href='/micuenta'><span>"+data.BID_DISPONIBLE+" Bids</span><img src='https://graph.facebook.com/"+data.ID_FB+"/picture' height=32 border=0/></a>").attr("data-original-title",data.NICK_USUARIO+" tienes "+data.BID_DISPONIBLE+" Bids para usar en las subastas");
                        vm.in_subasta(false);
                        _gaq.push(['_trackPageview', '/svip/'+vm.cod_subasta+'/anular/ok']);
                    } else {
                        vm.in_subasta(true);
                        _gaq.push(['_trackPageview', '/svip/'+vm.cod_subasta+'/anular/fail']);
                    }
                    showNotificacion(data.MENSAJE);
                }
            });
        }
    }
    
    this.reservar = function() {
        var vm = this;
        if(!vm.cargando() && !vm.in_subasta()) {
            $.ajax({
                url: '/?sec=svip&do=reservar',
                type: 'post',
                data: {
                    'ID_SVIP': this.id_svip
                },
                dataType: 'json',
                beforeSend: function() {
                    vm.cargando(true);
                },
                success: function(data) {
                    vm.resto_usuarios(data.RESTO_USUARIOS);
                    vm.cargando(false);
                    if(data.ERROR == 0) {
                        $btn_login.html("<a href='/micuenta'><span>"+data.BID_DISPONIBLE+" Bids</span><img src='https://graph.facebook.com/"+data.ID_FB+"/picture' height=32 border=0/></a>").attr("data-original-title",data.NICK_USUARIO+" tienes "+data.BID_DISPONIBLE+" Bids para usar en las subastas");
                        vm.in_subasta(true);
                        _gaq.push(['_trackPageview', '/svip/'+vm.cod_subasta+'/reservar/ok']);
                    } else {
                        vm.in_subasta(false);
                        _gaq.push(['_trackPageview', '/svip/'+vm.cod_subasta+'/reservar/fail']);
                    }
                    showNotificacion(data.MENSAJE);
                }
            });
        }
    }
}

$(document).ready(function(){
     $("#manual").on("click",function() {
            $.fancybox([
                'http://dev.lokiero.cl/img/tutorial/1.png',
                'http://dev.lokiero.cl/img/tutorial/2.jpg',
                'http://dev.lokiero.cl/img/tutorial/3.jpg',
                'http://dev.lokiero.cl/img/tutorial/4.jpg',
                'http://dev.lokiero.cl/img/tutorial/5.jpg',
                'http://dev.lokiero.cl/img/tutorial/6.jpg',
                'http://dev.lokiero.cl/img/tutorial/7.jpg',
                'http://dev.lokiero.cl/img/tutorial/8.jpg'
            ], {
                'padding'			: 0,
                'transitionIn'		: 'none',
                'transitionOut'		: 'none',
                'type'              : 'image',
                'changeFade'        : 0
            });
    });
});