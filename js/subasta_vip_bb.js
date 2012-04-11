var SubastaVip = Backbone.Model.extend({
    refresh: function() {
        var ws = this,
            ts = new Date();
        $.ajax({
            url: '?sec=svip&get=refresh&cod='+ws.get("COD_SUBASTA")+"&"+ts.getTime(),
            dataType: 'json',
            type: 'get',
            success: function(data) {
//                console.log(data);
                if(data!=null) {
                    if(data.ESTADO_SUBASTA == 4) {
                        data.RESTO_TIEMPO = "Terminada!";
                        clearInterval(cp._refresh);
                    } else if(data.ESTADO_SUBASTA == 2) {
                        data.RESTO_TIEMPO = "Anulada!";
                        clearInterval(cp._refresh);
                    } else if(data.ESTADO_SUBASTA == 1) {
                        data.RESTO_TIEMPO = "Activada!";
                    }
                    ws.set({
                        MONTO_SUBASTA: data.MONTO_SUBASTA,
                        RESTO_TIEMPO: data.RESTO_TIEMPO,
                        RESTO_TIEMPO_SEC: data.RESTO_TIEMPO_SEC,
                        NICK_USUARIO: data.NICK_USUARIO,
                        ESTADO_SUBASTA: data.ESTADO_SUBASTA
                    });
                    if(cp._segRefresh == 60) {
                        cp._segRefresh = 1;
                        clearInterval(cp._refresh);
                        cp._refresh = setInterval(function(){
                            cp._subVipModel.refresh();
                        }, cp._segRefresh * 1000);
                    }
                } else {
                    if(cp._segRefresh == 1) {
                        cp._segRefresh = 60;
                        clearInterval(cp._refresh);
                        cp._refresh = setInterval(function(){
                            cp._subVipModel.refresh();
                        }, cp._segRefresh * 1000);
                    }
                }
            }
        });
    },
    anular: function() {
        var ws = this;
        $.ajax({
            url: '?sec=svip&do=delReserva',
            type: 'post',
            data: {
                'ID_SVIP': ws.get("ID_SVIP")
            },
            dataType: 'json',
            success: function(data) {
//                console.log(data);
                var btn = $("#s_"+data.ID_SVIP),
                    btnAn = $("#anular");
                if(data.ERROR == 0) {
                    _gaq.push(['_trackPageview', '#/subasta/'+ws.get("COD_SUBASTA")+'/anular/ok']);
                    btn.html("Reserva!").attr("data-original-title", "Reserva tu cupo para ser parte de la subasta!");
                    btnAn.removeClass("disabled").fadeOut();
                    if(cp.$nBid.length==0) {
                        cp.$nBid = $("#nBid");
                    }
                    cp.$nBid.html(data.BID_DISPONIBLE);
                    ws.set({
                        'RESTO_USUARIOS': data.RESTO_USUARIOS
                    });
                } else {
                    _gaq.push(['_trackPageview', '#/subasta/'+ws.get("COD_SUBASTA")+'/anular/fail']);
                    btn.html("Reservado!").attr("data-original-title", "Ya eres parte de la subasta, solo debes esperar a que comience!");
                    btnAn.html("Anular reserva").removeClass("disabled");
                    btnAn.fadeIn();
                }
                showNotificacion(data.MENSAJE);
            }
        });
    },
    reservar: function() {
        var ws = this;
        $.ajax({
            url: '?sec=svip&do=reservar',
            type: 'post',
            data: {
                'ID_SVIP': ws.get("ID_SVIP")
            },
            dataType: 'json',
            success: function(data) {
//                console.log(data);
                var btn = $("#s_"+data.ID_SVIP),
                    btnAn = $("#anular");
                if(data.ERROR == 0) {
                    _gaq.push(['_trackPageview', '#/subasta/'+ws.get("COD_SUBASTA")+'/reservar/ok']);
                    btn.html("Reservado!").attr("data-original-title", "Ya eres parte de la subasta, solo debes esperar a que comience!").removeClass("disabled");
                    btnAn.html("Anular reserva");
                    btnAn.fadeIn();
                    if(cp.$nBid.length==0) {
                        cp.$nBid = $("#nBid");
                    }
                    cp.$nBid.html(data.BID_DISPONIBLE);
                    ws.set({
                        'RESTO_USUARIOS': data.RESTO_USUARIOS
                    });
                } else {
                    _gaq.push(['_trackPageview', '#/subasta/'+ws.get("COD_SUBASTA")+'/reservar/fail']);
                    btn.html("Reserva!").attr("data-original-title", "Reserva tu cupo para ser parte de la subasta!").removeClass("disabled");
                    btnAn.fadeOut();
                }
                showNotificacion(data.MENSAJE);
            }
        });
    },
    doBid: function() {
        var ws = this;
        if(this.get("ESTADO_SUBASTA") == 3) {
            $.ajax({
                url: '?sec=svip&do=bid',
                type: 'post',
                data: {
                    'ID_SVIP': ws.get("ID_SVIP"),
                    'COD_SUBASTA': ws.get("COD_SUBASTA")
                },
                dataType: 'json',
                success: function(data) {
                    ws.set({
                        MONTO_SUBASTA: data.MONTO_SUBASTA,
                        RESTO_TIEMPO: data.RESTO_TIEMPO,
                        RESTO_TIEMPO_SEC: data.RESTO_TIEMPO_SEC,
                        NICK_USUARIO: data.NICK_USUARIO,
                        BID_RESTO: data.BID_RESTO,
                        ESTADO_SUBASTA: data.ESTADO_SUBASTA
                    });
                }
            });
        }
    },
    change: function() {
        this.view.refresh();
    },
    recarga: function(monto) {
        var ws = this;
        if(this.get("ESTADO_SUBASTA") == 3) {
            $.ajax({
                url: '?sec=svip&do=recarga',
                type: 'post',
                data: {
                    'ID_SVIP': ws.get("ID_SVIP"),
                    'COD_SUBASTA': ws.get("COD_SUBASTA"),
                    'MONTO_RECARGA':monto
                },
                dataType: 'json',
                success: function(data) {
                    if(data.ERROR == 0) {
                        ws.set({
                            NICK_USUARIO: data.NICK_USUARIO,
                            BID_RESTO: data.BID_RESTO,
                            RECARGA_RESTO: data.RECARGA_RESTO
                        });
                        if(cp.$nBid.length==0) {
                            cp.$nBid = $("#nBid");
                        }
                        cp.$nBid.html(data.BID_RESTO_US);
                    }
                    showNotificacion(data.MENSAJE);
                }
            });
        }
    }
});

