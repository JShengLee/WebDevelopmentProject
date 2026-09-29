const button = document.querySelector(".burger-icon");
const menu = document.querySelector("#mainmenu");

let isClosed = true;

button.addEventListener("click", function(){
    if (button.textContent.trim() === "☰"){
        if (isClosed){
            button.textContent = "X";
        }
    } else {
        if (!isClosed){
            button.textContent = "☰";
        }
    }
});

menu.addEventListener("shown.bs.collapse", function(){
    isClosed = false;
});

menu.addEventListener("hidden.bs.collapse", function(){
    isClosed = true;
});