<?php

/**
 * CPT Extensions object provides additional enhanced options for CPT
 * (custom post types) by allowing additional options in the CPT
 * post registration. The following enhanced options will be enabled when
 * registering custom post types:
 *
 * default_screen_layout - integer, sets the current user's default screen layout
 * which indicates the number of columns when visiting the CPT for the first time.
 * Valid values are 1 or 2, with the current default set to 2 columns.
 *
 * simplify_publish_box - boolean, simplifies the publish metabox by only
 * displaying "Move to Trash", and the "Publish" button.
 *
 * update_bulk_messages - boolean, customize the bulk message that appear when
 * editing or updating CPTs. I.e. "Post published" can become "Site published",
 * etc.
 *
 * dragndrop_sortable - boolean, enables drag and drop re-ordering in list view.
 * Default is false for disabling drag and drop support.
 *
 * metabox_layout - number, indicates enhanced layout to display 0 - normal, 1 -
 * tabbed interface, 2 - wizard like interface.
 *
 * show_edit_slug_box - boolean, determines if the slugbox is displayed.
 *
 * disable_autosave - boolean, determins if WordPress will autosave. Default is
 * false.
 *
 * remove_bulk_actions - array, bulk item operations to remove from the list view
 * drop down combo box, i.e. ['edit', 'trash']. The default is an empty array.
 *
 * remove_row_actions - array, row operations to remove from the list view i.e.
 * ['view', 'edit']. The default is an empty array.
 *
 * remove_quick_edit - boolean, removes the Quick Edit menu option in list view.
 * The default is false to allow the Quick Edit menu to appear.
 *
 * hide_title_field - boolean, hides the title field from the CPT add and edit
 * screen. The default is true to show the title field.
 *
 * menu_icon_font - string, supports the display of an icon font for a menu item.
 * If the given string is presented with a font name and character value, a menu
 * icon css definition is included in the header. Note: the original menu_icon
 * value should be set to an empty string.
 *
 * The following enhanced labels will be added to the labels reference within the
 * custom post type options:
 *
 * enter_title_here_text - string, the suggestion text that appears in the title
 * area of the CPT "Add New" screen. The default is a localized string for the
 * value 'Enter title here'.
 *
 * @author Stephen Carnam
 * @uses WP_Hooks extends class
 * @since 4.0.0
 */
namespace Steveorevo;
if ( class_exists( 'CPT_Extensions' ) ) return;

class CPT_Extensions extends WP_Hooks {

