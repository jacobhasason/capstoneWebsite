"use strict";

//$() function
const $ = selector => document.querySelector(selector);

//adding event handler for DOMContentLoaded
document.addEventListener("DOMContentLoaded", () => {
    
    //Attaching function calculate to calculate button
    $("#calculate").addEventListener("click", calculate);
    
    //Move focus to the product cost text box
    $("#product").focus();

});

//calculate function
const calculate = () => {
    
    //parsing the product cost
    const cost = parseFloat($("#product").value);
    
    //if else to check if input is a valid number
    if (isNaN(cost) || cost <= 0) {
        alert("Product cost must be a valid number greater than zero");
    } else {
        const total = calculateShipping(cost);
        $("#totalCost").value = total.toFixed(2);
    }
    //Leave focus on product cost
    $("#product").focus();

};

//function that calculates shinpping cost with product cost as a parameter
const calculateShipping = (productCost) => {
    let rate = 0;
    
    //Calculating shipping cost as a percentage of the product cost
    if (productCost < 50) {
        rate = 0.20;
    } else if (productCost < 200) {
        rate = 0.18;
    } else if (productCost < 500) {
        rate = 0.15;
    } else if (productCost < 1000) {
        rate = 0.12;
    } else {
        rate = 0.08;
    }
    
    //Returning the sum of the product cost and the shipping cost
    return productCost + (productCost * rate);
};