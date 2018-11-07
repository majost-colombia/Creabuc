$(window).ready(function(){
    $('#buscar').on("click", function(e){
        e.preventDefault();
        window.location.href = "index.php?seccion=categoria&categoria=" + $("#buscador").val() + "&nombre_categoria=" + $("#buscador option:selected").text();
    });

    $('#buscador2').on("change", function(e){
        window.location.href = "index.php?seccion=categoria&categoria=" + $("#buscador2").val() + "&nombre_categoria=" + $("#buscador2 option:selected").text();
    });
});