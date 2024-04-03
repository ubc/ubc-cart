<?php
final class UBCCARTCustomFields {
	public $post_type;
	// These hook into to the two core actions we need to perform; creating the metabox, and saving it's contents when it is posted
	public function __construct( $post_type ) {
		require_once( ABSPATH .'wp-includes/pluggable.php' );
		$this->post_type = $post_type;
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
		add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
		add_filter( 'save_post', array( $this, 'save_date_meta' ), 10, 2 );
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

		add_meta_box(
			'date_metabox',
			__( 'Product Dates', 'ubc_product' ),
			array( $this, 'date_metabox_callback' ),
			$this->post_type,
			'side',
			'high'
		);

	}

	public function proddatetime( ) {
		global $wp_locale, $post;
		$tab_index = 4;
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
				$tab_index_attribute = " tabindex=\"$tab_index\"";
		}
		$time_adj = current_time( 'timestamp' );
		$savedtime = get_post_meta( $post->ID, 'proddatetime', true );
		$proddatetime = date( 'Y-m-d H:i:s' , intval( $savedtime ) );
		$saveddate = ( ! empty( $savedtime ) ) ? mysql2date( 'M j, Y @ H:i', $proddatetime, false ) : gmdate( 'M j, Y @ H:i', $time_adj );
		echo '<label class="proddatetime" style="display: block;">Start: <span id="cdate"><strong> '.esc_html( $saveddate ).'</strong></span>&nbsp;<a id="cdatebtn" href="#" onclick="showdate();jQuery(\'#cproddate\').toggle(500);(jQuery(\'#cdatebtn\').text() === \'Edit\') ? jQuery(\'#cdatebtn\').text(\'Close\') : jQuery(\'#cdatebtn\').text(\'Edit\');
">Edit</a></span></label>';

		$edit = ( ! empty( $savedtime ) ) ? true:false;
		$prodjj = ($edit) ? mysql2date( 'd', $proddatetime, false ) : gmdate( 'd', $time_adj );
		$prodmm = ($edit) ? mysql2date( 'm', $proddatetime, false ) : gmdate( 'm', $time_adj );
		$prodaa = ($edit) ? mysql2date( 'Y', $proddatetime, false ) : gmdate( 'Y', $time_adj );
		$prodhh = ($edit) ? mysql2date( 'H', $proddatetime, false ) : gmdate( 'H', $time_adj );
		$prodmn = ($edit) ? mysql2date( 'i', $proddatetime, false ) : gmdate( 'i', $time_adj );
		$prodss = ($edit) ? mysql2date( 's', $proddatetime, false ) : gmdate( 's', $time_adj );

		$months = "<select id='prodsel' onchange='getval(this);' name=\"prodmm\"$tab_index_attribute>\n";
		for ( $i = 1; $i < 13; $i = $i + 1 ) {
			$monthnum = zeroise( $i, 2 );
			$months .= "\t\t\t" . '<option value="' . $monthnum . '"';
			if ( $i == $prodmm ) {
				$months .= ' selected="selected"';
			}
			$months .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
		}
		$months .= '</select>';
		echo '<div id="cproddate" class="timestamp-wrap" style="display:none;">';
		/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		$allowed_tags = array( 'select' => array( 'id' => array(), 'name' => array(), 'onchange' => array() ),'option' => array( 'value' => array(), 'selected' => array() ) );
		printf( esc_html__( '%1$s%2$s, %3$s @ %4$s : %5$s' ) ,  wp_kses( $months, $allowed_tags )  ,
			'<input type="text" id="prodjj" name="prodjj" value="' . esc_html( $prodjj ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodaa" name="prodaa" value="' . esc_html( $prodaa ) . '" size="4" maxlength="4"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodhh" name="prodhh" value="' . esc_html( $prodhh ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodmn" name="prodmn" value="' . esc_html( $prodmn ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />'
		);
		echo '</div><input type="hidden" id="prodss" name="prodss" value="' . esc_html( $prodss ) . '" />';

		echo "\n\n";
		foreach ( array( 'prodmm', 'prodjj', 'prodaa', 'prodhh', 'prodmn' ) as $timeunit ) {
			echo '<input type="hidden" id="hidden_' . esc_html( $timeunit ) . '" name="hidden_' . esc_html( $timeunit ) . '" value="' . esc_html( $$timeunit ). '" />' . "\n";
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="'. esc_html( $cur_timeunit ) . '" name="'. esc_html( $cur_timeunit ) . '" value="' . esc_html( $$cur_timeunit ) . '" />' . "\n";
		}
		echo '<input type="hidden" id="prodmm" name="prodmm" value="'. esc_html( $prodmm ) .'" size="7" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />';?>

		<input name="proddatetime" type="hidden" id="proddatetime" value="
		<?php
		if ( '' == ! $savedtime ) {
			echo esc_html( get_post_meta( $post->ID, 'proddatetime', true ) );
		} else {
			echo esc_html( $time_adj );
		}
		?>" />
			<script>

				function getval(sel){
					document.getElementById("prodmm").value = sel.value;
				}

				function showdate(){
					stringcdate = jQuery('#prodsel option:selected').text().slice( -3 )+' '+jQuery('#prodjj').val()*1+', '+jQuery('#prodaa').val()+' @ '+('00'+jQuery('#prodhh').val()).slice( -2 )+':'+('00'+jQuery('#prodmn').val()).slice( -2 );
					jQuery('#cdate strong').text(stringcdate);
				}
			</script>
<?php
	}

