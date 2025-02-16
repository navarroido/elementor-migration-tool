jQuery(document).ready(function($) {
    console.log('EMT Admin JS loaded'); // Debug line
    
    $(document).on('click', '.emt-install-plugin', function(e) {
        e.preventDefault();
        console.log('Install button clicked'); // Debug line
        
        const button = $(this);
        const slug = button.data('slug');
        console.log('Plugin slug:', slug); // Debug line
        
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
                console.log('Ajax response:', response); // Debug line
                if (response.success) {
                    button.text('Installed!');
                    location.reload();
                } else {
                    button.text('Install Now').prop('disabled', false);
                    alert(response.data || 'Installation failed');
                }
            },
            error: function(xhr, status, error) {
                console.log('Ajax error:', error); // Debug line
                button.text('Install Now').prop('disabled', false);
                alert('Installation failed. Please try again.');
            }
        });
    });
}); 