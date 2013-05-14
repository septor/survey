<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	view.php
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
e107::lan('survey');

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
	$sql -> db_Select("survey","*","survey_id='{$arg[0]}' ");
	if($row = $sql -> db_Fetch()){
		extract($row);
	}
	$parms=unserialize($survey_parms);
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
				$ser[] = $tp->toDB($x);
			}
			$_res[$fn] = serialize($ser);
			unset($ser);
		}
		else
		{
			$_res[$fn] = $tp->toDB($v);
		}
	}
	$results = serialize($_res);
	$sql -> db_Update("survey_results","results_results='{$results}' WHERE results_id='{$id}' ");
}

function delete_record($id)
{
	global $sql, $arg, $ns;
	$id = intval($id);
	$sid = intval($arg[0]);
	$sql -> db_Select("survey","*","survey_id='{$sid}' ");
	if($row = $sql -> db_Fetch())
	{
		$text = "<table><tr><td style='text-align:center'>";
		$text .= LAN_SUR28.":<br />{$row['survey_name']}<br /><br /></td></tr>";
		$text .= "<tr><td style='text-align:center;'><br />
		<form action='".e_SELF."?".e_QUERY."' method='POST'>
		<input type='hidden' name='result_id' value='{$id}'>
		<input type='submit' class='tbox' name='dconfirm' value='".LAN_SUR29."'>
		<input type='submit' class='tbox' name='cancel' value='".LAN_SUR30."'>
		</form></td></tr></table>
		";
		$ns -> tablerender(LAN_SUR29,$text);
	}
}

function delete_confirmed($id)
{
	global $sql, $ns;
	$id = intval($id);
	if($sql -> db_Delete("survey_results", "results_id='".$id."'"))
	{
		$msg = LAN_SUR31;
	}
	else
	{
		$msg = LAN_SUR32;
	}
	$ns -> tablerender("","<div style='text-align:center;'>{$msg}</div>");
}

function survey_search($resp,$stext)
{
	global $survey_fields;
	global $found_recs;
	$found=0;
	foreach($survey_fields as $sf)
	{
		$fn=$sf['field_number'];
		switch($sf['field_type'])
		{
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
			case(12):  //emailto
			case(10):  //email
			case(11):  //number
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
			case(6):  //separator
			break;
			case(9):  //calculation
			return field_calc($sf);
			break;
		}
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
	eval("\$total = \"$str\";");
	return $total;
}
$arg=explode(".",e_QUERY);

if($_POST['print'] || preg_match("/print/",e_QUERY)){define("SURVEY_PRINT",TRUE);}
define("SURVEY_PRINT",FALSE);

if($_POST['list'] || preg_match("/list/",e_QUERY)){define("SURVEY_LIST",TRUE);}
define("SURVEY_LIST",FALSE);

if(!(SURVEY_PRINT))
{
	require_once(HEADERF);
}
else
{
	echo "<link rel='stylesheet' href='".THEME."style.css'>";
}

$found_recs=array();
global $survey_fields;
global $_res;
global $selected_rec;
$sql -> db_Select("survey","*","survey_id='{$arg[0]}' ");
$selected_rec=$arg[2];
$search_text=$arg[1];


if($_POST['search'])
{
	$search_text = $_POST['search_text'];
	$arg[1] = $search_text;
}
if($selected_rec=="")
{
	$selected_rec=0;
}
if($row = $sql -> db_Fetch()){extract($row);}
if(!check_class($survey_viewclass) || !$survey_save_results){header("location:".e_BASE."index.php"); exit; }

if($_POST['edit'] && check_class($survey_editclass))
{
	//	$search_text="";
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
		require_once(FOOTERF);

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
	$_res[]=$row;
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
		$_res[$selected_rec]=$found_recs[$selected_rec];
	}
}

if(SURVEY_EDIT !== TRUE && SURVEY_PRINT !== TRUE)
{
	$text = "
	<form action='".e_SELF."?".$arg[0].".' method='POST'>
	<div style='text-align:center;'>
	<table><tr>
	<td class='forumheader3'><input class='tbox' type='text' name='search_text' value='{$search_text}'></td>
	<td class='forumheader3'><input type='submit' class='tbox' name='search' value='".LAN_SUR11."'></td>
	<td class='forumheader3'><input type='checkbox' class='tbox' name='list'>".LAN_SUR12."</td>
	</tr></table></div></div></form>
	";
}

