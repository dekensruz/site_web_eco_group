document.addEventListener('DOMContentLoaded', function() {
    const userModal = document.getElementById('userModal');
    const userForm = document.querySelector('#userModal form');
    const modalTitle = document.getElementById('userModalLabel');
    const submitButton = userForm.querySelector('button[type="submit"]');

    // Réinitialiser le formulaire et le titre quand le modal est fermé
    userModal.addEventListener('hidden.bs.modal', function () {
        // Toujours réinitialiser le formulaire à la fermeture du modal
        userForm.reset();
        const idInput = userForm.querySelector('input[name="id"]');
        if (idInput) idInput.value = '';
        // Réinitialiser tous les champs du formulaire
        userForm.querySelectorAll('input:not([type="hidden"]), select').forEach(field => {
            if (field.name !== 'token') {
                field.value = '';
            }
        });
    });

    // Configuration du modal pour l'ajout d'utilisateur uniquement
    userModal.addEventListener('show.bs.modal', function () {
        modalTitle.textContent = 'Ajouter un utilisateur';
        submitButton.textContent = 'Ajouter';
        // Réinitialiser le formulaire pour l'ajout
        userForm.reset();
        const idInput = userForm.querySelector('input[name="id"]');
        if (idInput) idInput.value = '';
        // Vider tous les champs du formulaire
        userForm.querySelectorAll('input:not([type="hidden"]), select').forEach(field => {
            if (field.name !== 'token') {
                field.value = '';
            }
        });
    });
});