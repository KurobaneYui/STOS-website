function set_random_card_img() {
    $("img.random-card-personInfoImg").each(function () {
        let max = 11;
        let min = 0;
        let random_number = Math.floor(Math.random() * (max - min + 1) + min);
        $(this).prop('src', "/assets/img/users/personInfoImg" + String(random_number));
    })
}

$(function () {
    set_random_card_img();
})