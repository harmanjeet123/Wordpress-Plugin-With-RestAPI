/*
* Send contact details for property single
*/
// Validation of contact form
jQuery(function() {
  // Initialize form validation on the registration form.
  jQuery("#property_contact").validate({
    // Specify validation rules
    rules: {
      name: "required",
      email: {
        required: true,
        email: true
      },
      phone: {
        required: true,
        minlength: 9,
        maxlength: 14
      }
    },
    // Specify validation error messages
    messages: {
      name: "Please enter your Name",
      phone: {
        required: "Please provide a Phone Number",
        minlength: "Your Phone number must be 10 characters long"
      },
      email: "Please enter a valid email address"
    },
    submitHandler: function(form) {
      //form.ajaxSubmit();
      formData = jQuery('#property_contact').serialize();
        jQuery.ajax({
            type : "POST",
            url : myAjax.ajaxurl,
            data:{
                action:'inmolink_propertydetail_datapost',
                formData:formData,
            },
            success: function(data) {
                jQuery('#contactform').html(data);
                // setTimeout(function() {
                //     location.reload();
                // }, 10000);
            }
        });
    }
  });
});

jQuery(document).on("click","#resetBtn", function(e){
    /*Clear all input type="text" box*/
    jQuery('#searchform input[type="text"]').val('');
    /*Clear textarea using id */
    jQuery('#searchform #location_id, #searchform #category_ids, #searchform #type_id, #searchform #bedrooms, #searchform #bathrooms, #searchform #list_price_min, #searchform #list_price_max').val('');

    jQuery('.searchdataform').val('Search');
});

jQuery(document).on('change','#searchform',function(){
  var form = jQuery(this);
  form.find('input[type=submit]').each(function(e){jQuery(this).val(jQuery(this).data('label') + ' (...)' ) })

  var q = jQuery(this).serialize();
  console.log(q);
  jQuery.ajax({
    type : "POST",
    url : myAjax.ajaxurl,
    dataType: "json",
    data:{
        action:'inmolink_ajaxsearch',
        get_args:q,
    },
    success: function(data) {
        form.find('input[type=submit]').each(function(e){jQuery(this).val(jQuery(this).data('label') + ' ('+ data.count +')' ) })
    }
  });
})

jQuery(document).on('change','#searchform.autosubmit',function(){
  var form = jQuery(this);
  form.submit();
});


jQuery(document).ready(function(){
  jQuery('select[name="listing_type"]').on('change',function(){
    var status = jQuery(this).val();
    jQuery.ajax({
        type : "POST",
        url : myAjax.ajaxurl,
        data:{action:'inmolink_ajaxsearch',priceToggle:1,status:status},
        success: function(togglePrice) {
          var togglePrice = JSON.parse(togglePrice)
          if(togglePrice){
            jQuery('#list_price_min').children('option').remove();
            jQuery('#list_price_min').append(togglePrice.list_price_min);
            jQuery('#list_price_min').multiselect('refresh');

            jQuery('#list_price_max').children('option').remove();
            jQuery('#list_price_max').append(togglePrice.list_price_max);
            jQuery('#list_price_max').multiselect('refresh');
          }//end if toggle price
        }//end success
    });//end ajax
  });//end on change
});//end doc ready