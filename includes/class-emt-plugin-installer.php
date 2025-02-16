<?php
class EMT_Plugin_Installer {
    private $required_plugins = array(
        'migrate-guru' => array(
            'name' => 'Migrate Guru',
            'slug' => 'migrate-guru',
            'required' => true
        )
    );

    public function __construct() {
        add_action('admin_notices', array($this, 'check_required_plugins'));
        add_action('wp_ajax_emt_install_plugin', array($this, 'ajax_install_plugin'));
    }

    public function check_required_plugins() {
        if (!current_user_can('install_plugins')) {
            return;
        }

        foreach ($this->required_plugins as $plugin) {
            if (!$this->is_plugin_installed($plugin['slug'])) {
                $this->display_install_notice($plugin);
            }
        }
    }

    private function is_plugin_installed($slug) {
        $installed_plugins = get_plugins();
        return isset($installed_plugins[$slug . '/plugin.php']);
    }

    private function display_install_notice($plugin) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php 
                printf(
                    __('Elementor Migration Tool requires %s to be installed and activated. %s', 'elementor-migration-tool'),
                    '<strong>' . esc_html($plugin['name']) . '</strong>',
                    '<button class="button button-primary emt-install-plugin" data-slug="' . esc_attr($plugin['slug']) . '">' . 
                    __('Install Now', 'elementor-migration-tool') . 
                    '</button>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    public function ajax_install_plugin() {
        check_ajax_referer('emt_install_plugin', 'nonce');

        if (!current_user_can('install_plugins')) {
            wp_send_json_error(__('You do not have permission to install plugins.', 'elementor-migration-tool'));
        }

        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        
        if (!$slug) {
            wp_send_json_error(__('Invalid plugin slug.', 'elementor-migration-tool'));
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $api = plugins_api('plugin_information', array(
            'slug' => $slug,
            'fields' => array(
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ),
        ));

        if (is_wp_error($api)) {
            wp_send_json_error($api->get_error_message());
        }

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(__('Plugin installed successfully.', 'elementor-migration-tool'));
    }
} 