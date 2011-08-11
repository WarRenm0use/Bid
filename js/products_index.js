$(function(){
    $(".rounded").corner();

		setInterval( function() {
    	$.ajax({
      	url:PRODUCT_URL,
				timeout: 2000,
        dataType:"json",
            success:function(data){
								//Analizar los resultados de cada producto
                for(var i=0;i<data["auctions"].length;++i)
								{
                	var product_json = data["auctions"][i];

									//Checkeo doble de los tiempos para garantizar sincronia de los relojes en funcion del smoothtime.
									var numericTimeLeft = product_json['numeric_time_left'];
									if(product_json['numeric_time_left2'] != null) numericTimeLeft = product_json['numeric_time_left2'];
									var timeLeft = product_json["time_left"];
									if(product_json["time_left2"] != null) timeLeft = product_json["time_left2"];

                  var product = $("#"+product_json["id"]);
									if(product.html() == null) continue;

									//Si cambio el precio del producto
                  if(product.find(".current_price").text() != product_json["current_price"])
                  {
										//Actualizar bidder
                   	product.find(".bidder").text(product_json["bidder"]);
                    product.find(".current_price").effect("highlight", {color:''}, 1000);
                    product.find(".time_left").effect().delay(200).effect("highlight", {color:''}, 1000);
                    product.find(".data").effect().delay(400).effect("highlight", {color:''}, 1000);
                  }

									//Siempre actualizar con la info del servidor el precio y el tiempo restante
                  product.find(".current_price").text(product_json["current_price"]);
				        	product.find(".savings").text(product_json['savings']);
                  if(timeLeft == 'FINALIZADO')
									{
										product.find(".time_left").css('color', 'black');
										product.find(".time_left").css('font-size', '16px');
									}
									product.find(".time_left").text(timeLeft);
									if(i==0) document.title = timeLeft+' '+product_json['product_name'];

									if(product_json["bidder"] == null)
									{
										if(timeLeft == '...cargando...') product.find(".bidder").text('...cargando...'); 
										else product.find(".bidder").text('sin bids');
									}
									//Dependiedo del estado, se muestra el tiempo del servidor o 00:00:00. Esto permite capturar los ultimos requests.
									if(product_json['state'] != 'closed')
									{
										if(numericTimeLeft <= 10)
										{
											//Flashear el counter entre 00:00:02 y 00:00:10
	                    if(product.find(".time_left").text() != '00:00:00' && product.find(".time_left").text() != '00:00:01') product.find(".time_left").effect("highlight", {color:''}, 800);
											//Las letras del counter en rojo
											product.find('.time_left').css('color', 'red');
										}
										else
										{
											//Letras normales en el color estandar
											product.find('.time_left').css('color','#741164');
										}
									}
									//else if(!product.hasClass('product_won')) document.location = document.location;
								}
              }
        });
    }, 1000);
    $("#boton_bid .button, #bid_producto .button").click( function () {
        var button = $(this);
        if(button.hasClass("login")) {
	      	return true;
				}
				if( $('.mybids').html() == "0" ) document.location = '/bids/buyBids';
	    	button.attr('disabled', 'disabled');
					$.ajax({
        	url: "/Bids/placeBid/" + button.attr("auction_id"),
          success: function(data) {
	 			    $(".mybids").html(data);
   			    button.attr('disabled', '');
   		    }
        });
  			return false;
			});    
	});
