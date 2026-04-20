const validateCustomerLogin = (event) => {
    const emailInput = document.getElementById('user_email');
    const emailValue = emailInput.value.trim();
    
    const emailRegex = /^(?!.*\.\.)[A-Za-z0-9](?:[A-Za-z0-9._%+-]{0,62}[A-Za-z0-9])?@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z]{2,})+$/;

    if (emailValue === "") {
        alert("Email is required.");
        event.preventDefault();
        emailInput.focus();
    } 
    else if (!emailRegex.test(emailValue)) {
        alert("Email is not formatted correctly.");
        event.preventDefault();
        emailInput.focus();
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener("submit", validateCustomerLogin);
    }
});
