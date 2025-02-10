// ============== Toggle Password Visibility ===================== 
function toggleVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ========================== Password Strength Checker =======================
document.getElementById('registerPassword').addEventListener('input', function () {
    const password = this.value;
    const lengthRequirement = document.getElementById('lengthRequirement');
    const uppercaseRequirement = document.getElementById('uppercaseRequirement');
    const lowercaseRequirement = document.getElementById('lowercaseRequirement');
    const numberRequirement = document.getElementById('numberRequirement');
    const symbolRequirement = document.getElementById('symbolRequirement');
    const signUpButton = document.getElementById('signUpButton');

    let isValid = true;

    // Check password requirements
    lengthRequirement.style.color = password.length >= 12 ? 'green' : 'red';
    uppercaseRequirement.style.color = /[A-Z]/.test(password) ? 'green' : 'red';
    lowercaseRequirement.style.color = /[a-z]/.test(password) ? 'green' : 'red';
    numberRequirement.style.color = /[0-9]/.test(password) ? 'green' : 'red';
    symbolRequirement.style.color = /[!@#$%^&*(),.?":{}|<>]/.test(password) ? 'green' : 'red';

    // Enable the signup button if all conditions are met
    isValid = password.length >= 12 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[!@#$%^&*(),.?":{}|<>]/.test(password);
    signUpButton.disabled = !isValid;
});

// ======================== Restrict paste action on login form password field =================================
document.getElementById('password').addEventListener('paste', function (e) {
    e.preventDefault();  // Prevent paste
    alert('Pasting is disabled in this field!');
});

// ======================== Restrict paste action on register form password fields =============================
document.getElementById('registerPassword').addEventListener('paste', function (e) {
    e.preventDefault();  // Prevent paste
    alert('Pasting is disabled in this field!');
});

document.getElementById('confirmPassword').addEventListener('paste', function (e) {
    e.preventDefault();  // Prevent paste
    alert('Pasting is disabled in this field!');
});

// ======================== Trigger password validation when the page loads ====================================
window.addEventListener('load', function () {
    document.getElementById('registerPassword').dispatchEvent(new Event('input'));
});

// ============== Toggle Password Visibility ===================== 
function toggleVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}