var SubastaVipCollection = Backbone.Collection.extend({
    model: SubastaVip,
    comparator: function(item) {
        return item.get('pid');
    },
    
    lala: function() {
        var n = this.models.length;
        for(var i=0; i<n; i++) {
            if(this.models[i].get("ESTADO_SUBASTA") == 1)
                this.models[i].refresh();
        }
    },
    lele: function() {
        var ws = this;
        $.ajax({
            url: '?sec=svip&get=subastas',
            dataType: 'json',
            data: {},
            success: function(data) {
                var n = ws.models.length;
                var m = data.length;
                var i;
                var menor = (n<m)?n:m;
                var mayor = (n<m)?m:n;
                for(i=mayor-menor; i<menor; i++) {
                    ws.models[i].set(data[i]);
                }
                if(m<n) {
//                    console.log("eliminar extra");
//                    console.log(ws.models);
                    for(i = 0; i<mayor-menor; i++) {
                        ws.at(i).view.remove();
                        ws.remove(ws.models[i]);
                    }
//                    console.log(ws.models);
                } else if(m>n){
//                    console.log("agregar extra");
                    for(i = n; i<m; i++) {
                        ws.add(data[i]);
                        
                    }
                }
            }
        });
    }
});

var SubastaVipMainView = Backbone.View.extend({
    el: $("#subasta"),
    $usuarios: null,
    $el: null,
    template: $("#subastaVipMainTmpl").template(),
    events: {
        "click .reservar": "reservar",
        "click #anular": "anular"
//        "click #go": "goSubasta"
    },
    initialize: function() {
        this.model.view = this;
    },
    render: function() {
        this.$el = $(this.el);
        this.$el.html($.tmpl(this.template, this.model));
        this.$el.find(".tp").tooltip({
            'live':true,
            'offset': 5
        });
        $("[property='og:image']").attr("content", "http://dev.lokiero.cl/producto/"+this.model.get("URL_IMAGEN"));
        $("[property='og:title']").attr("content", this.model.get("NOM_PRODUCTO")+" :: Lo Kiero!.cl - Subastas VIP");
        $title.html(this.model.get("NOM_PRODUCTO")+" :: Lo Kiero!.cl - Subastas VIP");
        
        return this;
    },
    anular: function() {
//        console.log("anular");
        var btn = this.$el.find("#anular");
        if(!btn.hasClass("disabled")) {
            _gaq.push(['_trackPageview', '#/subasta/'+this.model.get("COD_SUBASTA")+'/anular']);
            this.$usuarios = this.$el.find("#usuarios");
            btn.html("Anulando").addClass("disabled");
            this.model.anular();
        }
    },
    reservar: function() {
//        console.log("reservar");
        var btn = this.$el.find(".reservar");
        if(!btn.hasClass("disabled")) {
            _gaq.push(['_trackPageview', '#/subasta/'+this.model.get("COD_SUBASTA")+'/reservar']);
            this.$usuarios = this.$el.find("#usuarios");
            btn.html("Reservando").addClass("disabled");
            this.model.reservar();
        }
    },
    remove: function() {
//        console.log("SubastaMiniView.remove");
        $(this.el).fadeTo(1, 0.5);
    },
    refresh: function() {
        var nUs = this.model.get("RESTO_USUARIOS");
        if(nUs>1) {
            this.$usuarios.html("Faltan "+nUs+" usuarios").attr("data-original-title", nUs+" usuarios más y la subasta podra realizarse");
        } else if(nUs > 0){
            this.$usuarios.html("Solo falta "+nUs+" usuario!").attr("data-original-title", "Un usuario más y la subasta podra realizarse");
        } else {
            this.$usuarios.html("Minimo alcanzado ;)").attr("data-original-title", "Se alcanzó el minimo de usuarios para que la subasta se realice");
        }
        
    }
});



