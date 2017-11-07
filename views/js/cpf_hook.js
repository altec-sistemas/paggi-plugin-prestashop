$(function () {


    var options = {
        clearIfNotMatch: true,
        onComplete: function (cpf) {
            validateDoc(cpf);
        }
    };

    // Ação para o campo Cpf
    $('input[name=cpf]').mask('999.999.999-99', options);


    // form-error ou form-ok
});

function validateDoc(cpf) {
    $('#erro_cpf').hide();

    $.ajax({
        type: "GET",
        url: $('#validatedoc').val(),
        data: {cpf: cpf},
        dataType: "json",
        success: function (json) {
            if (json.status === true) {
                $('#validate-cpf').attr('class', 'required form-group form-ok');
                $('#submitAccount:disabled').removeAttr('disabled');
            } else {
                $('#erro_cpf').empty();
                $('#erro_cpf').append(json.error);
                $('#erro_cpf').show('slow');

                $('#validate-cpf').attr('class', 'required form-group form-error');
                $('#submitAccount').attr('disabled', 'disabled');
            }
        }
    });
}

function clearFields() {
    $('#erro_cpf').hide();


    $('#validate-cpf').removeClass('form-ok');
    $('#validate-cpf').removeClass('form-error');


    $('input[name=cpf]').val('');

}