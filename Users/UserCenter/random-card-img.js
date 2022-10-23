function set_random_card_img() {
    let max=10;
    let min=0;
    let random_number = Math.floor(Math.random()*(max-min+1)+min);
    $("img.random-card-personInfoImg").prop('src',"/assets/img/users/personInfoImg"+String(random_number));
}

$(function(){
    set_random_card_img();
})