	/**
	 * Ensure jquery dependencies are loaded.
	 */
	function init() {
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Set our additional defaults for CPT enhanced options. Where applicable,
	 * the default would be as if no option were available. I.e. slug box would
	 * normally appear and therefore the default show_edit_slug_box is true.
	 */
	function registered_post_type_1( $post_type, $args ) {
		$defaults = array(
			'default_screen_layout' => 2,
			'simplify_publish_box' => false,
			'update_bulk_messages' => false,
			'dragndrop_sortable' => false,
			'hide_title_textbox' => false,
			'show_edit_slug_box' => true,
			'disable_autosave' => false,
			'remove_bulk_actions' => [],
			'remove_row_actions' => [],
			'remove_quick_edit' => false,
			'hide_title_field' => true,
			'menu_icon_font' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		// Fill out enhanced labels.
		$labels = $args['labels'];
		$default_labels = array(
			'enter_title_here_text' => __( 'Enter title here' ),
			'publish_button' => __( 'Publish' ),
		);
		$labels = wp_parse_args( $labels, $default_labels );
		$args['labels'] = (object) $labels;

		// Update post type with enhanced defaults.
		global $wp_post_types;
		$wp_post_types[$post_type] = (object) $args;

		// Hook bulk_actions.
		add_filter( 'bulk_actions-edit-' . $post_type, array( $this, 'remove_bulk_actions' ) );
	}

	/**
	 * Gets the current list of post type arguments.
	 *
	 * @return array, An array containing the post type arguments.
	 */
	function get_post_args() {
		global $wp_post_types;
		global $post_type;
		if ( false === isset($post_type) ) return false;
		$args = $wp_post_types[$post_type];
		return $args;
	}

	/**
	 * Customize the bulk messages that appear when editing CPTs. These messages
	 * appear as a notification (i.e. Post published. View post). By supplying the
	 * singular and plural form, we will update the messages accordingly. I.e.
	 * Update our CPT bulk messages to read "Sites" vs "Posts".
	 */
	function bulk_post_updated_messages( $msg ) {
		$args = $this->get_post_args();
		if ( false === $args ) return;
		if ( isset( $args->update_bulk_messages ) ) {
			$singular = $args->labels->singular_name;
			$plural = $args->labels->name;
			foreach ( $msg['post'] as &$m ) {

				// Process plural form first
				$m = str_replace( __( 'Posts' ), $plural, $m );
				$m = str_replace( __( 'posts' ), strtolower( $plural ), $m );
				$m = str_replace( __( 'Post' ), $singular, $m );
				$m = str_replace( __( 'post' ), strtolower( $singular ), $m );
			}
		}
		return $msg;
	}

	/**
	 * Update our CPT update messages, i.e. read "Sites" vs "Posts".
	 */
	function post_updated_messages( $msg ) {
		return $this->bulk_post_updated_messages( $msg );
	}

	/**
	 * Provide persistent menu icon and set default screen layout.
	 */
	function admin_head_1() {
		global $wp_post_types;
		$css = '';
		foreach ( $wp_post_types as $post_type=>$args ) {
			if ( strpos( $args->menu_icon_font, '\e' ) !== false ) {
				$menu = new String( $args->menu_icon_font );
				$font = (string) $menu->getLeftMost( ' ' );
				$char = (string) $menu->delLeftMost( ' ' );
				$css .= "a.menu-icon-" . $post_type;
				$css .= " div.wp-menu-image:before { font: 400 20px/1 " . $font;
				$css .= " !important; content: '" . $char . "'; }\n";
			}

			// Set the default screen layout (1 or 2 columns) for the current user.
			if ( get_user_meta(  get_current_user_id(), 'screen_layout_' . $post_type, true) === ''  ) {
				update_user_meta( get_current_user_id(), 'screen_layout_' . $post_type, $args->default_screen_layout );
			}
		}
		if ( $css !== '' ) {
			echo "<style type=\"text/css\">\n" . $css . "</style>\n";
		}
	}

	/**
	 * Adjust our CPT layout accordingly.
	 */
	function admin_head() {
		global $post_type;
		$args = $this->get_post_args();
		if ( false === $args ) return;
		?>
		<style type="text/css">
			.wrap form .bottom {
				padding-bottom: 15px;
			}
			<?php
			/**
			 *  Simplify the publish panel.
			 */
			if ( true === $args->simplify_publish_box ): ?>
			body.post-type-<?php echo $post_type; ?> div#submitdiv .handlediv,
			body.post-type-<?php echo $post_type; ?> div#minor-publishing,
			body.post-type-<?php echo $post_type; ?> div#submitdiv h3 {
				display: none;
			}
			body.post-type-<?php echo $post_type; ?> div#major-publishing-actions {
				border: none;
			}
			<?php if ( get_current_screen()->action === 'add' ): ?>
			body.post-type-<?php echo $post_type; ?> div#delete-action {
				display: none;
			}
			<?php endif; ?>
			<?php endif; ?>
			<?php if ( false === $args->show_edit_slug_box ): ?>
			body.post-type-<?php echo $post_type; ?> #edit-slug-box {
				display: none;
			}
			<?php endif; ?>
			<?php if ( false === $args->hide_title_field ): ?>
			body.post-type-<?php echo $post_type; ?> div#titlediv {
				display: none;
			}
			<?php endif; ?>
			<?php
			/**
			 * Implement dragndrop_sortable stylesheet in list view.
			 */
			global $post_status;
			if ( true === $args->dragndrop_sortable && $post_status !== 'trash' ): ?>
			.wp-list-table.tags td:hover{ cursor: move; }
			.ui-sortable tr:hover {
				cursor: move;
			}
			.ui-sortable tr.alternate {
				background-color: #F9F9F9;
			}
			.ui-sortable tr.ui-sortable-helper {
				background-color: #F9F9F9;
				border-top: 1px solid #DFDFDF;
			}
			<?php endif; ?>
		</style>
		<script>
			(function($) {
				$(function() {
					<?php
					/**
					 * Implement dragndrop_sortable javascript behavior.
					 */
					global $post_status;
					if ( true === $args->dragndrop_sortable && $post_status !== 'trash' ): ?>
					$("#the-list").sortable({
						'helper': fixHelper,
						'items': 'tr',
						'axis': 'y',
						'update' : function(e, ui) {
							$.post( ajaxurl, {
								action: 'dragndrop_sortable',
								order: $("#the-list").sortable("serialize")
							});
						}
					});
					var fixHelper = function(e, ui) {
						ui.children().children().each(function() {
							$(this).width($(this).width());
						});
						return ui;
					};
					<?php endif; ?>
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Update the sort order via ajax when the user dragndrops.
	 */
	function wp_ajax_dragndrop_sortable() {
		global $wpdb;
		parse_str( $_POST['order'], $data );
		if ( is_array( $data ) ) {
			$id_arr = array();
			foreach ( $data as $key => $values ) {
				foreach ( $values as $position => $id ) {
					$id_arr[] = $id;
				}
			}

			$menu_order_arr = array( );
			foreach ( $id_arr as $key => $id ) {
				$results = $wpdb->get_results( "SELECT menu_order FROM $wpdb->posts WHERE ID = " . $id );
				foreach ( $results as $result ) {
					$menu_order_arr[] = $result->menu_order;
				}
			}
			sort( $menu_order_arr );
			foreach ( $data as $key => $values ) {
				foreach ( $values as $position => $id ) {
					$wpdb->update(
						$wpdb->posts,
						array( 'menu_order' => $menu_order_arr[$position] ),
						array( 'ID' => $id )
					);
				}
			}
		}
		die();
	}
	/**
	 * Initialize our sort order for our sortable CPTs
	 */
	function admin_init() {
		global $wp_post_types;
		global $wpdb;
		foreach ( $wp_post_types as $name=>$cpt ) {
			if( true === $cpt->dragndrop_sortable ) {
				$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = '" . $name . "' ";
				$sql .= "AND post_status IN ('publish', 'pending', 'draft', 'private', 'future') ";
				$sql .= "ORDER BY menu_order ASC";
				$results = $wpdb->get_results( $sql );
				foreach ( $results as $key => $result ) {
					$wpdb->update( $wpdb->posts, array( 'menu_order' => $key + 1), array( 'ID' => $result->ID ) );
				}
			}
		}
	}

	/**
	 * Implement our sort order when getting posts.
	 */
	function pre_get_posts( $wp_query ) {
		if ( isset( $wp_query->query['suppress_filters'] ) )
			$wp_query->query['suppress_filters'] = false;
		if ( isset( $wp_query->query_vars['suppress_filters'] ) )
			$wp_query->query_vars['suppress_filters'] = false;
		if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
			if ( isset( $wp_query->query['post_type'] ) ) {
				global $wp_post_types;
				$args = $wp_post_types[$wp_query->query['post_type']];
				if ( true === $args->dragndrop_sortable ) {
					$wp_query->set( 'orderby', 'menu_order' );
					if ( !isset( $wp_query->query['order'] ) || $wp_query->query['order'] == 'DESC' ) {
						$wp_query->set( 'order', 'ASC' );
					}
					return;
				}
			}
		}
		return $wp_query;
	}
	/**
	 * Implement our sort order when getting previous posts.
	 */
	function get_previous_post_where( $where ) {
		$args = $this->get_post_args();
		if ( true === $args->dragndrop_sortable ) {
			global $post;
			$current_menu_order = $post->menu_order;
			$where = "WHERE p.menu_order > '" . $current_menu_order . "' ";
			$where .= "AND p.post_type = '" . $post->post_type . "' AND p.post_status = 'publish'";
		}
		return $where;
	}
	/**
	 * Sort our previous posts.
	 */
	function get_previous_post_sort( $orderby ) {
		$args = $this->get_post_args();
		if ( true === $args->dragndrop_sortable ) {
			$orderby = 'ORDER BY p.menu_order ASC LIMIT 1';
		}
		return $orderby;
	}
	/**
	 * Implement our sort order when getting next posts.
	 */
	function get_next_post_where( $where ) {
		$args = $this->get_post_args();
		if ( true === $args->dragndrop_sortable ) {
			$current_menu_order = $post->menu_order;
			$where = "WHERE p.menu_order < '" . $current_menu_order . "' ";
			$where .= "AND p.post_type = '" . $post->post_type . "' AND p.post_status = 'publish'";
		}
		return $where;
	}
	/**
	 * Sort our next posts.
	 */
	function get_next_post_sort( $orderby ) {
		$args = $this->get_post_args();
		if ( true === $args->dragndrop_sortable ) {
			$orderby = 'ORDER BY p.menu_order DESC LIMIT 1';
		}
		return $orderby;
	}

	/**
	 * Customize the Publish button.
	 */
	function gettext( $translations, $text, $domain ) {
		if ( $text !== 'Publish' ) return $text;
		$args = $this->get_post_args();

		// Rewrite publish button for given post type.
		if ( false !== $args ) {
			$text = $args->labels->publish_button;
		}
		return $text;
	}

	/**
	 * Customize the suggestion text that appears in the title field.
	 */
	function enter_title_here( $text ) {
		$args = $this->get_post_args();
		if ( false === $args ) return $text;
		return $args->labels->enter_title_here_text;
	}


	/**
	 * Disable drafts
	 */
	function admin_enqueue_scripts() {
		$args = $this->get_post_args();
		if ( false === $args ) return;
		if ( true === $args->disable_autosave ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Remove bulk actions from list view.
	 */
	function remove_bulk_actions( $actions ) {
		$args = $this->get_post_args();
		if ( false === $args ) return $actions;
		foreach ( $args->remove_bulk_actions as $act ) {
			unset( $actions[$act] );
		}
		return $actions;
	}

	/**
	 * Disable Quick Edit and remove row actions from list view.
	 */
	function post_row_actions( $actions ) {
		$args = $this->get_post_args();
		if ( false === $args ) return $actions;
		foreach ( $args->remove_row_actions as $act ) {
			unset( $actions[$act] );
		}
		if ( $args->remove_quick_edit ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}
}