	public function prodxdatetime( ) {
		global $wp_locale, $post;
		$tab_index = 4;
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 ) {
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		}
		$time_adj = 0;//current_time('timestamp');
		//retrieve metadata value if it exists
		$savedtime = get_post_meta( $post->ID, 'prodxdatetime', true );
		$prodxdatetime = date( 'Y-m-d H:i:s', intval( $savedtime ) );
		$saveddate = mysql2date( 'M j, Y @ H:i', $prodxdatetime, false );
		if ( ( 'Jan 1, 1970 @ 00:00' === $saveddate ) || ( empty( $prodxdatetime ) ) ) {
			$saveddate = 'Never';
			$past = '';
			$never = '';
		} else {
			if ( strtotime( $prodxdatetime ) < current_time( 'timestamp' ) ) {
				$past = 'style="color:#a00;"';
				$never = 'Reset';
			}
		}
		echo '<label class="proddatetime" style="display: block;">End: <span id="expirydate" '.wp_kses_post( $past ).'><strong> '. esc_html( $saveddate ).'</strong></span>&nbsp;<a id="expirybtn" href="#" onclick="showcdate();jQuery(\'#cexpirydate\').toggle(500);(jQuery(\'#expirybtn\').text() === \'Edit\') ? jQuery(\'#expirybtn\').text(\'Close\') : jQuery(\'#expirybtn\').text(\'Edit\');
">Edit</a><a id="creset" onclick="creset();" href="#"> '. esc_html( $never ).'</a></span></label>';

		$edit = ( ! empty( $savedtime ) ) ? true:false;

		$prodxjj = ($edit) ? mysql2date( 'd', $prodxdatetime, false ) : gmdate( 'd', $time_adj );
		$prodxmm = ($edit) ? mysql2date( 'm', $prodxdatetime, false ) : gmdate( 'm', $time_adj );
		$prodxaa = ($edit) ? mysql2date( 'Y', $prodxdatetime, false ) : gmdate( 'Y', $time_adj );
		$prodxhh = ($edit) ? mysql2date( 'H', $prodxdatetime, false ) : gmdate( 'H', $time_adj );
		$prodxmn = ($edit) ? mysql2date( 'i', $prodxdatetime, false ) : gmdate( 'i', $time_adj );
		$prodxss = ($edit) ? mysql2date( 's', $prodxdatetime, false ) : gmdate( 's', $time_adj );

		$monthsx = "<select id='prodxsel' onchange='getvalx(this);' name=\"prodxmm\"$tab_index_attribute>\n";
		for ( $i = 1; $i < 13; $i = $i + 1 ) {
			$monthnum = zeroise( $i, 2 );
			$monthsx .= "\t\t\t" . '<option name="'.$prodxmm.'" value="' . $monthnum . '"';
			if ( $i == $prodxmm ) {
					$monthsx .= ' selected="selected"';
			}
			$monthsx .= '>' . $monthnum . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
		}
		$monthsx .= '</select>';

