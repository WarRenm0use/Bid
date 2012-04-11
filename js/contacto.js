$(document).ready(function(){
    var $form = $("#contacto"),
        $btn = $form.find("#btn");
        
    var personal = $form.bind("invalid-form.validate",
        function() {
            showNotificacion("Debes completar todos los campos");
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
                if(!$btn.hasClass("disabled")) {
                    $btn.addClass("disabled");
                    $.ajax({
                        url: form.action,
                        type: "post",
                        dataType: "json",
                        data: $(form).serializeArray(),
                        success: function(data) {
                            showNotificacion(data.MENSAJE);
                            $btn.removeClass("disabled");
                            if(data.ERROR == 0) form.reset();
                        }
                    });
                }
                return false;
            },
            success: function(label) {}
        });
});