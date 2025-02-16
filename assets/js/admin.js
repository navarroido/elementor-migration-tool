jQuery(document).ready(function($) {
    $('.emt-install-plugin').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const slug = button.data('slug');
        
        button.text('Installing...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'emt_install_plugin',
                slug: slug,
                nonce: emtAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.text('Installed!');
                    location.reload();
                } else {
                    button.text('Error').prop('disabled', false);
                    alert(response.data);
                }
            },
            error: function() {
                button.text('Error').prop('disabled', false);
                alert('Installation failed. Please try again.');
            }
        });
    });
}); 