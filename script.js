$('.price').keyup(function () {
    var sum = 0;
    $('.price').each(function() {
        sum += Number($(this).val());
    });
    $('#totalPrice').text(sum);
});