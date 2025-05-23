const toggleBtn = document.getElementById("toggle-freelancers");
const cards = document.querySelectorAll(".freelancer-chats .conversation-card");
let showingAll = false;
const initialVisibleCount = 3;

toggleBtn.addEventListener("click", () => {
  showingAll = !showingAll;
  cards.forEach((card, index) => {
    if (showingAll || index < initialVisibleCount) {
      card.classList.remove("hidden");
    } else {
      card.classList.add("hidden");
    }
  });
  toggleBtn.textContent = showingAll ? "Show Less" : "Show More";
});
