<?php

if (! class_exists('SunshineUpdater')) {
    class SunshineUpdater
    {
        private $api_url;
        private $product_id;
        private $product_name;
        private $type;
        private $text_domain;
        private $plugin_file;

        public $license_key;
        public $email;

        public function __construct($product_id, $product_name, $text_domain, $api_url, $type = 'theme', $plugin_file = '')
        {
            $this->product_id = $product_id;
            $this->product_name = $product_name;
            $this->text_domain = $text_domain;
            $this->api_url = $api_url;
            $this->type = $type;
            $this->plugin_file = $plugin_file;

            $this->email = 'test@email.com';
            $this->license_key = 'test';

            if ($type == 'theme') {
                add_filter('pre_set_site_transient_update_themes', [$this, 'checkForUpdate']);
            } elseif ($type == 'plugin') {
                add_filter('pre_set_site_transient_update_plugins', [$this, 'checkForUpdate']);
            }
        }

        public function getLicenseInfo()
        {
            if (! isset($this->email) || ! isset($this->license_key)) {
                return false;
            }

            $info = $this->callApi('info', [
                'p' => $this->product_id,
                'e' => $this->email,
                'l' => $this->license_key
            ]);

            return $info;
        }

        public function isUpdateAvailable() {
            $license_info = $this->getLicenseInfo();

            if ($this->isApiError($license_info)) {
                return false;
            }

            if (version_compare($license_info->version, $this->getLocalVersion(), '>')) {
                return $license_info;
            }

            return false;
        }

        private function callApi($action, $params)
        {
            $request = wp_remote_post($this->api_url . '/' . $action, ['timeout' => 15, 'sslverify' => false, 'body' => $params]);

            if (! is_wp_error($request)) {
                $request = json_decode(wp_remote_retrieve_body($request));
            }

            return $request;
        }

        private function isApiError($response)
        {
            if ($response === false) {
                return true;
            }

            if (! is_object($response)) {
                return true;
            }

            if (isset($response->error)) {
                return true;
            }

            return false;
        }

        private function getLocalVersion()
        {
            if ($this->isTheme()) {
                $theme_data = wp_get_theme();
                return $theme_data->Version;
            } else {
                $plugin_data = get_plugin_data($this->plugin_file, false);
                return $plugin_data['Version'];
            }
        }

        private function isTheme()
        {
            return $this->type == 'theme';
        }

        public function checkForUpdate($transient)
        {
            if (empty($transient->checked)) {
                return $transient;
            }

            $info = $this->isUpdateAvailable();

            if ($info !== false) {
                if ($this->isTheme()) {
                    // Theme update
                    $theme_data = wp_get_theme();
                    $theme_slug = $theme_data->get_template();

                    $transient->response[$theme_slug] = [
                        'new_version'   => $info->version,
                        'package'       => $info->package_url,
                        'url'           => $info->description_url
                    ];
                } else {
                    // Plugin update
                    $plugin_slug = plugin_basename($this->plugin_file);

                    $transient->response[$plugin_slug] = (object) [
                        'new_version'   => $info->version,
                        'package'       => $info->package_url,
                        'slug'          => $plugin_slug
                    ];
                }
            }

            return $transient;
        }
    }
}
