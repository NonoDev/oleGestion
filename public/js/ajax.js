$(document).ready(function(){
    $('#contratos').change(function()
    {
        $("#cosa option").remove();
        valor = $(this).val();

        console.log(valor);
        $.ajax({
            type: "GET",
            url: "rellenarContrato/"+valor,

            success : function(response)
            {
                $("#cosa").append(response);
            }
        });
        //}
    });
});