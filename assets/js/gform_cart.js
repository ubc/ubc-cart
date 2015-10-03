// -- Main js file to handle ajax operations
// -- All options and most cart operations are done via nonced ajax functions


var columns,columnsoff;

// -- chooseform - sets option for formid to be used on checkout page - option
function chooseform(element){
	formid = jQuery(element).val();
		//alert(cart_script_vars.pluginsUrl + '/ubc-cart/assets/img/ajax-loader.gif');
		 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 data: {action: 'cart_switch_form_action',cart_switch_form_action_nonce: cart_script_vars.cart_switch_form_action_nonce,js_data_for_php: formid},
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 beforeSend: function(){
		jQuery(element).parent().append('<img style="width:15px;margin-left:5px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
		 },
			 dataType: 'html',
			 success: function(response){
		jQuery('#spinner').remove();
		 }
		 });
		 return false;
}

jQuery( document ).ready(function() {
	jQuery('#gform_'+cart_script_vars.formid+' #gform_submit_button_'+cart_script_vars.formid).addClass('cartbtn');
	if (cart_script_vars.cartmenu != ''){
		jQuery('li#menu-item-'+cart_script_vars.cartmenu+' a').attr('data-after',cart_script_vars.cartitems);
		jQuery('<style>li#menu-item-'+cart_script_vars.cartmenu+' a:after{content: attr(data-after);color:red;margin-left:3px;}</style>').appendTo('head');
	}
	// -- sets the tax term from drop-down to be used as filter - option
	jQuery('#cartfilter').on('change', function($) {
		  //alert( this.value ); // or $(this).val()
		filter = this.value;
			 jQuery.ajax({
				 url: cart_script_vars.ajaxurl,
				 type: 'POST',
				 data: {action: 'cart_filter_action',cart_filter_action_nonce: cart_script_vars.cart_filter_action_nonce,js_data_for_php: filter},
				 error: function(jqXHR, textStatus) {alert(textStatus);},
				 beforeSend: function(){
			jQuery('#cartfilter').parent().append('<img style="width:15px;margin-left:5px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
			 },
				 dataType: 'html',
				 success: function(response){
			jQuery('#spinner').remove();
			 }
			 });
			 return false;
	});
	// toggles whether the menu item shows
	jQuery('#cartmenu_chk').on('change', function($) {
		checked_value = (jQuery(this).prop('checked') ? 1 : 0 ); //false or true
		//alert(checked_value);
			 jQuery.ajax({
					 url: cart_script_vars.ajaxurl,
					 type: 'POST',
					 data: {action: 'cart_menu_action',cart_menu_action_nonce: cart_script_vars.cart_menu_action_nonce,js_data_for_php: checked_value},
					 error: function(jqXHR, textStatus) {alert(textStatus);},
					 beforeSend: function(){
							jQuery('#cartmenu_chk').parent().append('<img style="width:15px;margin-left:5px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
				 },
					 dataType: 'html',
					 success: function(response){
						jQuery('#spinner').remove();
					 }
			 });
			 return false;
	});
});

// -- cartSelectColumns - sets the columns in the off and selected state - option
function cartSelectColumns(reset) {
	 var columnstr;
	if (reset){
		alert('reset');
		columnstr = 'reset';
	}
	else{
			columns = new Array();
			columnstr = '';
			jQuery("#sortable_selected li").each(function () {
				columns.push(this.id);
			});
			columnstr = columns.join(',');

			columnsoff = new Array();
			columnoffstr = '';
			jQuery("#sortable_available li").each(function () {
				columnsoff.push(this.id);
			});
			columnoffstr = columnsoff.join(',');
			columnstr += '*' + columnoffstr;
	}
	 jQuery.ajax({
		url: cart_script_vars.ajaxurl,
		type: 'POST',
		data: {
		action: 'cart_columns_action',
		cart_columns_action_nonce: cart_script_vars.cart_columns_action_nonce,
		js_data_for_php: columnstr
	},
		error: function(jqXHR, textStatus) {alert(textStatus);},
	beforeSend: function(){
			jQuery('#resetcols').parent().append('<img style="width:15px;margin-left:5px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
	},
		dataType: 'html',
		success: function(response){
		jQuery('#spinner').remove();
		jQuery('#cart-details').html('<p>'+ response +'</p>');
		if (reset)
			location.reload(true);
	}
	 });
}

// -- cart_delete_item - called from cart and form to delete item or reduce quantity
// -- onform parameter to handle page refresh for calculation fields to work
function cart_delete_item(element, itemnum, onform){
	 rownum = jQuery(element).parent().parent().index() + 1;
	 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 async: false,
			 data: {action: 'cart_delete_item_action',cart_delete_item_action_nonce: cart_script_vars.cart_delete_item_action_nonce,js_data_for_php: rownum},
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 beforeSend: function(){
			jQuery(element).attr('src',cart_script_vars.pluginsUrl+'/ubc-cart/assets/img/ajax-loader.gif');
		 },
			 dataType: 'html',
			 success: function(response){
				jQuery(element).attr('src', cart_script_vars.pluginsUrl +'/gravityforms/images/remove.png');
				var resarr = response.split("*");
				takeaction = resarr[0];
				quantcol = resarr[1]*1 + 1;
				itemrow = resarr[2];
				if (takeaction == 'remove'){
					var tr = jQuery('.cartfield_list tr:nth-child('+ itemrow+')');
					tr.remove();
				}
				if (takeaction == 'reduce'){
					var quantval = jQuery('.cartfield_list tr:nth-child('+ itemrow+') td:nth-child('+quantcol+') ').html();
					quantval-- ;
					jQuery('.cartfield_list tr:nth-child('+ itemrow+') td:nth-child('+quantcol+')').html(quantval);
				}
				jQuery('#cart-details .cartinput_list').parent().html(resarr[3]);
				cart_script_vars.cartitems = resarr[4];
				jQuery('li#menu-item-'+cart_script_vars.cartmenu+' a').attr('data-after',cart_script_vars.cartitems);
		 	}
	 });
	 return false;
}

// -- showcart - used on settings page as debug
function showcart(){
	 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 data: {action: 'cart_show_action',cart_show_action_nonce: cart_script_vars.cart_show_action_nonce},
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 //beforeSend: function(){alert('clicked');},
			 dataType: 'html',
			 success: function(response){
		//alert(response);
		jQuery('#cart-details').html('<p>'+ response +'</p>');
		 }
	 });
	 return false;
}

