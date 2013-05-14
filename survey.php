<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	survey.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."np_class.php");
require_once(e_PLUGIN."survey/survey.inc.php");
e107::plugLan('survey', e_LANGUAGE.'_front');

function np($url, $from, $view, $total, $td, $qs=""){
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
  if($total == 0){ return; }



  $ns = new e107table;
   
  echo "<table style='width:100%'>
  <tr>";
  if($from > 1){
   $s = $from-$view;
   echo "<td style='width:33%' class='nextprev'>";
   if($qs != ""){
		$text = "<div style='text-align:left'><span class='smalltext'><a href='{$url}?{$qs}.{$s}'>".NP_1."</a></span></div>";
   }else{
		$text = "<div style='text-align:left'><span class='smalltext'><a href='{$url}?{$s}'>".NP_1."</a></span></div>";
   }
   echo $text;
  }else{
   echo "<td style='width:33%'>&nbsp;";
  }
 
  echo "</td>\n<td style='width:34%' class='nextprev'>";
  $start = $from+1;
  $finish = $from+$view;
  if($finish>$total){
   $finish = $total;
  }
  $text = "<div style='text-align:center'><span class='smalltext'>$td $start - $finish of $total</span></div>";
  echo $text;
 
  $s = $from+$view;
  if($s < $total){
   echo "</td><td style='width:33%' class='nextprev'>";
   if($qs != ""){
		$text = "<div style='text-align:right'><span class='smalltext'><a href='".$url."?".$qs.".".$s."'>".NP_2."</a></span></div></td>";
   }else{
		$text = "<div style='text-align:right'><span class='smalltext'><a href='".$url."?".$s."'>".NP_2."</a></span></div></td>";
   }
   echo $text;
  }else{
   echo "</td><td style='width:33%'>&nbsp;</td>";
  }
  echo "</tr>\n</table>";
}

function already_voted($userlist){
	if(USER == TRUE){
		$voted = explode(".",$userlist);
		if(in_array(USERID,$voted)){return TRUE;}
	}
	return FALSE;
}

require_once(HEADERF);
$arg=explode(".",e_QUERY);

function getfield($f){
	global $fdata;
	foreach($fdata as $field){
		if($f == $field['field_number']){
			return $field;
		}
	}
}

function parse_survey_message($message){
	$tmp = explode("\n", $message);
	for($c=0; $c < count($tmp); $c++){ 
		if(preg_match("/\{(.*?)=(.*?)\}/", $tmp[$c],$m)){
			$field=getfield($m[2]);
			if($m[1] == "Q"){
				$ret .= show_form_question($field);	
			}
			if($m[1] == "F"){
				$ret .= show_form_field($field);
			}
		}else{
			$ret .= $tmp[$c];
		}
	}
	return $ret;
}

