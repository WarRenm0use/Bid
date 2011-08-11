var cache = new CacheProvider;

var Subasta = Backbone.Model.extend({
    refresh: function() {
        var ws = this;
        $.ajax({
            url: '?get=subasta&id='+ws.get("ID_SUBASTA"),
            dataType: 'json',
            type: 'get',
            success: function(data) {
                ws.set({
                    MONTO_SUBASTA: data.MONTO_SUBASTA,
                    RESTO_TIEMPO: data.RESTO_TIEMPO,
                    RESTO_TIEMPO_SEC: data.RESTO_TIEMPO_SEC
                });
            }
        });
    },
    doBid: function() {
        var ws = this;
//        console.log(this.view);
        console.log("Subasta.doBid: "+ws.get("ID_SUBASTA"));
        $.ajax({
            url: '?do=bid',
            type: 'post',
            data: {
                'ID_SUBASTA': ws.get("ID_SUBASTA")
            },
            dataType: 'json',
            success: function(data) {
//                console.log(data);
                cp.$nBid.html(data);
            }
        });
    },
    change: function() {
        this.view.refresh();
    }
});

var SubastaCollection = Backbone.Collection.extend({
    model: Subasta,
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
            url: '?get=subastas',
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
                    console.log("eliminar extra");
                    console.log(ws.models);
                    for(i = 0; i<mayor-menor; i++) {
                        ws.at(i).view.remove();
                        ws.remove(ws.models[i]);
                    }
                    console.log(ws.models);
                } else if(m>n){
                    console.log("agregar extra");
                    for(i = n; i<m; i++) {
                        ws.add(data[i]);
                        
                    }
                }
            }
        });
    }
});

var IndexView = Backbone.View.extend({
    el: $('#subastas'),
    indexTemplate: $("#indexTmpl").template(),
    initialize: function() {
//      _.bindAll(this, 'remove');
//      this.model.bind('remove', this.reRender);
    },
    render: function() {
//        console.log(this.model);
        this.el.empty();
        var n = this.model.length;
        for(var i=0; i<n; i++) {
            var view = new SubastaMiniView({
                model: this.model.at(i)
            });
            var aux = view.render().el;
//            console.log(aux);
            this.el.append(aux);
        }
        return this;
    },
    reRender: function() {
        console.log("IndexView.reRender");
        console.log(this);
        this.el.empty();
        var n = this.model.length;
        for(var i=0; i<n; i++) {
            var view = new SubastaMiniView({
                model: this.model.at(i)
            });
            var aux = view.render().el;
//            console.log(aux);
            this.el.append(aux);
        }
        return this;
    }
});

var SubastaMiniView = Backbone.View.extend({
//    el: $("#subastas"),
    $tiempo: null,
    $monto: null,
    template: $("#subastaMiniTmpl").template(),
    tagName: "div",
    className: "subastaMini",
    events: {
        "click #bid .bid-btn-mini": "doBid"
    },
    initialize: function() {
//        console.log("SubastaMiniView.initialize");
//      _.bindAll(this, 'render', 'close');
//      this.model.bind('change', this.render);
      this.model.view = this;
      
    },
    render: function() {
//        console.log("SubastaMiniView.render: "+this.model.get("ID_SUBASTA"));
        $(this.el).html($.tmpl(this.template, this.model));
        return this;
    },
    doBid: function() {
//        console.log("SubastaMiniView.doBid: "+this.model.get("ID_SUBASTA"));
//        console.log(this);
        this.model.doBid();
    },
    refresh: function() {
//        console.log(this.model.get("RESTO_TIEMPO_SEC"));
        $(this.el).html($.tmpl(this.template, this.model));
        this.$tiempo = $(this.el).find("#tiempo");
//        this.$monto = $(this.el).find("#monto");
        if(this.model.get("RESTO_TIEMPO_SEC") <= 10) {
            this.$tiempo.addClass("rojo");
        } else {
            this.$tiempo.removeClass("rojo");
        }
//        this.$tiempo.html(this.model.get("RESTO_TIEMPO"));
//        this.$monto.html("$ "+this.model.get("MONTO_SUBASTA"));
    },
    remove: function() {
        console.log("SubastaMiniView.remove");
        $(this.el).fadeTo(1, 0.5);
    }
});

