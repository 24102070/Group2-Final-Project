const usersList = document.querySelector(".direct-messages .users-chats");

setInterval(() => {
  fetch("userphp/users.php")
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not okay.");
      return response.text();
    })
    .then((data) => {
      usersList.innerHTML = data;
    })
    .catch((error) => {
      console.error("Fetch error: ", error);
    });
}, 500);