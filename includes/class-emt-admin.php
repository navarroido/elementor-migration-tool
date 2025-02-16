<?php
class EMT_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Elementor Migration', 'elementor-migration-tool'),
            __('Elementor Migration', 'elementor-migration-tool'),
            'manage_options',
            'elementor-migration-tool',
            array($this, 'render_admin_page'),
            'dashicons-migrate'
        );
    }

    public function enqueue_admin_assets($hook) {
        wp_enqueue_script(
            'emt-admin-script',
            EMT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            EMT_VERSION,
            true
        );

        wp_localize_script('emt-admin-script', 'emtAdmin', array(
            'nonce' => wp_create_nonce('emt_install_plugin')
        ));

        if ('toplevel_page_elementor-migration-tool' === $hook) {
            wp_enqueue_style(
                'emt-admin-style',
                EMT_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                EMT_VERSION
            );
        }
    }

    public function render_admin_page() {
        ?>
        <div class="wrap emt-admin-wrap">
            <h1><?php _e('Elementor Migration Tool', 'elementor-migration-tool'); ?></h1>
            
            <div class="emt-content-wrapper">
                <div class="emt-instructions">
                    <h2><?php _e('Migration Guide', 'elementor-migration-tool'); ?></h2>
                    <ol>
                        <li><?php _e('Make sure Migrate Guru is installed and activated', 'elementor-migration-tool'); ?></li>
                        <li><?php _e('Set up your Elementor hosting destination from <a href="https://elementor.com/hosting/" target="_blank">here</a>', 'elementor-migration-tool'); ?></li>
                        <li><?php _e('Follow the migration process in Migrate Guru or watch the video on this page', 'elementor-migration-tool'); ?></li>
                        <li><?php _e('Copy the token from your Elementor hosted site and paste it in the Migrate Guru wizard', 'elementor-migration-tool'); ?></li>
                        <li><?php _e('Verify your migrated site', 'elementor-migration-tool'); ?></li>
                        <li><?php _e('Share your experience <a href="https://www.trustpilot.com/review/hosting.elementor.com?utm_medium=Trustbox&utm_source=migrationPlugin" target="_blank">here</a>', 'elementor-migration-tool'); ?></li>
                    </ol>

                    <p><?php _e('Need help? We can assist you to migrate your site for free!', 'elementor-migration-tool'); ?></p>
                    <p><?php _e('Send us a message <a href="https://elementor.com/support/" target="_blank">here</a>', 'elementor-migration-tool'); ?></p>
                </div>
                
                <div class="emt-video">
                    <div class="emt-video-container">
                        <!-- Replace VIDEO_ID with your actual YouTube video ID -->
                        <iframe 
                            width="560" 
                            height="315" 
                            src="https://www.youtube.com/embed/lVx2Nlimt2c?si=XL97gWLxdX_nWXyw" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} 