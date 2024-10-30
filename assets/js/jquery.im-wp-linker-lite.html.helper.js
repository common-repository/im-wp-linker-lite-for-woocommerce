/**
* IM WP Linker
* 
* @author 	Igor Mirochnik
* @site		http://IM-Cloud.ru/
* @site		http://Ida-Freewares.ru/
* @license	GPLv3 or later
* 
*/

jQuery(function () {
	var jq = jQuery
	;
	
	jq('.im-wp-linker-lite-choose-all').click(function (e) {
		e.preventDefault();
		
		var self = jq(this)
		;
		
		self.parent().parent().find('.categorydiv label.selectit').each(function () {
			var item = jq(this)
			;
			if (!item.find('input').is(':checked'))	{
				item.trigger('click');
			}
		});
		
		return false;
	});

	jq('.im-wp-linker-lite-choose-clear').click(function (e) {
		e.preventDefault();
		
		var self = jq(this)
		;
		
		self.parent().parent().find('.categorydiv label.selectit').each(function () {
			var item = jq(this)
			;
			if (item.find('input').is(':checked'))	{
				item.trigger('click');
			}
		});
		
		return false;
	});

});