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
        
        // Check for multiple possible plugin base names
        $possible_bases = array(
            $slug . '/plugin.php',
            $slug . '/' . $slug . '.php',
            $slug . '/index.php'
        );

        foreach ($possible_bases as $base) {
            if (isset($installed_plugins[$base])) {
                return true;
            }
        }

        return false;
    }

    private function display_install_notice($plugin) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php 
                printf(
                    __('Elementor Migration Tool requires %s to be installed and activated. %s', 'elementor-migration-tool'),
                    '<strong>' . esc_html($plugin['name']) . '</strong>',
                    '<button type="button" class="button button-primary emt-install-plugin" data-slug="' . esc_attr($plugin['slug']) . '">' . 
                    __('Install Now', 'elementor-migration-tool') . 
                    '</button>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    public function ajax_install_plugin() {
        // Add error logging
        error_log('EMT: Ajax install plugin called');
        
        if (!check_ajax_referer('emt_install_plugin', 'nonce', false)) {
            error_log('EMT: Nonce verification failed');
            wp_send_json_error('Security check failed');
            return;
        }

        if (!current_user_can('install_plugins')) {
            error_log('EMT: User lacks permission');
            wp_send_json_error('You do not have permission to install plugins.');
            return;
        }

        $slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
        error_log('EMT: Installing plugin with slug: ' . $slug);
        
        if (!$slug) {
            error_log('EMT: No slug provided');
            wp_send_json_error('Invalid plugin slug.');
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        try {
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
                error_log('EMT: API Error: ' . $api->get_error_message());
                wp_send_json_error($api->get_error_message());
                return;
            }

            $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
            $result = $upgrader->install($api->download_link);

            if (is_wp_error($result)) {
                error_log('EMT: Install Error: ' . $result->get_error_message());
                wp_send_json_error($result->get_error_message());
                return;
            }

            // Get the plugin basename
            $plugin_basename = $upgrader->plugin_info();
            
            if (!$plugin_basename) {
                error_log('EMT: Could not determine plugin basename');
                wp_send_json_error('Plugin installed but could not be activated.');
                return;
            }

            $activation_result = activate_plugin($plugin_basename);
            
            if (is_wp_error($activation_result)) {
                error_log('EMT: Activation Error: ' . $activation_result->get_error_message());
                wp_send_json_error('Plugin installed but could not be activated: ' . $activation_result->get_error_message());
                return;
            }

            error_log('EMT: Plugin successfully installed and activated');
            wp_send_json_success('Plugin installed and activated successfully.');

        } catch (Exception $e) {
            error_log('EMT: Exception: ' . $e->getMessage());
            wp_send_json_error('Installation failed: ' . $e->getMessage());
        }
    }

    public function install_required_plugins() {
        if (!current_user_can('install_plugins')) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $results = array();

        foreach ($this->required_plugins as $plugin) {
            if (!$this->is_plugin_installed($plugin['slug'])) {
                $result = $this->install_plugin($plugin['slug']);
                $results[$plugin['slug']] = $result;
            } elseif (!$this->is_plugin_active($plugin['slug'])) {
                $result = $this->activate_plugin($plugin['slug']);
                $results[$plugin['slug']] = $result;
            }
        }

        return $results;
    }

    private function install_plugin($slug) {
        try {
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
                return array('success' => false, 'message' => $api->get_error_message());
            }

            $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
            $result = $upgrader->install($api->download_link);

            if (is_wp_error($result)) {
                return array('success' => false, 'message' => $result->get_error_message());
            }

            // Get the plugin basename
            $plugin_basename = $upgrader->plugin_info();
            
            if (!$plugin_basename) {
                return array('success' => false, 'message' => 'Could not determine plugin basename');
            }

            // Activate the plugin
            $activation_result = $this->activate_plugin($plugin_basename);
            
            if (!$activation_result['success']) {
                return $activation_result;
            }

            return array('success' => true, 'message' => 'Plugin installed and activated successfully');

        } catch (Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    private function activate_plugin($plugin_basename) {
        if (!is_plugin_active($plugin_basename)) {
            $activation_result = activate_plugin($plugin_basename);
            
            if (is_wp_error($activation_result)) {
                return array('success' => false, 'message' => $activation_result->get_error_message());
            }
        }
        return array('success' => true, 'message' => 'Plugin activated successfully');
    }

    private function is_plugin_active($slug) {
        $plugin_basename = $this->get_plugin_basename($slug);
        return is_plugin_active($plugin_basename);
    }

    private function get_plugin_basename($slug) {
        $possible_bases = array(
            $slug . '/plugin.php',
            $slug . '/' . $slug . '.php',
            $slug . '/index.php'
        );

        foreach ($possible_bases as $base) {
            if (file_exists(WP_PLUGIN_DIR . '/' . $base)) {
                return $base;
            }
        }

        return $slug . '/' . $slug . '.php'; // Default fallback
    }
} 