function hello_world(){
    alert("This website is made by:\n Edbert Taidy\n Ehren Thor\n Ceana Joy\n Clare Ng\n Miguel Velasquez\n Mohammed Irfaan\n Harshni Balasubramanian\n");
}


document.querySelector("form").addEventListener("submit", function(event) {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    if (username === "" || password === "") {
        event.preventDefault(); // Stop form from submitting
        alert("Username and password are required!"); // Provide user feedback
    }
});


