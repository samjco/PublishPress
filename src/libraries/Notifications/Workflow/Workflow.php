<?php
/**
 * @package     PublishPress\Notifications
 * @author      PressShack <help@pressshack.com>
 * @copyright   Copyright (C) 2017 PressShack. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace PublishPress\Notifications\Workflow;

use PublishPress\Notifications\Traits\Dependency_Injector;

class Workflow {

	use Dependency_Injector;

	/**
	 * The post of this workflow.
	 *
	 * @var WP_Post
	 */
	protected $workflow_post;

	/**
	 * An array with arguments set by the action
	 *
	 * @var array
	 */
	protected $action_args;

	/**
	 * The constructor
	 *
	 * @param WP_Post  $workflow_post
	 */
	public function __construct( $workflow_post ) {
		$this->workflow_post = $workflow_post;
	}

	/**
	 * Returns a list of receivers ids for this workflow
	 *
	 * @return array
	 */
	protected function get_receivers() {
		/**
		 * Filters the list of receivers for the notification workflow.
		 *
		 * @param WP_Post $workflow
		 * @param array   $args
		 */
		$receivers = apply_filters( 'publishpress_notif_run_workflow_receivers', [], $this->workflow_post, $this->action_args );

		if ( ! empty( $receivers ) ) {
			// Sanitize values
			$receivers = array_map( 'intval', $receivers );

			// Remove duplicate IDs
			$receivers = array_unique( $receivers, SORT_NUMERIC );
		}

		// Check if the receivers have muted or not this workflow
		foreach ( $receivers as $index => $receiver ) {
			$channel = get_user_meta( $receiver, 'psppno_workflow_channel_' . $this->workflow_post->ID, true );

			if ( 'mute' === $channel) {
				unset( $receivers[ $index ] );
			}
		}

		return $receivers;
	}

	/**
	 * Returns the content for the notification, as an associative array with
	 * the following keys:
	 *     - subject
	 *     - body
	 *
	 * @return string
	 */
	protected function get_content() {
		$content = [ 'subject' => '', 'body' => '' ];
		/**
		 * Filters the content for the notification workflow.
		 *
		 * @param WP_Post $workflow
		 * @param array   $args
		 */
		$content = apply_filters( 'publishpress_notif_run_workflow_content', $content, $this->workflow_post, $this->action_args );

		if ( ! array_key_exists( 'subject', $content ) ) {
			$content['subject'] = '';
		}

		if ( ! array_key_exists( 'body', $content ) ) {
			$content['body'] = '';
		}

		// Replace placeholders in the subject and body
		$content['subject'] = $this->filter_shortcodes( $content['subject'] );
		$content['body']    = $this->filter_shortcodes( $content['body'] );

		return $content;
	}

	/**
	 * Runs this workflow without applying any filter. We assume it was
	 * already filtered in the query.
	 *
	 * @param array $args
	 */
	public function run( $args ) {
		$this->action_args = $args;

		// Who will receive the notification?
		$receivers = $this->get_receivers();

		// If we don't have receivers, abort
		if ( empty( $receivers ) ) {
			return;
		}

		// What will the notification says?
		$this->get_service( 'shortcodes' )->register( $this->workflow_post, $this->action_args );
		$content = $this->get_content();
		$this->get_service( 'shortcodes' )->unregister();

		/**
		 * Triggers the notification. This can be caught by notification channels.
		 *
		 * @param WP_Post $workflow_post
		 * @param array   $action_args
		 * @param array   $receivers
		 * @param array   $content
		 */
		do_action( 'publishpress_notif_notify', $this->workflow_post, $this->action_args, $receivers, $content );
	}

	/**
	 *
	 */
	protected function filter_shortcodes( $text ) {
		return do_shortcode( $text );
	}
}
