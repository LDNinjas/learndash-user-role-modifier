( function( $ ) { 'use strict';
	jQuery( document ).ready( function() {
		var LURMFRONTEND ={
			init: function() {

				this.roleOnChange();
				this.displayRoleOptions();
				this.displayCustomRoleField();
				this.hideRoleOption();
				this.displayChildFields();
				this.createUserRole();
				this.deleteRole();
			},

			/**
			 * delete role
			 */
			deleteRole: function() {

				$( document ).on( 'click', '.lurm-trash', function(e) {

					e.preventDefault();

					var confirmed = confirm("Do you really want to delete the user role?");

					if( confirmed ) {

						let self = $(this);
						let parent = self.parents( '.lurm-child-wrapper' );
						let val = parent.find( '.lurm-role-option' ).text();
						let selectedVal = $( '.lurm-select-text-wrap' ).text();

						if( val == selectedVal ) {
							$( '.lurm-select-text-wrap' ).text( 'Select a role' );
						}
						parent.remove();

						let data = {
							'action'          : 'delete_user_role',
							'role_name'		  : val
						};

						jQuery.post( LURM.ajaxURL, data, function( response ) {} );
					}
				} );
			},

			/**
			 * create user role
			 */
			createUserRole: function() {

				$( document ).on( 'click', '.lurm-role-text-field button', function(e) {

					e.preventDefault();

					var confirmed = confirm( "Do you really want to create this user role?" );

					if( confirmed ) {

						let self = $(this);
						self.text( self.text()+'...' );

						let selectedRole = $( '.lurm-select-text-wrap' ).text();

						selectedRole = selectedRole.trim();
						selectedRole = selectedRole.replace(/\s+/g, ' ' );
						
						let groupID = self.attr( 'data_group-id' );

						let isChecked = $( '.lurm-checkbox' ).prop( 'checked' );
						let checkEnabled = '';

						if (isChecked) {
							checkEnabled = 'true';
						} else {
							checkEnabled = 'false';
						}
						
						let newRole = $( '.lurm-role-text-field input' ).val();

						if( 'Any Other' == selectedRole && ! newRole ) {
							$( '.lurm-role-text-field input' ).css( 'border', '2px solid red' );
							return false;
						}

						let data = {
							'action'          : 'create_user_role',
							'role_name'		  : newRole,
							'is_checked'	  : checkEnabled,
							'selected_option' : selectedRole,
							'group_id'		  : groupID
						};

						jQuery.post( LURM.ajaxURL, data, function( response ) {

							let selectedRole = $( '.lurm-select-text-wrap' ).text();

							selectedRole = selectedRole.trim();
							selectedRole = selectedRole.replace(/\s+/g, ' ' );

							if( 'Any Other' == selectedRole ) {

								let newRole = $( '.lurm-role-text-field input' ).val();
								let newRollKey = newRole.replace(/\s+/g, '_').toLowerCase();
								$( '.lurm-select-text-wrap' ).text( newRole );
								let html = '<div class="lurm-role-option" data-role_key="'+newRollKey+'">'+newRole+'</div>';
								$( '.lurm-select-role-text' ).after( html );
							}

							$( '.lurm-role-text-field input' ).hide();
							$( '.lurm-role-text-field button' ).hide();
						} );
					}
				} );
			},

			/**
			 * display chield field
			 */
			displayChildFields: function() {

				$( document ).on( 'click', '.lurm-checkbox', function() {

					var isChecked = $( '.lurm-checkbox' ).prop( 'checked' );

					if (isChecked) {
						$( '.lurm-role-dropdown-wrapper' ).show();
						$( '.lurm-role-text-field button' ).text( 'Update' );
					} else {
						$( '.lurm-role-dropdown-wrapper' ).hide();
						$( '.lurm-role-text-field button' ).hide();
					}
				} );
			},

			/**
			 * hide role option
			 */
			hideRoleOption: function() {

				$(document).on('click', function(event) {
					if ( ! $( event.target ).closest( '.lurm-role-dropdown-wrapper').length && ! $( event.target ).closest( '.lurm-trash').length ) {
						$( '.lurm-inner-wrap' ).hide();
					}
				} );
			},

			/**
			 * display custom rle field
			 */
			displayCustomRoleField: function() {

				$( document ).on( 'click', '.lurm-select-role-text', function() {
					$( '.lurm-role-text-field input' ).show();
					$( '.lurm-role-text-field button' ).text( 'Create Role' );
				} );
			},

			/**
			 * display role option
			 */
			displayRoleOptions: function() {

				$( document ).on( 'click', '.lurm-role-dropdown-header', function() {
					$( '.lurm-inner-wrap' ).toggle();
				} );
			},

			/**
			 * group on change
			 */
			roleOnChange: function() {

				$( document ).on( 'click', '.lurm-role-option', function() {
					
					$( '.lurm-role-text-field button' ).show();
					$( '.lurm-inner-wrap' ).hide();

					let self = $(this);

					if( ! self.attr( 'data-role_key' ) ) {
						$( '.lurm-role-text-field input' ).show();
					} else {
						$( '.lurm-role-text-field input' ).hide();
					}

					let groupID = self.attr( 'data-role_key' );
					$( '.lurm-select-text-wrap' ).html( self.text() ); 
					
					let selectedRole = $( '.lurm-select-text-wrap' ).text();

					selectedRole = selectedRole.trim();
					selectedRole = selectedRole.replace(/\s+/g, ' ' );

					if( 'Any Other' == selectedRole ) {
						$( '.lurm-role-text-field button' ).text( 'Create Role' );
					} else {
						$( '.lurm-role-text-field button' ).text( 'Update' );
					}

				} );
			},
		}
		LURMFRONTEND.init();
	} );
} )( jQuery );