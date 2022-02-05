var url = window.location.origin+"/proyecto_2_sd/manejador_objetos/api.php";


function renderizar_objetos(objetos){
    let html_write = "";
    objetos.forEach(objeto => {

        let properties_array = Object.entries(objeto);

        let html_write_properties = "";
        properties_array.forEach(element => {
            if(element[0] != "id"){
                html_write_properties = html_write_properties + `
                    <p><span>${element[0]}: </span>${element[1]}</p>
                `;
            }
        });

        html_write = html_write + `
        <div class="object" data-id=${objeto.id}>
            <h5>id: ${objeto.id}</h5>
            ${html_write_properties}
            <h6 class="eliminar-objeto">Eliminar Objeto</h6>
            <h6 class="replicar-objeto">Replicar Objeto</h6>
        </div>
        
        `;
    });


    $(".objects-container").html(html_write);
}



$( document ).ready(function() {
    
    $(".add-atribute-button").click(function() {
        $(".create-objects-container").append(`
            <div class="input-row">
                <input type="text">
                <input type="text">
                <button>X</button>
            </div>
        `);
    });

    $(document).on('click','.input-row > button',function(){
        $(this).parent().remove();
    });



    $(".add-object-button").click(function() {
        let continueLoop = true;
        let jsonObject = new Object();
        $(".create-objects-container").children('div').each(function () {
            if(continueLoop){
                let inputRow =  $(this);
                let property = $(this).children("input:nth-child(1)");
                let value = $(this).children("input:nth-child(2)");
                if( property.val().length == 0 || value.val().length == 0 ){
                    continueLoop = false;
                    alert("No pueden haber campos vacíos");
                }
                else if(property.val() == "id"){
                    continueLoop = false;
                    alert("El id es auto generado");
                }
                else{
                    jsonObject[property.val()] = value.val();
                }
            }
        });
        if(!continueLoop){
            return;
        }
        // console.log(jsonObject);
        jsonObject = JSON.stringify(jsonObject);
        $.ajax({
            type: "post",
            url: url,
            data: "message=" + jsonObject,
            success: function(result){
                result = JSON.parse(result);
                console.log(result);
                renderizar_objetos(result);
            }
        });

    });

    
    $(document).on('click','.eliminar-objeto',function(){
        let id_to_delete = $(this).parent().attr("data-id");

        $.ajax({
            type: "delete",
            url: url + "?object_id=" + id_to_delete,
            success: function(result){
                result = JSON.parse(result);
                console.log(result);
                if(!result.status){
                    renderizar_objetos(result);
                }
            }
        });

    });

    $(document).on('click','.replicar-objeto',function(){
        let id = $(this).parent().attr("data-id");
        var url = window.location.origin+"/proyecto_2_sd/manejador_objetos/api_replicacion.php";
        $.ajax({
            type: "POST",
            url: url,
            data:"id=" + id,
            success: function(result){
                alert(result)
                // result = JSON.parse(result);
                // console.log(result);
                //if(!result.status){
                //    renderizar_objetos(result);
                //}
            }
        });

    });




    $.ajax({
        type: "get",
        url: url + "?objects=all",
        success: function(result){
            result = JSON.parse(result);
            console.log(result);
            renderizar_objetos(result);
        }
    });
    
    
});


