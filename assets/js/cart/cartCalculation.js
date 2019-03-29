//This js manages the Cart's page - Calculation according to quantities given or set.

$(document).ready(function () {

    //Calculates the TOTAL amount of the order displayed at the bottom of the page each time the page is loaded.
    var totalSum = function () {
        var totalHT = 0;
        $('.subtotalHT').each(function (index, item) {
            totalHT += parseFloat($(item).html());
        });

        var roundedTotalHt = totalHT.toFixed(2);
        $("#totalHT").text(roundedTotalHt);

        var TotalTTC = totalHT * 1.2;
        var roundedTotalTtc = TotalTTC.toFixed(2);
        $("#totalTTC").text(roundedTotalTtc);

    };
    totalSum();

    //This calculates the sub-total amounts of each orderline.
    //There's a selector which allows to select up to 10 products. Above, an input field is given to the user.
    $("select").change(function () {
        var select = $(this);
        var quantity = select.val();
        var price = $(this).parent().parent().siblings('.price-box').find('.price').text();
        var subTotal = (price * quantity);

        var subtotalHT = select.parent().siblings(".subtotalHT-box").find('.subtotalHT');
        var subtotalTTC = select.parent().siblings(".subtotalTTC-box").find('.subtotalTTC');

        var roundedsubTotalHt = subTotal.toFixed(2);
        subtotalHT.text(roundedsubTotalHt);

        //TODO : change the taxes according to the country selected
        var subTotalTTC = subTotal * 1.2;
        var roundedsubTotalTtc = subTotalTTC.toFixed(2);
        subtotalTTC.text(roundedsubTotalTtc);

        //If quantity is above 10
        if (quantity === "ou plus...") {
            subtotalHT.text('0,00');
            subtotalTTC.text('0,00');
            $("input").keyup(function () {
                var input = $(this);

                var subtotalHT = input.parent().siblings(".subtotalHT-box").find('.subtotalHT');
                var subtotalTTC = input.parent().siblings(".subtotalTTC-box").find('.subtotalTTC');

                var quantityB = input.val();
                var subTotal = (price * quantityB);

                var roundedsubTotalHt = subTotal.toFixed(2);
                subtotalHT.text(roundedsubTotalHt);

                var subTotalTTC = subTotal * 1.2;
                var roundedsubTotalTtc = subTotalTTC.toFixed(2);
                subtotalTTC.text(roundedsubTotalTtc);

                //Recalculate the TOTAL amount.
                totalSum();
            });
        }

        //Recalculate the TOTAL amount.
        totalSum();

    })
        .change();

    //Removing an orderline from the cart thanks to the ID of the product since the order nor the order lines are not yet recorded to the database yet.
    $(".removeFromCart").find(".btn").click(function (e) {
        e.preventDefault();
        var button = $(this);
        var articleId = button.parent().siblings().find('#form-group').find('.form-control')[0].id;

        // * @Route("/remove_from_cart", name="_removeFromCart")
        var path = "/remove_from_cart";

        $.ajax({
            type: 'POST',
            url: path,
            data: {articleId},
            success: function (data) {
                button.parent().parent().parent().remove();
                totalSum();
                if (!$(".exist").length) {
                    //If our cart is empty, we redirect the user to an other page.
                    var redirectToIndex = "/";
                    $(location).attr("href", redirectToIndex);
                }
            }
        });

    });
});