$(document).ready(function(){
    var $prod = $(".prodForm"),
        nProd = $prod.length,
        $prodAux;
    for(var i=0; i<nProd; i++) {
        $prodAux = $($prod[i]);
        $prodAux.bind("invalid-form.validate",
            function() {
                showNotificacion("Debes ingresar una cantidad");
            }).validate({
            rules: {},
            errorPlacement: function(error, element) {
            },
            submitHandler: function(form) {
                $.ajax({
                    url: '/?sec=carro&do=add',
                    type: 'post',
                    dataType: 'json',
                    data: $(form).serializeArray(),
                    success: function(data) {
                        showNotificacion(data.MENSAJE);
                        if(data.ERROR == 0) {
                            $carroBtn.html("<a href='/carro'>Carro de compra ("+data.PRODUCTOS.length+")</a>").attr("data-original-title", "Tienes "+data.PRODUCTOS.length+"  productos en tu carro de compra por un total de $"+data.MONTO_PRODUCTOS_H);
                        }
                    }
                });
                return false;
            },
            success: function(label) {
            }
        });
    }
        
});