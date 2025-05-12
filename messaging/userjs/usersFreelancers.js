const freelancersList = document.querySelector(
  ".freelancers-area .freelancer-chats"
);

setInterval(() => {
  fetch("userphp/freelancerUsers.php")
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not okay...");
      return response.text();
    })
    .then((data) => {
      freelancersList.innerHTML = data;
    })
    .catch((error) => {
      console.error("Fetch error: ", error);
    });
}, 500);
