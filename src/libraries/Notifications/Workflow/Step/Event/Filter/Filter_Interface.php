<?php
/**
 * @package     PublishPress\Notifications
 * @author      PressShack <help@pressshack.com>
 * @copyright   Copyright (C) 2017 PressShack. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace PublishPress\Notifications\Workflow\Step\Event\Filter;

use PublishPress\Notifications\Traits\Dependency_Injector;

interface Filter_Interface {

	/**
	 * Function to render and returnt the HTML markup for the
	 * Field in the form.
	 *
	 * @return string
	 */
	public function render();

	/**
	 * Function to save the metadata from the metabox
	 *
	 * @param int     $id
	 * @param WP_Post $post
	 */
	public function save_metabox_data( $id, $post );

	/**
	 * Filters and returns the arguments for the query which locates
	 * workflows that should be executed.
	 *
	 * @param array $query_args
	 * @param array $action_args
	 * @return array
	 */
	public function get_run_workflow_query_args( $query_args, $action_args );
}
