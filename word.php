<?php

require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");

function survey_search($resp,$stext)
{
	global $survey_fields;
	global $found_recs;
	$found=0;
	foreach($survey_fields as $sf)
	{
		$fn=$sf['field_number'];
		//		echo "$fn = {$sf['field_type']} + ";
		switch($sf['field_type'])
		{
			case(7):  //date
			$r=unserialize($resp['results_results']);
			$text_to_search = $r[$fn];
			break;
			case(3):  //checkbox
			$r=unserialize($resp['results_results']);
			$rr=unserialize($r[$fn]);
			$text_to_search=implode(".",$rr);
			break;
			default:
			$r=unserialize($resp['results_results']);
			$text_to_search=$r[$fn];
			break;
		}
		if(preg_match("/$stext/",$text_to_search))
		{
			$found=1;
		}
	}
	if($found)
	{
		$found_recs[]=$resp;
	}
}

function get_val($fn)
{
	global $survey_fields,$_res,$selected_rec;
	foreach($survey_fields as $sf)
	{
		if($sf['field_number'] == $fn)
		{
			return floatval(field_value($_res[$selected_rec],$sf));
		}
	}
}

function field_value($resp,$sf)
{
	if($sf['field_type'] != 6)
	{
		$fn=$sf['field_number'];
		switch($sf['field_type'])
		{
			case(9):  //calculator
			$str=$sf['field_choices'];
			$i=0;
			while(preg_match("/\{(.*?)\}/",$str,$matches) && $i<5)
			{
				$val=get_val($matches[1]);
				$str=str_replace("{".$matches[1]."}",$val,$str);
				$i++;
			}
			eval("\$total = ".$str.";");
			return $total;
			break;
			case(3):  //checkbox
			$r=unserialize($resp['results_results']);
			$rr=unserialize($r[$fn]);
			return implode(", ",$rr);
			break;
			case(6):  //separator
			break;
			default:
			$r=unserialize($resp['results_results']);
			$rr=$r[$fn];
			return $rr;
			break;
		}
	}
}

$arg=explode(".",e_QUERY);

$found_recs=array();
global $survey_fields;
$sql -> db_Select("survey","*","survey_id='{$arg[0]}' ");
$selected_rec=$arg[2];
$search_text=$arg[1];

if($_POST['search_text'])
{
	$search_text=$_POST['search_text'];
}
if($selected_rec=="")
{
	$selected_rec=0;
}
if($row = $sql -> db_Fetch()){extract($row);}
if(!check_class($survey_viewclass) || !$survey_save_results){header("location:".e_BASE."index.php"); exit; }

$survey_fields=unserialize($survey_parms);
$qry = "results_survey_id='{$arg[0]}' ORDER BY results_datestamp ASC";
$numresults = $sql -> db_Select("survey_results","*",$qry);
$first_response=9999999999;
$last_response=0;
while($row = $sql -> db_Fetch())
{
	$first_response = ($row['results_datestamp'] < $first_response) ? $row['results_datestamp'] : $first_response;
	$last_response = ($row['results_datestamp'] > $last_response) ? $row['results_datestamp'] : $last_response;
	$_res[]=$row;
	if($search_text)
	{
		survey_search($row,$search_text);
	}
}

$numresults=count($found_recs);
if(count($found_recs))
{
	$_res[$selected_rec]=$found_recs[$selected_rec];
}

$file_type = "msword";
$file_ending = "doc";

header("Content-Type: application/$file_type");
header("Content-Disposition: attachment; filename=$survey_name.$file_ending");
header("Pragma: no-cache");
header("Expires: 0");

$now_date = date('d-m-Y H:i');
$title = "$survey_name - $now_date";

//define separator (defines columns in excel)
$sep = "\t";
echo("$title\n\n");
print "\n---------------------------------------";
print "---------------------------------------\n";
foreach($survey_fields as $sf)
{
	$caption = "{$sf['field_text']}";
}
$reclist = ($search_text) ? $found_recs : $_res;
foreach($reclist as $r)
{
	foreach($survey_fields as $sf)
	{
		$value = field_value($r,$sf);
		$value = str_replace("\r", " ", $value);
		$value = str_replace("\n", " ", $value);
		echo "".$value." \n";
	}
	print "\n---------------------------------------";
	print "---------------------------------------\n";
}
return (true);
?>