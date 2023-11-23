<?php
/**
 * Property Features Section template.
 */
$features = array();
foreach ($property->features as $value) 
{
	$featureType = (string)$value->attr_id->name;
	$feature = (string)$value->name;

	if(!isset($features[$featureType]))
		$features[$featureType] = array();

	$features[$featureType][] = $feature;
}

foreach ($features as $featureType => $singleFeatures) {
	echo '<li class="features">';
	echo '<h4>'.$featureType.'</h4>';
	foreach ($singleFeatures as $feature) {
		echo '<p>'.$feature.'</p>';
	}
	echo '</li>';
}
