
window.addEvent('domready', function() {
try {	
	
	// Handle dependency of GROUP_ID from EVENT type
	$$('[name="jform[event]"]').addEvent('change', function(){
		var disableGroup = (this.value == 'on_register_user' || this.value == 'on_remove_user');
		$$('[name="jform[jgroup_id]"]').setProperty('disabled', disableGroup);
	});	
	$$('[name="jform[event]"]').fireEvent('change');

} catch(e) {}	
});