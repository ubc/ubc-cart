<?php
// Only if Gravity Forms plugin activated
if ( class_exists( 'GFForms' ) ) {
	GFForms::include_addon_framework();

	// -- Class Name : GFCartAddOn
	// -- Purpose : Creates a settings page under Gravity Forms
	// -- Settings for
	// --			 1. Checkout Page.
	// --			 2. Taxterm filter.
	// --			 3. Columns choice.
	// --			 4. Cart Settings.
	// --			 5. Debug functions.
	// -- Has to be aware of whether UBC ePayments plugin is active or not
	// -- Keeps  the default options and labels information.
	// -- Created On : March 21st 2015
	class GFCartAddOn extends GFAddOn {
		protected $_version = '1.0';
		protected $_min_gravityforms_version = '1.7.9999';
		protected $_slug = 'ubc_cart_options';
		protected $_path = 'ubc_cart/ubc_cart_options.php';
		protected $_full_path = __FILE__;
		protected $_url = 'http://www.gravityforms.com';
		protected $_title = 'UBC Cart';
		protected $_short_title = 'UBC Cart';
		public $field_order = array( 'prodid', 'prodtitle', 'prodexcerpt', 'prodquantity', 'prodmaxed', 'prodprice', 'prodshipping','prodshippingint' );
		public $field_labels = array( 'ID', 'Title', 'Description', 'Quantity', 'Maxed', 'Price', 'Shipping', 'Shipping (Int.)' );
		public $default_options = array( 'cartColumns' => 'prodid', 'cartColumnsoff' => 'prodtitle,prodexcerpt,prodquantity,prodmaxed', 'formid' => '0', 'filter' => '', 'ubcepayment' => false, 'showcartmenu' => '','cartname' => 'New Shopping Cart','cartpid' => '' );

		// -- Function Name : init
		// -- Params : None
		// -- Purpose : Loads stylesheet for options page
		public function init() {
			parent::init();
			wp_register_style( 'optionsStylesheet', plugins_url( 'css/options.css', __FILE__ ) );
			wp_enqueue_style( 'optionsStylesheet' );
		}


		// -- Function Name : is_columns_valid
		// -- Params : columns string
		// -- Purpose : Validates columns
		public function is_columns_valid($columnsoptionStr) {
			$columns = array();
			if ( ! empty( $columnsoptionStr ) ) {
				$columns = explode( ',', $columnsoptionStr );
			}
			$i = substr_count( $columnsoptionStr, ',' );
			$valid = true;
			if ( 0 < $i ) {
				foreach ( $columns as $column ) {
					if ( ! in_array( $column,$this->field_order ) ) {
						$valid = false;
						break;
					}
				}
			}
			return $valid;
		}


		// -- Function Name : is_term_valid
		// -- Params : termid
		// -- Purpose : Validates term can be blank or valid term and nothing else
		public function is_term_valid($term) {
			if ( get_term_by( 'id',$term,'ubc_product_type' ) || '' == $term ) {
				return true;
			} else {
				return false;
			}
		}

		// -- Function Name : is_cartmenu_valid
		// -- Params : $value is the menuid or empty (not to be shown)
		// -- Purpose : Validates value checkbox can be '' or valid menuid and nothing else
		public function is_cartmenu_valid($value) {
			if ( '' == $value || is_int( $value ) ) {
				return true;
			} else {
				return false;
			}
		}

		// -- Function Name : is_formid_valid
		// -- Params : formid
		// -- Purpose : Validates formid valid form and nothing else
		// -- Checkout form initially set to zero
		public function is_formid_valid($form_id) {
			if ( RGFormsModel::get_form( $form_id ) || '0' == $form_id ) {
				return true;
			} else {
				return false;
			}
		}

		// -- Function Name : is_cartname_valid
		// -- Params : valid string
		// -- Purpose : Validates string length > 0 and nothing else
		public function is_cartname_valid($value) {
			if ( empty( $value ) ) {
				return false;
			} else {
				return true;
			}
		}

		// -- Function Name : is_cartpid_valid
		// -- Params : postid
		// -- Purpose : Check if valid postid and nothing else
		public function is_cartpid_valid($value) {
			if ( '' == $value || is_int( $value ) ) {
				return true;
			} else {
				return false;
			}
		}

		// -- Function Name : is_cartoption_valid
		// -- Params : cart options
		// -- Purpose : Validates all keys and values of the cart options
		public function is_cartoption_valid( $options ) {
			//check if ALL keys are valid and they are the only ones
			if ( count( array_diff( array_keys( $this->default_options ), array_keys( $options ) ) == 0 ) ) {
				if ( self::is_columns_valid( $options['cartColumns'] ) && self::is_columns_valid( $options['cartColumnsoff'] ) && self::is_term_valid( $options['filter'] ) && self::is_formid_valid( $options['formid'] ) && self::is_cartmenu_valid( $options['showcartmenu'] ) && self::is_cartname_valid( $options['cartname'] ) && self::is_cartpid_valid( $options['cartpid'] ) ) {
					return true;
				} else {
					return false;
				}
			}
		}


		// -- Function Name : add_price_column
		// -- Params : None
		// -- Purpose : Adds the price field to options
		function add_price_column() {
			$cartoptions = get_option( 'ubc_cart_options', $this->default_options );
			$cartoptions['ubcepayment'] = true;
			$cartarrOn = array();
			if ( ! empty( $cartoptions['cartColumns'] ) ) {
				$cartarrOn = explode( ',', $cartoptions['cartColumns'] );
			}
			$cartarrOff = array();
			if ( ! empty( $cartoptions['cartColumnsoff'] ) ) {
				$cartarrOff = explode( ',', $cartoptions['cartColumnsoff'] );
			}
			if ( ! ( in_array( 'prodprice', $cartarrOn ) || in_array( 'prodprice', $cartarrOff ) ) ) {
				array_push( $cartarrOff, 'prodprice' );
				array_push( $cartarrOff, 'prodshipping' );
				array_push( $cartarrOff, 'prodshippingint' );
				$cartoptions['cartColumnsoff'] = implode( ',', $cartarrOff );
			}
			if ( $this->is_cartoption_valid( $cartoptions ) ) {
				update_option( 'ubc_cart_options', $cartoptions );
			}
		}

		// -- Function Name : remove_price_column
		// -- Params : None
		// -- Purpose : Removes the price field from options
		function remove_price_column() {
			$cartoptions = get_option( 'ubc_cart_options', $this->default_options );
			$cartoptions['ubcepayment'] = false;
			$cartarrOn = array();
			if ( ! empty( $cartoptions['cartColumns'] ) ) {
				$cartarrOn = explode( ',', $cartoptions['cartColumns'] );
			}
			$cartarrOff = array();
			if ( ! empty( $cartoptions['cartColumnsoff'] ) ) {
				$cartarrOff = explode( ',', $cartoptions['cartColumnsoff'] );
			}
			if ( in_array( 'prodprice', $cartarrOn ) ) {
				unset( $cartarrOn[ array_search( 'prodprice', $cartarrOn ) ] );
				unset( $cartarrOn[ array_search( 'prodshipping', $cartarrOn ) ] );
				unset( $cartarrOn[ array_search( 'prodshippingint', $cartarrOn ) ] );
				$cartoptions['cartColumns'] = implode( ',', $cartarrOn );
			}
			if ( in_array( 'prodprice', $cartarrOff ) ) {
				unset( $cartarrOff[ array_search( 'prodprice', $cartarrOff ) ] );
				unset( $cartarrOff[ array_search( 'prodshipping', $cartarrOff ) ] );
				unset( $cartarrOff[ array_search( 'prodshippingint', $cartarrOff ) ] );
				$cartoptions['cartColumnsoff'] = implode( ',', $cartarrOff );
			}
			if ( $this->is_cartoption_valid( $cartoptions ) ) {
				update_option( 'ubc_cart_options', $cartoptions );
			}
		}

		// -- Function Name : plugin_page
		// -- Params : None
		// -- Purpose : Creates the actual Settings page under Gravity Forms
		public function plugin_page() {

			function cart_init() {
				if ( class_exists( 'UBC_CBM' ) ) {
					$this->add_price_column();
				} else {
					$this->remove_price_column();
				}
			}

			//when plugin loaded checks for ePayments
			add_action( 'plugins_loaded', 'cart_init' );

			//when page loaded checks for ePayments
			if ( class_exists( 'UBC_CBM' ) ) {
				$this->add_price_column();
			} else {
				$this->remove_price_column();
			}

			//**********************************
			//*	CART OPTIONS				*
			//**********************************
			$cartoptions = get_option( 'ubc_cart_options', $this->default_options );
			?>
			<h3>General Features</h3>
				<div style="width:100%" class="panel-instructions">
				<ol>
					<li>Adds a new advanced field "UBC Cart" created for Gravity Forms.</li>
					<li>Adds two new merge tags "ubccart_subtotal" and "ubccart_shipping" created for use in any calculation field.</li>
					<li>New post type "UBC Products" created with custom taxonomy "UBC Product Types".</li>
					<li>Works with plugin "UBC ePayments" (if activated, price fields available as choices).</li>
					<li>Choice of what data you'd like to collect in the cart - choices include ID, Title, Excerpt, Quantity, Price.</li>
					<li>[show-cart] - shortcode to show "mini" cart on any page.</li>
				</ol>
			</div>

				   <h3>1. Setup your Gravity Form.</h3><br>
			<div style="width:100%" class="panel-instructions">
				<h3>Add the UBC Cart advanced field</h3><br>
				<img src="<?php echo esc_url( plugins_url( 'assets/img/cartfld.gif',dirname( __FILE__ ) ) ); ?>">
			</div>
			<div style="width:100%" class="panel-instructions">
				<h3>Add a calculation field using the merge tag</h3><br>
				<img src="<?php echo esc_url( plugins_url( 'assets/img/merge.gif',dirname( __FILE__ ) ) ); ?>">
			</div>

			<div class="panel-instructions">
<h3>Set the form as your checkout form.</h3>Select your checkout form that you want the UBC Cart functionality to applied to from the drop down below.</div>

			<?php
			//get the formid option here for formid and display in select - default = 0
			global $allowedposttags;
			$allowedposttags['select'] = array( 'onchange' => array(),'class' => array(),'style' => array() );
			$allowedposttags['option'] = array( 'value' => array(),'selected' => array() );
			$form_option = $cartoptions['formid'];
			$forms = RGFormsModel::get_forms( $active, 'title' );
			$select = '<div class="gcolumn_wrapper" style="height:60px;">
				<select id="chooseform" onchange="chooseform(this)">';
			$select .= '<option value="0">Please Choose Checkout Form:</option>';
			foreach ( $forms as $form ) {
				if ( $form->is_active ) {
					if ( $form->id == $form_option ) {
						$select .= '<option value="' . absint( $form->id ) . '" selected="selected">' . esc_html( $form->title ) . '</option>';
					} else {
						$select .= '<option value="' . absint( $form->id ) . '">' . esc_html( $form->title ) . '</option>';
					}
				}
			}
			$select .= '</select>';
			echo wp_kses_post( $select );
			?>

			<a href="<?php echo esc_url( site_url( '/checkout/' ) ); ?>" class="button-primary" style="margin-left:20px;" onclick="window.location.href='/checkout/'">Go to Checkout</a></div><hr>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery("#sortable_available, #sortable_selected").sortable({connectWith: '.sortable_connected', placeholder: 'placeholder', receive: function( event, ui ) {cartSelectColumns(false);}});
					jQuery(".sortable_connected li").hover(
						function () {
							jQuery(this).addClass("field_hover");
						},
						function () {
							jQuery(this).removeClass("field_hover");
						}
					);
				});
			</script>
			<h3>2. Pick your Column Choices for the cart.</h3>
			<div class="panel-instructions">
				<?php if ( $cartoptions['ubcepayment'] ) {
						echo "<img src='".esc_url( plugins_url( 'assets/img/Active.gif',dirname( __FILE__ ) ) )."'>";
} else {
				echo "<em style='color:red;'>UBC ePayments plugin is not installed/activated. No pricing fields are available.</em>";
}
			?>
			<br>Drag & drop to order and select which columns are displayed in the form.</div>
			<div class="gcolumn_wrapper">
				<div class="gcolumn_container_left">
					<div class="gform_select_column_heading">Active Columns</div>
					<ul id="sortable_selected" class="sortable_connected ui-sortable">
<?php
$colstr = $cartoptions['cartColumns'];
if ( ! empty( $colstr ) ) {
	$colarr = explode( ',', $colstr );
	foreach ( $colarr as $key => $coltxt ) {
		echo '<li id="' . esc_html( $coltxt ) . '" class="ui-sortable-handle">' . esc_html( $this->field_labels[ array_search( $coltxt, $this->field_order ) ] ) . '</li>';
	}
}
?>
					</ul>
				</div>
				<div class="column-arrow-mid"></div>
				<div id="available_column" class="gcolumn_container_right">
					<div class="gform_select_column_heading"> Inactive Columns</div>
					<ul id="sortable_available" class="sortable_connected ui-sortable">
						<?php
						$colstr = $cartoptions['cartColumnsoff'];
						if ( ! empty( $colstr ) ) {
							$colarr = explode( ',', $colstr );
							foreach ( $colarr as $key => $coltxt ) {
								echo '<li id="' . esc_html( $coltxt ) . '" class="ui-sortable-handle">' . esc_html( $this->field_labels[ array_search( $coltxt, $this->field_order ) ] ) . '</li>';
							}
						}
						?>
					</ul>
				</div>
			</div>
			<div class="panel-buttons">
						<input id="resetcols" class="button-primary" type="button" onclick="resetColumns();cartSelectColumns(true);" value="Reset to defaults">
						<script>
							function resetColumns() {
								<?php $labelstr = implode( ',', $this->field_labels );?>
								htmlstrOnst = "<?php echo esc_html( $cartoptions['cartColumns'] );?>";
								htmlstrOffst = "<?php echo esc_html( $cartoptions['cartColumnsoff'] );?>";
								if (htmlstrOnst)
									htmlstrOn = htmlstrOnst.split(",");
								else  htmlstrOn = new Array();
								if (htmlstrOffst)
									htmlstrOff = htmlstrOffst.split(",");
								else  htmlstrOff = new Array();
								labelsstr = "<?php echo esc_html( $labelstr );?>";
								labels = labelsstr.split(",");

								onhtmlstr = '';offhtmlstr='';
								for (index = 0, len = htmlstrOn.length; index < len; ++index) {
									onhtmlstr += '<li id="+htmlstrOn[index]+" class="ui-sortable-handle">'+labels[index]+'</li>';
								}
								for (index = 0, len = htmlstrOff.length; index < len; ++index) {
									offhtmlstr += '<li id="+htmlstrOff[index]+" class="ui-sortable-handle">'+labels[index]+'</li>';
								}
								jQuery('.gcolumn_container_left #sortable_selected').html(onhtmlstr);
								jQuery('.gcolumn_container_right #sortable_available').html(offhtmlstr);
							}
						</script>
			</div>
			<br><hr><h3>3. Setup your items filter (optional).</h3>
			<div class="panel-instructions">Select the taxonomy term from the dropdown below to choose a subset of items available for the cart. If this is not set, all items will be available. Note: You will need to add a few "UBC Products" to see any terms. </div>
			<div class="panel-buttons">
				<?php
					$filter_option = $cartoptions['filter'];
					wp_dropdown_categories( 'id=cartfilter&show_option_none=Select filter&show_count=1&orderby=name&echo=1&taxonomy=ubc_product_type&selected=' . $filter_option );
				?>
				<a style="margin-left:20px;" class="button-primary" type="button" href="<?php echo esc_url( get_post_type_archive_link( 'ubc_product' ) );?>">Archive Page</a>
			</div><br><hr>

			<br><h3>4. Cart Settings.</h3>
			<div style="width:100%" class="panel-instructions">
			<?php
			$str1 = '';
			$ptitle = $cartoptions['cartname'];
			$page = get_post( $cartoptions['cartpid'] );
			if ( is_null( $page ) ) {
				$str1 = 'Cart page does not exist (or not assigned) so, clicking save will create a new page with the given title.';
			} else {
				$str1 = 'Cart page exists so, clicking save will change the page title.';
			}

			?>
				<p id="status_cartname"><?php echo esc_html( $str1 ); ?></p>
				<h3>Cart Name/Label?. <input id="cartname" style="font-weight:normal;" type="text" name="cartname" value="<?php echo esc_html( $cartoptions['cartname'] ); ?>" />
				<a style="font-weight:normal;" class="button-primary" type="button" onclick="savecartname()">Save</a></h3>

			<?php
			$str2 = '';
			$locations = get_nav_menu_locations();
			if ( ! isset( $locations['primary'] ) ) {
				$str2 = 'Location "primary" does not exist so, clicking the checkbox will create the location';
				//Does Mainmenu exist
				$menu_obj = wp_get_nav_menu_object( 'Mainmenu' );
				if ( ! $menu_obj ) {
					$str2 .= ' and Mainmenu does not exist exist either - location primary will be created, a menu Mainmenu will be created and assigned to primary - the item will be added to the menu.';
				} else {
					$str2 .= ' but, Mainmenu exists - location primary will be created, and Mainmenu will be assigned to primary - the item will be added to the menu.';
				}
			} else {
				$str2 = 'Location "primary" exists';
				//Check if primary does not already have a menu assigned then assign this
				if ( ! has_nav_menu( 'primary' ) ) { //HOLD ON Mainmenu may exist
					$menu_obj = wp_get_nav_menu_object( 'Mainmenu' );
					if ( ! $menu_obj ) {
						$str2 .= ' but, does not have a menu assigned - a menu called Mainmenu will be created and assigned to primary - the item will be placed into this menu';
					} else {
						$str2 .= ' but, does not have a menu assigned - a menu called Mainmenu exists and will be assigned to primary - the item will be placed into this menu';
					}
				} else {
					$str2 .= ' and has a menu assigned - the item will be added to the menu.';
				}
			}
				//checkbox disabled until page exists
			if ( is_null( $page ) ) {
				$cartoptions['showcartmenu'] = ''; //Need to save this option.
				if ( $this->is_cartoption_valid( $cartoptions ) ) {
					update_option( 'ubc_cart_options', $cartoptions );
				}
				$disabled = 'disabled';
				echo '<style>#cartmenu_option{display:none;}</style>';
			} else {
				$disabled = '';
				echo '<style>#cartmenu_option{display:block;}</style>';
			}
			?>
				<span id="cartmenu_option"><p><?php echo esc_html( $str2 ); ?></p>
				<h3>Do you want to show the Cart in the primary menu?.
				<input id="cartmenu_chk" type="checkbox" name="showcartmenu" value="0"<?php checked( ! empty( $cartoptions['showcartmenu'] ) ); echo esc_html( $disabled ) ?> /></h3></span>
			</div>

			<br><h3>5. Debugging and Testing.</h3>
			<div class="panel-instructions">
				<ol>
					<li>Try clicking on "Add to Cart" button - this will add a "dummy" item to the cart and you should see a session id at the bottom of cart.</li>
					<li>Check if cart columns reflect the choices you made above.</li>
					<li>Click on "Go to Checkout" and make sure that the "Dummy" items show on the form and the display columns are correct.</li>
					<li>Click on "Delete Cart" to empty cart. (flush session vars)</li>
				</ol>
			</div>
			<div class="gcolumn_wrapper">
				<a href="#" class="button-primary" onclick="addtocart()">Add to Cart</a>
				<a href="#" class="button-primary" onclick="showcart()">Show Cart</a>
				<a href="#" class="button-primary" onclick="deletecart()">Delete Cart</a>
				<a href="#" class="button-primary" onclick="reset_settings()">Reset Settings to Default</a>
				<a href="<?php echo esc_url( site_url( '/checkout/' ) ); ?>" class="button-primary" style="margin-left:20px;">Go to Checkout</a>
				<div id="cart-details"></div>
			</div>
		<?php
		}
	}
}
