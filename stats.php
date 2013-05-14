<?php

require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
e107::plugLan('survey', e_LANGUAGE.'_front');

require_once(HEADERF);
$arg = explode(".",e_QUERY);

$found_recs = array();
global $survey_fields;
$sql -> db_Select("survey","*","survey_id='{$arg[0]}' ");

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
}

if($numresults)
{
	$cnv = new convert;
	$text .= "<table align='center'>";
	$text .="<tr>";
	$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR14.":</div></td><td class='forumheader2'><div class='defaulttext'>{$numresults}</div></td>";
	$text .= "</tr>";
	$text .="<tr>";
	$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR19.":</div></td><td class='forumheader2'><div class='defaulttext'>".$cnv -> convert_date($first_response,"long")."</div></td>";
	$text .= "</tr>";
	$text .="<tr>";
	$text .= "<td class='forumheader2'><div class='defaulttext'>".LAN_SUR20.":</div></td><td class='forumheader2'><div class='defaulttext'>".$cnv -> convert_date($last_response,"long")."</div></td>";
	$text .= "</tr>";
	$text .="<tr>";
	$text .= "<td colspan='2'><br /></td>";
	$text .= "</tr>";
	$text .="<tr>";
	$text .= "<td class='fcaption'>".LAN_SUR16."</td><td class='fcaption'>".LAN_SUR17."</td>";
	$text .= "</tr>";
	$i=0;
	foreach($survey_fields as $sf)
	{
		if($sf['field_type'] != 6)
		{
			$fn=$sf['field_number'];
			$text.="<tr><td class='forumheader3'>{$sf['field_text']}</td><td class='forumheader3'>";
			switch($sf['field_type'])
			{
				case(1):  //text
				$text .= "[".LAN_SUR21."]</td></tr>";
				break;
				case(2):  //textarea
				$text .= "[".LAN_SUR22."]</td></tr>";
				break;
				case(8):  //name
				$text .= "[".LAN_SUR23."]</td></tr>";
				break;
				case(7):  //date
				$text .= "[".LAN_SUR24."]</td></tr>";
				break;
				case(3):  //checkbox
				$choices=explode(",",$sf['field_choices']);
				$text .= "<table style='width:100%'>";
				foreach($_res as $_rs)
				{
					$r=unserialize($_rs['results_results']);
					$rr=unserialize($r[$fn]);
					foreach($choices as $choice)
					{
						foreach($rr as $_r)
						{
							if(trim($choice) == trim($_r))
							{
								$count[$choice]++;
							}
							//							if(in_array($choice,$rr)){
							//								$count[$choice]++;
							//							}
						}
					}
				}
				foreach($choices as $choice)
				{
					if($count[$choice]==""){$count[$choice]=0;}
					$per=sprintf("%.1f",$count[$choice]/$numresults*100);
					$text .= "<tr><td style='width:40%'>{$choice}</td><td style='width:35%; text-align:right;'>{$count[$choice]}</td></td><td style='width:25%; text-align:right;'>{$per}%</td></tr>";
				}
				$text .= "</table>";
				unset($count);
				break;
				case(4):  //radio
				case(5):  //dropdown
				$choices=explode(",",$sf['field_choices']);
				$text .= "<table style='width:100%'>";
				foreach($choices as $choice){
					$cnt=0;
					foreach($_res as $_r){
						$r=unserialize($_r['results_results']);
						if(trim($r[$fn])==trim($choice)){
							$cnt++;
						}
					}
					$per=sprintf("%.1f",$cnt/$numresults*100);
					$text .= "<tr><td style='width:40%'>{$choice}</td><td style='width:35%; text-align:right;'>{$cnt}</td></td><td style='width:25%; text-align:right;'>{$per}%</td></tr>";
				}
				$text .= "</table>";
				$text .= "</td></tr>";
				break;
				case(6):  //separator
				break;
			}
		}
		$i++;
	}
	$text .= "</table>";
}
else
{
	$text .= "<br /><div style='text-align:center;'>".LAN_SUR15.".</div>";
}

$ns -> tablerender("results of survey: {$survey_name}",$text);
require_once(FOOTERF);
?>