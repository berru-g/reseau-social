document.addEventListener('DOMContentLoaded', function () {
    // 👇 Like sans reload brutal
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            // ❤️ Anim
            const rect = this.getBoundingClientRect();
            const floatingHeart = document.createElement('i');
            floatingHeart.classList.add('fas', 'fa-heart', 'floating-heart');
            floatingHeart.style.position = 'fixed';
            floatingHeart.style.left = `${rect.left + rect.width / 2}px`;
            floatingHeart.style.top = `${rect.top}px`;
            document.body.appendChild(floatingHeart);
            setTimeout(() => {
                floatingHeart.remove();
            }, 1000);

            // ⏳ Recharge après anim
            fetch(url)
                .then(response => response.text())
                .then(() => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                });
        });
    });

    // 🔐 Gestion des partages
    document.querySelectorAll('.toggle-share').forEach(button => {
        button.addEventListener('click', function () {
            const fileId = this.getAttribute('data-file-id');

            fetch('../includes/toggle_share.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'file_id=' + fileId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');
                        if (data.is_public) {
                            this.classList.replace('btn-secondary', 'btn-success');
                            icon.classList.replace('fa-lock', 'fa-lock-open');
                            this.title = 'Public';
                        } else {
                            this.classList.replace('btn-success', 'btn-secondary');
                            icon.classList.replace('fa-lock-open', 'fa-lock');
                            this.title = 'Privé';
                        }
                    }
                });
        });
    });

    // 🗑️ Suppression
    document.querySelectorAll('.delete-file').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const fileId = this.getAttribute('data-file-id');

            if (confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
                fetch('../includes/delete_file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'file_id=' + fileId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.file-card').remove();
                        } else {
                            alert('Erreur lors de la suppression: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue');
                    });
            }
        });
    });
});
