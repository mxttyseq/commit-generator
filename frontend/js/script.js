import { API_URL } from "./config.js";

const button = document.getElementById("btn");

button.addEventListener("click", () => {
    const input = document.getElementById("diff");
    const val = input.value;
    const data = {data: val};
    const output = document.getElementById("output");
    button.disabled = true;
    button.innerText = "Please wait...";
    console.log("clicked");

    fetch(API_URL, {
       method: "POST",
       headers: {
           "Content-Type": "application/json"
       },
       body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        output.parentElement.style.display = "block";
        //document.getElementById("result-container").style.display = "block";
        output.innerText = data.response
        console.log(data)
    })
    .catch(error => {
        console.log("Error from url", API_URL, 'err:', error);
    })
    .finally(() => {
        button.disabled = false;
        button.innerText = "Generate";
    })
});