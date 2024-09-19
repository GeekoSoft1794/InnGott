



function Actualizar() {
    
    $.ajax({
        url: 'controller/usuariosController.php', // URL del recurso al que se enviará la solicitud
        type: 'POST', // Método HTTP
        data: {
            Accion: 'Actualizar',
            id: ''
            // Puedes agregar más datos que quieras enviar
        },
        dataType: 'json', // Tipo de datos que esperas recibir en la respuesta
        success: function(response) {
            // Código que se ejecuta si la solicitud tiene éxito
            console.log('Respuesta recibida:', response);
        },
        error: function(xhr, status, error) {
            // Código que se ejecuta si la solicitud falla
            console.error('Error en la solicitud:', status, error);
        }
    });
}

