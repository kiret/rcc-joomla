EasyDiscuss.module('replies', function($) {

	var module = this;

	EasyDiscuss.require()
	.view('comment.form')
	.script('comments')
	.language(
		'COM_EASYDISCUSS_REPLY_LOADING_MORE_COMMENTS',
		'COM_EASYDISCUSS_REPLY_LOAD_ERROR')
	.done(function() {

		EasyDiscuss.Controller(
			'Replies',
			{
				defaultOptions:
				{
					termsCondition: null,

					sort: null,

					'{replyItem}': '.discussReplyItem'
				}
			},
			function(self)
			{
				return {

					init: function() {

						// Implement reply items.
						self.initItem(self.replyItem());
					},

					initItem: function(item) {

						item.implement(
								EasyDiscuss.Controller.Reply.Item,
								{
									controller: {
										parent: self
									},
									'termsCondition': self.options.termsCondition
								}
							);
					},

					addItem: function(html) {

						// Wrap item as jQuery object
						var replyItem = $(html);

						// Prepend/append item based on sorting
						if (self.options.sort == 'latest') {
							replyItem.prependTo(self.element);
						} else {

							if ($('.replyLoadMore').length == 0)
							{
								// If there's no read more on the page, just append it.
								replyItem.appendTo(self.element);
							}
							else
							{
								// check if load more controller exists and if all replies has been loaded
								$('.replyLoadMore').controller().loadedAllReplies && replyItem.appendTo(self.element);
							}
						}

						// Implement reply item
						self.initItem(replyItem);
					},

					replaceItem: function(id, html) {

						var replyItem = $(html);

						self.replyItem('[data-id=' + id + ']')
							.replaceWith(replyItem);

						self.initItem(replyItem);
					}
				};
			}

		);

		EasyDiscuss.Controller(
			'Reply.Item',
			{
				defaultOptions:
				{
					// Properties
					id: null,
					termsCondition: null,

					// Views
					view:
					{
						commentForm: 'comment.form'
					},

					// Elements
					'{addCommentButton}' : '.addComment',
					'{commentFormContainer}': '.commentFormContainer',
					'{commentNotification}'	: '.commentNotification',
					'{commentsList}'	: '.commentsList',
					'{commentLoadMore}'	: '.commentLoadMore',

					'{editReplyButton}' : '.editReplyButton',
					'{cancelReplyButton}' : '.cancel-reply',
					'{composerContainer}' : '.discuss-composer-container',
					'{composer}' : '.discuss-composer',

					'{alertMessage}': '.alertMessage'
				}
			},
			function(self )
			{
				return {
					init: function()
					{
						self.options.id = self.element.data('id');

						// Implement comments list.
						self.commentsList().implement(EasyDiscuss.Controller.Comment.List);

						// Implement comment pagination.
						self.commentLoadMore().length > 0 && self.commentLoadMore().implement(EasyDiscuss.Controller.Comment.LoadMore, {
							controller: {
								list: self.commentsList().controller()
							}
						});
					},

					'{editReplyButton} click': function()
					{
						self.edit();
					},

					alert: function(message, type, hideAfter) {

						if (type === undefined) type = 'info';

						self.removeAlert();

						$('<div class="alert alertMessage"></div>')
							.addClass('alert-' + type)
							.html(message)
							.prependTo(self.composerContainer());

						if (hideAfter) {

							setTimeout(function() {

								self.alertMessage()
									.fadeOut('slow', function() {

										self.removeAlert();
									});
							}, hideAfter);
						}
					},

					removeAlert: function() {

						self.alertMessage().remove();
					},

					edit: function() {

						self.editReplyButton()
							.addClass('btn-loading');

						// Remove any existing composer
						EasyDiscuss.ajax('site.views.post.editReply', {id: self.options.id})
							.done(function(id, composer) {

								self.composer().remove();

								// Insert composer
								self.composerContainer()
									.append(composer);

								// Initialize composer
								discuss.composer.init('.' + id);
							})
							.fail(function() {

								self.alert('Unable to load composer.', 'error', 3000);
							})
							.always(function() {

								self.editReplyButton()
									.removeClass('btn-loading');
							});
					},

					'{composer} save': function(el, event, html) {

						var replyItem = $(html).filter('.discussReplyItem');

						if (replyItem.length > 0) {

							self.composer().remove();

							self.parent.replaceItem(self.options.id, replyItem);

							var postLocation = replyItem.find('.postLocation');

							if (postLocation.length > 0) {

								var options = $.parseJSON(postLocation.find('.locationData').val());

								EasyDiscuss.require()
									.script('location')
									.done(function($) {
										postLocation.implement(
											'EasyDiscuss.Controller.Location.Map',
											options
										);
									});
							}
						}
					},

					'{composer} cancel': function() {

						self.composer().remove();
					},

					'{addCommentButton} click': function()
					{
						// Retrieve the comment form and implement it.
						var commentForm = self.view.commentForm({
							'id'	: self.options.id
						});

						$(commentForm).implement(
							EasyDiscuss.Controller.Comment.Form,
							{
								container: self.commentFormContainer(),
								notification: self.commentNotification(),
								commentsList: self.commentsList(),
								loadMore: self.commentLoadMore(),
								termsCondition: self.options.termsCondition
							}
						);

						self.commentFormContainer().html(commentForm).toggle();
					}
				};
			}
		);

		EasyDiscuss.Controller(
			'Replies.LoadMore',
			{
				defaultOptions:
				{
					id: null,
					sort: null
				}
			},
			function(self)
			{
				return {
					init: function() {
						self.loadedAllReplies = false;
					},

					'{self} click': function(el) {
						if (el.enabled()) {

							// Disable load more button
							el.disabled(true);

							// Set button to loading mode
							el.addClass('btn-loading').html($.language('COM_EASYDISCUSS_REPLY_LOADING_MORE_COMMENTS'));

							// Call for more reply
							EasyDiscuss.ajax('site.views.post.getReplies', {
								id: self.options.id,
								start: self.list.replyItem().length,
								sort: self.options.sort
							}).done(function(html, nextCycle) {

								// Filter html to get only li elements
								var items = $(html).filter('li');

								// Implement reply controller
								items.implement(
									EasyDiscuss.Controller.Reply.Item,
									{
										controller: {
											parent: self.list
										},
										'termsCondition': self.list.options.termsCondition
									}
								);

								// Append replies to list
								self.list.element.append(items);

								// Check if there are more replies to load
								if (nextCycle) {
									el.enabled(true);
								} else {
									el.hide();
									self.loadedAllReplies = true;
								}
							}).fail(function() {
								el.addClass('btn-danger').html($.language('COM_EASYDISCUSS_REPLY_LOAD_ERROR'));
							}).always(function() {
								el.removeClass('btn-loading');
							});
						}
					}
				};
			}
		);

		module.resolve();
	});
});
