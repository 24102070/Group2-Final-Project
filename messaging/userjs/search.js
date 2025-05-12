const searchUser = document.getElementById("searchUser"),
  searchFreelancer = document.getElementById("searchFreelancer"),
  searchCompany = document.getElementById("searchCompany");

searchUser.onkeyup = () => {
  let searchTerm = searchUser.value;

  fetch("userphp/searchUser.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "searchTerm=" + encodeURIComponent(searchTerm),
  })
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.text(); // Or .json() if you're returning JSON
    })
    .then((data) => {
      console.log(data);
      // Handle rendering results here
    })
    .catch((error) => {
      console.error("Fetch error:", error);
    });
};

searchFreelancer.onkeyup = () => {
  let searchTerm = searchUser.value;
  console.log("Search term:", searchTerm);

  fetch("userphp/searchFreelancer.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "searchTerm=" + encodeURIComponent(searchTerm),
  })
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.text(); // Or .json() if you're returning JSON
    })
    .then((data) => {
      console.log(data);
      // Handle rendering results here
    })
    .catch((error) => {
      console.error("Fetch error:", error);
    });
};

searchCompany.onkeyup = () => {
  let searchTerm = searchUser.value;

  fetch("userphp/searchCompany.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "searchTerm=" + encodeURIComponent(searchTerm),
  })
    .then((response) => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.text(); // Or .json() if you're returning JSON
    })
    .then((data) => {
      console.log(data);
      // Handle rendering results here
    })
    .catch((error) => {
      console.error("Fetch error:", error);
    });
};