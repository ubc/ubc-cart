<?php
final class UBCCARTCustomFields {
	public $post_type;
	// These hook into to the two core actions we need to perform; creating the metabox, and saving it's contents when it is posted
	public function __construct( $post_type ) {
		require_once( ABSPATH .'wp-includes/pluggable.php' );
		$this->post_type = $post_type;
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
		add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'printmessages' ) );
	}

	public function create_meta_box( $post_type ) {
		add_meta_box(
			'location_information_meta_box',
			'Pricing Fields',
			array( $this, 'print_meta_box' ),
			$this->post_type,
			'normal',
			'high'
		);
	}

	public function print_meta_box( $post, $metabox ) {
		?>

			<input type="hidden" name="meta_box_ids[]" value="<?php echo esc_html( $metabox['id'] ); ?>" />

			<?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' ); ?>

			<table class="form-table">
			<tr><th><label for="price"><?php esc_html_e( 'Price', 'plugin-namespace' ); ?></label></th>
			<td>$<input name="price" type="text" id="price" value="
			<?php
			if ( ! '' == get_post_meta( $post->ID, 'price', true ) ) {
				echo esc_html( get_post_meta( $post->ID, 'price', true ) );
			} else {
				echo '0.0';
			}
			?>" class="regular-text"></td></tr>
			<tr><th><label for="maxitems"><?php esc_html_e( 'Max items per cart', 'plugin-namespace' ); ?></label></th>
			<td>&nbsp;&nbsp;&nbsp;<input name="maxitems" type="text" id="maxitems" value="
			<?php
			if ( ! get_post_meta( $post->ID, 'maxitems', true ) == '' ) {
				echo absint( get_post_meta( $post->ID, 'maxitems', true ) );
			} else {
				echo '50';
			}
			?>" class="regular-text"></td></tr>
			<tr><th><label for="shipping"><?php esc_html_e( 'Shipping', 'plugin-namespace' ); ?></label></th>
			<td>$<input name="shipping" type="text" id="shipping" value="
			<?php
			if ( ! get_post_meta( $post->ID, 'shipping', true ) == '' ) {
				echo esc_html( get_post_meta( $post->ID, 'shipping', true ) );
			} else {
				echo '0.0';
			}
			?>" class="regular-text"></td></tr>
		<tr><th><label for="shippingint"><?php esc_html_e( 'Shipping International', 'plugin-namespace' ); ?></label></th>
			<td>$<input name="shippingint" type="text" id="shippingint" value="
			<?php
			if ( ! get_post_meta( $post->ID, 'shippingint', true ) == '' ) {
				echo esc_html( get_post_meta( $post->ID, 'shippingint', true ) );
			} else {
				echo '0.0';
			}
			?>" class="regular-text"></td></tr>
			</table>

			<input type="hidden" name="<?php echo esc_html( $metabox['id'] ); ?>_fields[]" value="price" />
			<input type="hidden" name="<?php echo esc_html( $metabox['id'] ); ?>_fields1[]" value="maxitems" />
			<input type="hidden" name="<?php echo esc_html( $metabox['id'] ); ?>_fields[]" value="shipping" />
			<input type="hidden" name="<?php echo esc_html( $metabox['id'] ); ?>_fields[]" value="shippingint" />
		<?php
	}

	public function printmessages() {
		$error_flag = get_option( 'cartcf_error_flag', false );
		if ( $error_flag ) {
			echo '<div class="error"><p>ERROR: The price/shipping fields have to be numeric with no currency symbol and 2 decimals - the field has been reset to 0 OR the maximum items per cart has to be an integer - field has been reset to 1.</p></div>';
		}
	}

	public function save_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }

		if ( ! isset( $_POST[ $metabox['id'] . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $metabox['id'] . '_nonce' ], 'save_' . $metabox['id'] ) ) {

			if ( empty( $_POST['meta_box_ids'] ) ) { return; }

			foreach ( $_POST['meta_box_ids'] as $metabox_id ) {
				// Verify thhe request to update this metabox
				if ( isset( $_POST[ $metabox_id . '_nonce' ] ) && ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ) { continue; }

				// Determine if the metabox contains any fields that need to be saved
				if ( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ) { continue; }

				// Iterate through the registered fields
				foreach ( $_POST[ $metabox_id . '_fields' ] as $field_id ) {
					// Update or create the submitted contents of the fields as post meta data
					// http://codex.wordpress.org/Function_Reference/update_post_meta

					if ( filter_var( $_POST[ $field_id ], FILTER_VALIDATE_FLOAT ) === false ) {
						update_option( 'cartcf_error_flag', true );
						update_post_meta( $post_id, $field_id, '0.0' );
					} else {
						update_option( 'cartcf_error_flag', false );
						$fmt = '%.2n';
						update_post_meta( $post_id, $field_id, money_format( $fmt,$_POST[ $field_id ] ) );
					}
				}
				foreach ( $_POST[ $metabox_id . '_fields1' ] as $field_id ) {
					if ( filter_var( $_POST[ $field_id ], FILTER_VALIDATE_INT ) === false ) {
						update_option( 'cartcf_error_flag', true );
						update_post_meta( $post_id, $field_id, '1' );
					} else {
						update_option( 'cartcf_error_flag', false );
						update_post_meta( $post_id, $field_id, $_POST[ $field_id ] );
					}
				}
			}
		}
		return $post;
	}
}
