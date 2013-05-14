<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	print.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."np_class.php");
require_once(e_PLUGIN."survey/survey.inc.php");
e107::plugLan('survey', e_LANGUAGE.'_front');

echo "<link rel='stylesheet' href='".THEME."style.css'>";

function np($url, $from, $view, $total, $td, $qs="")
{
	/*
	# Next previous pages
	# - parameter #1:		string $url, refer url
	# - parameter #2:		int $from, start figure
	# - parameter #3:		int $view, items per page
	# - parameter #4:		int $total, total items
	# - parameter #5:		string $td, comfort text
	# - parameter #6:		string $qs, QUERY_STRIING, default null
	# - return				null
	# - scope					public
	*/
	if($total == 0)
	{
		return;
	}
	$ns = new e107table;
	echo "<table style=\"width:100%\">
	<tr>";
	if($from >= 1)
	{
		$s = $from-$view;
		echo "<td style=\"width:33%\" class=\"nextprev\">";
		if($qs != "")
		{
			$text = "<div style=\"text-align:left\"><span class=\"smalltext\"><a href=\"".$url."?".$qs.".".$s."\">".NP_1."</a></span></div>";
		}
		else
		{
			$text = "<div style=\"text-align:left\"><span class=\"smalltext\"><a href=\"".$url."?".$s."\">".NP_1."</a></span></div>";
		}
		echo $text;
	}
	else
	{
		echo "<td style=\"width:33%\">&nbsp;";
	}

	echo "</td>\n<td style=\"width:34%\" class=\"nextprev\">";
	$start = $from+1;
	$finish = $from+$view;
	if($finish>$total)
	{
		$finish = $total;
	}
	$text = "<div style=\"text-align:center\"><span class=\"smalltext\">$td $start - $finish ".LAN_SUR25." $total</span></div>";
	echo $text;

	$s = $from+$view;
	if($s < $total)
	{
		echo "</td><td style=\"width:33%\" class=\"nextprev\">";
		if($qs != "")
		{
			$text = "<div style=\"text-align:right\"><span class=\"smalltext\"><a href=\"".$url."?".$qs.".".$s."\">".NP_2."</a></span></div></td>";
		}
		else
		{
			$text = "<div style=\"text-align:right\"><span class=\"smalltext\"><a href=\"".$url."?".$s."\">".NP_2."</a></span></div></td>";
		}
		echo $text;
	}
	else
	{
		echo "</td><td style=\"width:33%\">&nbsp;</td>";
	}
	echo "</tr>\n</table>";
}

function update_record($id)
{
	global $sql, $arg, $tp;
	$id = intval($id);
	$sid = intval($arg[0]);
	$sql -> db_Select("survey","*","survey_id='{$sid}' ");
	if($row = $sql -> db_Fetch())
	{
		extract($row);
	}
	$parms = unserialize($survey_parms);
	foreach($parms as $parm)
	{
		$fn = $parm['field_number'];
		$v = $_POST['results'][$fn];
		$fvalue[$f]=$v;

		if($parm['field_type'] == 3)
		{
			$ser = array();
			foreach($v as $x)
			{
				$ser[]=$tp->toDB($x);
			}
			$res[$fn]=serialize($ser);
			unset($ser);
		}
		else
		{
			$res[$fn]=$tp->toDB($v);
		}
	}
	$results=serialize($res);
	//	$qry = "results_results='$results' WHERE results_id='{$id}' ";
	$sql -> db_Update("survey_results","results_results='{$results}' WHERE results_id='{$id}' ");
}

