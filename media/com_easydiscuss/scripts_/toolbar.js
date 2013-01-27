EasyDiscuss.module('toolbar', function($) {

	var module = this;

	EasyDiscuss
	.require()
	.script('responsive')
	.done(function($) {

		EasyDiscuss.Controller(

			'Toolbar',
			{
				defaultOptions:
				{

					'{items}'	: '.toolbarItem',
					'{dropdowns}'	: '.dropdown-menu',

					// Notifications
					'{notificationLink}' : '.notificationLink',
					'{notificationDropDown}'	: '.notificationDropDown',
					'{notificationResult}'	: '.notificationResult',
					'{notificationItems}'	: '.notificationItem',
					'{notificationLoader}'	: '.notificationLoader',

					// Messaging
					'{messageLink}' : '.messageLink',
					'{messageDropDown}'	: '.messageDropDown',
					'{messageResult}'	: '.messageResult',
					'{messageLoader}'	: '.messageLoader',
					'{messageItems}'	: '.messageItem',

					// Logout
					'{logoutForm}'	: '#logoutForm',
					'{logoutButton}'	: '.logoutButton',

					// Login
					'{loginLink}'	: '.loginLink',
					'{loginDropDown}'	: '.loginDropDown',

					// Profile
					'{profileLink}'	: '.profileLink',
					'{profileDropDown}'	: '.profileDropDown'

				}
			},

			function(self) { return {

				init: function()
				{
					// Apply responsive layout on the toolbar.
					$.responsive(self.element, {

						elementWidth: function()
						{
							return self.element.outerWidth(true) - 15;
						},
						conditions:
						{
							at: (function() {

								var listWidth = 0;

								self.items().each(function(i , element ) {
									listWidth += $(element).outerWidth(true);
								});

								return listWidth;
							})(),

							alsoSwitch:
							{
								'.dc_toolbar-wrapper'	: 'narrow',
								'.dc-button'	: 'show',
								'#dc_toolbar'	: 'hidden-height'
							}
						}
					});

				},

				'{logoutButton} click' : function()
				{
					self.logoutForm().submit();
				},

				'{loginLink} click' : function()
				{
					self.messageDropDown().hide();
					self.notificationDropDown().hide();

					self.loginDropDown().toggle();
				},

				'{profileLink} click' : function()
				{
					self.messageDropDown().hide();
					self.notificationDropDown().hide();

					self.profileDropDown().toggle();
				},

				'{messageLink} click' : function()
				{
					// Hide other drop downs.
					self.profileDropDown().hide();
					self.notificationDropDown().hide();

					// If the current drop down is not active, we need to get the data.
					if (self.messageDropDown().css('display') == 'none')
					{
						var params	= {};

						params[$('.easydiscuss-token').val()]	= 1;

						EasyDiscuss.ajax('site.views.conversation.load', params,
						{
							beforeSend: function()
							{
								// Ensure that the loader is shown all the time.
								self.messageLoader().show();

								// Clear off all notification items first.
								self.messageItems().remove();
							},
							success: function(html)
							{
								// Remove loading indicator.
								self.messageLoader().hide();

								self.messageResult().append(html);
							}
						});
					}

					// Toggle the notification drop down
					self.messageDropDown().toggle();
				},

				'{notificationLink} click' : function()
				{
					self.messageDropDown().hide();
					self.profileDropDown().hide();

					// If the current drop down is not active, we need to get the data.
					if (self.notificationDropDown().css('display') == 'none')
					{
						var params	= {};

						params[$('.easydiscuss-token').val()]	= 1;

						EasyDiscuss.ajax('site.views.notifications.load', params,
						{
							beforeSend: function()
							{
								// Ensure that the loader is shown all the time.
								self.notificationLoader().show();

								// Clear off all notification items first.
								self.notificationItems().remove();
							},
							success: function(html)
							{
								// Remove loading indicator.
								self.notificationLoader().hide();

								self.notificationResult().append(html);
							}
						});
					}

					// Toggle the notification drop down
					self.notificationDropDown().toggle();

				}

			} }
		);

		module.resolve();
	});


});
