import { API_URL } from "./config.js";

const button = document.getElementById("btn");

button.addEventListener("click", () => {
    const input = document.getElementById("diff");
    const val = input.value;
    const data = {data: val};

    fetch(API_URL, {
       method: "POST",
       headers: {
           "Content-Type": "application/json"
       },
       body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Api response:", data.response);
    })
    .catch(error => {
        console.log("Error from url", API_URL, 'err:', error);
    })
});