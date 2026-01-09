// Main JavaScript for innocent-site

// Highlight active navigation link
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('nav a');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.html')) {
            link.classList.add('active');
        }
    });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Form validation and submission (for contact page)
const contactForm = document.querySelector('#contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.querySelector('#name').value.trim();
        const email = document.querySelector('#email').value.trim();
        const message = document.querySelector('#message').value.trim();
        const submitBtn = document.querySelector('#submit-btn');
        const btnText = document.querySelector('.btn-text');
        const btnLoading = document.querySelector('.btn-loading');
        
        // Clear previous feedback
        const feedback = document.getElementById('form-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
        
        // Client-side validation
        if (name === '' || email === '' || message === '') {
            showFormMessage('Please fill in all required fields', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showFormMessage('Please enter a valid email address', 'error');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        
        // Prepare form data
        const formData = new FormData(contactForm);
        
        // Send AJAX request
        fetch('contact-handler.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFormMessage(data.message, 'success');
                contactForm.reset();
            } else {
                const errorMsg = data.errors ? data.errors.join(', ') : 'An error occurred. Please try again.';
                showFormMessage(errorMsg, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFormMessage('An error occurred. Please try again later or email us directly at adam@theinnocent.co.uk', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        });
    });
}

// Show form feedback messages
function showFormMessage(message, type) {
    const feedback = document.getElementById('form-feedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.className = 'form-feedback ' + type;
        feedback.style.display = 'block';
        feedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        // Fallback to alert if feedback element not found
        alert(message);
    }
}

// Email validation helper function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
