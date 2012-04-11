var Carro = Backbone.Model.extend({
    change: function() {
//        console.log("carro.change");
        this.view.updateCarro();
    },
    toggle: function(est) {
        var ws = this;
        $.ajax({
            url: '?sec=carro&do=toggle',
            type: 'post',
            dataType: 'json',
            data: {
                'ID_CARRO': ws.get("ID_CARRO"),
                'ESTADO_CARRO': est
            },
            success: function(data) {
//                console.log(ws);
//                console.log(ws.view.collection);
//                console.log(data);
                cp._carroProductos = new Productos(data.PRODUCTOS);
                cp._carro = new Carro(data.MODELO);
                cp._carro.view = ws.view;
                cp._carroProductos.view = ws.view;
                ws.view.model = cp._carro;
                ws.view.collection = cp._carroProductos;
                ws.view.render();
            }
        });
    }
});

var ProductoCarro = Backbone.Model.extend({
    del: function(cb) {
        console.log(cb);
        var ws = this,
            btn = $(ws.view.el).find("#delete");
        $.ajax({
            url: '?sec=carro&do=delete',
            type: 'post',
            dataType: 'json',
            data: {
                'ID_CARRO_PROD': ws.get("ID_CARRO_PROD")
            },
            beforeSend: function() {
                btn.tooltip("hide");
                btn.attr("data-original-title", "Quitando del carro...").addClass("disabled");
                btn.tooltip("show");
            },
            success: function(data) {
//                console.log(data);
                showNotificacion(data.MENSAJE);
                if(data.ERROR == 0) {
                    btn.tooltip("hide");
                    ws.view.options.viewParent.model.set({
                        MONTO_CARRO: data.MONTO_CARRO,
                        MONTO_CARRO_H: data.MONTO_CARRO_H,
                        N_PRODUCTOS: data.N_PRODUCTOS
                    });
                    ws.view.die();
                } else {
                    btn.tooltip("hide");
                    btn.attr("data-original-title", "Quitar del carro").removeClass("disabled");
                    btn.tooltip("show");
                }
            }
        });
    }
});

var Productos = Backbone.Collection.extend({
    model: ProductoCarro,
    addToCarrito: function(data) {
        $.ajax({
            url: '?sec=carro&do=addProd',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data) {
//                console.log(data);
            }
        });
        return true;
    },
    update: function(newModel) {
        this.set({
            
        });
    },
    change: function() {
        this.view.updateModel();
    }
});

var CarroView = Backbone.View.extend({
    template1: $("#carro1Tmpl").template(),
    template2: $("#carro2Tmpl").template(),
    el: $("#contenido"),
    events: {
        "click #paso2": "paso2",
        "click #paso1": "paso1"
    },
    initialize: function() {
        this.collection.view = this;
        this.model.view = this;
//        this.collection.bind("add", this.updateCollection, this);
    },
    render: function() {
        this.$el = $(this.el);
        this.el.empty();
        $flechas.fadeOut();
        if(this.model.get("ESTADO_CARRO") == 0) {
            this.$el.html($.tmpl(this.template1, this.model));
        } else if(this.model.get("ESTADO_CARRO") == 1){
            this.$el.html($.tmpl(this.template2, this.model));
        }
        this.carro = this.$el.find("#carro");
        for(var i=0; i<this.collection.length; i++) {
            var ws = this;
            var fila = new carroFilaView({
                model: this.collection.models[i],
                viewParent: ws
            });
            $(fila.render().el).prependTo(this.carro);
        }
        this.$el.find("#inner").hide().fadeIn();
        this.$btnPagarTop = $("#btnPagarTop");
        this.$btnPagarBottom = $("#btnPagarBottom");
        this.$el.find(".tp").tooltip({
            'live':true,
            'offset': 5
        });
//        this.$invitarBtn = this.$el.find("#btnInvitar");
//        this.$invitarBtn.on("click", invitar);
        return this;
    },
    paso2: function() {
        this.model.toggle(1);
    },
    paso1: function() {
        this.model.toggle(0);
    },
    updateCarro: function() {
//        console.log("updateCarro");
        this.render();
//        this.$btnPagarTop.html("Pagar $"+this.model.get("MONTO_CARRO"));
//        this.$btnPagarBottom.html("Pagar $"+this.model.get("MONTO_CARRO"));
        $carroBtn.html("<a href='/#/carro'>Carro de compra ("+this.model.get("N_PRODUCTOS")+")</a>").attr("data-original-title", "Tienes "+this.model.get("N_PRODUCTOS")+"  productos en tu carro de compra por un total de $"+this.model.get("MONTO_CARRO_H"));
    }
});

var carroFilaView = Backbone.View.extend({
    template1: $("#carro1FilaTmpl").template(),
    template2: $("#carro2FilaTmpl").template(),
    tagName: "tr",
    events: {
        "click a#delete": "del"
    },
    initialize: function() {
      this.model.view = this;
    },
    render: function() {
        if(this.options.viewParent.model.get("ESTADO_CARRO") == 0) {
            $(this.el).html($.tmpl(this.template1, this.model));
        } else if(this.options.viewParent.model.get("ESTADO_CARRO") == 1) {
            $(this.el).html($.tmpl(this.template2, this.model));
        }
        return this;
    },
    del: function(e) {
        var btn = $(e.target).parent();
        if(!btn.hasClass("disabled")) {
            this.model.del();
        }
        return false;
    },
    die: function() {
//        console.log("view.die");
        var ws = this;
        $(this.el).fadeOut('slow', function(){
            $(ws.el).remove();
        });
        this.model.collection.remove(this.model);
    }
});

var bidPacksView = Backbone.View.extend({
    template: $("#bidPacksTmpl").template(),
    el: $("#contenido"),
    lista: null,
    productos: null,
    events: {
        "click #agregar": "save",
        "keypress .enterInput": "saveOnEnter",
    },
    initialize: function() {
    },
    render: function() {
        this.el.empty();
        this.el.html($.tmpl(this.template, this.model));
        this.productos = new ProductoCollection(this.model.get("PRODUCTOS"));
        var n = this.productos.length;
        this.lista = this.$("#productos");
        this.lista.hide();
        $flechas.fadeOut();
        for(var i=0; i<n; i++) {
            var view = new bidPackView({
                model: this.productos.at(i)
            });
            this.lista.append(view.render().el);
        }
        this.formu = this.$("#bidForm");
        this.valForm = this.formu.validate({errorPlacement: function(error, element) {}});
        this.lista.fadeIn();
        return this;
    },
    saveOnEnter: function(e) {
        if (e.keyCode == 13) {
//            console.log("enter");
            if(this.valForm.checkForm()) {
                var data = this.getForm();
                this.productos.addToCarrito(data);
            } else {
                showNotificacion("Debes ingresar numeros");
            }
            return false;
        } else return true;
    },
    save: function() {
        if(this.valForm.checkForm()) {
//            console.log("agregar");
            var data = this.getForm();
//            console.log(data)
            this.productos.addToCarrito(data);
        } else {
            showNotificacion("Debes ingresar numeros");
        }
        return false;
    },
    getForm: function() {
        return this.formu.serializeArray();
    }
});