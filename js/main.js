/**
 * Main JavaScript file for Impact Eco Group website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const menu = document.getElementById('menu');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            menu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.mobile-menu-btn') && !event.target.closest('#menu')) {
            if (menu.classList.contains('active')) {
                menu.classList.remove('active');
            }
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
                // Close mobile menu after clicking a link
                menu.classList.remove('active');
            }
        });
    });

    // Testimonial slider
    const testimonials = [
        {
            text: "Grâce à Impact Eco Group, notre communauté a pu adopter des pratiques agricoles durables qui ont considérablement amélioré nos rendements tout en préservant nos terres.",
            author: "Jean Mutombo, Agriculteur à Kananga"
        },
        {
            text: "Les formations sur la prévention de la corruption ont transformé notre façon de travailler. Nous avons maintenant des processus plus transparents et efficaces.",
            author: "Marie Kabongo, Administratrice locale"
        },
        {
            text: "Le programme d'éducation a permis à mes enfants de retourner à l'école tout en apprenant l'importance de protéger notre environnement.",
            author: "Paul Tshilumba, Parent à Luiza"
        }
    ];

    const testimonialContainer = document.querySelector('.testimonial-slider');
    if (testimonialContainer) {
        let currentTestimonial = 0;
        
        // Create initial testimonial
        updateTestimonial();
        
        // Set interval to change testimonial every 5 seconds
        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            updateTestimonial();
        }, 5000);
        
        function updateTestimonial() {
            testimonialContainer.innerHTML = `
                <div class="testimonial-card" style="opacity: 0;">
                    <p>${testimonials[currentTestimonial].text}</p>
                    <div class="testimonial-author">${testimonials[currentTestimonial].author}</div>
                </div>
            `;
            
            // Fade in animation
            setTimeout(() => {
                const card = testimonialContainer.querySelector('.testimonial-card');
                card.style.transition = 'opacity 0.5s ease';
                card.style.opacity = 1;
            }, 50);
        }
    }

    // Animate elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.service-card, .project-card, .about-image, .about-text');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementPosition < windowHeight - 100) {
                element.classList.add('animate');
            }
        });
    };
    
    // Add animation class to CSS
    const style = document.createElement('style');
    style.innerHTML = `
        .service-card, .project-card, .about-image, .about-text {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .service-card.animate, .project-card.animate, .about-image.animate, .about-text.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .service-card:nth-child(1), .project-card:nth-child(1) {
            transition-delay: 0.1s;
        }
        
        .service-card:nth-child(2), .project-card:nth-child(2) {
            transition-delay: 0.2s;
        }
        
        .service-card:nth-child(3), .project-card:nth-child(3) {
            transition-delay: 0.3s;
        }
        
        .service-card:nth-child(4), .project-card:nth-child(4) {
            transition-delay: 0.4s;
        }
    `;
    document.head.appendChild(style);
    
    // Run on load and scroll
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);

    // Handle form submission messages
    const urlParams = new URLSearchParams(window.location.search);
    const messageParam = urlParams.get('message');
    const errorsParam = urlParams.get('errors');
    
    if (messageParam === 'success') {
        // Create success message
        const successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success';
        successMessage.innerHTML = 'Votre message a été envoyé avec succès!';
        successMessage.style.backgroundColor = 'var(--success-color)';
        successMessage.style.color = 'white';
        successMessage.style.padding = '10px';
        successMessage.style.borderRadius = '5px';
        successMessage.style.marginBottom = '20px';
        
        // Insert before the form
        const contactForm = document.querySelector('.contact-form form');
        if (contactForm) {
            contactForm.parentNode.insertBefore(successMessage, contactForm);
            
            // Remove after 5 seconds
            setTimeout(() => {
                successMessage.style.opacity = '0';
                successMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(() => successMessage.remove(), 500);
            }, 5000);
        }
    } else if (errorsParam) {
        try {
            const errors = JSON.parse(decodeURIComponent(errorsParam));
            
            // Display errors next to form fields
            for (const [key, value] of Object.entries(errors)) {
                if (value) {
                    const fieldName = key.replace('Err', '');
                    const field = document.getElementById(fieldName);
                    
                    if (field) {
                        // Add error class to field
                        field.style.borderColor = 'var(--error-color)';
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.innerHTML = value;
                        errorMessage.style.color = 'var(--error-color)';
                        errorMessage.style.fontSize = '0.8rem';
                        errorMessage.style.marginTop = '5px';
                        
                        // Insert after the field
                        field.parentNode.appendChild(errorMessage);
                        
                        // Clear error on input
                        field.addEventListener('input', function() {
                            this.style.borderColor = '';
                            const errorMsg = this.parentNode.querySelector('.error-message');
                            if (errorMsg) errorMsg.remove();
                        });
                    }
                }
            }
        } catch (e) {
            console.error('Error parsing form errors:', e);
        }
    }
});