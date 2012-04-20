var addSub = function() {
    var $this = $(this);
    if(!$this.hasClass("disabled")) {
        var $fila = $this.parent().parent();
        $.ajax({
            url: '/?sec=carro&do=addSub',
            type: 'post',
            dataType: 'json',
            data: {
                id_svip: $fila.attr("id_svip")
            },
            beforeSend: function() {
                $this.addClass("disabled");
//                _gaq.push(['_trackPageview', 'agendamientoSAP/reservas/confirmar/DO']);
//                $this.attr("data-original-title", "Confirmando reserva...").addClass("disabled");
            },
            success: function(data) {
                showNotificacion(data.MENSAJE);
                if(data.ERROR == 0) {
                    $carroBtn.html("<a href='/carro'>Carro de compra ("+data.PRODUCTOS.length+")</a>").attr("data-original-title", "Tienes "+data.PRODUCTOS.length+"  productos en tu carro de compra por un total de $"+data.MONTO_CARRO_H);
//                    _gaq.push(['_trackPageview', 'agendamientoSAP/reservas/confirmar/OK']);
                } else {
                    $this.removeClass("disabled");
//                    _gaq.push(['_trackPageview', 'agendamientoSAP/reservas/confirmar/FAIL']);
                }
            }
        });
    }
    return false;
}

$(document).ready(function() {
    $sub = $(".addSub");
    $sub.on("click", addSub);
});