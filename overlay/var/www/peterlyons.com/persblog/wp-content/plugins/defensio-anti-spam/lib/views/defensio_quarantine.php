<?php 
// Helper methods common to all quarantine views
function defensio_page_count($total_items, $items_per_page) {
	if ($items_per_page <= 0)
		return 1; // avoid division by zero
		
	return ceil(floatval($total_items) / floatval($items_per_page));
}

function defensio_current_sorting($v) {
	$sort = strtolower($v['order']);

	if ($sort == '' || $sort == 'spaminess')
		return "spaminess";
	else
		return $sort;
}

function defensio_current_view_type() {
	$type = strtolower($_GET['type']);
	if ($type == 'all' || $type == '')
		return 'all';
	else
		return $type;
}

function defensio_spaminess_level($spaminess) {
	if ($spaminess <= 0.55)
		return 'Somewhat spammy';
	elseif ($spaminess <= 0.70)
		return 'Moderately spammy';
	elseif ($spaminess <= 0.9)
		return 'Quite spammy';
	else
		return 'Very spammy';
}

function defensio_class_for_spaminess($spaminess) {
	if($spaminess <= 0.55)
		return 'defensio_spam0';
	elseif($spaminess <= 0.65)
		return 'defensio_spam1';
	elseif($spaminess <= 0.70)
		return 'defensio_spam2';
	elseif($spaminess <= 0.75)
		return 'defensio_spam3';
	elseif($spaminess <= 0.80)
		return 'defensio_spam4';
	elseif($spaminess <= 0.85)
		return 'defensio_spam5';
	elseif($spaminess <= 0.90)
		return 'defensio_spam6';
	elseif($spaminess <= 0.95)
		return 'defensio_spam7';
	elseif($spaminess < 1)
		return 'defensio_spam8';
	else
		return 'defensio_spam9';
}

function defensio_date_title_format($date){
	return strftime("%B %d, %Y", strtotime($date));
}


include_once('defensio_quarantine_html.php');

?>