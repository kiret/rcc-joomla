EasyDiscuss.module('likes', function($) {
	var module = this;

	EasyDiscuss.Controller('Likes' ,
	{
		defaultOptions:
		{
			postId: null,
			registeredUser: null,

			// Action buttons
			'{likeButton}'	: '.btnLike',
			'{unlikeButton}'	: '.btnUnlike',
			'{likeText}'	: '.likeText'
		}
	},
	function(self) {
		return {

			init: function()
			{
				self.options.postId = self.element.data('postid');

				self.element.data('like', true);

				// Add a loading class.
				// self.likeText().addClass( 'loadingBar' );

				// Set the like data.
				// self.getLikesData();
			},

			getLikesData: function()
			{
				EasyDiscuss.ajax('site.views.likes.getData' ,
				{
					'id'	: self.options.postId
				})
				.done(function(result ) {
					self.likeText().html(result);
				});
			},

			likeItem: function()
			{
				if (!self.options.registeredUser)
				{
					return false;
				}

				EasyDiscuss.ajax('site.views.likes.like' ,
				{
					'postid' : self.options.postId
				})
				.done(function(result )
				{
					self.likeText().html(result);
				});
			},

			'{likeButton} click' : function(element )
			{
				// If user is not logged in, do not allow them to click this.
				self.likeItem();
				$(element).addClass('btn-primary');
			},

			'{unlikeButton} click' : function(element )
			{
				$(element).removeClass('btn-primary');
				self.likeItem();
			},

			'{unlikeButton} mouseover' : function(element )
			{
				$(element).find('i')
					.removeClass('icon-ed-love')
					.addClass('icon-ed-remove');
			},
			'{unlikeButton} mouseout' : function(element )
			{
				$(element).find('i')
					.removeClass('icon-ed-remove')
					.addClass('icon-ed-love');
			}
		};
	});

	$(document).on('mouseover.discussLikes', '.discussLikes', function() {

		var e = $(this);

		if (e.data('like') == undefined) {
			var registeredUser = e.attr('data-registered-user') === 'true';

			e.implement(
				EasyDiscuss.Controller.Likes,
				{
					registeredUser: registeredUser
				}
			);
		}
	});

	module.resolve();
});