function show_survey($snum)
{
	global $sql, $ns, $tell_required, $_res, $fdata, $survey_class, $tp;
	$snum = intval($snum);
	$sql -> db_Select("survey","*","survey_id='{$snum}' ");
	if($row = $sql -> db_Fetch())
	{
//		extract($row);
		if(!check_class($row['survey_class']))
		{
			$ns->tablerender("Error - {$row['survey_name']}",LAN_SUR6);
			return;
		}
		if($row['survey_class'] != e_UC_PUBLIC && $row['survey_once'])
		{
			if(already_voted($row['survey_user']))
			{
				$ns->tablerender("Error - {$row['survey_name']}",LAN_SUR2);
				return;
			}
		}
		$fdata = unserialize($row['survey_parms']);
		$frm = new myform;
		$ret .= $frm -> form_open("post", e_SELF);
		$ret .= $frm -> form_hidden("survey_id", $row['survey_id']);
		$ret .= "<table style='width:96%'>";
		if(check_class($row['survey_viewclass']) && $row['survey_save_results'])
		{
			$ret .= "<tr><td colspan='2' style='text-align:center;'>[<a href='".e_PLUGIN."survey/view.php?{$row['survey_id']}'>".LAN_SUR1."</a>]</td></tr>";
			$ret .= "<tr><td colspan='2' style='text-align:center;'>[<a href='".e_PLUGIN."survey/stats.php?{$row['survey_id']}'>".LAN_SUR13."</a>]</td></tr>";
		}
		if(preg_match("/\{Q=(\d*?)\}/",$row['survey_message']))
		{
			$ret .= "<tr><td>";
			$ret .= parse_survey_message($row['survey_message']);				
			$ret .= "</td></tr>";
		}
		else
		{
			$ret .= "<tr><td colspan='2'>{$row['survey_message']}</td></tr>";
			foreach($fdata as $field)
			{
				if(!$field['field_hidden'] && $field['field_type'] != 9){
					$fn = $field['field_number'];
					$ret .= "\n<tr>";
					if($field['field_type'] != 6)
					{
						$ret .= "<td class='forumheader3' style='vertical-align:top;'>";
						$ret .= show_form_question($field);
						$ret .= "</td>";
						$ret .= "<td class='forumheader3'>";
					}
					else
					{
						$ret .= "<td colspan='2'>";
					}
					$ret .= show_form_field($field);
					$ret .= "</tr>";
				}
			}
		}
		$ret .= "<tr><td class='forumheader3' colspan='2'>".LAN_SUR4."</td></tr>";
		$ret .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
		$ret .= $frm -> form_button("submit","submit",LAN_SUR5);
		$ret .= "</td></tr>";
		$ret .= $frm -> form_close();
		$ret .= "</table>";
	}
	$ns->tablerender($row['survey_name'], $ret);
}

