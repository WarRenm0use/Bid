var bloqDes = function() {
    $.ajax({
        url: '/?sec=carro&do=bloqdes',
//        type: 'post',
        dataType: 'json',
//        data: {
//            'ESTADO_CARRO': est
//        },
        success: function(data) {
            if(data.ERROR == 0) {
                window.location.reload();
            } else {
                showNotificacion(data.MENSAJE);
            }
        }
    });
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
                showNotificacion(data.MENSAJE);
                if(data.ERROR == 0) {
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
                    $carroBtn.html("<a href='/carro'>Carro de compra ("+data.N_PRODUCTOS+")</a>").attr("data-original-title", "Tienes "+data.N_PRODUCTOS+"  productos en tu carro de compra por un total de $"+data.MONTO_CARRO_H);
                } else {
                    $this.tooltip("hide");
                    $this.attr("data-original-title", "Quitar del carro").removeClass("disabled");
                    $this.tooltip("show");
                }
            }
        });
    }
    e.preventDefault();
};

$(document).ready(function() {
    $del = $(".delete");
    $del.on("click", eliminar);
    $bloqdes = $(".bloqdes");
    $bloqdes.on("click", bloqDes);
});