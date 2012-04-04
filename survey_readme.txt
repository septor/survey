+---------------------------------------------------------------+
|	e107 website system
|
|	survey_readme.txt
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+

The survey plugin allows the site admin to produce any sort of form for their e107 system.

The results of these surveys can be placed in any or all of three places:
1) email    - It will be emailed to the entered email address(es).
2) database - The results will all be stored in the database so that you can view statistical data later.
3) forum    - The results can be entered as a new thread in a selected forum topic.


--------------------------------------------------------------------------------------
** Templating the survey
--------------------------------------------------------------------------------------
When designing a form, the config screen will now show you what each field number is.  These field numbers are very important when creating your template.  The template will be your "Survey message" on the config screen.  The system will automatically detect whether that is a simple message or a template.

You simply enter straight HTML code into the survey message field.  When you would like the field question to appear, you would enter the code {Q=#) #=field number.  To show the form options for the question, enter the code (F=#}.

The {x=#} code must appear on a line by itself.

Example:

<table>
<tr><td colspan='2' style='color:blue;'>Please fill out my survey</td></tr>
<tr><td>
{Q=1}
</td><td>
{F=1}
</td></tr>
</table>
--------------------------------------------------------------------------------------

If the survey is restricted to a userclass or members only, you can then prevent the users from entering results into the survey more than once.  The tracking of users that have filled out the survey is stored independantly of of the results.  This means that the admin can not know exactly what users fill out specific data.  Also, the email that is sent does not reflect any user data.

8/22/2003 ( v0.1b ) 
	+ Initial Release

8/23/2003 ( v0.1b2 ) 
	+ Added ability to have multiple options with 'checkbox' type.
	+ Added ability to post survey results to forum.

8/26/2003 ( v0.2 ) 
	+ Added multilanguage support
	+ Added 'separator' field type.  Thanks to jalist for the help in rendering this in a menu.
	+ Added the ability to move fields up / down. You can delete a field by deleting the question.
	
9/12/2003 ( v0.3 ) 
	+ Added 'date' field type. The value will be stored in the database as a timestamp, but will be converted as a date with the date() function when displayed on the 'view' screen, forum, and email.  Thanks to jalist for the new DHTML calendar code inclusion.
	+ Added 'name' field type.  If the survey is restricted to members or userclass, this field type will automatically display the users' name, if not, it will display a textbox for entering their name.
	+ Viewing responses now allows you to step through all response records.
	+ Fixed problem with email reporting 'array' on checkbox fields.
	+ Added the ability to template the display of the form.  You now have complete control over the appearance of the form.  Please see instructions above.
	* This version REQUIRES 0.602 due to the calendar code need.

9/28/2003 ( v0.4 ) (Thanks to doorsoft for many suggestions and some code for this update)
	This release really moves the survey out of being just for surveys and allows the admin to create any sort of database for storing/showing data. I will be creating some functions to easily retrieve the data from other plugins (just in case someone wants to do that).

	+ Split the 'view' and 'stat' pages into two files.
	+ Added search functionality into the 'view' page.
	+ Added the ability to list all records on one page.
	+ Added more language phrases.
	+ Added a 'calculation' field type.
	+ Added the ability to marks fields as 'hidden'.
	+ Added the ability to edit the field data.
	+ Fixed the move up/down arrow in IE. I guess I really should have tested that in crappy (my opinion) IE!
	+ Added export to Excel or Word document (by doorsoft)

10/18/2003 (not released)
	+ Adding print capability to the view page.
	
12/13/2003 (v0.41)
	+ Added edit links to the 'view' list.
	
1/30/2004 (v0.42)
	+ Added two new field types (number and email). email will show the registered email address if survey restricted to class.
	+ 'view survey results' will no longer appear if not allowed to view results.
	+ Fixed the date field type.  jalist broke it in .604 and I just now realized it :)
	+ Finally added an expandit() to the config, page is much smaller now.

3/7/2004 (v0.43)
	+ Added a strip_tags() to the email text.
	+ Fixed the date field.  It is now stored as text and not a datestamp, but at least it works.
	+ Added new fieldtype 'mailto'.  If used, the user can enter a valid email address in the field.  The results of the survey will then be emailed there also.
	+ Added ability to create a new survey based on an existing one.  This was requested a LONG time ago and I just never got around to it.  Now it's finally done.

3/13/2004 (v0.44)
	+ Fixed the expandit() (to show main config) function with the help of Lolo Irie.
	+ Added submission date/time to the email.

1/21/2006 (v0.45)
	+ Updated plugint to be compatible with e107 version 0.7+
	
4/3/2012 (v0.48)
	+ Fixed a SQL Injection exploit. Now maintained by septor.