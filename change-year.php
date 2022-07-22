<?php
/*
Plugin Name: Change year
Description: Change year
Author: Yelpix LLC
*/

class Example_Background_Processing_Change_Year {

	/**
	 * @var WP_Example_Request
	 */
	protected $process_single;

	/**
	 * @var WP_Example_Process
	 */
	protected $process_all;

	/**
	 * Example_Background_Processing constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	//	add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 100 );
		add_action( 'init', array( $this, 'process_handler' ) );
	}

	/**
	 * Init
	 */
	public function init() {
		require_once plugin_dir_path(__FILE__) . 'classes/wp-async-request.php';
    require_once plugin_dir_path(__FILE__) . 'classes/wp-background-process.php';
		require_once plugin_dir_path(__FILE__) . 'class-logger.php';
		require_once plugin_dir_path(__FILE__) . 'async-requests/class-example-request.php';
		require_once plugin_dir_path(__FILE__) . 'background-processes/class-example-process.php';

		$this->process_single = new WP_Example_Request_Change_Year();
		$this->process_all    = new WP_Example_Process_Change_Year();
		add_action('admin_notices', array($this, 'author_admin_notice_change_year'), 90);
		add_action( 'wp_ajax_my_action_change_year', array($this, 'my_action_change_year_callback' ));
		/*
 * Добавляем новое меню в Админ Консоль 
 */
 
// Хук событие 'admin_menu', запуск функции 'mfp_Add_My_Admin_Link()'
add_action( 'admin_menu', array($this, 'mfp_Add_My_Admin_Link') );
	}


 
// Добавляем новую ссылку в меню Админ Консоли
public function mfp_Add_My_Admin_Link()
{
 add_menu_page(
 'Change year', // Название страниц (Title)
 'Change year', // Текст ссылки в меню
 'manage_options', // Требование к возможности видеть ссылку 
 'change-year/change-year-page.php' // 'slug' - файл отобразится по нажатию на ссылку
 );
}
	
	
public function author_admin_notice_change_year(){
	if(get_current_screen()->id == 'change-year/change-year-page') { ?>
	<div class="notice referrals-statistics">
	<div class="total-ref">
		<div class="total-ref-ttl">
			Progress:
		</div>
		<div class="total-ref-count">
			<?php echo '<span class="current-progress">' . get_site_option( 'allStrs_progress' ) . '</span> / ' . get_site_option( 'allStrs' ); ?>
		</div>
	</div>
	</div>
	<style>
		.referrals-statistics {
			overflow: hidden
		}
		.referrals-statistics * {
			box-sizing: border-box;
		}
		.total-ref {
			padding: 25px;
			line-height: 20px;
		}
		.total-ref-ttl {
			width: 220px;
  float: left;
  font-size: 50px;
  line-height: 60px;
		}
		.total-ref-count {
			font-size: 70px;
			font-weight: bold;
			padding: 20px 0;
			
		}
		.subsubsub .mine {
			display: none;
		}
		@media (max-width: 767px) {
			.total-ref-ttl {
				width: 100%;
				float: none;
			}
			.total-ref-ttl {
				line-height: normal;
			}
			.total-ref {
				line-height: normal;
				padding: 0;
			}
			.total-ref-count {
				font-size: 50px;
			}
		}
</style>
<script>
jQuery(function() {
	var data = {
			action: 'my_action_change_year',
		};
	setInterval(function() {
		jQuery.ajax({
  method: "POST",
  url: ajaxurl,
  data: data,
					
					success:function(response){
						jQuery('.current-progress').text(response);
    }
})
	}, 5000);	
})
</script>
	<?php };
    
}


	
function my_action_change_year_callback() {
	echo get_site_option( 'allStrs_progress' );
	wp_die();
}
	
protected	function is_queue_empty() {
	$identifier = 'wp_example_process';
			global $wpdb;
			$table  = $wpdb->options;
			$column = 'option_name';
			if ( is_multisite() ) {
				$table  = $wpdb->sitemeta;
				$column = 'meta_key';
			}
			$key = $wpdb->esc_like( $identifier . '_batch_' ) . '%';
			$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*)
			FROM {$table}
			WHERE {$column} LIKE %s
		", $key ) );
			return ( $count > 0 ) ? false : true;
		}
	protected	function is_queue_empty_count() {
	$identifier = 'wp_example_process';
			global $wpdb;
			$table  = $wpdb->options;
			$column = 'option_name';
			if ( is_multisite() ) {
				$table  = $wpdb->sitemeta;
				$column = 'meta_key';
			}
			$key = $wpdb->esc_like( $identifier . '_batch_' ) . '%';
			$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*)
			FROM {$table}
			WHERE {$column} LIKE %s
		", $key ) );
			return  $count;
		}
	

	/**
	 * Process handler
	 */
	public function process_handler() {
		if ( ! isset($_GET['old_year']) || ! isset($_GET['new_year']) || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'update_year') ) {
			return;
		}
			$this->handle_all($_GET['old_year'], $_GET['new_year']);
			exit;
	}


	/**
	 * Handle all
	 */
	protected function handle_all($year, $new_year) {

		
		$allStrs = $this->get_names($year, $new_year);
		update_site_option( 'allStrs', count($allStrs) );
		update_site_option( 'allStrs_progress', 0 );
		
		foreach ( $allStrs as $str ) {
			$this->process_all->push_to_queue( $str );
		}
		$this->process_all->save()->dispatch();
	}
	protected function get_names($year, $new_year) {
		$my_posts = new WP_Query;
	$myposts = $my_posts->query([ 'post_status'=>'any', 'posts_per_page'=> -1 ]);
$pattern = '(?:<h3>.*<a href=.*target="_blank".*>)(.*'.$year.'.*)(?:<\/a><\/h3>)';
		$allCods = [];
		
		foreach( $myposts as $pst ){

			if (   preg_match_all( '/'. $pattern .'/i', $pst->post_content, $matches ) )
{
				foreach( $matches[1] as $value) {
					$allCods[] = ['id'=>$pst->ID, 'str'=> $value, 'type'=>'post_content', 'old_year'=>$year, 'new_year'=>$new_year];
				}
				
		};
			if (   preg_match_all( '/'. $year .'/i', $pst->post_title, $matches ) )
{
				foreach( $matches[0] as $value) {
					$allCods[] = ['id'=>$pst->ID, 'str'=> $value, 'type'=>'post_title', 'old_year'=>$year, 'new_year'=>$new_year];
				}
				
		};
			
		
	};
		echo '<script>';
		$url = admin_url( 'admin.php?page=change-year/change-year-page.php');
		echo "document.location.href = '".$url."'";
		echo '</script>';
return $allCods;
}
}

new Example_Background_Processing_Change_Year();