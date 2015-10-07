=== UBC Cart Plugin by Shaffiq Rahemtulla at Arts ISIT, UBC ===

Contributors: shaffiqr

== Description ==

The UBC Cart plugin combines a Gravity Forms Addon and a session management layer which when combined with the UBC ePayments plugin, gives you a shopping cart functionality.

* This is a private plugin and for use only within UBC Vancouver.

== Requirements ==

** Wordpress 4.2
** Gravity Forms Plugin (Commercial)

** Optional - UBC ePayment plugin for payment and pricing fields

== Installation ==

1. Install and activate plugin as usual.
2. Click on the settings link and follow the instructions.

== Gravity Forms Add-on ==

Uses Gravity forms hooks and filters to create a new advanced button and field in the forms editor call "UBC Cart" - this field essentially mimics the standard GF list field but with the following changes.

The values of the field are gathered from the session variable and the field inputs are read-only. No changes to this field can be made directly - All changes are made to the session variable (UBC Cart) and then reflected on the form.

Price and shipping subtotals are also calculated independently (from the session var) and are made available via merge tags. This can be used in any calculation field. e.g. To calculate the GST, Add a price field and make it a a calculation field (setting). Then select {ubccart_subtotal} * 0.05 as the calculation.

All price fields are summed to give the total (Standard Gravity Forms behaviour).

In conjunction with UBC ePayments plugin, this gives you the shopping cart functionality - because you select this form in the ePayments settings page and the total is sent to CBM.

In addition, the entries and notification and other features of Gravity Forms are available to you.

== Session Management ==

Adds $_SESSION-like functionality to WordPress.

Every visitor, logged in or not, is issued an instance of WP_Session. Their instance will be identified by an ID stored in the _wp_session cookie. Typically, session data will be stored in a WordPress transient.

This provides plugin and theme authors the ability to use WordPress-managed session variables without having to use the standard PHP $_SESSION superglobal.

https://github.com/ericmann/wp-session-manager

The actual data in the session object is stored in a short-lived transient. The expiration date of the transient is advanced every time the object is touched (either a read or a write) because it’s still active.  It is essentially just a serialized associative array and can store just about anything WordPress needs to store.

The advantage of using transients (which are just WordPress options) is that they can be backed by memcached in certain optimized configurations.  Also, the session data will always be available where WordPress’ data is available since they both exist in the same place.

The wrapper class for UBC Cart is basically just a setter and getter class for the session manager.

== Version 1.0 ==

Release date: October 1, 2015

* New shortcodes and menu button added.

== Version 0.9 ==

Release date: August 7, 2015

* Fixed linting errors as per phpcs and 'Wordpress-Extra' standard

== Version 0.8 ==

Release date: March 21, 2015

* Initial Release
