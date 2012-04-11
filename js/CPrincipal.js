var cache = new CacheProvider,
    id_request = 0,
    $registroBtn,
    $invitarBtn,
    $comprarBtn,
    confirmacion,
    subasta,
    $notificacion,
    $flechas,
    $modal,
    modal,
    $askNick = true,
    $title;
    
var showNotificacion = function(msg) {
    $notificacion.html("<p>"+msg+"</p>");
    $notificacion.fadeIn();
    setTimeout("$notificacion.fadeOut()", 3000);
}

var IndexView = Backbone.View.extend({
    el: $('#contenido'),
    indexTemplate: $("#indexTmpl").template(),
    initialize: function() {
    },
    render: function() {
        this.el.empty();
        var n = this.model.length,
        clearDiv = $("<div class='clear'></div>");
        var view = new SubastaVipMainView({
            model: this.model
        });
        var aux = $(view.render().el).hide();
        this.el.append(aux);
        aux.hide().fadeIn();
        this.el.append(clearDiv);
        $flechas.fadeIn();
        var url = "http://dev.lokiero.cl/#/subasta/"+this.model.get("COD_SUBASTA");
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
                facebook: {layout: 'button_count', url: url},
                twitter: {count: 'horizontal',via:'lo_kiero', url: url}
            },
            enableHover: true,
            enableCounter: false,
            enableTracking: true
        });
        return this;
    }
});

