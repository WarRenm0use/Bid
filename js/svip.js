ko.bindingHandlers.fadeVisible = {
    init: function(element, valueAccessor) {
        // Initially set the element to be instantly visible/hidden depending on the value
        var value = valueAccessor();
        $(element).toggle(ko.utils.unwrapObservable(value)); // Use "unwrapObservable" so we can handle values that may or may not be observable
    },
    update: function(element, valueAccessor) {
        // Whenever the value subsequently changes, slowly fade the element in or out
        var value = valueAccessor();
        ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
    }
};
var SVipViewModel = function(id, cod, rb, rr, est, bidder, rt, rts, ms, in_sub) {
    this.titulo = $("title");
    this.tituloBase = this.titulo.html();
    this.cod_subasta = cod;
    this.in_subasta = ko.observable(in_sub);
    this.bid_resto = ko.observable(rb);
    this.recarga_resto = ko.observable(rr);
    this.id_svip = id;
    this.estado_subasta = ko.observable(est);
    this.nick_usuario = ko.observable(bidder);
    this.resto_tiempo = ko.observable(rt);
    this.resto_tiempo_sec = ko.observable(rts);
    this.monto_subasta = ko.observable(ms);
    this.cargando = ko.observable(false);
    this.refreshInterval = null;
    this.ultimos = ko.observableArray([]);
    this.ganador = ko.observable(false);

    this.showUltimos = ko.computed(function(){
        return (this.ultimos().length>0 && this.estado_subasta()==3);
    }, this);
    
    this.getGanador = function() {
        var ws = this,
            ts = new Date();
        $.ajax({
            url: '/?sec=svip&get=ganador&cod='+ws.cod_subasta+"&"+ts.getTime(),
            dataType: 'json',
            type: 'get',
            success: function(data) {
//                console.log("ganador");
//                console.log(data);
                if(data.IS_GANADOR == 1) ws.ganador(true);
                else ws.ganador(false);
                if(data!=null) {
                    if(data.ESTADO_SUBASTA != ws.estado_subasta())
                        ws.estado_subasta(data.ESTADO_SUBASTA);
                    if(data.MONTO_SUBASTA != ws.monto_subasta())
                        ws.monto_subasta(data.MONTO_SUBASTA);
                    if(data.NICK_USUARIO != ws.nick_usuario())
                        ws.nick_usuario(data.NICK_USUARIO);
                    ws.resto_tiempo(data.RESTO_TIEMPO);
                    ws.resto_tiempo_sec(data.RESTO_TIEMPO_SEC);
                } else {

                }
            }
        });
    }
    
    
    
    this.hasRecarga = ko.computed(function(){
        return (this.recarga_resto()>0);
    }, this);
    
    this.hasBid = ko.computed(function(){
        return (this.bid_resto()>0);
    }, this);
    
    this.bid_texto = ko.computed(function(){
        return "Lo Kiero! ("+this.bid_resto()+")";
    },this);
    
    this.tiempo_texto = ko.computed(function(){
        var est = this.estado_subasta();
//        console.log("tiempo_texto: "+this.estado_subasta());
        switch(parseInt(est)) {
            case 0: //pendiente
                return this.resto_tiempo();
                break;
            case 1: //activada
                this.titulo.html("Ya esta por comenzar! :: "+this.tituloBase);
                return "Ya esta por comenzar!";
                break;
            case 2: //anulada
                return "Anulada!";
                break;
            case 3: //en curso
                this.titulo.html(this.resto_tiempo()+" :: "+this.tituloBase);
                return this.resto_tiempo();
                break;
            case 4: //terminada
//                console.log("tiempo_texto: termino");
                if(this.ganador()) {
                    this.titulo.html("Ganaste! :: "+this.tituloBase);
                    return "Ganaste!";
                } else {
                    this.titulo.html("Termino! :: "+this.tituloBase);
                    return "Termino!";
                }
                break;
        }
    },this);
    
    this.recarga_texto = ko.computed(function(){
//        if(this.recarga_resto() > 0)
            return "Recarga Bids ("+this.recarga_resto()+")";
//        else
//            return "Ya no puedes recargar";
    },this);
    
    this.texto_monto_subasta = ko.computed(function(){
        return "$ "+this.monto_subasta();
    }, this);
    
    this.refresh = function() {
        var ws = this,
            ts = new Date();
        $.ajax({
            url: '/?sec=svip&get=refresh&cod='+ws.cod_subasta+"&"+ts.getTime(),
            dataType: 'json',
            type: 'get',
            success: function(todo) {
                if(todo!=null) {
                    var sub = todo.SUBASTA;
                    var last = todo.ULTIMOS;
                    var nLast = last.length;
                    if(sub!=null) {
                        if(sub.ESTADO_SUBASTA != ws.estado_subasta())
                            ws.estado_subasta(sub.ESTADO_SUBASTA);
                        if(sub.MONTO_SUBASTA != ws.monto_subasta())
                            ws.monto_subasta(sub.MONTO_SUBASTA);
                        if(sub.NICK_USUARIO != ws.nick_usuario())
                            ws.nick_usuario(sub.NICK_USUARIO);
                        ws.resto_tiempo(sub.RESTO_TIEMPO);
                        ws.resto_tiempo_sec(sub.RESTO_TIEMPO_SEC);
                    }
                    if(last!=null && nLast>0) {
                        ws.ultimos(last);
                    }
                }
            }
        });
    }
    
    this.is_disabled = ko.computed(function(){
        return (this.estado_subasta()!=3 || this.cargando() || this.bid_resto()<=0);
    }, this);
    
    this.is_visible = ko.computed(function (){
        return (this.estado_subasta() == 1 || this.estado_subasta() == 3);
    }, this);
    
    this.doBid = function() {
        var ws = this;
        if(!this.is_disabled()) {
            $.ajax({
                url: '/?sec=svip&do=bid',
                type: 'post',
                data: {
                    'ID_SVIP': ws.id_svip,
                    'COD_SUBASTA': ws.cod_subasta
                },
                dataType: 'json',
                beforeSend: function(){
                    ws.cargando(true);
                },
                success: function(data) {
                    ws.cargando(false);
                    ws.bid_resto(data.BID_RESTO);
                    if(data.ESTADO_SUBASTA != ws.estado_subasta())
                        ws.estado_subasta(data.ESTADO_SUBASTA);
                    if(data.MONTO_SUBASTA != ws.monto_subasta())
                        ws.monto_subasta(data.MONTO_SUBASTA);
                    if(data.NICK_USUARIO != ws.nick_usuario())
                        ws.nick_usuario(data.NICK_USUARIO);
                }
            });
        }
    }
    
    this.res_texto = ko.computed(function(){
        if(this.in_subasta()) {
            return "Tu cupo ya esta <b>Reservado</b>!";
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
    
    this.is_reserva_disabled = ko.computed(function(){
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
                    vm.cargando(false);
                    if(data.ERROR == 0) {
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
    
    this.is_recarga_disabled = ko.computed(function(){
        return (this.estado_subasta()!=3 || this.cargando() || !this.hasRecarga());
    }, this);
    
    this.recarga = function() {
        var ws = this;
        if(this.hasRecarga()) {
            var monto = $("#nBidRec option:selected").val();
            if(this.estado_subasta() == 3) {
                $.ajax({
                    url: '/?sec=svip&do=recarga',
                    type: 'post',
                    data: {
                        'ID_SVIP': ws.id_svip,
                        'COD_SUBASTA': ws.cod_subasta,
                        'MONTO_RECARGA':monto
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        ws.cargando(true);
                    },
                    success: function(data) {
                        ws.cargando(false);
                        if(data.ERROR == 0) {
                            $btn_login.html("<a href='/micuenta'><span>"+data.BID_RESTO_US+" Bids</span><img src='https://graph.facebook.com/"+data.ID_FB+"/picture' height=32 border=0/></a>").attr("data-original-title",data.NICK_USUARIO+" tienes "+data.BID_RESTO_US+" Bids para usar en las subastas");
                            ws.bid_resto(data.BID_RESTO);
                            ws.recarga_resto(data.RECARGA_RESTO);
                        }
                        showNotificacion(data.MENSAJE);
                    }
                });
            }
        } else {
//            showNotificacion("Alcanzaste el limite de recargas");
        }
    }
    
    this.interval = ko.computed(function(){
        var ws = this;
//        if(this.estado_subasta()==3) this.refresh();
//        console.log("interval: "+this.estado_subasta());
        switch(parseInt(this.estado_subasta())) {
            case 0: //pendiente
//                return setInterval(function(){
//                    ws.refresh();
//                }, 600000);
                break;
            case 1: //activada
                this.refreshInterval = setInterval(function(){
                    ws.refresh();
                }, 60000);
                break;
            case 2: //anulada
                break;
            case 3: //en curso
                this.refreshInterval = setInterval(function(){
                    ws.refresh();
                }, 1000);
                break;
            case 4: //terminada
                clearInterval(this.refreshInterval);
                this.getGanador();
                break;
        }
    }, this);
}