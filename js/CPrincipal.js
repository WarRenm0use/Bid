var cache = new CacheProvider;

var Subasta = Backbone.Model.extend({
    refresh: function() {
//        console.log("Subasta.refresh: "+this.get("ID_SUBASTA"));
    },
    doBid: function() {
//        console.log(this);
        console.log("Subasta.doBid: "+this.get("ID_SUBASTA"));
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
                model: this.model.models[i]
            })
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
      this.model.bind('change', this.render);
      this.model.view = this;
    },
    render: function() {
        console.log("SubastaMiniView.render: "+this.model.get("ID_SUBASTA"));
//        $.tmpl(sg.template, sg.model).appendTo(sg.el);
        this.el = $.tmpl(this.template, this.model);
        return this;
    },
    doBid: function() {
        console.log("SubastaMiniView.doBid");
//        console.log(this.model);
//        this.model.doBid();
    }
});

var SubastaView = Backbone.View.extend({
    el: $("#subastas"),
    template: $("#subastaTmpl").template(),
    tagName: "div",
    className: "subasta",
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
        $.tmpl(sg.template, sg.model).appendTo(sg.el);
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