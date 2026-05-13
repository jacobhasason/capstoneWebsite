"use strict";

const $d = selector => document.querySelector(selector);

document.addEventListener("DOMContentLoaded", () => {
    const today = new Date();
    const dateString = today.toLocaleDateString();


    $d("footer div:last-child").textContent = dateString;
});
