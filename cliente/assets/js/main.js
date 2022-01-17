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
                    qq = property.val();
                    continueLoop = false;
                    alert("No pueden haber campos vacíos");
                }
                else{
                    jsonObject[property.val()] = value.val();
                }
            }
        });
        if(!continueLoop){
            return;
        }
        console.log(jsonObject);
        jsonObject = JSON.stringify(jsonObject);
        send(jsonObject);
    });

    

    init();
    
});


var socket;
function init(){
    socket = new WebSocket("ws://localhost:9000");
    socket.onopen = function(msg) {
        // alert("Welcome - status "+this.readyState);
        };
        socket.onmessage = function(msg) {
        alert("Received: "+msg.data);
        };
        socket.onclose = function(msg) {
        alert("Disconnected - status "+this.readyState);
        };
}
function send(msg){
    if(msg.length > 0) {
    socket.send(msg);
    }
}
function quit(){ 
    socket.close(); 
}
function reconnect(){ 
    quit(); init();
}