// -- save cart name - if name/label changed in options
function savecartname(){
	 jQuery.ajax({
		url: cart_script_vars.ajaxurl,
		type: 'POST',
		data: {action: 'cart_savename_action',cart_savename_action_nonce: cart_script_vars.cart_savename_action_nonce,js_data_for_php: jQuery('#cartname').val()},
		error: function(jqXHR, textStatus) {alert(textStatus);},
		beforeSend: function(){
			jQuery('#cartname').parent().append('<img style="width:15px;margin-left:5px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
		},
		dataType: 'html',
		success: function(response){
			//alert(response);
			jQuery('#status_cartname').text("Cart page exists so, clicking save will change the page title.");
			//change status on settings page to "Cart page exists so, clicking save will change the page title."
			jQuery('#cartmenu_chk').removeAttr("disabled");
			jQuery('#cartmenu_option').show();
			jQuery('#spinner').remove();
		}
	 });
	 return false;
}

// -- deletecart - used on settings page as debug - also need to add this to cart
function deletecart(){
	 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 data: {action: 'cart_delete_action',cart_delete_action_nonce: cart_script_vars.cart_delete_action_nonce},
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 beforeSend: function(){},
			 dataType: 'html',
			 success: function(response){
				jQuery('#cart-details .cartinput_list').parent().html('<p>'+ response +'</p>');
				cart_script_vars.cartitems = 0; 
				jQuery('li#menu-item-'+cart_script_vars.cartmenu+' a').attr('data-after',cart_script_vars.cartitems);
			 }
	 });
	 return false;
}

// -- reset_settings - reset to defaults
function reset_settings(){
	 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 data: {action: 'cart_reset_settings_action',cart_reset_settings_action_nonce: cart_script_vars.cart_reset_settings_action_nonce},
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 beforeSend: function(){},
			 dataType: 'html',
			 success: function(response){alert('settings reset')}
	 });
	 return false;
}


// -- addtocart - Adds item with postid to cart
function addtocart(obj,postid){
	 jQuery.ajax({
			 url: cart_script_vars.ajaxurl,
			 type: 'POST',
			 async: false,
			 data: {
		action: 'cart_add_action',
		cart_add_action_nonce: cart_script_vars.cart_add_action_nonce,
		js_data_for_php: postid
		 },
			 error: function(jqXHR, textStatus) {alert(textStatus);},
			 beforeSend: function(){
				jQuery(obj).find('i').removeClass('icon-shopping-cart');
				jQuery(obj).prepend('<img style="width:11px;height:11px;" id="spinner" src="'+ cart_script_vars.pluginsUrl +'/ubc-cart/assets/img/ajax-loader.gif">');
		 },
			 dataType: 'html',
			 success: function(response){
				var resarr = response.split("*");
				jQuery('#spinner').remove();
				jQuery(obj).find('i').addClass('icon-shopping-cart');
				jQuery('#cart-details').html('<p>'+ resarr[0] +'</p>');
				cart_script_vars.cartitems = resarr[1]; 
				jQuery('li#menu-item-'+cart_script_vars.cartmenu+' a').attr('data-after',cart_script_vars.cartitems);
		 }
	 });
	 return false;
 }


function mygformDeleteListItem(element, max){
	alert('override this');
	var tr = jQuery(element).parent().parent();
	var parent = tr.parent();
	tr.remove();
	mygformToggleIcons(parent, max);
	gformAdjustClasses(parent);
}

function mygformToggleIcons(table, max){
	var rowCount = table.children().length;
	table.find(".delete_list_item").css("visibility", "visible");
}

function gformDeleteListItem(element, max){
	alert('override this');
		var tr = jQuery(element).parent().parent();
		var parent = tr.parent();
		tr.remove();
		gformToggleIcons(parent, max);
		gformAdjustClasses(parent);
}

function gformAdjustClasses(table){
	var rows = table.children();
	for(var i=0; i<rows.length; i++){
		var odd_even_class = (i+1) % 2 == 0 ? "gfield_list_row_even" : "gfield_list_row_odd";
		jQuery(rows[i]).removeClass("gfield_list_row_odd").removeClass("gfield_list_row_even").addClass(odd_even_class);
	}
}
