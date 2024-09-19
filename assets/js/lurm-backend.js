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
				this.applySelect2();
				this.displayGroupBundleChildFields();
				this.enrolledIntoGroups();
				this.tagsOnChange();
			},

			/**
			 * tag dropdown on change
			 */
			tagsOnChange: function() {

				$( document ).on( 'change', '.lurm-course-tags', function() {
					
					$( '.lurm-enrolled-course-btn' ).show();
					$( '.lurm-enrolled-course-btn button' ).show();
					$( '.lurm-enrolled-course-btn button' ).text( 'Update' );
					
					setTimeout( function(){

						$( '.lurm-main-wrapper .select2-search__field' ).removeAttr( 'style' );
						$( '.lurm-main-wrapper .select2-search__field' ).css( 'width', '300px' );
						$( '.lurm-main-wrapper .select2-selection' ).css( 'width', '300px' );
						$( '.lurm-main-wrapper .select2-selection__rendered' ).css( 'width', '300px' );
					}, 2000 );
				} );
			},

			/**
			 * Enrolled into group 
			 */
			enrolledIntoGroups: function() {

				$( document ).on( 'click', '.lurm-enrolled-course-btn button', function(e) {

					e.preventDefault();

					let self = $(this);
					let groupID = self.attr( 'data-group_id' );

					if( ! groupID ) {
						alert( 'Please create group first...' );
					}

					var confirmed = confirm("Are you sure you want to update the tags?");
					
					let isChecked = $( '.lurm-group-bundle-checkbox' ).prop( 'checked' );

					if( isChecked ) {
						isChecked = 'true';
					} else {
						isChecked = 'false';
					}

					if( confirmed ) {

						self.text( 'Updating...' );
						let Tags = $( '.lurm-course-tags' ).val();

						let data = {
							'action'          : 'assign_course_to_group',
							'tags'		      : JSON.stringify( Tags ),
							'group_id'		  : groupID,
							'is_check'		  : isChecked
						};

						jQuery.post( LURM.ajaxURL, data, function( response ) {
							$( '.lurm-enrolled-course-btn button' ).text( 'Update' );
						} );
					}
				} );
			},

			/**
			 * display group bundle child fields
			 */
			displayGroupBundleChildFields: function() {

				$( document ).on( 'click', '.lurm-group-bundle-checkbox', function() {

					var isChecked = $( '.lurm-group-bundle-checkbox' ).prop( 'checked' );

					if (isChecked) {
						$( '.lurm-group-bundle-content' ).show();
						$( '.mld-lurm-update-group-status' ).hide();
						$( '.lurm-enrolled-course-btn' ).show();
					} else {
						$( '.mld-lurm-update-group-status' ).show();
						$( '.lurm-group-bundle-content' ).hide();
					}
				} );
			},

			/**
			 * apply select 2
			 */
			applySelect2: function() {

				$( '.lurm-course-tags' ).select2( {
					placeholder: "Select tag(s)",
					allowClear: true
				} );

				setTimeout( function() {
					$( '.lurm-main-wrapper .select2-search__field' ).removeAttr( 'style' );
					$( '.lurm-main-wrapper .select2-search__field' ).css( 'width', '300px' );
					$( '.lurm-main-wrapper .select2-selection' ).css( 'width', '300px' );
					$( '.lurm-main-wrapper .select2-selection__rendered' ).css( 'width', '300px' );
				}, 2000 );
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
					let self = $(this);

					let groupID = self.attr( 'data_group-id' );

					if( ! groupID ) {
						alert( 'Please create group first...' );
					}

					let selectedVal = $( '.lurm-select-text-wrap' ).text();

					selectedVal = selectedVal.trim();
					selectedVal = selectedVal.replace(/\s+/g, ' ' );

					let confirmationText = "Do you really want to update?";
					
					if( 'Any Other' == selectedVal ) {
						confirmationText = "Do you really want to create this user role?";
					}

					var confirmed = confirm( confirmationText );

					if( confirmed ) {

						self.text( self.text()+'...' );

						let newRole = $( '.lurm-role-text-field input' ).val();
						
						if( newRole ) {

							var replacedVal = newRole.replace(/ /g, '_');
							var finalVal = replacedVal.toLowerCase();
							$( '.lurm-select-text-wrap' ).attr( 'role_key', finalVal );
						}

						let selectedRole = $( '.lurm-select-text-wrap' ).text();
						selectedRole = selectedRole.trim();
						selectedRole = selectedRole.replace(/\s+/g, ' ' );
						let selectedKey = $( '.lurm-select-text-wrap' ).attr( 'role_key' );

						let isChecked = $( '.lurm-checkbox' ).prop( 'checked' );
						
						let checkEnabled = '';

						if (isChecked) {
							checkEnabled = 'true';
						} else {
							checkEnabled = 'false';
						}

						if( 'Any Other' == selectedRole && ! newRole ) {
							$( '.lurm-role-text-field input' ).css( 'border', '2px solid red' );
							return false;
						}

						let data = {
							'action'            : 'create_user_role',
							'role_name'		    : newRole,
							'is_checked'	    : checkEnabled,
							'selected_option'   : selectedRole,
							'group_id'		    : groupID,
							'selected_role_key' : selectedKey
						};

						jQuery.post( LURM.ajaxURL, data, function( response ) {

							let selectedRole = $( '.lurm-select-text-wrap' ).text();

							selectedRole = selectedRole.trim();
							selectedRole = selectedRole.replace(/\s+/g, ' ' );

							if( 'Any Other' == selectedRole ) {

								let newRole = $( '.lurm-role-text-field input' ).val();
								let newRollKey = newRole.replace(/\s+/g, '_').toLowerCase();
								$( '.lurm-select-text-wrap' ).text( newRole );
								var replacedValue = newRole.replace(/ /g, '_');
            					var finalValue = replacedValue.toLowerCase();
            					$( '.lurm-select-text-wrap' ).attr( 'role_key', finalValue );

								let html = '<div class="lurm-child-wrapper"><div class="lurm-role-option" style="width: 85%;" data-role_key="'+newRollKey+'">'+newRole+'</div><div class="lurm-trash dashicons dashicons-trash"></div></div>';
								$( '.lurm-select-role-text' ).after( html );
							}

							$( '.lurm-role-text-field input' ).hide();
							$( '.lurm-role-text-field button' ).text( 'Update' );
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
						$( '.mld-lurm-update-group-status' ).hide();
						$( '.lurm-role-dropdown-wrapper' ).show();
						$( '.lurm-role-text-field button' ).text( 'Update' );
					} else {
						$( '.mld-lurm-update-group-status' ).show();
						$( '.lurm-role-dropdown-wrapper' ).hide();
						// $( '.lurm-role-text-field button' ).hide();
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

					let role_key = self.attr( 'data-role_key' );
					$( '.lurm-select-text-wrap' ).html( self.text() );	 
					$( '.lurm-select-text-wrap' ).attr( 'role_key', role_key );

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