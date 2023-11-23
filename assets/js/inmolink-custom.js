jQuery(document).ready(function(){
    
    if(typeof(Cookies.get('shortlist')) != "undefined" && Cookies.get('shortlist') != ""){
    	jQuery('.shortlist_counter').html(Cookies.get('shortlist').split(',').length);
    }else{
        jQuery('.shortlist_counter').html('0');
    }

	jQuery(document).on('click','.add_to_shortlist',function(){
		var shortlist = Cookies.get('shortlist');
		if(typeof(shortlist) != "undefined" && shortlist !== null && shortlist !== ""){

			var ref = jQuery(this).data('ref');
			shortlist = Cookies.get('shortlist').split(',');

			if(jQuery.inArray(ref,shortlist) != -1 ){
				shortlist.splice( jQuery.inArray(ref,shortlist), 1 );
				jQuery(this).html(jQuery(this).data('label-add'));
			}else{
				shortlist.push(ref);
				jQuery(this).html(jQuery(this).data('label-remove'));
			}
			shortlist = shortlist.join(',');

		}else{
			shortlist = jQuery(this).data('ref');
			jQuery(this).html(jQuery(this).data('label-remove'));
		}

		Cookies.set('shortlist',shortlist, { expires: 7 , path: '/' });

        var shortlistCount = 0;
        if(shortlist != "")
            shortlistCount = shortlist.split(',').length;

    	jQuery('.shortlist_counter').html(shortlistCount);
    	jQuery(parent.document).find('.shortlist_counter').html(shortlistCount);
    })
    

    /*jQuery('input[type="reset"]').click(function()
    {
        jQuery(this).closest('form').find("select").val('');
        jQuery(this).closest('form').find('input[type=text]').val('').attr('value','');
        return false;
    })*/
    
    // Patch for sliders behind Divi tabs not showing slide until rotation.
    jQuery('.content-slider').each(function(i,e){
        var n_slides = jQuery(e).find(".sliderHiddenSrc").length
        var poster = jQuery(e).find(".sliderHiddenSrc")[n_slides-1]
        jQuery(poster).attr('src',jQuery(poster).data('src'))
    })

    jQuery(".content-slider").lightSlider({
        gallery:false,
        item:1,
        slideMargin: 0,
        speed:500,
        auto:false,
        loop:true,
        onSliderLoad: function(el) {
            jQuery('.content-slider').removeClass('cS-hidden');

            var poster = jQuery(el).find(".sliderHiddenSrc")[1]
            if(poster !== undefined)
            {
                jQuery(poster).attr('src',jQuery(poster).data('src'))
                jQuery(poster).removeClass('sliderHiddenSrc')
            }
        },
        onBeforeNextSlide: inmolinkRevealSliderImages, 
        onBeforePrevSlide: inmolinkRevealSliderImages,
        onBeforeSlide: inmolinkRevealSliderImages
        
    });

    jQuery('#image-gallery').lightSlider({
        gallery:true,
        item:1,
        thumbItem:9,
        slideMargin: 0,
        speed:500,
        auto:false,
        loop:true,
        onSliderLoad: function() {
            jQuery('#image-gallery').removeClass('cS-hidden');
        }  
    });

    jQuery('.popup-gallery').magnificPopup({
        delegate: 'a',
        type: 'image',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
        },
        image: {
            tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
            titleSrc: function(item) {
                return item.el.attr('title') + '<small></small>';
            }
        }
    });

});

function inmolinkRevealSliderImages(el){
    jQuery(el).find(".sliderHiddenSrc").each(function(arr,e){
        jQuery(e).attr('src',jQuery(e).data('src')) 
        jQuery(e).removeClass('sliderHiddenSrc')
    })
}
jQuery(document).ready(function(){
    var img_url = jQuery(".logo-agent-src").html();
    var img_app = '<img src="'+img_url+'">';
    jQuery(".logo-agent-src").html('');
    jQuery(".logo-agent-src").append(img_app);

})