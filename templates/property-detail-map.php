<?php
/**
 * Property Google Map template.
 */
$get_googleAPI_option = get_option('inmolink_option_name');
$get_google_api_key = $get_googleAPI_option['google_api_key'];
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $get_google_api_key; ?>"></script>
<iframe width="100%" height="480" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.it/maps?q=<?php echo $property->zipcode . ' '. $property->province_id->name; ?>&output=embed"></iframe>
