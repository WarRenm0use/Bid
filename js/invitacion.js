ko.bindingHandlers.fadeVisible = {
    init: function(element, valueAccessor) {
        // Initially set the element to be instantly visible/hidden depending on the value
        var value = valueAccessor();
        $(element).toggle(ko.utils.unwrapObservable(value)); // Use "unwrapObservable" so we can handle values that may or may not be observable
    },
    update: function(element, valueAccessor) {
        // Whenever the value subsequently changes, slowly fade the element in or out
        var value = valueAccessor();
        ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
    }
};

var InvitacionesViewModel = function(disp, usada, inv) {
    var self = this;
    this.inv_disp = ko.observable(disp);
    this.inv_usada = ko.observable(usada);
    this.inv = ko.observableArray(inv);
    this.inv_uiActive = ko.observable(false);
    
    this.hasInvitaciones = ko.computed(function(){
        return (this.inv_disp()>0);
    }, this);
    
    this.disInvitar = ko.computed(function(){
        return !((this.inv_disp()>0) && !this.inv_uiActive());
    }, this);
    
    this.hasSentInvitaciones = ko.computed(function(){
        return (this.inv().length>0);
//        return true;
    }, this);
    
    this.inv_texto = ko.computed(function(){
        return "Invita más amigos ("+this.inv_disp()+")";
    }, this);
    
    this.ESTADO_INVITACION_TEXT = ko.computed(function(){
//        console.log(this);
    });
    
    this.enviarInvitaciones = function(response) {
//        var ws = this;
//        console.log(ws);
//        console.log(response);
        if(response) {
            $.ajax({
                url: '/?sec=invitacion&do=invitar',
                type: 'post',
                data: response,
                dataType: 'json',
                success: function(data) {
//                    console.log(data);
                    self.inv_disp(data.MODELO.INVITACION_DISP);
                    self.inv_usada(data.MODELO.INVITACION_USADA);
                    self.inv(data.INVITACIONES);
//                    console.log(self.inv());
                    self.inv_uiActive(false);
                }
            });
        } else self.inv_uiActive(false);
    }
    
    this.invitar = function() {
        var ws = this;
        if(!this.disInvitar()) {
            $.ajax({
                url: '/?sec=invitacion&get=invitacion',
                dataType: 'json',
                data: {},
                success: function(data) {
                    if(data.INVITACION_DISP>0) {
                        self.inv_uiActive(true);
                        FB.ui({
                            method: 'apprequests', 
                            title: 'Invita a tus amigos y gana Bids!',
                            filters: ['app_non_users'],
                            message: 'Registrate gratis y subasta productos con hasta un 100% de descuento!', 
                            exclude_ids: data.INVITADOS,
                            max_recipients: data.INVITACION_DISP
                        }, ws.enviarInvitaciones);
                    }
                }
            });
        }
        return false;
    }
    
    this.del = function(d, e) {
        var ws = this,
            $this = $(e.target);
        
        if(!$this.hasClass("disabled")) {
            if(ws.ESTADO_INVITACION == 0) {
                $.ajax({
                    url: '/?sec=invitacion&do=delete',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'id_request': ws.ID_REQUEST,
                        'id_to': ws.ID_TO
                    },
                    beforeSend: function() {
                        $this.tooltip('hide');
                        $this.attr("data-original-title", "Eliminando invitación...").addClass("disabled");
                        $this.tooltip('show');
                    },
                    success: function(data) {
                        $this.removeClass("disabled");
                        showNotificacion(data.MENSAJE);
                        $this.tooltip('hide');
                        if(data.ERROR == 0) {
                            self.inv.remove(d);
                            self.inv_disp(data.INVITACION_DISP);
                            self.inv_usada(data.INVITACION_USADA);
                        } else {
                            $this.attr("data-original-title", "Eliminar invitación");
                        }
                    }
                });
            } else showNotificacion("No puedes eliminar esa invitación");
        }
        return false;
    }
}

var delInvitacion = function(e) {
    var $this = $(this),
        $fila;
    if(!$this.hasClass("disabled")) {
        $fila = $this.parent().parent();
        $.ajax({
            url: '/?sec=invitacion&do=delete',
            type: 'post',
            dataType: 'json',
            data: {
                'id_request': $fila.attr("id_req"),
                'id_to': $fila.attr("id_to")
            },
            beforeSend: function() {
                $this.tooltip('hide');
                $this.attr("data-original-title", "Eliminando invitación...").addClass("disabled");
                $this.tooltip('show');
            },
            success: function(data) {
//                console.log(data);
                $this.removeClass("disabled");
                showNotificacion(data.MENSAJE);
                $this.tooltip('hide');
                if(data.ERROR == 0) {
                    $this.fadeOut(function() {
                        $fila.remove();
                    });
                } else {

                }
            }
        });
    }
    e.preventDefault();
}

$(document).ready(function(){
//    var $btnInvitar = $("#btnInvitar"),
//        $btnEliminar = $(".delete");
//    $btnInvitar.on("click", invitar);
//    $btnEliminar.on("click", delInvitacion);
    var $form = $("#formu");
    if($form.length>0) {
        $formVal = $form.bind("invalid-form.validate",
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
                        url: '/?sec=invitacion&do=aceptar',
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
                            console.log(data);
                            msg.html(data.MENSAJE);
                            if(data.ERROR == 0) {
                                btn.val("Guardado!");
                                setTimeout(function(){
                                    window.location.href="/";
                                }, 2000);
                            } else {
                                $(".id_request").attr("disabled", false)
                                btn.val("Listo!").removeClass("disabled");
                            }
                        }
                    });
                }
                return false;
            },
            success: function(label) {
            }
        });
    }
});