var SubastaView = Backbone.View.extend({
    el: $("#subastas"),
    template: $("#subastaTmpl").template(),
    events: {
        "click #bid .bid-btn": "doBid"
    },
    initialize: function() {
//        console.log("SubastaMiniView.initialize");
//      _.bindAll(this, 'render', 'close');
//      this.model.bind('change', this.render);
      this.model.view = this;
    },
    render: function() {
        var sg = this;
        sg.el.empty();
//        console.log("SubastaView.render: "+this.model.get("ID_SUBASTA"));
        $.tmpl(sg.template, sg.model).appendTo(sg.el).hide().fadeIn();
//        this.el = $.tmpl(this.template, this.model);
        return this;
    },
    doBid: function() {
//        console.log("SubastaView.doBid");
//        console.log(this);
        this.model.doBid();
    },
    refresh: function() {
        this.$tiempo = $(this.el).find("#tiempo");
        this.$monto = $(this.el).find("#monto");
        if(this.model.get("RESTO_TIEMPO_SEC") < 10) {
            this.$tiempo.addClass("rojo");
        } else {
            this.$tiempo.removeClass("rojo");
        }
        this.$tiempo.html(this.model.get("RESTO_TIEMPO"));
        this.$monto.html("$ "+this.model.get("MONTO_SUBASTA"));
    }
});

var CPrincipal = Backbone.Controller.extend({
    _data:null,
    _subModel:null,
    _subView:null,
    _subastas:null,
    _refresh:null,
    _index:null,
    $nBid:null,

    routes: {
        "!/": "index",
        "!/subasta/:id": "subasta"
    },

    initialize: function(options) {
//        console.log("initialize");
        this.$nBid = $("#nBid");
        return this;
    },
    
    index: function() {
        var ws = this;
        if(this._subView) {
//            this._subView.remove();
            this._subView = null;
            this._subModel = null;
        }
//        console.log("index");
        clearInterval(this._refresh);
//        console.log(ws._subastas);
        if(ws._subastas == null) {
//            console.log("subastas: null");
            $.ajax({
                url: '?get=subastas&ord=0',
                dataType: 'json',
                data: {},
                success: function(data) {
//                    ws._data = data;
//                    console.log(data);
                    ws._subastas = new SubastaCollection(data);
                    ws._index = new IndexView({
                        model: ws._subastas
                    }); 
                    ws._index.render();
                    Backbone.history.loadUrl();
                    
                }
            });
        } else {
            if(ws._index == null) {
//                console.log("index: null");
                ws._index = new IndexView({
                    model: ws._subastas
                }); 
                ws._index.render();
                Backbone.history.loadUrl();
            } else {
//                console.log("index: cargado");
                ws._refresh = setInterval(function(){
                    ws._subastas.lele();
                }, 500);
//                ws._index.render();
//                Backbone.history.loadUrl();
            }
        }
    },
    
    subasta:function(id) {
//        console.log("subasta");
        if(this._index) {
//            this._index.remove();
            this._index = null;
            this._subastas = null;
        }
        clearInterval(this._refresh);
        var ws = this;
        if(ws._subModel == null) {
//            console.log("subModel: null");
            $.ajax({
                url: '?get=subasta&id='+id,
                dataType: 'json',
                data: {},
                success: function(data) {
//                    ws._data = data;
                    ws._subModel = new Subasta(data);
                    ws._subView = new SubastaView({
                        model: ws._subModel
                    });
                    ws._subView.render();
                    Backbone.history.loadUrl();
                }
            });
        } else {
            if(ws._subView == null) {
//                console.log("subView: null");
                ws._subView = new SubastaView({
                    model: ws._subModel
                });
                ws._subView.render();
                Backbone.history.loadUrl();
            } else {
//                console.log("subasta: cargado");
                ws._refresh = setInterval(function(){
                    ws._subModel.refresh();
                }, 1000);
            }
        }
    }
});

$(document).ready(function() {
    cp = new CPrincipal();
    Backbone.history.start();
});