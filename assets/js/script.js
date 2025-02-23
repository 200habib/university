let burger = document.querySelector(".burger");
let navLinks = document.querySelector(".nav_links");

let burg = document.querySelector(".burg")
burg.style.display = "none";
burger.addEventListener('click', function () {
    // console.log('hhhh');

    if (burg.style.display === "none" ) {
        burg.style.display = "block";
        console.log('display_block');
        document.body.style.overflow = "hidden";
    } else {
        burg.style.display = "none";
         document.body.style.overflow = "";
        console.log('display_non');
    }
console.log('je bien cliquer');
});