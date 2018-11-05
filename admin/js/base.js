$(function () {
	
	
	$('.subnavbar').find ('li').each (function (i) {
	
		var mod = i % 3;
		
		if (mod === 2) {
			$(this).addClass ('subnavbar-open-right');
		}
		
	});
	
	$('#categoria').on("change", function(e){
		if(parseInt($(this).val()) === 999){
            $("#CreaCat").modal("show");
            $("#crear_cat").on("click", function(e){
            	var nombre = $("#nombre_categoria").val();
            	$.post("index.php?seccion=categorias&accion=crear_categoria",{ 'nombre': nombre }).done(function(response){
            		if(response.substring(0,5) === "Error"){
            			alert(response);
                        $("#CreaCat").modal("hide");
					} else {
						var data = response.split(",");
						$("#categoria").append('<option value="' + data[0] + '" selected>' + data[1] + '</option>');
                        $("#CreaCat").modal("hide");
					}
				});
			});
		}
	});
	
});