function survey_search($resp,$stext)
{
	global $survey_fields;
	global $found_recs;
	$found=0;
	foreach($survey_fields as $sf)
	{
		$fn=$sf['field_number'];
		switch($sf['field_type']){
			case(1):  //text
			case(2):  //textarea
			case(4):  //radio
			case(5):  //dropdown
			case(8):  //name
			$r=unserialize($resp['results_results']);
			$text_to_search=$r[$fn];
			break;

			case(7):  //date
			$r=unserialize($resp['results_results']);
			$text_to_search = $r[$fn];
			break;
			case(3):  //checkbox
			$r=unserialize($resp['results_results']);
			$rr=unserialize($r[$fn]);
			$text_to_search=implode(".",$rr);
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

function field_value($resp,$sf)
{
	if($sf['field_type'] != 6)
	{
		$fn=$sf['field_number'];
		//		echo "[[$fn]]";
		switch($sf['field_type'])
		{
			case(1):  //text
			case(2):  //textarea
			case(8):  //name
			$r=unserialize($resp['results_results']);
			$rr=$r[$fn];
			return $rr;
			break;
			case(7):  //date
			$r=unserialize($resp['results_results']);
			$rr=$r[$fn];
			return $rr;
			break;
			case(3):  //checkbox
			$r=unserialize($resp['results_results']);
			$rr=unserialize($r[$fn]);
			return implode("<br />",$rr);
			break;
			case(4):  //radio
			case(5):  //dropdown
			$r=unserialize($resp['results_results']);
			$rr=$r[$fn];
			return $rr;
			break;
			//			case (6):  //separator
			//			break;
			case(9):  //calculation
			return field_calc($sf);
			break;
		}
	}
}

function get_val($fn)
{
	global $survey_fields,$res,$selected_rec;
	foreach($survey_fields as $sf)
	{
		if($sf['field_number'] == $fn)
		{
			return floatval(field_value($res[$selected_rec],$sf));
		}
	}
}

function field_calc($sf)
{
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
}

//require_once(HEADERF);
$arg=explode(".",e_QUERY);

$found_recs=array();
global $survey_fields;
global $res;
global $selected_rec;
$sql -> db_Select("survey","*","survey_id='{$arg[0]}' ");
$selected_rec=$arg[2];
$search_text=$arg[1];


if($_POST['search'])
{
	$search_text=$_POST['search_text'];
	$arg[1]=$search_text;
}
if($selected_rec=="")
{
	$selected_rec=0;
}
if($row = $sql -> db_Fetch()){extract($row);}
if(!check_class($survey_viewclass) || !$survey_save_results){header("location:".e_BASE."index.php"); exit; }

if($_POST['edit'] && check_class($survey_editclass))
{
	$search_text="";
	define("SURVEY_EDIT",TRUE);
}
define("SURVEY_EDIT",FALSE);

if($_POST['update'])
{
	if(check_class($survey_editclass))
	{
		update_record($_POST['result_id']);
		// Update the record
	}
	else
	{
		header("location:".e_BASE."index.php"); exit;
	}
}

if($_POST['delete'])
{
	if(check_class($survey_editclass))
	{
		delete_record($_POST['result_id']);
		// Delete the record
	}
	else
	{
		header("location:".e_BASE."index.php"); exit;
	}
}

if($_POST['dconfirm'])
{
	if(check_class($survey_editclass))
	{
		delete_confirmed($_POST['result_id']);
		// Delete the record
	}
	else
	{
		header("location:".e_BASE."index.php"); exit;
	}
}

$survey_fields=unserialize($survey_parms);
$qry = "results_survey_id='{$arg[0]}' ORDER BY results_datestamp ASC";
$numresults = $sql -> db_Select("survey_results","*",$qry);
$first_response=9999999999;
$last_response=0;
while($row = $sql -> db_Fetch())
{
	$first_response = ($row['results_datestamp'] < $first_response) ? $row['results_datestamp'] : $first_response;
	$last_response = ($row['results_datestamp'] > $last_response) ? $row['results_datestamp'] : $last_response;
	$res[]=$row;
	if($search_text)
	{
		survey_search($row,$search_text);
	}
}
if($search_text)
{
	$numresults=count($found_recs);
	if(count($found_recs))
	{
		$res[$selected_rec]=$found_recs[$selected_rec];
	}
}

$cv = new convert;
$title = "$survey_name - ".$cv->convert_date(time(),"long");

if($numresults)
{
	$cnv = new convert;
	$text = "<table align='center' border='1'>";
	$i=0;
	if($_POST['list'])
	{
		echo "</table><table><tr>";
		foreach($survey_fields as $sf){
			$text .= "<td class='fcaption'>{$sf['field_text']}</td>";
		}
		$text .= "</tr>";
		$reclist = ($search_text) ? $found_recs : $res;
		$sr=0;
		foreach($reclist as $r)
		{
			$selected_rec=$sr;
			$sr++;
			$text .= "<tr>";
			foreach($survey_fields as $sf)
			{
				$text .= "<td class='forumheader3'>";
				$text .= field_value($r,$sf);
				$text .= "</td>";
			}
			$text .= "</tr>";
		}
	}
	else
	{
		$text .="<tr>";
		$text .= "<td colspan='2'>".$title."</td>";
		$text .= "</tr>";
		$text .="<tr>";
		$text .= "<td class='fcaption'>".LAN_SUR16."</td><td class='fcaption'>".LAN_SUR17."</td>";
		$text .= "</tr>";
		$_r=unserialize($res[$selected_rec]['results_results']);
		$_res_id=$res[$selected_rec]['results_id'];
		if(SURVEY_EDIT === TRUE)
		{
			$text .= "<form action='".e_SELF."?".e_QUERY."' method='POST'>";
		}
		$s_f=$survey_fields;
		foreach($s_f as $_sf)
		{
			$text .= "<tr><td class='forumheader3'>{$_sf['field_text']}</td><td class='forumheader3'>";
			//				echo "SELREC = {$selected_rec}<br />";
			$text .= field_value($res[$selected_rec],$_sf);

			$text .= "</td></tr>";
		}
	}
	$text .= "</table>";
}
else
{
	$text .= "<br /><div style='text-align:center;'>".LAN_SUR15.".</div>";
}

echo $text;
echo "
<SCRIPT LANGUAGE='JavaScript'>
function printPage() {
if (window.print) {
agree = confirm('".LAN_SUR33."');
if (agree) window.print();
}
}
</script>
<BODY OnLoad='printPage()'>";
?>