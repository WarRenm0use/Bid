var desForm, $bloqdes,
bloqDes = function() {
//    if(desForm.checkForm()) {
        $.ajax({
            url: '/?sec=carro&do=bloqdes',
    //        type: 'post',
            dataType: 'json',
            success: function(data) {
                if(data.ERROR == 0) {
                    window.location.reload();
                } else {
                    showNotificacion(data.MENSAJE);
                }
            }
        });
//    } else {
//        
//    }
},
eliminar = function (e) {
    var $this = $(this);
    if(!$this.hasClass("disabled")) {
        var $fila = $this.parent().parent();
        $.ajax({
            url: '/?sec=carro&do=delete',
            type: 'post',
            dataType: 'json',
            data: {
                'ID_CARRO_PROD': $fila.attr("id_carro_prod")
            },
            beforeSend: function() {
                $this.tooltip("hide");
                $this.attr("data-original-title", "Quitando del carro...").addClass("disabled");
                $this.tooltip("show");
            },
            success: function(data) {
//                console.log(data);
                showNotificacion(data.MENSAJE);
                if(data.ERROR == 0) {
                    $bloqdes.off("click");
                    if(data.TIENE_DESPACHO > 0) {
                        $bloqdes.on("click", function(){$desForm.submit()});
                    } else {
                        $("#despacho").remove();
                        $bloqdes.on("click", bloqDes);
                    }
                    $this.tooltip("hide");
                    var $nFilas = $(".delete");
                    if($nFilas.length > 1) {
                        $fila.fadeOut(function() {
                            $fila.remove();
                        });
                    } else {
                        var $tabla = $fila.parent().parent(),
                            $cont = $tabla.parent();
                            
                        $tabla.fadeOut(function() {
                            $tabla.remove();
                            $cont.html("<h1>Carro de compra</h1><p>No tienes productos en el carro.</p>");
                        });
                    }
                    $carroBtn.html("<a href='/carro'>Carro de compra ("+data.N_PRODUCTOS+")</a>").attr("data-original-title", "Tienes "+data.N_PRODUCTOS+"  productos en tu carro de compra por un total de $"+data.MONTO_PRODUCTOS_H);
                    $("#carro_total").html("<strong>$"+data.MONTO_PRODUCTOS_H+"</strong>");
                } else {
                    $this.tooltip("hide");
                    $this.attr("data-original-title", "Quitar del carro").removeClass("disabled");
                    $this.tooltip("show");
                }
            }
        });
    }
    e.preventDefault();
},

cargaDespacho = function(id) {
    $.ajax({
        url: "/?sec=carro&get=direccion",
        type: "post",
        dataType: "json",
        data: {
            id_dir: id
        },
        success: function(data) {
            $("#dir", $desForm).val(data.DIRECCION);
            $("#ema", $desForm).val(data.EMA_RECEPTOR);
            $("#nom", $desForm).val(data.NOM_RECEPTOR);
            $("#tel", $desForm).val(data.TEL_RECEPTOR);
            $("#com").val(data.ID_COMUNA).trigger("liszt:updated");
        }
    });
};

$(document).ready(function() {
    $desForm = $("#desForm");
    desForm = $desForm.bind("invalid-form.validate",
        function() {
            showNotificacion("Debes completar todos los campos para el despacho");
        }).validate({
            rules: {},
            errorPlacement: function(error, element) {
                var $e = $(element);
                if($e.hasClass("error")) {
                    $e.parent().parent().addClass("error");
                } else {
                    $e.parent().parent().removeClass("error");
                }
            },
            submitHandler: function(form) {
//                console.log("enviar");
                $.ajax({
                    url: form.action,
                    type: "post",
                    dataType: "json",
                    data: $(form).serializeArray(),
                    success: function(data) {
//                        console.log("recibido");
//                        console.log(data);
//                        $btn.removeClass("disabled");
                        if(data.ERROR == 0) bloqDes();
                        else showNotificacion(data.MENSAJE);
                    }
                });
                return false;
            },
            success: function(label) {}
        });
    $del = $(".delete");
    $del.on("click", eliminar);
    $bloqdes = $(".bloqdes");
    if($desForm.length>0) {
        $bloqdes.on("click", function(){$desForm.submit()});
    } else {
        $bloqdes.on("click", bloqDes);
    }
    $(".chzn-select").chosen();
    var $idDir = $("#id_dir");
    if($idDir.length>0) {
        var $dirSel = $idDir.chosen().change(function(){
            var $sel = $(this).find("option:selected");
            cargaDespacho($sel.val());
        });
        cargaDespacho($dirSel.find("option:selected").val());
    }
});