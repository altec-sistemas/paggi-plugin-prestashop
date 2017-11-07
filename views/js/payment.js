$("form").find(".delete").click(function (e) {

    e.preventDefault();

    var tag = this;
    var id_card = $(tag).data("id");

    $.post(endpointDeleteCard, {PAGGI_TASK_CARD: "DELETE_CARD", PAGGI_CARD_ID: id_card},
            function (data) {


                if (data.status) {
                    $(tag).parent().parent().remove();
                }

                $(".validation_msg").html(data.message).show();
            }
    )

});

$("form").submit(function () {

    if ($(this).find("input[name=PAGGI_CHOOSE_CARD_ID]:checked").length == 0) {

        $(".validation_msg").html(methodPaymentMessage).show();

        return false;
    }

});

$(".card").click(function () {
    $(".validation_msg").hide();
    $(this).find("input[name=PAGGI_CHOOSE_CARD_ID]").prop("checked", true).attr('checked', 'checked');
    $.uniform.update();
});
