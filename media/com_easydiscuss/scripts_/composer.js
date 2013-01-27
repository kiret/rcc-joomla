EasyDiscuss.module('composer', function($) {

	var module = this;

	EasyDiscuss.Controller(
		'Composer',
		{
			defaultOptions:
			{
				editor: null,
				'{editor}': '.dc_reply_content',
				'{tabs}': '.formTabs [data-foundry-toggle=tab]',
				'{form}': 'form[name=dc_submit]',
				'{attachments}': 'input.fileInput',

				'{saveButton}': '.save-reply',
				'{cancelButton}': '.cancel-reply',

				'{notification}': '.replyNotification'
			}
		},
		function(self )
		{
			return {
				init: function()
				{
					// Composer ID
					self.id = self.element.attr('data-id');

					// Composer editor
					var editor = self.element.attr('data-editor') || self.options.editor;

					if (editor == 'bbcode') {

						EasyDiscuss.require()
							.library(
								'markitup',
								'autogrow'
							)
							.done(function($) {
								self.editor()
									.markItUp({set: 'bbcode_easydiscuss'})
									.autogrow({lineBleed: 1});
							});
					}

					// Automatically select the first tab
					self.tabs(':first').tab('show');

					// Resolve composer so plugin scripts can execute
					EasyDiscuss.module(self.id, function() {
						this.resolve(self);
					});
				},

				'{saveButton} click': function() {

					self.save();
				},

				'{cancelButton} click': function() {

					self.trigger('cancel');
				},

				save: function()
				{
					var params = self.form().serializeJSON();

					// Ambiguity with normal reply form
					params.content = params.dc_reply_content;

					params.files = self.attachments();

					EasyDiscuss.ajax(
							'site.views.post.saveReply',
							params,
							{
								type: 'iframe',

								reloadCaptcha: function() {
									Recaptcha.reload();
								}
							})
						.done(function(content) {

							self.trigger('save', content);
						})
						.fail(function(type, message) {

							console.log(arguments);

							console.log(self.notification());

							self.notification()
								.addClass('alert-' + type)
								.html(message)
								.show();
						});
				}
			};
		}
	);

	module.resolve();
});
