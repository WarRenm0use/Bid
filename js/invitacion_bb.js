var Invitacion = Backbone.Model.extend({
    update: function(newModel) {
        this.set({
            INVITACION_DISP: newModel.INVITACION_DISP,
            INVITACION_TOTAL: newModel.INVITACION_TOTAL,
            INVITACION_USADA: newModel.INVITACION_USADA
        });
    },
    change: function() {
        this.view.updateModel();
    },
    del: function(cb) {
        console.log("model.del");
        var ws = this;
        $.ajax({
            url: '?sec=invitacion&do=delete',
            type: 'post',
            dataType: 'json',
            data: {
                'id_request': ws.get("ID_REQUEST"),
                'id_to': ws.get("ID_TO")
            },
            success: function(data) {
                console.log(data);
                showNotificacion(data.MENSAJE);
                if(data.ERROR == 0) {
                    ws.view.die();
                } else {
                    
                }
            }
        });
    }
});

var InvitacionCollection = Backbone.Collection.extend({
    model: Invitacion,
    change: function() {
        this.view.updateCollection();
    },
//    add: function() {
//        
//    }
});

var InvitacionUsuariosCollection = Backbone.Collection.extend({});

var InvitacionLineView = Backbone.View.extend({
    fila: $("#invitacionFilaTmpl").template(),
    tagName: "tr",
    events: {
        "click #delete": "del"
    },
    initialize: function() {
        this.model.view = this;
    },
    render: function() {
        $(this.el).html($.tmpl(this.fila, this.model));
//        console.log("render");
//        console.log(this);
        return this;
    },
    del: function() {
//        console.log("view.del");
//        console.log(this.model);
//        $(this.el).remove();
        this.model.del();
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

var InvitacionesView = Backbone.View.extend({
    el: $("#contenido"),
    $el: null,
    template: $("#invitacionesTmpl").template(),
    fila: $("#invitacionFilaTmpl").template(),
    $invitarBtn: null,
    initialize: function() {
        this.collection.view = this;
        this.model.view = this;
        this.collection.bind("add", this.updateCollection, this);
//        this.collection.bind("remove", this.removeCollection, this);
    },
    render: function() {
        this.$el = $(this.el);
        this.el.empty();
        $flechas.fadeOut();
        this.$el.html($.tmpl(this.template, this.model));
//        $.tmpl(this.fila, this.collection.models).prependTo(this.$el.find("#invitaciones"));
        for(var i=0; i<this.collection.length; i++) {
            var fila = new InvitacionLineView({
                model: this.collection.models[i]
            });
            $(fila.render().el).prependTo(this.$el.find("#invitaciones"));
        }
        this.$el.find("#inner").hide().fadeIn();
        this.$invitarBtn = this.$el.find("#btnInvitar");
        this.$invitarBtn.on("click", invitar);
        return this;
    },
    updateModel: function() {
//        console.log("updateModel");
        this.$el.html($.tmpl(this.template, this.model));
        this.$invitarBtn.on("click", invitar);
    },
    updateCollection: function() {
//        console.log("updateCollection");
//        $.tmpl(this.fila, this.collection.models).prependTo(this.$el.find("#invitaciones"));
        for(var i=0; i<this.collection.length; i++) {
            var fila = new InvitacionLineView({
                model: this.collection.models[i]
            });
            $(fila.render().el).prependTo(this.$el.find("#invitaciones"));
        }
    },
    removeCollection: function() {
//        console.log("remove");
//        console.log(this.collection);
    }
});

var InvitacionView = Backbone.View.extend({
    el: $("#subastas"),
    template: $("#invAcepta").template(),
    events: {
        "click #acepta": "acepta"
    },
    initialize: function() {
      this.model.view = this;
    },
    render: function() {
        var sg = this;
        sg.el.empty();
        $("#titulo").html("Invitacion");
        $.tmpl(sg.template, sg.model).appendTo(sg.el).hide().fadeIn();
        return this;
    },
    acepta: function() {
//        console.log("invitacion.acepta: "+id_request);
//        console.log("SubastaView.doBid");
//        console.log(this);
//        this.model.doBid();
        conecta();
    }
});

var InvitacionUsuariosView = Backbone.View.extend({
    el: $("#contenido"),
    $el: null,
    template: $("#invitacionUsuariosTmpl").template(),
    $btnGuardar: null,
    initialize: function() {
        this.collection.view = this;
    },
    render: function() {
        this.$el = $(this.el);
        this.el.empty();
        $flechas.fadeOut();
        this.$el.html($.tmpl(this.template, this.collection));
        this.$el.find("#inner").hide().fadeIn();
        this.$btnGuardar = this.$el.find("#btnGuardar");
        this.validator = $("#formu").bind("invalid-form.validate",
            function() {
                $(".msg").html("Debes seleccionar a alguien");
            }).validate({
            rules: {
                id_request: {
                    required: true,
                    minlength: 1
                }
            },
            errorPlacement: function(error, element) {
            },
            submitHandler: function(form) {
                var btn = $("#btnGuardar", form),
                    msg = $(".msg");
                    
                if(!btn.hasClass("disabled")) {
                    $.ajax({
                        url: '?sec=invitacion&do=aceptar',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            'id_request': $("input[name='id_request']:checked", form).val()
                        },
                        beforeSend: function() {
                            $(".id_request").attr("disabled", true)
                            btn.val("Guardando...").addClass("disabled");
                            msg.html("");
                        },
                        success: function(data) {
//                            console.log(data);
                            msg.html(data.MENSAJE);
                            if(data.ERROR == 0) {
                                btn.val("Guardado!");
                                setTimeout(function(){
                                    window.location.href="#!/";
                                }, 2000);
                            } else {
                                $(".id_request").attr("disabled", false)
                                btn.val("Guardar").removeClass("disabled");
                            }
                        }
                    });
                }
                return false;
            },
            success: function(label) {
            }
        });
        return this;
    }
});