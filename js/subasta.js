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
//        console.log("Subasta.doBid: "+ws.get("ID_SUBASTA"));
        $.ajax({
            url: '?do=bid',
            type: 'post',
            data: {
                'ID_SUBASTA': ws.get("ID_SUBASTA")
            },
            dataType: 'json',
            success: function(data) {
//                console.log(data);
                ws.set({
                    MONTO_SUBASTA: data.MONTO_SUBASTA,
                    RESTO_TIEMPO: data.RESTO_TIEMPO,
                    RESTO_TIEMPO_SEC: data.RESTO_TIEMPO_SEC
                });
                if(cp.$nBid.length==0) {
                    cp.$nBid = $("#nBid");
                }
                cp.$nBid.html(data.BID_RESTO);
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

var SubastaMiniView = Backbone.View.extend({
//    el: $("#subastas"),
    $tiempo: null,
    $monto: null,
    $lastBidder: null,
    $el: null,
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
        this.$el = $(this.el);
        this.$tiempo = this.$el.find("#tiempo");
        this.$monto = this.$el.find("#monto");
        this.$lastBidder = this.$el.find("#bidder");
        this.$el.html($.tmpl(this.template, this.model));
        return this;
    },
    doBid: function() {
//        console.log("SubastaMiniView.doBid: "+this.model.get("ID_SUBASTA"));
//        console.log(this);
        this.model.doBid();
    },
    refresh: function() {
//        console.log(this.model);
//        $(this.el).html($.tmpl(this.template, this.model));
//        this.$tiempo = $(this.el).find("#tiempo");
//        this.$monto = $(this.el).find("#monto");
        if(this.model.get("RESTO_TIEMPO_SEC") <= 10) {
            this.$tiempo.addClass("rojo");
        } else {
            this.$tiempo.removeClass("rojo");
        }
        
//        console.log(this.$tiempo);
        this.$tiempo = this.$el.find("#tiempo");
        this.$monto = this.$el.find("#monto");
        this.$lastBidder = this.$el.find("#bidder");
        if(this.model.get("MONTO_SUBASTA")==0) {
            this.$lastBidder.html("No Bid");
        } else {
            this.$lastBidder.html(this.model.get("NICK_USUARIO"));
        }
        this.$tiempo.html(this.model.get("RESTO_TIEMPO"));
        this.$monto.html("$ "+this.model.get("MONTO_SUBASTA"));
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
//        console.log(this.model);
//        console.log("SubastaView.render: "+this.model.get("ID_SUBASTA"));
//        $.tmpl(sg.template, sg.model).appendTo(sg.el).hide().fadeIn();
        this.el = $.tmpl(this.template, this.model);
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