

setInterval(() => {
  fetch("userphp/companyUsers.php")
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not okay.");
      return response.text();
    })
    .then((data) => {
      companiesList.innerHTML = data;
    })
    .catch((error) => {
      console.error("Fetch error: ", error);
    });
}, 500);