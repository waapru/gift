$(function(){
	$.giftCart = function(){
		var w = $('#gift-p-list-wr');
		if ( w.size() > 0 )
		$.post(w.data('url')+'gift-cart-list/',function(response){
			w.replaceWith(response);
			$('#gift-p-list-wr').on('click','a[name="modal"]',function(){
				var self = $(this),
					modal = $(self.attr('href'));
				modal.arcticmodal();
				return false;
			});
		});
	}
	$('body').on('click','.modal-block button',function(){
		var f = $(this).closest('.modal-block').find('form'),
			action = f.attr('action');
		if ( !$(this).is('[disable]') ){
			$(this).attr('disable','disable')
			$(this).text('Ждите...');
			$.post(action,f.serializeArray(),function(response){
				$.arcticmodal('close');
				$.giftCart();
			});
		}
		return false;
	})
	$.giftCart();
})