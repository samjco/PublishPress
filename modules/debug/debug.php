<?php
/**
 * @package PublishPress
 * @author  PublishPress
 *
 * Copyright (c) 2018 PublishPress
 *
 * ------------------------------------------------------------------------------
 * Based on Edit Flow
 * Author: Daniel Bachhuber, Scott Bressler, Mohammad Jangda, Automattic, and
 * others
 * Copyright (c) 2009-2016 Mohammad Jangda, Daniel Bachhuber, et al.
 * ------------------------------------------------------------------------------
 *
 * This file is part of PublishPress
 *
 * PublishPress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

use PublishPress\Core\Modules\AbstractModule;
use PublishPress\Core\Modules\ModuleInterface;
use PublishPress\Debug\DebuggerTrait;

if (!class_exists('PP_Debug')) {


    /**
     * Class PP_Debug.
     *
     * @todo Rename this class for PSR-2 compliance.
     */
    class PP_Debug extends AbstractModule implements ModuleInterface
    {
        use DebuggerTrait;

        const SETTINGS_SLUG = 'pp-debug';

        public $module_name = 'debug';

        public $module;

        public $isEnabled = false;

        public $isInitialized = false;

        protected $debugEngine;

        /**
         * Construct the PP_Debug class
         */
        public function __construct()
        {
            $this->twigPath = __DIR__ . '/twig';

            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'           => __('Debug', 'publishpress'),
                'module_url'      => $this->module_url,
                'icon_class'      => 'dashicons dashicons-feedback',
                'slug'            => 'debug',
                'autoload'        => true,
                'general_options' => [
                    'debug_enabled' => 'off',
                ],
            ];

            $publishpress = PublishPress();

            $this->module      = $publishpress->register_module($this->module_name, $args);
            $this->debugEngine = $publishpress->getDebugEngine();

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            // Register our settings
            add_action('admin_init', [$this, 'register_settings']);

            if ($this->debugEngine->isEnabled()) {
                add_action('publishpress_admin_menu', [$this, 'action_admin_menu'], 30);
            }
        }

        /**
         * Load the capabilities onto users the first time the module is run
         *
         * @since 1.11.0
         */
        public function install()
        {
            $this->setDefaultCapabilities();
        }

        /**
         * Upgrade our data in case we need to
         *
         * @since 1.11.0
         */
        public function upgrade($previous_version)
        {
            if (version_compare($previous_version, '1.11', '<')) {
                $this->log('[debug]: upgrading for < 1.11');
                $this->setDefaultCapabilities();
            }
        }

        /**
         * Set default capabilities.
         *
         * @since 1.11.0
         */
        protected function setDefaultCapabilities()
        {
            $role = get_role('administrator');

            if (is_object($role)) {
                $role->add_cap('manage_debug_entries');

                $this->log('[debug]: setting default capabilities');
            }
        }

        /**
         * Register settings for notifications so we can partially use the Settings API
         * (We use the Settings API for form generation, but not saving)
         *
         * @since 0.7
         */
        public function register_settings()
        {
            add_settings_section(
                $this->module->options_group_name . '_general',
                false,
                '__return_false',
                $this->module->options_group_name
            );

            add_settings_field(
                'debug_enabled',
                __('Debug enabled', 'publishpress'),
                [$this, 'settings_debug_enabled_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );
        }

        /**
         * Enable or disable the debug
         *
         * @since 1.11.0
         */
        public function settings_debug_enabled_option()
        {
            $options = [
                'off' => __('Disabled', 'publishpress'),
                'on'  => __('Enabled', 'publishpress'),
            ];

            $enabled = isset($this->module->options->debug_enabled) ? $this->module->options->debug_enabled : false;

            echo '<select id="debug_enabled" name="' . $this->module->options_group_name . '[debug_enabled]">';
            foreach ($options as $value => $label) {
                echo '<option value="' . esc_attr($value) . '"';
                echo selected($enabled, $value);
                echo '>' . esc_html($label) . '</option>';
            }

            echo '</select>';
        }

        /**
         * Settings page for the dashboard
         *
         * @since 0.7
         */
        public function print_configure_view()
        {
            settings_fields($this->module->options_group_name);
            do_settings_sections($this->module->options_group_name);
        }

        /**
         * Add necessary things to the admin menu
         */
        public function action_admin_menu()
        {
            // Main Menu
            add_submenu_page(
                'pp-calendar',
                esc_html__('Debug', 'publishpress'),
                esc_html__('Debug', 'publishpress'),
                apply_filters('publishpress_manage_debug_entries_cap', 'manage_debug_entries'),
                'pp-manage-debug',
                [$this, 'render_admin_page']
            );
        }

        /**
         *
         */
        public function render_admin_page()
        {
            global $publishpress;

            $publishpress->settings->print_default_header($publishpress->modules->debug);

            $debugContent = $this->debugEngine->getLog();

            echo '<div class="wrap">';
            echo nl2br($debugContent);
            echo '</div>';

            $publishpress->settings->print_default_footer($publishpress->modules->debug);
        }
    }
}
