
Migur.define("preview", function(options) {


	// Add observer to PEVIEW tab
	// to notify user if letter is not saved yet.
	$$('#tabs-newsletter li')[4].addEvent('click', function(){
		var nid = $$('[name=newsletter_id]')[0].getProperty('value');

		if (nid == '' || nid < 1) {
			alert(Joomla.JText._('PLEASE_SAVE_THE_NEWSLETTER_FIRST', "Please save the newsletter first!"));
			return false;
		}
	});


	var self = this;

	this.autocompleter = null;

	this.init = function(options) {
		
		$$('[data-control="tab-preview"]')[0].addEvent('click', this.update);
		
		$$('[data-control="preview-send"]')[0].addEvent('click', this.send);

		if (options && options.autocompleter) {
			
			this.autocompleter = options.autocompleter;
			this.autocompleter.addEvent('boxSelect', this.update);
		}	
		
	}


	// Panel updating method
	this.update = function(bit) {

		if ( !$$('[name=newsletter_id]')[0].get('value') ) {
			return;
		}

		var email = '';//bit? escape(bit[1]) : '';

		var nsId = $$('[name=newsletter_id]')[0].get('value');
		$('tab-preview-html-container').setProperty(
			'src', 
			migurSiteRoot + 'index.php?option=com_newsletter&task=newsletter.render&type=html&email='+email+'&newsletter_id='+nsId);

		var nid = $$('[name=newsletter_id]')[0].get('value');

		new Request({
			url: migurSiteRoot + 'index.php?option=com_newsletter&task=newsletter.render&type=plain&htmlencoded=true&email='+email+'&newsletter_id=' + nid,
			onComplete: function(res){

				$('tab-preview-plain-container').removeClass('preloader');

				if (res) {
					$('tab-preview-plain-container')
					.set('html', res);
				} else {
					alert("An unknown error occured");
				}
			}
		}).send();

		$('tab-preview-plain-container').addClass('preloader');
	}	
	
	
	// Sending of preview method
    this.send = function(ev) {
		
		ev.stop();
		
		var emails = this.autocompleter? this.autocompleter.getBoxes() : [];
		
		if (emails.length < 1) {
			var val = $('jform_newsletter_preview_email').getProperty('value');
			if (document.formvalidator.handlers.email.exec(val) == false) {
				alert(Joomla.JText._('PLEASE_INPUT_A_VALID_EMAIL', 'Please input a valid email'));
				return;
			}
			emails.push([null, val, -1]);
		}

        var type = ($('tab-preview-html').hasClass('active') == true)? 'html' : 'plain';
        new Request({
            url: migurSiteRoot + 'index.php?option=com_newsletter&task=newsletter.sendpreview&tmpl=component',
            data: {
                newsletter_id: $$('[name=newsletter_id]')[0].get('value'),
                emails: emails,
                type: type
            },
            onComplete: function(res){
				
				$('send-preview-preloader').removeClass('preloader');
                
				try{res = JSON.decode(res);}
				catch (e) {res = null;}
				
                var text;
                if (res && res.state == true) {
                    text = res.messages.join("\n");
                } else {
                    text = Joomla.JText._('AN_ERROR_OCCURED', "An error occured!") + "\n" +
							res.messages.join("\n");
                }
                alert(text);
            }
        }).send();
		
		$('send-preview-preloader').addClass('preloader');
    };
	
	
	// Let's initialize this stuff!
	this.init(options);
});	
