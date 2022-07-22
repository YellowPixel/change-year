<?php

class WP_Example_Process_Change_Year extends WP_Background_Process_Change_Year {

	use WP_Example_Logger_Change_Year;

	/**
	 * @var string
	 */
	protected $action = 'example_process_change_year';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		$message = $this->get_message( $item );

		//$this->really_long_running_task();
		if($message) {
			$post = get_post($message['id']);
			if($message['type'] === 'post_content') {
				$content = $post->post_content;
				$new_year_str =  str_replace($message['old_year'], $message['new_year'], $message['str']);
				
				$new_content = str_replace($message['str'], $new_year_str, $content);
				//error_log(print_r('content: ' . $new_year_str, true));
				// Создаем массив данных
$my_post = array();
$my_post['ID'] = $message['id'];
$my_post['post_content'] = $new_content;

// Обновляем данные в БД
wp_update_post( wp_slash($my_post) );
			} elseif ($message['type'] === 'post_title') {
				$title = $post->post_title;
				$new_year_str =  str_replace($message['old_year'], $message['new_year'], $title);
				//error_log(print_r('title: ' . $new_year_str, true));
				// Создаем массив данных
$my_post = array();
$my_post['ID'] = $message['id'];
$my_post['post_title'] = $new_year_str;

// Обновляем данные в БД
wp_update_post( wp_slash($my_post) );
			};
			$i = get_site_option( 'allStrs_progress' );
			$i++;
			update_site_option( 'allStrs_progress', $i );
			return false;
		} else {
			
			return $item;
		}
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}

}