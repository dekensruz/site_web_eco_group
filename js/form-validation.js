/**
 * Script de validation du formulaire de contact pour Impact Eco Group
 */

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le formulaire de contact
    const contactForm = document.querySelector('.contact-form form');
    
    if (contactForm) {
        // Ajouter un écouteur d'événement pour la soumission du formulaire
        contactForm.addEventListener('submit', function(event) {
            // Empêcher la soumission par défaut du formulaire
            event.preventDefault();
            
            // Valider le formulaire
            if (validateForm()) {
                // Si le formulaire est valide, le soumettre
                this.submit();
            }
        });
        
        // Ajouter des écouteurs d'événements pour la validation en temps réel
        const inputs = contactForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                // Supprimer les messages d'erreur lorsque l'utilisateur commence à taper
                const errorElement = this.parentNode.querySelector('.error-message');
                if (errorElement) {
                    errorElement.remove();
                }
                this.style.borderColor = '';
            });
        });
    }
    
    // Fonction pour valider le formulaire entier
    function validateForm() {
        let isValid = true;
        
        // Valider chaque champ
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const subjectInput = document.getElementById('subject');
        const messageInput = document.getElementById('message');
        
        if (!validateField(nameInput)) isValid = false;
        if (!validateField(emailInput)) isValid = false;
        if (!validateField(subjectInput)) isValid = false;
        if (!validateField(messageInput)) isValid = false;
        
        return isValid;
    }
    
    // Fonction pour valider un champ spécifique
    function validateField(field) {
        let isValid = true;
        let errorMessage = '';
        
        // Supprimer les messages d'erreur existants
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Valider en fonction du type de champ
        if (field.value.trim() === '') {
            errorMessage = 'Ce champ est requis';
            isValid = false;
        } else if (field.id === 'name') {
            // Validation du nom (lettres et espaces uniquement)
            if (!/^[a-zA-ZÀ-ÿ ]*$/.test(field.value)) {
                errorMessage = 'Seuls les lettres et les espaces sont autorisés';
                isValid = false;
            }
        } else if (field.id === 'email') {
            // Validation de l'email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                errorMessage = 'Format d\'email invalide';
                isValid = false;
            }
        }
        
        // Afficher le message d'erreur si nécessaire
        if (!isValid) {
            field.style.borderColor = 'var(--error-color)';
            
            const errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.innerHTML = errorMessage;
            errorElement.style.color = 'var(--error-color)';
            errorElement.style.fontSize = '0.8rem';
            errorElement.style.marginTop = '5px';
            
            field.parentNode.appendChild(errorElement);
        } else {
            field.style.borderColor = 'var(--success-color)';
        }
        
        return isValid;
    }
    
    // Afficher les messages de succès ou d'erreur provenant de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const messageParam = urlParams.get('message');
    const errorsParam = urlParams.get('errors');
    
    if (messageParam === 'success') {
        // Créer un message de succès
        const successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success';
        successMessage.innerHTML = '<i class="fas fa-check-circle"></i> Votre message a été envoyé avec succès! Nous vous répondrons dans les plus brefs délais.';
        successMessage.style.backgroundColor = 'var(--success-color)';
        successMessage.style.color = 'white';
        successMessage.style.padding = '15px';
        successMessage.style.borderRadius = '5px';
        successMessage.style.marginBottom = '20px';
        successMessage.style.display = 'flex';
        successMessage.style.alignItems = 'center';
        
        // Ajouter une icône
        const icon = successMessage.querySelector('i');
        if (icon) {
            icon.style.marginRight = '10px';
            icon.style.fontSize = '1.2rem';
        }
        
        // Insérer avant le formulaire
        if (contactForm) {
            contactForm.parentNode.insertBefore(successMessage, contactForm);
            
            // Faire défiler jusqu'au message
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Supprimer après 5 secondes
            setTimeout(() => {
                successMessage.style.opacity = '0';
                successMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(() => successMessage.remove(), 500);
            }, 5000);
        }
    } else if (errorsParam) {
        try {
            const errors = JSON.parse(decodeURIComponent(errorsParam));
            
            // Afficher les erreurs à côté des champs du formulaire
            for (const [key, value] of Object.entries(errors)) {
                if (value) {
                    const fieldName = key.replace('Err', '');
                    const field = document.getElementById(fieldName);
                    
                    if (field) {
                        // Ajouter une classe d'erreur au champ
                        field.style.borderColor = 'var(--error-color)';
                        
                        // Ajouter un message d'erreur
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.innerHTML = value;
                        errorMessage.style.color = 'var(--error-color)';
                        errorMessage.style.fontSize = '0.8rem';
                        errorMessage.style.marginTop = '5px';
                        
                        // Insérer après le champ
                        field.parentNode.appendChild(errorMessage);
                    }
                }
            }
        } catch (e) {
            console.error('Erreur lors de l\'analyse des erreurs du formulaire:', e);
        }
    }
});