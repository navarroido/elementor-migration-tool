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

                    <hr>

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

            <?php
            // More robust check for Migrate Guru
            function is_migrate_guru_active() {
                // Check if get_plugins() function exists
                if (!function_exists('get_plugins')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                $all_plugins = get_plugins();
                $is_installed = isset($all_plugins['migrate-guru/migrate-guru.php']);
                
                // Check for both normal and network activation
                $is_activated = in_array('migrate-guru/migrate-guru.php', (array) get_option('active_plugins', array()));
                if (is_multisite()) {
                    $is_activated = $is_activated || is_plugin_active_for_network('migrate-guru/migrate-guru.php');
                }

                return $is_installed && $is_activated;
            }

            // Only show notice if Migrate Guru is not installed or not activated
            if (!is_migrate_guru_active()) : ?>
                <div class="notice notice-warning">
                    <p>
                        <?php _e('Elementor Migration Tool requires Migrate Guru to be installed and activated.', 'elementor-migration-tool'); ?>
                        <a href="<?php echo admin_url('plugin-install.php?s=migrate+guru&tab=search&type=term'); ?>">
                            <?php _e('Install Now', 'elementor-migration-tool'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
} 