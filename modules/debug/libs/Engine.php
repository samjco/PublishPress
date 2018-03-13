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

namespace PublishPress\Debug;

class Engine
{
    /**
     * @var float
     */
    protected $startTime;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $logPath;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Engine constructor.
     *
     * @param string $logPath
     * @param int $startTime
     */
    public function __construct($logPath, $startTime = null)
    {
        $this->logPath = $logPath;

        if (!empty($startTime)) {
            $this->startTime = $startTime;
        } else {
            $this->startTime = microtime(true);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (is_null($this->enabled)) {
            $options = get_option('publishpress_debug_options');

            if (!empty($options)) {
                $options = maybe_unserialize($options);

                if (isset($options->debug_enabled)) {
                    $this->enabled = 'on' === $options->debug_enabled;
                }
            }
        }

        return $this->enabled;
    }

    /**
     * Initialize the hooks.
     */
    public function init()
    {
        // Only initialize if enabled.
        if (!$this->isEnabled() || $this->initialized) {
            return;
        }

        add_action('publishpress_debug_log', [$this, 'addLog'], 1, 2);

        do_action('publishpress_debug_log', '##### Debug engine started #####');
        do_action('publishpress_debug_log', 'REQUEST_URI: ' . $_SERVER['REQUEST_URI']);

        if (function_exists('get_current_user_id')) {
            $user  = get_user_by('ID', get_current_user_id());
            $roles = implode(', ', $user->roles);
            do_action('publishpress_debug_log', 'USER: %s, %s, roles: %s', [$user->ID, $user->user_login, $roles]);
        }

        $this->initialized = true;
    }

    /**
     * This method accepts dynamic arguments. If present, they will be used to parse
     * the log message using sprintf.
     *
     * @param string $log
     * @param array  $arguments
     */
    public function addLog($log, $arguments = array())
    {
        if (!empty($log)) {
            // Check if we have additional parameters
            if (!empty($arguments)) {
                $log = vsprintf($log, $arguments);
            }

            $ms  = microtime(true) - $this->startTime;
            $ms  = round($ms * 1000);

            $log = sprintf(
                "\n[%s] %s ms  -  %s",
                date('Y-m-d H:m:s'),
                $ms,
                $log
            );

            file_put_contents($this->logPath, $log, FILE_APPEND);
        }
    }

    /**
     * @return string
     */
    public function getLog()
    {
        if (file_exists($this->logPath)) {
            return file_get_contents($this->logPath);
        }

        return '';
    }
}