if($_POST['submit']){
	$cnv = new convert;
	$mailto_addresses = "";
	$submit_time = time();
	$mailtext = LAN_SUR42.": ".$cnv -> convert_date($submit_time,"long")."\n\n";
	
	$sql -> db_Select("survey","*","survey_id=".intval($_POST['survey_id']));
	if($row = $sql -> db_Fetch()){
		extract($row);
	}
	if($survey_class != e_UC_PUBLIC && $survey_once){
		if(already_voted($survey_user)){
			$ns -> tablerender("Error - {$survey_name}",LAN_SUR2);
			require_once(FOOTERF);
			exit;
		}
	}

	if(!check_class($survey_class)){
		$ns -> tablerender("Error - {$survey_name}",LAN_SUR6);
		require_once(FOOTERF);
		exit;
	}

	$parms=unserialize($survey_parms);
	$reshow = 0;
	foreach($parms as $parm)
	{
		$fn = $parm['field_number'];
		$v = $_POST['results'][$fn];
		$fvalue[$f]=$v;
		// CHECK FOR EMPTY REQUIRED FIELDS		
		if($parm['field_req'])
		{
			switch($parm['field_type'])
			{
				case 2:	//textarea
				case 7:	//date
				case 8:	//name
				case 10:	//email
				case 11:	//number
				case 12: //emailto
				case 1:  //text
					if(ltrim(rtrim($v))=="")
					{
						$tell_required[$fn]=1;
						$reshow=1;
					}
					break;
				case 3:  //checkbox
					if(!count($_POST['results'][$fn]))
					{
						$tell_required[$fn]=1;
						$reshow=1;
					}
					break;
				case 4:	//radio
					if(!$_POST['results'][$fn])
					{
						$tell_required[$fn] = 1; 
						$reshow=1;
					}
					break;
				case 5:  //dropdown
					if($v == "---")
					{
						$tell_required[$fn] = 1;
						$reshow=1;
					}
					break;
			}
		}
		// CHECK FIELD CONTENTS
		switch($parm['field_type'])
		{
			case 12:	//emailto
				if($v && !preg_match("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i",$v))
				{
					$error_text[$fn] = LAN_SUR40;	
					$reshow=1;
				}
				else
				{
					$mailto_addresses .= $v.",";
				}
				break;
			case 10:	//email
				if($v && !preg_match("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i",$v))
				{
					$error_text[$fn] = LAN_SUR40;	
					$reshow=1;
				}
				break;
			case 11:	//number
				if(!is_numeric($v))
				{
					$error_text[$fn] = LAN_SUR41;	
					$reshow=1;
				}
				break;
		}
		if($parm['field_type'] == 3)
		{
			$ser = array();
			foreach($v as $x)
			{
				$ser[] = $tp->toDB($x);
			}
			$_res[$fn]=serialize($ser);
			unset($ser);
		}
		else
		{
			$_res[$fn] = $tp->toDB($v);
		}	
		switch($parm['field_type'])
		{
			case (3):  //checkbox
				$mailtext .="{$parm['field_text']}: ".implode(", ",$v)." \n";
				break; 
			case (6): //separator
				$mailtext .="{$parm['field_text']} \n";
			case (12): //emailto
				break;
			default:
				$mailtext .="{$parm['field_text']}: {$v} \n";
				break;
		}
	}
	if($reshow)
	{
		show_survey($_POST['survey_id']);
		require_once(FOOTERF);
		exit;
	}
	if($survey_forum)
	{
		require_once(e_PLUGIN."forum/forum_class.php");
		$survey_forum = new e107forum;
		$postInfo = array();
		$threadInfo = array();

		if (USER)
		{
			$postInfo['post_user'] = USERID;
			$threadInfo['thread_lastuser'] = USERID;
			$threadInfo['thread_user'] = USERID;
			$threadInfo['thread_lastuser_anon'] = '';
		}
		else
		{
			$postInfo['post_user_anon'] = $_POST['anonname'];
			$threadInfo['thread_lastuser_anon'] = $_POST['anonname'];
			$threadInfo['thread_user_anon'] = $_POST['anonname'];
		}

		$postInfo['post_ip'] = e107::getIPHandler()->getIP(FALSE);

		$time = time();

		$postInfo['post_entry'] = $tp->toDB($mailtext);
		$postInfo['post_forum'] = $forumId;
		$postInfo['post_datestamp'] = $time;
		$threadInfo['thread_lastpost'] = $time;
		$threadInfo['thread_sticky'] = 0;
		$threadInfo['thread_name'] = $row['survey_name'];
		$threadInfo['thread_forum_id'] = $row['survey_forum'];
		$threadInfo['thread_active'] = 1;
		$threadInfo['thread_datestamp'] = $time;
		$threadInfo['thread_options'] = '';

		$survey_forum->threadAdd($threadInfo, $postInfo);
		$survey_forum->forumUpdateCounts($forumId);
	}

	if($survey_mailto)
	{
		require_once(e_HANDLER."mail.php");
		sendemail($survey_mailto,LAN_SUR7." {$survey_name}",strip_tags($mailtext));
	}

	if($mailto_addresses)
	{
		require_once(e_HANDLER."mail.php");
		sendemail($mailto_addresses,LAN_SUR7." {$survey_name}",strip_tags($mailtext));
	}

	if(USER == TRUE && !already_voted($survey_user))
	{
		$survey_user.=".".USERID;
		$sql -> db_Update("survey", "survey_user='{$survey_user}' WHERE survey_id='$survey_id' ");
	}
	$results=serialize($_res);
	$sid = intval($_POST['survey_id']);
	$sql -> db_Insert("survey_results","0,{$submit_time},{$sid},'{$results}'");
	$text=LAN_SUR8;
	if($survey_submit_message)
	{
		$text = $tp->toHTML($survey_submit_message, true);
	}

	if(check_class($survey_viewclass) && $survey_save_results)
	{
		$text .= "<br /><br /><div style='text-align:center;'>";
		$text .= "[<a href='".e_PLUGIN."survey/view.php?".$survey_id."'>".LAN_SUR1."</a>]";
		$text .= "</div>";
	}

	$ns -> tablerender(LAN_SUR9,$text);
	require_once(FOOTERF);
	exit;
}

show_survey($arg[0]);
require_once(FOOTERF);
?>