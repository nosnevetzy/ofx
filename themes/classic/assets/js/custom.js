/*
 * Custom code goes here.
 * A template should always ship with an empty custom.js
 */

 $(document).ready(function () {
 	$('body').on('change', '.product-variants [data-product-attribute]', function (e) {
 		var input = $(e.target).closest('#add-to-cart-or-refresh').find('input.product-refresh-home');
	    input.click();
	  });

 	var $productRefresh = "";

 	$('body').on('click', 'input.product-refresh-home', function (event, extraParameters) {
	    $productRefresh = $(this);
	    event.preventDefault();
	
	    var query = $(event.target.form).serialize() + '&ajax=1&action=productrefresh';
	    var actionURL = $(event.target.form).attr('action');
	   	$.post(actionURL, query, null, 'json').then(function (resp) {
	      prestashop.emit('updateProductQuantity', {
	        reason: {
	          productUrl: resp.productUrl
	        }
	      });
	    });
	});

 	prestashop.on('updateProductQuantity', function (event) {
	 	$.post(event.reason.productUrl, { ajax: '1', action: 'refresh' }, null, 'json').then(function (resp) {
	  			$productRefresh.closest('.product-price-and-shipping').find('.product-prices').replaceWith(resp.custom_product_prices);
	  	});
	 });

 });