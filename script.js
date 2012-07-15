var $j = jQuery.noConflict();

$j(document).ready(function() {
    $j('input[name*="attending"]:not(:checked)').each(function(){
        if(this.value == "yes"){
            $j("#" + $j(this).attr("class")).hide();
            console.log("checked on startup: " + $j(this).attr("class") + " - " + $j(this).val());   
        }
    });

    $j('textarea[name*="comment"]').tipsy({trigger: 'focus', gravity: 'w', title: 'tooltip', fade: true });
    $j('#buttonRemove').tipsy({trigger: 'hover', gravity: 'n', title: 'tooltip',live: true,  opacity: 0.5});
    $j('#buttonAdd').tipsy({trigger: 'hover', gravity: 'n', title: 'tooltip', live: true,  opacity: 0.5});

    $j('#buttonAdd').click(function() {
        var num     = $j('.personItem').length - 1; // how many "duplicatable" input fields we currently have
        var newNum  = new Number(num + 1);      // the numeric ID of the new input field being added

        // create the new element via clone(), and manipulate it's ID using newNum value
        var newElem = $j('#personRegistration' + num).clone().attr('id', 'personRegistration' + newNum);
 
        // manipulate the name/id values of the input inside the new element
        newElem.children('.extrainfo').attr('id', 'extrainfo' + newNum).hide();
        newElem.find('span').removeClass('error');
        newElem.find('input').removeClass('error');
        newElem.find('.extrainfo' + num).attr('class', 'extrainfo' + newNum);
        newElem.find('#attendBox' + num).attr('id', 'attendBox' + newNum);
        newElem.find('input, textarea').attr('name', function(){
            var oldname = $j(this).attr('name');
            var newname = oldname.replace('['+(newNum -1)+']','['+newNum+']');
            return newname;
        });
        newElem.find('.number').html('# ' + (newNum + 1));

        newElem.find('input').removeAttr('checked');
        newElem.find('input[type=text]').removeAttr('value');
 
        // insert the new element after the last "duplicatable" input field
        $j('#personRegistration' + num).after(newElem);
 
        // enable the "remove" button
        $j('#buttonRemove').removeAttr('disabled');
        $j('#buttonRemove').show();
 
        // business rule: you can only add 5 names
        if (newNum == 4){
            $j('#buttonAdd').attr('disabled','disabled');
            $j('#buttonAdd').hide();
        }
     });

    $j('#buttonRemove').click(function() {
        var num = $j('.personItem').length - 1; // how many "duplicatable" input fields we currently have
        $j('#personRegistration' + num).remove();     // remove the last element

        console.log("numer remove: " + num);
     
        // enable the "add" button
        $j('#buttonAdd').removeAttr('disabled');
        $j('#buttonAdd').show();

        // if only one element remains, disable the "remove" button
        if (num-1 == 0){
            $j('#buttonRemove').attr('disabled','disabled');
            $j('#buttonRemove').hide();
        }
    });

    $j('#buttonRemove').attr('disabled','disabled');
    $j('#buttonRemove').hide();

    $j('#rsvp-form').submit(function() {
        return validate();
    });

    $j('#buttonRemove').click(function(){
        $j(this).tipsy('hide');
    });
});

$j(function(){
	$j('input[name*="attending"]').live('change', function(){
        console.log('log: ' + this.value);
    	if(this.value == "yes"){
	    	$j("#" + $j(this).attr("class")).show("slow");
	    } else {
	    	$j("#" + $j(this).attr("class")).hide("slow");
	    }
	});
});

function validate(){
    var errorMessage = '';
    console.log("Validation...");
    var result = true;
    $j('input[name*="forname"]').each(function(index){
        $j(this).removeClass('error');
        if($j(this).val() <= 1){
            $j(this).addClass('error');
            errorMessage += addErrorMessage('Achternaam #' + (index+1) + ' is niet ingevuld');
            result = false;
        }       
    });
    $j('input[name*="prename"]').each(function(index){
        $j(this).removeClass('error');
        if($j(this).val() <= 1){
            $j(this).addClass('error');
            errorMessage += addErrorMessage('Voornaam #' + (index+1) + ' is niet ingevuld');
            result = false;
        }       
    });

    $j('span').each(function(){
        $j(this).removeClass('error');     
    });

    var names = [];

    $j('input[type="radio"]').each(function(index) {
        // Creates an array with the names of all the different checkbox group.
        names[$j(this).attr('name')] = true;
        console.log('attending.val: '+ $j(this).attr('name'));
    });

        // Goes through all the names and make sure there's at least one checked.
    var counter = 1;
    for (name in names) {
        var radio_buttons = $j("input[name='" + name + "']");
        if (radio_buttons.filter(':checked').length == 0) {
            radio_buttons.filter(':first').parent().addClass('error');
            errorMessage += addErrorMessage('Aanwezigheid #'+ counter +' is niet ingevuld');
            result = false;
        } 
        counter = counter +1;
    }

    console.log("result " + result);
    
    showErrorMessage(errorMessage);
    //debugger;
    return result;
}


function addErrorMessage(text){
    return '<li>' + text + '</li>';
}

function showErrorMessage(text){
    if(text.length >= 1){
        text = '<ul class="bullet_arrow2 imglist">' + text + '</ul>';
        $j('#message-box').html(text).show("slow");
    }
}




