"use strict";

const validateCustomerInfo = (event) => {
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email_address').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    const emailRegex = /^(?!.*\.\.)[A-Za-z0-9](?:[A-Za-z0-9._%+-]{0,62}[A-Za-z0-9])?@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z]{2,})+$/;
    const passwordRegex = /^(?:(?=.*[a-z])(?=.*[A-Z])(?=.*\d)|(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9])|(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9])|(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])).{8,}$/;

    if (!firstName || !lastName) {
        alert("First and Last name are required.");
        event.preventDefault();
    } else if (!emailRegex.test(email)) {
        alert("Invalid email format.");
        event.preventDefault();
    } else if (!passwordRegex.test(password)) {
        alert("Password must contain a number, an uppercase letter, a lowercase letter, and one of the following special \n\
characters: !@#$%^&* Additionally, the password must be at least 8 characters long");
        event.preventDefault();
    } else if (password !== confirmPassword) {
        alert("Passwords do not match.");
        event.preventDefault();
    }
};

const validateBillingAddress = (event) => {
    const line1 = document.getElementById('bill_line1').value.trim();
    const city = document.getElementById('bill_city').value.trim();
    const state = document.getElementById('bill_state').value.trim();
    const zip = document.getElementById('bill_zip').value.trim();
    const phone = document.getElementById('bill_phone').value.trim();

    const zipRegex = /^\d{5}(-\d{4})?$/;
    const phoneRegex = /^(?:\+1\s?)?(?:\(\d{3}\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;

    if (!line1 || !city || !state) {
        alert("Address Line 1, City, and State are required for billing.");
        event.preventDefault();
    } else if (!zipRegex.test(zip)) {
        alert("Invalid Billing Zip Code.");
        event.preventDefault();
    } else if (!phoneRegex.test(phone)) {
        alert("Invalid Billing Phone Number.");
        event.preventDefault();
    }
};

const validateShippingAddress = (event) => {
    const line1 = document.getElementById('ship_line1').value.trim();
    const city = document.getElementById('ship_city').value.trim();
    const state = document.getElementById('ship_state').value.trim();
    const zip = document.getElementById('ship_zip').value.trim();
    const phone = document.getElementById('ship_phone').value.trim();

    const zipRegex = /^\d{5}(-\d{4})?$/;
    const phoneRegex = /^(?:\+1\s?)?(?:\(\d{3}\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;

    if (!line1 || !city || !state) {
        alert("Address Line 1, City, and State are required for shipping.");
        event.preventDefault();
    } else if (!zipRegex.test(zip)) {
        alert("Invalid Shipping Zip Code.");
        event.preventDefault();
    } else if (!phoneRegex.test(phone)) {
        alert("Invalid Shipping Phone Number.");
        event.preventDefault();
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const customerForm = document.getElementById('form_customer_info');
    const billingForm = document.getElementById('form_billing_address');
    const shippingForm = document.getElementById('form_shipping_address');

    if (customerForm) customerForm.addEventListener("submit", validateCustomerInfo);
    if (billingForm) billingForm.addEventListener("submit", validateBillingAddress);
    if (shippingForm) shippingForm.addEventListener("submit", validateShippingAddress);
});