var CPrincipal = Backbone.Router.extend({
    _data:null,
    _subModel:null,
    _subView:null,
    _subastas:null,
    _refresh:null,
    _index:null,
    _invView:null,
    _invModel:null,
    _subVipView:null,
    _subVipModel:null,
    $nBid:null,
    _hash:null,
    _catView: null,
    _catModel: null,
    _invitacion: null,
    _invitaciones: null,
    _segRefresh: 1,

    routes: {
        "/": "index",
        "/subasta/:cod": "subasta",
        "/subastas": "subastas",
        "/invitacion/:id": "invitacion",
        "/tienda": "tienda",
        "/tienda/:cat": "categoria",
        "/quienes-somos": "nosotros",
        "/terminos": "terminos",
        "/faq": "faq",
        "/contacto": "contacto",
        "/invitaciones": "invitaciones",
        "/facebook/:req": "facebook",
        "/bids": "bids",
        "/bids/": "bids",
        "/carro": "carro",
        "/carro/": "carro",
        "/pago": "pago",
        "/pago/": "pago"
    },
    initialize: function(options) {
//        console.log("initialize");
        if(window.location.hash == "") window.location = "#/";
        this.$nBid = $("#nBid");
        $registroBtn = $("#registro");
        $invitarBtn = $("#invitar");
        $comprarBtn = $("#compraBid");
        $carroBtn = $("#carroCompra");
        $flechas = $("#flechas");
        return this;
    },
    facebook: function(req) {
//        console.log("facebook: "+req);
        clearInterval(this._refresh);
        $askNick = false;
        $("#contenedor").hide();
        $("#pie").hide();
        $("body").css({
            "background-color": "white",
            "background-image": "none"
        });
        var sec = "";
        if(req.length > 0) sec = "&sec=invitacion";
        
        modal = $("<div class='modal hide fade in'><div class='modal-header'><h3>Lo Kiero.cl</h3></div><div class='modal-body'><p>Vamos a Lo Kiero!</p></div><div class='modal-footer'><p id='msg' style='float:left;'></p><a target='_top' href='http://dev.lokiero.cl/"+req+sec+"' class='btn primary'>Vamos!</a></div></div>");
        $modal = modal.modal({
            'show': true,
            'backdrop': false
        });
    },
    nosotros: function() {
        clearInterval(this._refresh);
        if(this._hash != "quienes-somos") {
            var data = {},
                ws = this;
            data.TITULO = "Quienes Somos";
            data.TEXTO = "lala lele";
            ws._pagina = new Pagina(data);
            ws._pagView = new PaginaView({
                model: ws._pagina
            });
            ws._pagView.render();
            ws._hash = "quienes-somos";
            _gaq.push(['_trackPageview', '#/'+ws._hash]);
            $title.html(data.TITULO+" :: Lo Kiero!.cl - Subastas VIP");
        }
    },
    terminos: function() {
        clearInterval(this._refresh);
        if(this._hash != "terminos") {
            var data = {},
                ws = this;
            data.TITULO = "Terminos y Condiciones de Uso";
            data.TEXTO = "lala lele";
            ws._pagina = new Pagina(data);
            ws._pagView = new PaginaView({
                model: ws._pagina
            });
            ws._pagView.render();
            ws._hash = "terminos";
            _gaq.push(['_trackPageview', '#/'+ws._hash]);
            $title.html(data.TITULO+" :: Lo Kiero!.cl - Subastas VIP");
        }
    },
    faq: function() {
        clearInterval(this._refresh);
        if(this._hash != "faq") {
            var data = {},
                ws = this;
            data.TITULO = "Preguntas frecuentes";
            data.TEXTO = "<p>lala</p> <p>lele</p>";
            ws._pagina = new Pagina(data);
            ws._pagView = new PaginaView({
                model: ws._pagina
            });
            ws._pagView.render();
            this._hash = "faq";
            _gaq.push(['_trackPageview', '#/'+this._hash]);
            $title.html(data.TITULO+" :: Lo Kiero!.cl - Subastas VIP");
        }
    },
    fracaso: function() {
        clearInterval(this._refresh);
        if(this._hash != "fracaso") {
            var data = {},
                ws = this;
            data.TITULO = "Algo salio mal :(";
            data.TEXTO = "lala lele";
//            ws._pagina = new Pagina(data);
            ws._pagView = new FracasoView({
//                model: ws._pagina
            });
            ws._pagView.render();
            this._hash = "fracaso";
        }
    },
    categoria: function(cat) {
        clearInterval(this._refresh);
//        console.log("categoria: "+cat);
        var ws = this;
        if(!ws._hash != "categoria") {
            this._hash = "categoria";
            $.ajax({
                url: '?sec=producto&get=byCatHash&hash='+cat,
                dataType: 'json',
                data: {},
                success: function(data) {
//                    console.log(data);
                    ws._categoria = new Categoria(data);
                    ws._catView = new CategoriaView({
                        model: ws._categoria
                    });
                    ws._catView.render();
                }
            });
        }
    },
    
    bids: function() {
//        console.log("bids");
        clearInterval(this._refresh);
        var ws = this;
        if(ws._hash != "bids") {
            $.ajax({
                url: '?sec=producto&get=byCatHash&hash=bidPack',
                dataType: 'json',
                data: {},
                success: function(data) {
//                    console.log(data);
                    if(data.ERROR == 0) {
                        ws._categoria = new Categoria(data);
                        ws._catView = new bidPacksView({
                            model: ws._categoria
                        });
                        ws._catView.render();
                        ws._hash = "bids";
                        $title.html("Compra Bids :: Lo Kiero!.cl - Subastas VIP");
                        _gaq.push(['_trackPageview', '#/'+ws._hash]);
                    } else {
                        ws.navigate("/");
                    }
                }
            });
        }
    },
    
    invitaciones: function() {
//        console.log("invitaciones");
        clearInterval(this._refresh);
        var ws = this;
        if(this._hash != "invitaciones") {
            $.ajax({
                url: '?sec=invitacion&get=all',
                dataType: 'json',
                data: {},
                success: function(data) {
                    if(data.LOGIN == 1) {
                        ws._invitaciones = new InvitacionCollection(data.INVITACIONES);
                        ws._invitacion = new Invitacion(data.MODELO);
                        ws._invView = new InvitacionesView({
                            collection: ws._invitaciones,
                            model: ws._invitacion
                        });
                        ws._invView.render();
                        ws._hash = "invitaciones";
                        _gaq.push(['_trackPageview', '#/'+ws._hash]);
                        $title.html("Invitaciones :: Lo Kiero!.cl - Subastas VIP");
                    } else {
                        window.location.href="#/";
                    }
                }
            });
        }
    },
    
    carro: function() {
//        console.log("carro");
        clearInterval(this._refresh);
        var ws = this;
        if(this._hash != "carro") {
            $.ajax({
                url: '?sec=carro&get=all',
                dataType: 'json',
                data: {},
                success: function(data) {
//                    console.log(data);
                    if(data.LOGIN == 1) {
                        ws._carroProductos = new Productos(data.PRODUCTOS);
                        ws._carro = new Carro(data.MODELO);
                        ws._carroView = new CarroView({
                            collection: ws._carroProductos,
                            model: ws._carro
                        });
                        ws._carroView.render();
                        ws._hash = "carro";
                        _gaq.push(['_trackPageview', '#/'+ws._hash]);
                        $title.html("Carro de compra :: Lo Kiero!.cl - Subastas VIP");
                    } else {
                        window.location.href="#/";
                    }
                }
            });
        }
    },
    
    pago: function() {
//        console.log("pago");
        clearInterval(this._refresh);
        var ws = this;
        if(this._hash != "pago") {
            $.ajax({
                url: '?sec=carro&get=pago',
                dataType: 'json',
                data: {},
                success: function(data) {
//                    console.log(data);
                    if(data.LOGIN == 1) {
                        ws._carroProductos = new Productos(data.PRODUCTOS);
                        ws._carro = new Carro(data.MODELO);
                        ws._carroView = new CarroView({
                            collection: ws._carroProductos,
                            model: ws._carro
                        });
                        ws._carroView.render();
                        ws._hash = "pago";
                        _gaq.push(['_trackPageview', '#/'+ws._hash]);
                        $title.html("Confirmar compra :: Lo Kiero!.cl - Subastas VIP");
                    } else {
                        window.location.href="#/";
                    }
                }
            });
        }
    },
    
    invitacion: function(id) {
        clearInterval(this._refresh);
//        console.log(id);
        id_request = id;
        $askNick = false;
        var ws = this;
        if(id!=undefined && id!=null && id!="") {
            if(this._hash != "invitacion") {
                $.ajax({
                    url: '?sec=invitacion&get=inv_request&id_request='+id,
                    dataType: 'json',
                    data: {},
                    success: function(data) {
//                        console.log(data);
                        if(data != null) {
                            ws._invList = new InvitacionUsuariosCollection(data);
                            ws._invListView = new InvitacionUsuariosView({
                                collection: ws._invList
                            });
                            ws._invListView.render();
                            ws._hash = "invitacion";
                            _gaq.push(['_trackPageview', '#/'+ws._hash+'/id']);
                            $title.html("Invitacion :: Lo Kiero!.cl - Subastas VIP");
                        } else {
                            window.location.href="#/";
                        }
                    }
                });
            } else {
//                console.log("invitacion: cargada");
            }
        } else {
            window.location.href="#/";
        }
    },
    
    index: function() {
        clearInterval(this._refresh);
//        console.log("index");
        var ws = this;
        if(this._subView) {
            this._subView = null;
            this._subModel = null;
        }
        if(this._hash != "index") {
            $.ajax({
                url: '?sec=svip&get=nextSubasta',
                dataType: 'json',
                data: {},
                success: function(data) {
                    ws._subastas = new SubastaVip(data);
                    ws._index = new IndexView({
                        model: ws._subastas
                    });
                    ws._index.render();
                    ws._hash = "index";
                    _gaq.push(['_trackPageview', '#/']);
                }
            });
        }
    },
    
    subasta:function(cod) {
        clearInterval(this._refresh);
        var ws = this;
        $.ajax({
            url: '?sec=svip&get=subasta&cod='+cod,
            dataType: 'json',
            data: {},
            success: function(data) {
                ws._subVipModel = new SubastaVip(data);
                ws._subVipView = new SubastaVipView({
                    model: ws._subVipModel
                });
                ws._subVipView.render();
                ws._subVipModel.refresh();
                ws._hash = "subasta";
                _gaq.push(['_trackPageview', '#/'+ws._hash+'/'+cod]);
            }
        });
    },

    subastas: function() {
        
    }
});

$(document).ready(function() {
    $title = $("title");
    cp = new CPrincipal();
    Backbone.history.start();
    $notificacion = $("#notificacion");
    $(".tp").tooltip({
        'live':true,
        'offset': 5
    });
});