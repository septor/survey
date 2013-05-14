<?php
require_once(e_HANDLER."form_handler.php");
e107::plugLan('survey', e_LANGUAGE.'_front');

class myform extends form
{
	function form_select($form_name, $form_options, $form_value)
	{
		$ret = "\n<select name='".$form_name."' class='tbox'>";
		foreach($form_options as $o)
		{
			$sel = ($o == $form_value) ? " SELECTED" : "";
			$ret .= "\n<option {$sel}>{$o}</selected>";
		}
		$ret .= "\n</select>";
		return $ret;
	}
}

function show_form_question($field)
{
	global $sql, $ns, $tell_required,$_res,$survey_class,$error_text;
	$fn = $field['field_number'];
	if($field['field_req'])
	{
		$ret.="* ";
	}
	$ret .= $field['field_text'];
	if($tell_required[$fn]){$ret .= "<br /><div style='color:red;'>".LAN_SUR3."</div>";}
	if($error_text[$fn]){$ret .= "<br /><div style='color:red;'>{$error_text[$fn]}</div>";}
	return $ret;
}

function show_form_field($field)
{
	global $sql, $ns, $tp, $tell_required,$_res,$survey_class,$style;
	$frm = new myform;
	$fn = $field['field_number'];
	switch($field['field_type'])
	{
		case 1:  // text
			list($size, $maxlength) = explode(",", $field['field_choices']);
			$ret .= $frm->form_text("results[".$fn."]", $size, $_res[$fn], $maxlength);
			break;
			
		case 2: // textarea
			list($cols, $rows) = explode(",", $field['field_choices']);
			$ret .= $frm->form_textarea("results[".$fn."]", $cols, $rows, $_res[$fn]);
			break;

		case 3:  //checkbox
			$options = explode(",", $field['field_choices']);
			$checked_vals = unserialize($_res[$fn]);
			foreach($checked_vals as $k => $v)
			{
				$checked_vals[$k] = trim($v);
			}
			foreach($options as $o)
			{
				$ch = (in_array(trim($o), $checked_vals)) ? 1 : 0;
				$ret .= "<div style='vertical-align:top'>".$frm->form_checkbox("results[".$fn."][]", $o, $ch)."&nbsp;".$o."</div>";
			}
			break;

		case 4:  //radio
			$options = explode(",", $field['field_choices']);
			foreach($options as $o)
			{
				$ch = ($_res[$fn] == $o) ? 1 : 0;
				$ret .= $frm->form_radio("results[".$fn."]", $o, $ch)."&nbsp;".$o."<br />";
			}
			break;

		case 5:  //dropdown
			$options = explode(",", $field['field_choices']);
			$o = array();
			$o[0] = "---";
			foreach($options as $x)
			{
				$o[] = trim($x);
			}
			$ret .= $frm->form_select("results[".$fn."]", $o, $_res[$fn]);
			break;

		case 6:  //separator
			$options = explode(",", $field['field_choices']);
			if($options[0] == "menu")
			{
				$oldstyle = $style;
				if($options[1] != ""){ $style = $options[1]; }
				ob_end_flush();
				ob_start();
				$ns->tablerender($tp->toHTML($field['field_text']), "");
				$ret .= ob_get_contents();
				ob_end_clean();
				$style = $oldstyle;
			}
			else
			{
				$ret .= $tp->toHTML($field['field_text']);
			}
			break;

		case 7:  //date
			if($field['field_choices'] == "dmy")
			{
				$fmt = "d/m/Y";
				$calfmt = "dd/mm/y";
				$calmsg = "dd/mm/yyyy";
			}
			else
			{
					$fmt = "m/d/Y";
					$calfmt = "mm/dd/y";
					$calmsg = "mm/dd/yyyy";
			}
			if($_res[$fn])
			{
				$xdate = $_res[$fn];
			}
			$ret .= "
			<input class='tbox' type='text' name='results[".$fn."]' id='date_".$fn."' value='".$xdate."' />
			<input class='tbox' type='button' name='reset' value=' ... ' id='trigger_".$fn."' /> ".$calmsg."
			<script type='text/javascript'>
				Calendar.setup({
				inputField     :    'date_".$fn."',
				ifFormat       :    '".$calfmt."',
				button         :    'trigger_".$fn."',
				singleClick    :    true
				});
			</script>
			";
			break;

		case 8:  //name
			if($survey_class)
			{
				$ret .= $frm->form_hidden("results[".$fn."]", USERNAME).USERNAME;
			}
			else
			{
				$ret .= $frm->form_text("results[".$fn."]", $size, $_res[$fn], $maxlength);
			}
			break;

		case 10:  //email
			list($size, $maxlength) = explode(",", $field['field_choices']);
			$ret .= $frm->form_text("results[".$fn."]", $size, $_res[$fn], $maxlength);
			break;

		case 11:  // number

		case 12:	 //emailto
			list($size, $maxlength) = explode(",", $field['field_choices']);
			$ret .= $frm->form_text("results[".$fn."]", $size, $_res[$fn], $maxlength);
			break;
	}
	return $ret;
}

?>