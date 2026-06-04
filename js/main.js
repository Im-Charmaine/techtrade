
// Handles form validation, mobile menu, and dynamic interactions

// Show mobile menu when hamburger button is clicked
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu.style.display === 'flex') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'flex';
    }
}

// Validate login form before submitting
function validateLogin() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    let valid = true;

    // Check email is not empty
    if (email.value.trim() === '') {
        alert('Please enter your email address');
        email.focus();
        valid = false;
    }
    // Check password is not empty
    else if (password.value.trim() === '') {
        alert('Please enter your password');
        password.focus();
        valid = false;
    }

    return valid;
}

// Validate register form
function validateRegister() {
    const name = document.getElementById('full_name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const role = document.getElementById('role');
    let valid = true;

    if (name.value.trim() === '') {
        alert('Please enter your full name');
        name.focus();
        valid = false;
    }
    else if (email.value.trim() === '') {
        alert('Please enter your email address');
        email.focus();
        valid = false;
    }
    else if (password.value.length < 6) {
        alert('Password must be at least 6 characters');
        password.focus();
        valid = false;
    }
    else if (password.value !== confirm.value) {
        alert('Passwords do not match');
        confirm.focus();
        valid = false;
    }
    else if (role.value === '') {
        alert('Please select if you are a buyer or seller');
        role.focus();
        valid = false;
    }

    return valid;
}

// Validate post listing form
function validateListing() {
    const title = document.getElementById('title');
    const price = document.getElementById('price');
    const category = document.getElementById('category_id');
    let valid = true;

    if (title.value.trim() === '') {
        alert('Please enter a product title');
        title.focus();
        valid = false;
    }
    else if (price.value === '' || price.value <= 0) {
        alert('Please enter a valid price');
        price.focus();
        valid = false;
    }
    else if (category.value === '') {
        alert('Please select a category');
        category.focus();
        valid = false;
    }

    return valid;
}

// Confirm before deleting something
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this?');
}

// Auto-hide alert messages after 5 seconds
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
}

// Run when page loads
document.addEventListener('DOMContentLoaded', function() {
    autoHideAlerts();
});

// DARK MODE//

// Check if user previously selected dark mode
function initDarkMode() {
    const savedTheme = localStorage.getItem('techtrade-theme');
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        updateThemeIcon(true);
    }
}

// Toggle between light and dark
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-theme') === 'dark';
    
    if (isDark) {
        html.removeAttribute('data-theme');
        localStorage.setItem('techtrade-theme', 'light');
        updateThemeIcon(false);
    } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('techtrade-theme', 'dark');
        updateThemeIcon(true);
    }
}

// Change the icon between moon and sun
function updateThemeIcon(isDark) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = isDark ? 'ti ti-sun' : 'ti ti-moon';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    autoHideAlerts();
    initDarkMode();
});
