var cache = new CacheProvider;
var id_request = 0;
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

var CPrincipal = Backbone.Controller.extend({
    _data:null,
    _subModel:null,
    _subView:null,
    _subastas:null,
    _refresh:null,
    _index:null,
    _invView:null,
    _invModel:null,
    $nBid:null,

    routes: {
        "!/": "index",
        "!/subasta/:id": "subasta",
        "!/invitacion/:id": "invitacion"
    },

    invitacion: function(id) {
//        console.log(id);
        id_request = id;
        var ws = this;
        if(ws._invView == null) {
            $.ajax({
                url: '?get=inv_request&id_request='+id,
                dataType: 'json',
                data: {},
                success: function(data) {
//                    console.log(data);
                    ws._invModel = new Invitacion(data[0]);
                    ws._invView = new InvitacionView({
                        model: ws._invModel
                    });
                    ws._invView.render();
                    Backbone.history.loadUrl();
                }
            });
        } else {
            console.log("invitacion: cargada");
        }
    },
    
    initialize: function(options) {
//        console.log("initialize");
        if(window.location.hash == "") window.location = "#!/";
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
                console.log("index: null");
                ws._index = new IndexView({
                    model: ws._subastas
                }); 
                ws._index.render();
                Backbone.history.loadUrl();
            } else {
                console.log("index: cargado");
//                ws._refresh = setInterval(function(){
//                    ws._subastas.lele();
//                }, 1000);
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
                console.log("subasta: cargado");
//                ws._refresh = setInterval(function(){
//                    ws._subModel.refresh();
//                }, 1000);
            }
        }
    }
});

$(document).ready(function() {
    cp = new CPrincipal();
    Backbone.history.start();
});