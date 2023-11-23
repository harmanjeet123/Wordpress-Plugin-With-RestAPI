<?php


/**
 * Fetch properties function
 */
function inmolink_fetch_properties($method, $url, $data) {
  $curl = curl_init();
  if(is_array($data) && !isset($data['ln']))
    $data['ln'] = get_locale();

  $settings_option = get_option('inmolink_option_name');

  $apiUrl = '';
  if(substr($settings_option['api_base_url'] , -1) == '/'){
    $apiUrl = $settings_option['api_base_url'];
  } else {
    $apiUrl = $settings_option['api_base_url'].'/';
  }

  $api_base_url = $apiUrl.$url;
  $api_access_token = $settings_option['api_access_token'];

  $data = array_filter($data);

  switch ($method){
    case "POST":
      curl_setopt($curl, CURLOPT_POST, 1);
      if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      }
    break;
    case "PUT":
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
      if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      }
    break;
    default:
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
      if ($data) {
        //curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $api_base_url .= "?" . http_build_query($data);
      }
  }

  // OPTIONS:
  curl_setopt($curl, CURLOPT_URL, $api_base_url);
  curl_setopt($curl, CURLOPT_VERBOSE, true);
  curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 1);
  curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'access_token: '.$api_access_token,
  ));
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_TIMEOUT, 10);



  // EXECUTE:
  //echo "<strong>Request</strong><br/>" . print_r(curl_getinfo($curl)) . "<br/>";
  error_log(__FILE__.":".__LINE__. " method ".$method);
  error_log(__FILE__.":".__LINE__. " api_base_url ".$api_base_url);
  error_log(__FILE__.":".__LINE__. " api_access_token ".$api_access_token);
  error_log(__FILE__.":".__LINE__. " data: ".print_r($data,1));
  $result = curl_exec($curl);



  if(curl_errno($curl))
    echo __LINE__. " error ".curl_error($curl);


  curl_close($curl);
  return json_decode($result);
}
