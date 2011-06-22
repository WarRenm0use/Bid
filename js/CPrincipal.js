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
                    RESTO_TIEMPO: data.RESTO_TIEMPO
                });
            }
        });
    },
    doBid: function() {
        var ws = this;
        $.ajax({
            url: '?do=bid',
            type: 'post',
            data: {
                'ID_SUBASTA': ws.get("ID_SUBASTA")
            },
            success: function(data) {
                console.log("Bid: "+data.responseText);
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
            this.models[i].refresh();
        }
    }
});

var IndexView = Backbone.View.extend({
    el: $('#subastas'),
    indexTemplate: $("#indexTmpl").template(),

    render: function() {
        console.log(this.model);
        this.el.empty();
        var n = this.model.length;
        for(var i=0; i<n; i++) {
            var view = new SubastaMiniView({
                model: this.model.at(i)
            });
            this.el.append(view.render().el);
        }
        return this;
    }
});

var SubastaMiniView = Backbone.View.extend({
//    el: $("#subastas"),
    template: $("#subastaMiniTmpl").template(),
    tagName: "div",
    className: "subastaMini",
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
        console.log("SubastaMiniView.render: "+this.model.get("ID_SUBASTA"));
        $(this.el).html($.tmpl(this.template, this.model));
        return this;
    },
    doBid: function() {
        console.log("SubastaMiniView.doBid: "+this.model.get("ID_SUBASTA"));
//        console.log(this.model);
        this.model.doBid();
    },
    refresh: function() {
        $(this.el).find("#tiempo").html(this.model.get("RESTO_TIEMPO"));
        $(this.el).find("#monto").html("$ "+this.model.get("MONTO_SUBASTA"));
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
      this.model.bind('change', this.render);
      this.model.view = this;
    },
    render: function() {
        var sg = this;
        sg.el.empty();
        console.log("SubastaMiniView.render: "+this.model.get("ID_SUBASTA"));
        $.tmpl(sg.template, sg.model).appendTo(sg.el).hide().fadeIn();
//        this.el = $.tmpl(this.template, this.model);
        return this;
    },
    doBid: function() {
        console.log("SubastaView.doBid");
//        console.log(this.model);
        this.model.doBid();
    }
});

var CPrincipal = Backbone.Controller.extend({
    _data:null,
    _subModel:null,
    _subView:null,
    _subastas:null,
    _refresh:null,
    _index:null,

    routes: {
        "!/": "index",
        "!/subasta/:id": "subasta"
    },

    initialize: function(options) {
        console.log("initialize");
        return this;
    },
    
    index: function() {
        var ws = this;
        this._subView = null;
        console.log("index");
        clearInterval(this._refresh);
        if(ws._index == null) {
            $.ajax({
                url: '?get=subastas',
                dataType: 'json',
                data: {},
                success: function(data) {
                    ws._data = data;
                    ws._subastas = new SubastaCollection(data);
                    ws._index = new IndexView({
                        model: ws._subastas
                    }); 
                    ws._index.render();
                    Backbone.history.loadUrl();
                    ws._refresh = setInterval(function(){
                        ws._subastas.lala();
                    }, 1000);
                }
            });
        }
    },
    
    subasta:function(id){
        console.log("subasta");
        this._index = null;
        clearInterval(this._refresh);
        var ws = this;
        if(ws._subView == null) {
            $.ajax({
                url: '?get=subasta&id='+id,
                dataType: 'json',
                data: {},
                success: function(data) {
                    ws._data = data;
                    ws._subModel = new Subasta(data);
                    ws._subView = new SubastaView({
                        model: ws._subModel
                    });
                    ws._subView.render();
                    Backbone.history.loadUrl();
                    ws._refresh = setInterval(function(){
                        ws._subModel.refresh();
                    }, 1000);
                }
            });
        }
    }
});

$(document).ready(function() {
    cp = new CPrincipal();
    Backbone.history.start();
});