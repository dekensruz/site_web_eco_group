/**
 * Script de validation du numéro de téléphone pour Impact Eco Group
 */

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le champ téléphone
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        // Ajouter un écouteur d'événement pour la validation en temps réel
        phoneInput.addEventListener('input', function(e) {
            // Supprimer tous les caractères non numériques sauf + et -
            let value = this.value.replace(/[^0-9+\-\s]/g, '');
            
            // Limiter la longueur à 20 caractères
            if (value.length > 20) {
                value = value.substring(0, 20);
            }
            
            // Mettre à jour la valeur du champ
            this.value = value;
            
            // Supprimer les messages d'erreur lorsque l'utilisateur commence à taper
            const errorElement = this.parentNode.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
            this.style.borderColor = '';
        });
        
        // Ajouter un écouteur d'événement pour la validation lors de la perte de focus
        phoneInput.addEventListener('blur', function() {
            validatePhoneField(this);
        });
    }
    
    // Fonction pour valider le champ téléphone
    function validatePhoneField(field) {
        let isValid = true;
        let errorMessage = '';
        
        // Supprimer les messages d'erreur existants
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Valider le format du numéro de téléphone
        if (field.value.trim() === '') {
            // Le champ téléphone est optionnel, donc pas d'erreur si vide
            field.style.borderColor = '';
            return true;
        } else {
            // Vérifier si le numéro contient au moins 8 chiffres
            const digitsCount = field.value.replace(/[^0-9]/g, '').length;
            if (digitsCount < 8) {
                errorMessage = 'Le numéro de téléphone doit contenir au moins 8 chiffres';
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
    
    // Exposer la fonction de validation pour qu'elle soit accessible depuis form-validation.js
    window.validatePhoneField = validatePhoneField;
});