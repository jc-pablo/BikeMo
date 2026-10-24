// Only changes how the password field is displayed. PHP handles login and validation.
function togglePassword(id = 'password') {
    const input = document.getElementById(id);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
}
