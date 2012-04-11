var SVipViewModel = function(id, cod, rb, rr, est, bidder, rt, rts, ms, in_sub) {
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
    
    this.interval = ko.computed(function(){
        var ws = this;
        switch(this.estado_subasta()) {
            case 0: //pendiente
//                return setInterval(function(){
//                    ws.refresh();
//                }, 600000);
                break;
            case 1: //activada
                return setInterval(function(){
                    ws.refresh();
                }, 60000);
                break;
            case 2: //anulada
                break;
            case 3: //en curso
                return setInterval(function(){
                    ws.refresh();
                }, 1000);
                break;
            case 4: //terminada
                clearInterval(this.interval());
                break;
        }
    }, this);
    
    this.hasRecarga = ko.computed(function(){
        return (this.recarga_resto()>0);
    }, this);
    
    this.hasBid = ko.computed(function(){
        return (this.bid_resto()>0);
    }, this);
    
    this.bid_texto = ko.computed(function(){
        return "BID! ("+this.bid_resto()+")";
    },this);
    
    this.tiempo_texto = ko.computed(function(){
        switch(this.estado_subasta()) {
            case 0: //pendiente
                return this.resto_tiempo();
                break;
            case 1: //activada
                return "Ya esta por comenzar!";
                break;
            case 2: //anulada
                return "Anulada!";
                break;
            case 3: //en curso
                return this.resto_tiempo();
                break;
            case 4: //terminada
                return "Terminada!";
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
//        console.log("refresh");
        var ws = this,
            ts = new Date();
        $.ajax({
            url: '/?sec=svip&get=refresh&cod='+ws.cod_subasta+"&"+ts.getTime(),
            dataType: 'json',
            type: 'get',
            success: function(data) {
//                console.log(data);
                if(data!=null) {
                    ws.estado_subasta(data.ESTADO_SUBASTA);
                    ws.monto_subasta(data.MONTO_SUBASTA);
                    ws.resto_tiempo(data.RESTO_TIEMPO);
                    ws.resto_tiempo_sec(data.RESTO_TIEMPO_SEC);
                    ws.nick_usuario(data.NICK_USUARIO);
                    
//                    if(data.ESTADO_SUBASTA == 4) {
//                        data.RESTO_TIEMPO = "Terminada!";
//                        clearInterval(cp._refresh);
//                    } else if(data.ESTADO_SUBASTA == 2) {
//                        data.RESTO_TIEMPO = "Anulada!";
//                        clearInterval(cp._refresh);
//                    } else if(data.ESTADO_SUBASTA == 1) {
//                        data.RESTO_TIEMPO = "Activada!";
//                    }
                    
//                    if(cp._segRefresh == 60) {
//                        cp._segRefresh = 1;
//                        clearInterval(cp._refresh);
//                        cp._refresh = setInterval(function(){
//                            cp._subVipModel.refresh();
//                        }, cp._segRefresh * 1000);
//                    }
                } else {
//                    if(cp._segRefresh == 1) {
//                        cp._segRefresh = 60;
//                        clearInterval(cp._refresh);
//                        cp._refresh = setInterval(function(){
//                            cp._subVipModel.refresh();
//                        }, cp._segRefresh * 1000);
//                    }
                }
            }
        });
    }
    
    this.doBid = function() {
        var ws = this;
        if(this.estado_subasta() == 3) {
            $.ajax({
                url: '/?sec=svip&do=bid',
                type: 'post',
                data: {
                    'ID_SVIP': ws.id_svip,
                    'COD_SUBASTA': ws.cod_subasta
                },
                dataType: 'json',
                success: function(data) {
//                    console.log(data);
                    ws.monto_subasta(data.MONTO_SUBASTA);
//                    ws.resto_tiempo(data.RESTO_TIEMPO);
//                    ws.resto_tiempo_sec(data.RESTO_TIEMPO_SEC);
                    ws.nick_usuario(data.NICK_USUARIO);
                    ws.bid_resto(data.BID_RESTO);
                    ws.estado_subasta(data.ESTADO_SUBASTA);
//                    ws.estado_subasta(4);
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
//                    vm.resto_usuarios(data.RESTO_USUARIOS);
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
//                    vm.resto_usuarios(data.RESTO_USUARIOS);
                    vm.cargando(false);
                    if(data.ERROR == 0) {
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
                    success: function(data) {
//                        console.log(data);
                        if(data.ERROR == 0) {
//                            ws.nick_usuario(data.NICK_USUARIO);
                            ws.bid_resto(data.BID_RESTO);
                            ws.recarga_resto(data.RECARGA_RESTO);

//                            if(cp.$nBid.length==0) {
//                                cp.$nBid = $("#nBid");
//                            }
//                            cp.$nBid.html(data.BID_RESTO_US);
                        }
                        showNotificacion(data.MENSAJE);
                    }
                });
            }
        } else {
//            showNotificacion("Alcanzaste el limite de recargas");
        }
    }
}