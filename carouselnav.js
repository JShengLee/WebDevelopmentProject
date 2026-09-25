const track = document.querySelector(".carouseltrack");
const card = document.querySelectorAll(".carouselcard");

const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

let currentPos = 0;

function visibleCards(){
    const carousel = document.querySelector(".carousel").getBoundingClientRect();

    let visibleCards = 0;

    card.forEach((currentCard) => {
        const cardRect = currentCard.getBoundingClientRect();

        if (cardRect.left >= carousel.left && cardRect.right <= carousel.right){
            visibleCards++;
        }
    });

    return visibleCards;
}

function maxPos(){
    return card.length - visibleCards();
}

// const maxPos = card.length - visibleCards();

function moveAmount(){
    const cardwidth = card[0].getBoundingClientRect().width;
    const gap = parseFloat(getComputedStyle(track).gap);

    return cardwidth + gap;
}

nextBtn.addEventListener("click", () => {
    if (currentPos < maxPos()){
        currentPos++;

        track.style.transform = `translateX(-${currentPos * moveAmount()}px)`;
    }
});

prevBtn.addEventListener("click", () =>{
    if (currentPos > 0){
        currentPos = currentPos - 1;

        track.style.transform = `translateX(-${currentPos * moveAmount()}px)`;
    }
});