if($numresults)
{
	$cnv = new convert;
	$text .= "<table align='center'>";
	if(SURVEY_EDIT !== TRUE && SURVEY_PRINT !== TRUE)
	{
		$text .="<tr>";
		$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR14.":</div></td><td class='forumheader2'><div class='defaulttext'>{$numresults}</div></td>";
		$text .= "</tr>";
		$text .="<tr>";
		$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR19.":</div></td><td class='forumheader2'><div class='defaulttext'>".$cnv -> convert_date($first_response,"long")."</div></td>";
		$text .= "</tr>";
		$text .="<tr>";
		$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR20.":</div></td><td class='forumheader2'><div class='defaulttext'>".$cnv -> convert_date($last_response,"long")."</div></td>";
		$text .= "</tr>";

		echo $survey_edit;
		$opts=".print";
		if(SURVEY_LIST === TRUE)
		{
			$opts .= ".list";
		}
		$text .= "
		<tr><td colspan='2'><a href='word.php?".$survey_id."'><img src='images/Word.png' alt='".LAN_SUR100."' title='".LAN_SUR100."' width='32' height='32' border='0'></a>
		<a href='excel.php?".$survey_id."'><img src='images/Excel.png' title='".LAN_SUR101."' alt='".LAN_SUR101."' width='32' height='32' border='0'></a>
		<a href=\"javascript:open_window('view.php?{$survey_id}.{$search_text}.{$selected_rec}{$opts}')\"><img src='images/print.png' alt='".LAN_SUR35."' title='".LAN_SUR34."' width='32' height='32' border='0'></a></td></tr>

		</td></tr>
		";
	}
	$i=0;
	if(SURVEY_LIST === TRUE)
	{
		$text .= "</table><table><tr><br />";
		if(check_class($survey_editclass) && SURVEY_PRINT !== TRUE)
		{
		$text .= "<td class='fcaption'>&nbsp;</td>"; }
		foreach($survey_fields as $sf)
		{
			$text .= "<td class='fcaption'>{$sf['field_text']}</td>";
		}
		$text .= "</tr>";
		$reclist = ($search_text) ? $found_recs : $_res;
		$sr=0;
		foreach($reclist as $r)
		{
			$selected_rec=$sr;
			$sr++;
			$text .= "<tr>";
			if(check_class($survey_editclass) && SURVEY_PRINT !== TRUE)
			{
				//$_rid=$_res[$selected_rec];
				$text.= "<td class='forumheader3'>
				<form action='".e_SELF."?{$arg[0]}.{$arg[1]}.".$selected_rec."' method='POST'>
				<input class='tbox' type='submit' name='edit' value='".LAN_SUR36."'>
				</form></td>
				";
			}
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
		if(check_class($survey_editclass) && SURVEY_PRINT !== TRUE && SURVEY_EDIT !== TRUE)
		{
			$text.= "
			<form action='".e_SELF."?".e_QUERY."' method='post'>
			<tr>
			<td colspan='2' style='text-align:center'><input class='tbox' type='submit' name='edit' value='".LAN_SUR26."'></td>
			</tr></form>
			";
		}

		$text .="<tr>";
		$text .= "<td colspan='2'><br /></td>";
		$text .= "</tr>";
		$text .="<tr>";
		$text .= "<td class='fcaption'>".LAN_SUR16."</td><td class='fcaption'>".LAN_SUR17."</td>";
		$text .= "</tr>";
		$_r = unserialize($_res[$selected_rec]['results_results']);
		$_res_id = $_res[$selected_rec]['results_id'];
		if(SURVEY_EDIT === TRUE)
		{
			$text .="<form action='".e_SELF."?".e_QUERY."' method='POST'>";
		}
		$s_f=$survey_fields;
		foreach($s_f as $_sf)
		{
			if($_sf['field_type'] == 6)   //separator
			{
				$text .= "<tr><td colspan='2'>".show_form_field($_sf)."</td></tr>";
			}
			else
			{
				$text .= "<tr><td class='forumheader3'>{$_sf['field_text']}</td><td class='forumheader3'>";
				if(SURVEY_EDIT === TRUE)
				{
					$_f=$_sf['field_number'];
					$_res[$_f]=$_r[$_f];
					$text .= show_form_field($_sf);
				}
				else
				{
					$text .= field_value($_res[$selected_rec],$_sf);
				}
				$text .= "</td></tr>";
			}
		}
		if(SURVEY_EDIT === TRUE)
		{
			$text.= "
			<tr>
			<td class='fcaption' colspan='2' style='text-align:center'>
			<input type='hidden' name='result_id' value='".$_res_id."'>
			<input class='tbox' type='submit' name='update' value='".LAN_SUR27."'>
			<input class='tbox' type='submit' name='delete' value='".LAN_SUR99."'>
			</td>
			</tr></form>
			";
		}
	}
	$text .= "</table>";
}
else
{
	$text .= "<br /><div style='text-align:center;'>".LAN_SUR15.".</div>";
}
$table_title=LAN_SUR18.": {$survey_name}";
if(SURVEY_PRINT === TRUE)
{
	$cv = new convert;
	if($search_text)
	{
		$table_title .= " - ".LAN_SUR37.": {$search_text}";
	}
	if(isset($arg[2]))
	{
		$table_title .= " - ".LAN_SUR39.": ".($selected_rec+1);
	}
	else
	{
		$table_title .= " - ".LAN_SUR38.": ".($selected_rec+1);
	}
}
$ns -> tablerender($table_title,$text);

if(!$_POST['list'] && SURVEY_PRINT !== TRUE)
{
	np(e_SELF, $selected_rec, 1, $numresults, LAN_SUR10,$arg[0].".{$search_text}");
}

if(!(SURVEY_PRINT))
{
	require_once(FOOTERF);
}
else
{
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
}
?>