var SubastaVipView = Backbone.View.extend({
    el: $("#contenido"),
    template: $("#subastaVipTmpl").template(),
    $el: null,
    _refresh: null,
    events: {
        "click #bid": "doBid",
        "click #recarga": "recarga"
    },
    initialize: function() {
//        console.log("SubastaMiniView.initialize");
//      _.bindAll(this, 'render', 'close');
//      this.model.bind('change', this.render);
      this.model.view = this;
    },
    render: function() {
//        var sg = this;
//        sg.el.empty();        
//        console.log("SubastaVipView.render");
//        console.log(this.el);
//        console.log(this.model);
//        console.log("SubastaView.render: "+this.model.get("ID_SVIP"));
//        $.tmpl(sg.template, sg.model).appendTo(sg.el).hide().fadeIn();
//        this.el = $.tmpl(this.template, this.model).show();
//        console.log(this.el);
        this.$el = $(this.el);
        this.el.empty();
        $flechas.fadeOut();
        this.$tiempo = this.$el.find("#tiempo");
        this.$monto = this.$el.find("#monto");
        this.$lastBidder = this.$el.find("#bidder");
        this.$el.html($.tmpl(this.template, this.model));
        var ws = this;
        this.$tiempo = this.$el.find("#tiempo");
        this.$monto = this.$el.find("#monto");
        this.$nBidSub = this.$el.find("#nBidSub");
        this.$recarga = this.$el.find("#recarga");
        this.$bidder = this.$el.find("#bidder");
        this.$botones = this.$el.find("#botones");
        this.$el.find(".tp").tooltip({
            'live':true,
            'offset': 5
        });
        var url = "http://dev.lokiero.cl/?sec=svip&id="+this.model.get("COD_SUBASTA"),
            titulo = this.model.get("NOM_PRODUCTO")+" :: Lo Kiero!.cl - Subastas VIP";
        $("[property='og:image']").attr("content", "http://dev.lokiero.cl/producto/"+this.model.get("URL_IMAGEN"));
        $("[property='og:title']").attr("content", titulo);
        $title.html(titulo);
        $('#shareme').sharrre({
            share: {
                googlePlus: true,
                facebook: true,
                twitter: true,
                digg: false,
                delicious: false,
                stumbleupon: false,
                linkedin: false
            },
            buttons: {
                googlePlus: {size: 'medium', url: url},
                facebook: {layout: 'button_count', url: url, action: 'like', send: 'false', faces: 'false',},
                twitter: {count: 'horizontal',via:'lo_kiero', url: url}
            },
            enableHover: true,
            enableCounter: false,
            enableTracking: true
        });
        console.log("sharrre: "+url);
        return this;
    },
    doBid: function() {
//        console.log("SubastaView.doBid");
//        console.log(this);
        this.model.doBid();
    },
    recarga: function() {
        if(!this.$recarga.hasClass("disabled")) {
            var rec = this.$("#nBidRec option:selected");
            this.model.recarga(rec.val());
        }
    },
    refresh: function() {
//        this.$tiempo = $(this.el).find("#tiempo");
//        this.$monto = $(this.el).find("#monto");
//        this.$nBidSub = $(this.el).find("#nBidSub");
//        this.$recarga = $(this.el).find("#recarga");
//        this.$bidder = $(this.el).find("#bidder");
        if(this.model.get("RESTO_TIEMPO_SEC") < 10) {
            this.$tiempo.addClass("rojo");
        } else {
            this.$tiempo.removeClass("rojo");
        }
        this.$recarga.html("Recarga Bids ("+this.model.get("RECARGA_RESTO")+")");
        this.$bidder.html(this.model.get("NICK_USUARIO"));
        this.$nBidSub.html(this.model.get("BID_RESTO"));
        this.$tiempo.html(this.model.get("RESTO_TIEMPO"));
        this.$monto.html("$ "+this.model.get("MONTO_SUBASTA"));
        if(this.$botones.hasClass("active")) {
            if(this.model.get("ESTADO_SUBASTA") == 4) this.$botones.removeClass("active").fadeOut();
        } else {
            if(this.model.get("ESTADO_SUBASTA") == 3) this.$botones.addClass("active").fadeIn();
        }
    }
});