		echo '<div id="cexpirydate" class="timestamp-wrap" style="display:none;">';
		/* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
		$allowed_tags = array( 'select' => array( 'id' => array(), 'name' => array(), 'onchange' => array() ),'option' => array( 'value' => array(), 'selected' => array() ) );
		printf( esc_html__( '%1$s%2$s, %3$s @ %4$s : %5$s' ) , wp_kses( $monthsx, $allowed_tags ) ,
			'<input type="text" id="prodxjj" name="prodxjj" value="' . esc_html( $prodxjj ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodxaa" name="prodxaa" value="' . esc_html( $prodxaa ) . '" size="4" maxlength="4"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodxhh" name="prodxhh" value="' . esc_html( $prodxhh ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />' ,
			'<input type="text" id="prodxmn" name="prodxmn" value="' . esc_html( $prodxmn ) . '" size="2" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />'
		);
		echo '</div><input type="hidden" id="prodxss" name="prodxss" value="' . esc_html( $prodxss ) . '" />';

		echo "\n\n";
		foreach ( array( 'prodxmm', 'prodxjj', 'prodxaa', 'prodxhh', 'prodxmn' ) as $timeunit ) {
			echo '<input type="hidden" id="hidden_' . esc_html( $timeunit ) . '" name="hidden_' . esc_html( $timeunit ) . '" value="' . esc_html( $$timeunit ) . '" />' . "\n";
			$cur_timeunit = 'cur_' . $timeunit;
			echo '<input type="hidden" id="'. esc_html( $cur_timeunit ) . '" name="'. esc_html( $cur_timeunit ) . '" value="' . esc_html( $$cur_timeunit ) . '" />' . "\n";
		}
		echo '<input type="hidden" id="prodxmm" name="prodxmm" value="'.esc_html( $prodxmm ).'" size="7" maxlength="2"' . esc_html( $tab_index_attribute ) . ' autocomplete="off" />';?>

		<input name="prodxdatetime" type="hidden" id="prodxdatetime" value="
		<?php
		if ( '' == ! $savedtime ) {
			echo esc_html( get_post_meta( $post->ID, 'prodxdatetime', true ) );
		} else {
			echo esc_html( $time_adj );
		}
		?>" />
		<script>

			function getvalx(sel){
				document.getElementById("prodxmm").value = sel.value;
			}

			function creset(){
				jQuery('#expirydate strong').text('Never');
				jQuery('#creset').remove();
				//now set all inputs to 1/1/1970 00:00
				jQuery('#prodxjj').val('01');
				jQuery('#prodxaa').val('1970');
				jQuery('#prodxhh').val('00');
				jQuery('#prodxmn').val('00');
				jQuery('#prodxss').val('00');
				jQuery('#prodxsel').val('01');
				jQuery('#prodxmm').val('01');
			}
			function showcdate(){
				stringcdate = jQuery('#prodxsel option:selected').text().slice( -3 )+' '+jQuery('#prodxjj').val()*1+', '+jQuery('#prodxaa').val()+' @ '+('00'+jQuery('#prodxhh').val()).slice( -2 )+':'+('00'+jQuery('#prodxmn').val()).slice( -2 );
				if ((stringcdate === 'Jan 1, 1970 @ 00:00'))
					stringcdate = 'Never';
				jQuery('#expirydate strong').text(stringcdate);
			}
		</script>

<?php
	}

	public function save_date_meta( $post_id ) {

		if ( ! isset( $_POST['ubc_product_nonce'] ) || ! wp_verify_nonce( $_POST['ubc_product_nonce'], 'date_metabox_nonce' ) ) {
			return;
		}

		// Check if the current user has permission to edit the post. */
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( isset( $_POST['proddatetime'] ) ) {
			$new_proddatetime = strtotime( $_POST['prodaa'].'-'.$_POST['prodmm'].'-'.$_POST['prodjj'].' '.$_POST['prodhh'].':'.$_POST['prodmn'].':'.$_POST['prodss'] );
			update_post_meta( $post_id, 'proddatetime', $new_proddatetime );
		}

		if ( isset( $_POST['prodxdatetime'] ) ) {
			$new_prodxdatetime = strtotime( $_POST['prodxaa'].'-'.$_POST['prodxmm'].'-'.$_POST['prodxjj'].' '.$_POST['prodxhh'].':'.$_POST['prodxmn'].':'.$_POST['prodxss'] );
			update_post_meta( $post_id, 'prodxdatetime', $new_prodxdatetime );
		}

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

	public function date_metabox_callback( $post ) {
?>

			<form action="" method="post">
				<?php
				// add nonce for security
				wp_nonce_field( 'date_metabox_nonce', 'ubc_product_nonce' );
				?>

			<label for "proddate"><?php __( 'Date', 'ubc_product' ); ?></label>
			<div id="proddatetimediv" class="">
			<?php $this->proddatetime(); ?></div>
			<div id="prodxdatetimediv" class="">
			<?php $this->prodxdatetime(); ?>
			</div>
			</form>

	<?php }

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
						$formatter = new NumberFormatter( 'en_US', NumberFormatter::DECIMAL );
						update_post_meta( $post_id, $field_id, $formatter->format( $_POST[ $field_id ] ) );
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
