$(window).ready(function(){
    $('#buscador').on("change", function(e){
        if($(this).val() > 0) {
            $('#form_busqueda').attr("action", "index.php?seccion=categoria&categoria=" + $("#buscador").val() + "&nombre_categoria=" + $("#buscador option:selected").text());
        } else {
            $('#form_busqueda').attr("action", "");
        }
    });

    $('#buscador2').on("change", function(e){
        if($(this).val() > 0) {
            $('#form_busqueda').attr("action", "index.php?seccion=categoria&categoria=" + $("#buscador2").val() + "&nombre_categoria=" + $("#buscador2 option:selected").text()).submit();
        } else {
            $('#form_busqueda').attr("action", "");
        }
    });
});