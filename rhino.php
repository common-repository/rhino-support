<?php
/*
  Plugin Name: Rhino Support
  Plugin URI: http://rhinosupport.com/wp-plugin
  Description: Allows WordPress to connect to a Rhino Support account via the API. The plugin gives the ability to create and list tickets easily in a WordPress site.
  Version: 1.0.62
  Author: Rhino Support
  Author URI: http://rhinosupport.com
  Text Domain: WP Rhino Support
  License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

ob_start();
require_once(dirname(__FILE__) . '/core/class-rhino-pluginmethods.php');
require_once(dirname(__FILE__) . '/core/RhinoTinyMCEPlugin.php');
define( 'RHINO_PLUGIN_DIRECTORY', 'rhino-support');

if (!class_exists('WP_Rhino_Support')) {

	class WP_Rhino_Support extends Rhino_Pluginmethods {
		
		
		
		function WP_Rhino_Support() {
			$this->Constructor(__FILE__);
		}

		function rhino_activate() {
				add_option('wprhinosupport_key', '');
		}

		function rhino_deactivate() {

		}


		function rhino_create_menu() {
			add_menu_page('Rhino Support','Rhino-Support',	'administrator', RHINO_PLUGIN_DIRECTORY.'/admin/rhino-settings.php', '', $this->pluginURL . '/images/wprhino.png');
			add_submenu_page( RHINO_PLUGIN_DIRECTORY.'/admin/dashboard.php', "WP Rhino Support Setings", "Setings", 'administrator', RHINO_PLUGIN_DIRECTORY.'/admin/rhino-settings.php');
		}

		function rhino_register_setting() {
				//register settings
				register_setting( 'rhino_settings_group', 'wprhinosupport_key' );
				register_setting( 'rhino_settings_group', 'wprhinosupport_remote_auth' );
				register_setting( 'rhino_settings_group', 'wprhinosupport_version');
				register_setting( 'rhino_settings_group', 'wprhinosupport_non_logged_departments');
				register_setting( 'rhino_settings_group', 'wprhinosupport_logged_departments');
				register_setting( 'rhino_settings_group', 'wprhinosupport_wlm_members_departments');
				register_setting( 'rhino_settings_group', 'wprhinosupport_create_thankyou');
				register_setting( 'rhino_settings_group', 'wprhinosupport_create_thankyou_value1');
				register_setting( 'rhino_settings_group', 'wprhinosupport_create_thankyou_value2');
				add_option( 'wprhinosupport_create_thankyou', 'custom');
				add_option( 'wprhinosupport_create_thankyou_value1', 'Thank you for your support request. Our team will be in touch with you soon.');
				register_setting( 'rhino_settings_group', 'wprhinosupport_external_internal');
				add_option( 'wprhinosupport_external_internal', 'internal');

				register_setting( 'rhino_settings_group', 'wprhinosupport_scroller_tab');
				add_option( 'wprhinosupport_scroller_tab', 'specific');

				register_setting( 'rhino_settings_group', 'wprhinosupport_supporttab_ids');

				register_setting( 'rhino_settings_group', 'wprhinosupport_scroller_pixel');
				register_setting( 'rhino_settings_group', 'wprhinosupport_scroller_color');
				register_setting( 'rhino_settings_group', 'wprhinosupport_scroller_online');
				register_setting( 'rhino_settings_group', 'wprhinosupport_scroller_offline');

				add_option( 'wprhinosupport_scroller_pixel', '100');
				add_option( 'wprhinosupport_scroller_color', '#E3E3E3');
				add_option( 'wprhinosupport_scroller_online', 'Live Chat');
				add_option( 'wprhinosupport_scroller_offline', 'Contact Us');
				
				add_option( 'rhino_license_status', '');
				add_option( 'rhino_last_license_check', '');
		}


		function GetTicketList() {
			
			$tlists = '';
			$userId = $this->get_user_info(wp_get_current_user()->user_email);
			$supportUrl = $this->get_website_info('supportURL');

			$tickets = $this->fetch_data_from_api('Ticket',wp_get_current_user()->user_email);
			$json_obj = json_decode( $tickets );

			if(empty($json_obj))
				return '<tr><td> No Tickets Created Yet! </td><td></td><td></td><td></td></tr>';

			foreach ($json_obj as $val) {
				$viewticketlink = $supportUrl.'/single.htm?id='.$val->id.'&user='.$userId;
				$latestmessage = stripslashes(strip_tags($this->get_latest_message($val->id)->messageContents));
				$latestmessage = htmlentities($latestmessage, ENT_QUOTES);
				$tickettitle = stripslashes(strip_tags(substr($val->title, 0, 37)));
				$tlists .= '
							 <tr>
								 <td><a class="rhinopopover" data-html="true" data-toggle="popover" data-content="<div style=padding:5px><b>Message Preview</b><br /><br />'.$latestmessage.'<br /><br /></div>" href="'.$viewticketlink.'" target="_blank">'.$tickettitle.'</a></td>
								 <td>'.date_format(date_create($val->creationDate), "d M Y").'</td>
								 <td>'.$this->get_department($val->department).'</td>
								 <td>'.$val->status.'</td>
							 </tr>
								   ';
			}
			return $tlists;
		}

		function rhino_short_code_list_tickets() {
			
			if(!is_user_logged_in()) {
				echo 'You need to login to view your existing tickts';
				return;
			}

			wp_enqueue_style('rhino_style', $this->pluginURL.'/css/rhino_style.css');
			 
			return <<<sc
<link rel="stylesheet" type="text/css" href="http://cdn.datatables.net/plug-ins/be7019ee387/integration/bootstrap/3/dataTables.bootstrap.css">
<div class='Rhinolistdiv'>

<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th>Subject</th>
			<th>Created</th>
			<th>Department</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		{$this->GetTicketList()}
	</tbody>
</table>
</div>

<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/1.10.1/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="http://cdn.datatables.net/plug-ins/be7019ee387/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

<script type="text/javascript">
jQuery(document).ready(function() {

     jQuery(".rhinopopover").popover({
        trigger: "hover",
        'placement': 'auto'
    });

    jQuery("#example").dataTable({bFilter: false, "bLengthChange": false, "bInfo": false});
});
</script>
sc;
		}

		function rhino_short_code_create() {
			
			
			wp_enqueue_style('rhino_style', $this->pluginURL.'/css/rhino_style.css');

			wp_enqueue_script('select2.min',  $this->pluginURL.'/js/select2.min.js');
			wp_enqueue_style('select2', $this->pluginURL.'/css/select2.css');

			wp_enqueue_script('deptselect',  $this->pluginURL.'/js/deptselect.js');

			wp_enqueue_style('rhino_responsive_style', $this->pluginURL.'/css/rhino_responsive_style.css');
			wp_enqueue_script('rhino_responsive_js',  $this->pluginURL.'/js/rhino_responsive_scripts.js');
				

			$passedval = array();
			$depts = '';

			$passedval['email'] = "";
			$passedval['name'] = "";
			$passedval['subject'] = "";
			$passedval['message'] = "";

			//check if the user has submitted a ticket,
			if(isset($_POST['RhinoSupportAction']))	{
				
				$curlstatus = $this->create_ticket();
				
				//check if curl was  successful
				if($curlstatus == 200) {
					
					echo '<p class="rhinosuccess"><b>Successfully Created a new Ticket</b></p>';

					if (get_option('wprhinosupport_create_thankyou') == 'custom') {
						echo nl2br('<p class="rhinosuccess">'.get_option('wprhinosupport_create_thankyou_value1').'</p>');
						return;
					} elseif (get_option('wprhinosupport_create_thankyou') == 'redirect') {
						if(get_option('wprhinosupport_external_internal') == 'external') {
							 $url = get_option('wprhinosupport_create_thankyou_value2');
						} elseif(get_option('wprhinosupport_external_internal') == 'internal') {
							$url = get_permalink(get_option('wprhinosupport_create_thankyou_value2'));
						}
						 header('location: '.$url);
						 exit();
					}
				} else { //if it's not successful, we echo the http status and redirect it back to the create ticket page with the post data
					echo '<p class="rhinoerror">Sorry, we were unable to submit your ticket. The curl request returned a '. $curlstatus .' HTTP status.</p>';
				}

				 $passedval['email'] = $_POST['txtuseremail'];
				 $passedval['name'] = stripslashes($_POST['txtdisplayname']);
				 $passedval['subject'] = stripslashes($_POST['txtsubject']);
				 $passedval['message'] = stripslashes($_POST['txtmessage']);
			}

			$current_user = wp_get_current_user();
			$rhino_depts = array();
			if(is_user_logged_in()) {
				
				//check if wlm is activated
				if (is_plugin_active('wishlist-member/wpm.php')) {
					// user internal WLM API functions to get the membership level of the user
					if ( function_exists( 'wlmapi_get_member_levels' ) ) {
						$levels = wlmapi_get_member_levels($current_user->ID);
						if(is_null($levels)) {
							$rhino_depts = explode(',', get_option( 'wprhinosupport_logged_departments' ));
						} else {
							$membersdepartments = maybe_unserialize(get_option( 'wprhinosupport_wlm_members_departments' ));
							$rhino_depts = $membersdepartments[key($levels)];

							// Check if there's a department saved on the member's level, if there's none then 
							// use the departments for logged in user
							if (!array_filter($rhino_depts)) {
								$rhino_depts = explode(',', get_option( 'wprhinosupport_logged_departments' ));
							}

						}
					} 
				} else { // if wlm is not activated then we go to the option for logged in members
					$rhino_depts = explode(',', get_option( 'wprhinosupport_logged_departments' ));
				}
				
				if (!array_filter($rhino_depts))	{
					//if departments for logged users is empty then we get all the depts using API
					$rhino_departments = $this->fetch_data_from_api('Department');
					$json_obj = json_decode( $rhino_departments );
					foreach ($json_obj as $val) {
						if(!$val->private)
							$depts .= '<option value="'.$val->id.'">'.$val->name.'</option>';
					}	
				} else {
					foreach($rhino_depts as $val) {
						if(!$val->private)
							$depts .= '<option value="'.$val.'">'.$this->get_department($val).'</option>';
					}
				}
			} else {
				$rhino_depts = explode(',', get_option( 'wprhinosupport_non_logged_departments' ));
				if (!array_filter($rhino_depts))	{
					//if departments for NON logged users is empty then we get all the depts using API
					$rhino_departments = $this->fetch_data_from_api('Department');
					$json_obj = json_decode( $rhino_departments );
					foreach ($json_obj as $val) {
						if(!$val->private)
							$depts .= '<option value="'.$val->id.'">'.$val->name.'</option>';
					}
				} else {
					foreach($rhino_depts as $val) {
						if(!$val->private)
							$depts .= '<option value="'.$val.'">'.$this->get_department($val).'</option>';
					}
				}
			}

			if(is_user_logged_in()) {
				$textbox_name = '<input readonly="yes" class="disabler_text" name="txtdisplayname" value="'.$current_user->display_name.'" placeholder="Please enter name" type="text" tabindex="2" required>';
				$textbox_email = '<input readonly="yes" class="disabler_text" name="txtuseremail" value="'.$current_user->user_email.'" placeholder="Please enter your email address" type="email" tabindex="2" required>';
			} else {
//				$textbox_name = '<input class="input width_300" id="inputName" type="text" name="txtdisplayname" placeholder="Name *" required value="'.$passedval['name'].'" />';
//				$textbox_email = '<input class="input width_300" id="inputEmail" type="email" name="txtuseremail"  placeholder="Email *" required value="'.$passedval['email'].'" />';
				$textbox_name = '<input  name="txtdisplayname" value="'.$passedval['name'].'" placeholder="Please enter name" type="text" tabindex="2" required>';
				$textbox_email = '<input  name="txtuseremail" value="'.$passedval['email'].'" placeholder="Please enter your email address" type="email" tabindex="2" required>';
			}
			
			// Get Custom Text added in the clien's Rhino Account via API
			$ticket_form_fields = $this->get_ticket_form();	
			
			// This is not the custom messages
			$not_custom = array('Your Name', 'Email Address', 'Select Department', 'Subject', 'Message');
			
			if (is_array($ticket_form_fields[0]->formFields->field))
			{
				// Loop through the result and select only those that are not custom text
				foreach ($ticket_form_fields[0]->formFields->field as $form) {
					if(!in_array($form->name, $not_custom)) {
						$text_custom .=  '<div>
								<label>
									<span>'.$form->name.': </span>
									<input placeholder="'.$form->name.'" name="txtcustom[]" type="tel" tabindex="3" required>
								</label>
							</div>';
					}
				}
			}

			return <<<sc

<script type="text/javascript">
function showcc() {
	jQuery('#trcc').slideToggle();
}

function showbcc() {
	jQuery('#trbcc').slideToggle('slow');
}
</script>

<form id="contact-form"  method="post" enctype="multipart/form-data">
			<div>
				<label>
					<span>Email: (required)</span>
					{$textbox_email}
				</label>
				<br /> <a href="javascript: showcc();">Add CC</a> | <a href="javascript: showbcc();">Add BCC </a>
			</div>
			<div style="display:none;" id="trcc">
				<label>
					<span>CC:</span>
					<input name="txtcc" value="" placeholder="Example: email@domain.com, email2@domain.com" type="email" tabindex="2" >
				</label>
			</div>
			<div style="display:none;" id="trbcc">
				<label>
					<span>BCc:</span>
				<input name="txtbcc" value="" placeholder="Example: email@domain.com, email2@domain.com" type="email" tabindex="2" >
				</label>
			</div>
			<div>
				<label>
					<span>Name:</span>
					{$textbox_name}
				</label>
			</div>
			<div>
				<label>
					<span>Department: (required)</span>
					 <select id="e2" style="width:410px" name="txtdepartment" required>
					<option></option>
						{$depts}
					</select>
				</label>
			</div>
			{$text_custom}
			<div>
				<label>
					<span>Subject: (required)</span>
					<input placeholder="Subject*" name="txtsubject" type="tel" tabindex="3" required>
				</label>
			</div>
			<div>
				<label>
					<span>Message: (required)</span>
					<textarea name="txtmessage" placeholder="Include all the details you can" tabindex="5" required></textarea>
				</label>
			</div>
			<div>
				<label>
					<span>Upload File: </span>
					<input type="file" name="file" id="file">
					<br /><br />
				</label>
			</div>
			<div>
				<button name="submit" type="submit" id="">Submit Ticket</button>
				<input type="hidden" name="RhinoSupportAction">
			</div>
		</form>
sc;
		}

		function load_query() {
			
				wp_enqueue_script('jquery');
				
			
				/* only load latest jquery, tablesort
				 * and minisort if the current page being
				 * loaded is our admin settings page
				 * (added by mike)
				 */
				
				if(isset($_GET['page']) && ($_GET['page'] == RHINO_PLUGIN_DIRECTORY.'/admin/rhino-settings.php')) {
					
					wp_enqueue_style('rhino_style', $this->pluginURL.'/css/rhino_style.css');

					wp_enqueue_script('select2.min',  $this->pluginURL.'/js/select2.min.js');
					wp_enqueue_style('select2', $this->pluginURL.'/css/select2.css');

					wp_enqueue_script('deptselect',  $this->pluginURL.'/js/deptselect.js');
					
						//deregistering the built in jquery in wordpress for now and registering the latest as two
						// functions (minicolors and table sorter) needs the latest jquery for them to wok
						wp_deregister_script('jquery');
						wp_register_script('jquery', 'http://code.jquery.com/jquery-1.7.js');
						wp_enqueue_script('jquery');
						
						wp_enqueue_style('rhino_admin_style', $this->pluginURL.'/css/rhino_admin_style.css');

						wp_enqueue_script('minicolors',  $this->pluginURL.'/js/minicolors.js');
						wp_enqueue_style('minicolors', $this->pluginURL.'/css/minicolors.css');
						
						//also moved the jquery ui as this causes a conflict with wordress Widgets 
						wp_enqueue_script('rhino_jquery_ui',  $this->pluginURL.'/js/rhino_jquery_ui.js');
						wp_enqueue_style('rhino_jquery_ui', $this->pluginURL.'/css/rhino_jquery_ui.css');
						
						//tooltip
						wp_enqueue_script('rhino_tooltip',  $this->pluginURL.'/js/rhino_tooltip.js');
						
				}
		}

		function PreparePostPageOptions() {
			global $rhino_instance;
			$post_types = array('post', 'page', 'attachment') + get_post_types(array('_builtin' => false));
			foreach ($post_types AS $post_type) {
				add_meta_box('rhino_postpage_metabox', __('Rhino Support', 'rhino-support'), array(&$rhino_instance, 'PostPageOptions'), $post_type, 'normal', 'high' );
			}
		}

		// Post / Page Options Hook
		function PostPageOptions() {
			global $post;
			$supporttabids = explode(',', get_option('wprhinosupport_supporttab_ids'));
			if(($key = array_search($post->ID, $supporttabids)) !== false)
				$checked = true;
			?>
				<table border="0" width="100%">
					<tr>
						<td width="30%" valign="top">
							<h4>Support Tab Settings</h4>
					<input type = 'Radio' name ='radioscroller' value= 'dontdisplay' id="radioscroller0" <?php echo (get_option('wprhinosupport_scroller_tab') == 'dontdisplay') ? 'checked="true"': ''; ?> > Do not Display <br><br>
					<input type = 'Radio' name ='radioscroller' value= 'all' id="radioscroller1" <?php echo (get_option('wprhinosupport_scroller_tab') == 'all') ? 'checked="true"': ''; ?> > All sections of the site <br><br>
					<input type = 'Radio' name ='radioscroller' value= 'specific' id="radioscroller2" <?php echo (get_option('wprhinosupport_scroller_tab') == 'specific') ? 'checked="true"': ''; ?> > Specific pages or posts <br><br>
						</td>
						<td width="70%">
							<b><input type="checkbox" id="my_meta_box_check" name="chksupportid" <?php echo ($checked) ? 'checked ="true"': ''; ?> />
							<label for="my_meta_box_check">Display Support tab.</label></b> <br><br>
							Short Codes: You can use short codes to display Rhino features as defined below. <br><br>
							 <b>[rhinosupport_create]</b> Use to add a form on your page/post where your members can create tickets on your rhino supportdesk <br><br>
							 <b>[rhinosupport_listtickets]</b> Use to display all tickets created by the user currently logged in.
						</td>
					</tr>
				</table>
			<?php
		}

		// Save Post/Page Hook
		function SavePostPage() {

			update_option( 'wprhinosupport_scroller_tab', $_POST['radioscroller']);
			
			//get all saved id's first
			$supporttabids = explode(',', get_option('wprhinosupport_supporttab_ids'));

			//check if checkbox in meta box is checked
			if(isset($_POST['chksupportid'])) {
				//check if id is already saved, if not we append it
				if(!in_array($_POST['post_ID'], $supporttabids)) {
					$supporttabids[] = $_POST['post_ID'];
					//implode the array so we can have the id's separated by comma when saved
					$ids = implode(",", $supporttabids);
					update_option('wprhinosupport_supporttab_ids', $ids);
				}
			} else {
				if(($key = array_search($_POST['post_ID'], $supporttabids)) !== false) {
					unset($supporttabids[$key]);

					$ids = implode(",",$supporttabids);

					update_option('wprhinosupport_supporttab_ids', $ids);
				}
			}
		}

		 function Process() {
			global $post;

			if(!is_admin()) {
				
				//get department ids for logged in or non logged in members
				if(is_user_logged_in()) {
					
					//check if wlm is activated
					if (is_plugin_active('wishlist-member/wpm.php')) {
						// user internal WLM API functions to get the membership level of the user
						if ( function_exists( 'wlmapi_get_member_levels' ) ) {
							$current_user = wp_get_current_user();
							$levels = wlmapi_get_member_levels($current_user->ID);
							if(is_null($levels)) {
								$departmentids = get_option( 'wprhinosupport_logged_departments' );
							} else {
								$membersdepartments = maybe_unserialize(get_option( 'wprhinosupport_wlm_members_departments' ));
								$departmentids = implode(",", $membersdepartments[key($levels)]);

							}
						} 
					} else { // if wlm is not activated then we go to the option for logged in members
						$departmentids = get_option( 'wprhinosupport_logged_departments' );
					}
					
				} else {
					$departmentids = get_option( 'wprhinosupport_non_logged_departments' );
				}
				
				$scrollingcode = $this->get_website_info('sideCode', $departmentids);

				if(get_option('wprhinosupport_scroller_tab') == 'all') {
					echo $scrollingcode;
				} elseif (get_option('wprhinosupport_scroller_tab') == 'specific') {
					if (is_page() OR is_single()) {
						$supporttabids = explode(',', get_option('wprhinosupport_supporttab_ids'));
						if(in_array($post->ID, $supporttabids)) {
							echo $scrollingcode;
						}
					}
				}
			}
		}
		
		public function init() {

			// Added license checking here
			// We do licensecheck if
			// 1. there's no last time date saved yet
			// 2. if it's been over 24hours since the last checking
			$deducted = time() - get_option('rhino_last_license_check');
			
			if($deducted > 86400) {
				if(!get_option('rhino_last_license_check')) {
					$apifetchresult = $this->IsAPILegit();
					if( $apifetchresult['status'] ) {
						update_option('rhino_license_status', 1);
						update_option('rhino_last_license_check', time());
					} else {
						update_option('rhino_license_status', 0);
						update_option('rhino_last_license_check', time());
					}
				} else {
					update_option('rhino_last_license_check', time());
				}
			}
			
			// This if for getting additional tickets the logged in users created
			if(isset($_GET['rhinovtbe'])) {
				if(is_user_logged_in()) {
					
					$limit = $_GET['limit'];
					$offset = $_GET['offset'];
					
					$userId = $this->get_user_info(wp_get_current_user()->user_email);
					$supportUrl = $this->get_website_info('supportURL');

					$tickets = $this->fetch_data_from_api('Ticket',wp_get_current_user()->user_email, null, $limit, $offset);
					$json_obj = json_decode( $tickets );

					foreach ($json_obj as $val) {
						$viewticketlink = $supportUrl.'/single.htm?id='.$val->id.'&user='.$userId;
						$latestmessage = stripslashes(strip_tags($this->get_latest_message($val->id)->messageContents));
						$tickettitle = stripslashes(strip_tags(substr($val->title, 0, 37)));
						$tlists .= '<tr>
									<td><a class="rhino_tooltip" title="<b>Message Preview</b><br /><br />'.$latestmessage.'<br /><br />" href="'.$viewticketlink.'" target="_blank">'.$tickettitle.'</a></td>
									<td>'.date_format(date_create($val->creationDate), "d M Y").'</td>
									<td>'.$this->get_department($val->department).'</td>
									<td>'.$val->status.'</td>
								</tr>';
					}
					echo $tlists;
					die();
				}
			}
			
			if(isset($_POST['rhinosupportaction'])){
				switch ($_POST['rhinosupportaction']) {
					case 'ConvertTicket':
						$this->convert_ticket();
						break;
					case 'saveapikey':
						$this->save_api_key();
						break;
					case 'savedepartment':
						$this->save_department();
						break;
					case 'savecreateredirect':
						$this->save_create_redirect();
						break;
					case 'savescrollersection':
						$this->save_scroller_section();
						break;
					case 'savepostpagecheckbox':
						$this->save_post_page_checkbox();
						break;
					case 'saveredirection':
						$this->save_redirection();
						break;
				}
			}
		}
		
		/* Add the "Convert to RhinoSupport Ticket" link on the Comments Page
		 * 
		 */
		public function rhino_comment_row_actions($actions, $comment) {
		
			//fetch all the depts
			$rhino_departments = $this->fetch_data_from_api('Department');
					$json_obj = json_decode( $rhino_departments );
					foreach ($json_obj as $val) {
						if(!$val->private)
							$depts .= '<option value="'.$val->id.'">'.$val->name.'</option>';
					}
					
			wp_enqueue_script('select2.min',  $this->pluginURL.'/js/select2.min.js');
			wp_enqueue_style('select2', $this->pluginURL.'/css/select2.css');		
			
			add_thickbox();
			
			// If you noticed an extra form/form tags in the convert ticket form, that's because
			// for some reason, WP is stripping the form tags on the first comment on the Comments list
			// which makes the convert ticket form unable to work.. That's a quick workaround. 
			if ( $comment->comment_type != 'pingback' && ! get_comment_meta( $comment->comment_ID, 'rhino-ticket', true ) ) {
				$actions['rhino'] .= '<a class="thickbox" href="#TB_inline?width=500&height=500&inlineId=div'.$comment->comment_ID.'" >Convert to RhinoSupport Ticket</a>';
				$actions['rhino'] .= '<div id="div'.$comment->comment_ID.'" style="display:none;">
					<div>
						<form method="post"></form>
						<div class="form">
						<form method="post">
						<h3>Create a Rhino Support Ticket </h3> <br>
							<label><b>Name:</b></label> <br>
								<input class="input disabler_text width_300" type="text"  name="txtdisplayname" readonly="yes" value="'.$comment->comment_author.'" ><br><br>
							<label><b>Email:</b></label> <br>
								<input  class="input disabler_text width_300" type="text"  name="txtuseremail" readonly="yes" value="'.$comment->comment_author_email.'" ><br><br>
							<label><b>Subject:</b></label><br>
								<input   type="text" class="width_300" name="txtsubject" value="'.substr($comment->comment_content, 0, 37).'" ><br><br>		
							<label><b>Message:</b></label> <br>
								<textarea style=" opacity: 0.8; color: #000 !important; background: #e6e6e6; " readonly="yes" name="txtmessage" rows="6" class="width_300" cols="54">'.$comment->comment_content.'</textarea> <br>
							<label><b>Department:</b></label> <br>
							 <select id="e2" style="width:410px" name="txtdepartment">
								 '.$depts.'
							 </select>
							<p class="submit">
								<input type="hidden" name="rhinosupportaction" value="ConvertTicket">
								<input type="hidden" name="txtcommentid" value="'.$comment->comment_ID.'">
								<input type="submit" class="button-primary" value="Create a Rhino Support Ticket" />
							</p>
							</form>
						</div>
					</div>
				</div>';
		   }
		   return $actions;
		}
		
		function rhino_admin_notice() {
			?>
			<div class="<?php echo $this->RhinoAdminNotices['status']; ?>">
				<p><?php _e( $this->RhinoAdminNotices['message'], 'my-text-domain'  ); ?></p>
			</div>
			<?php
		}

		public function translate() {

		}

	}
}


if (class_exists('WP_Rhino_Support')) {
    $rhino_instance = new WP_Rhino_Support('WP_Rhino_Support');
}

if (isset($rhino_instance)) {
	
			if(get_option('rhino_license_status')) {
                //SHORT CODE HOOK
                add_shortcode('rhinosupport_create', array(&$rhino_instance, 'rhino_short_code_create') );
                add_shortcode('rhinosupport_listtickets', array(&$rhino_instance, 'rhino_short_code_list_tickets') );
				
				//adding the scrolling tab filter
				add_filter('wp_head', array(&$rhino_instance, 'Process'));
				
				//meta_box
				add_action('admin_init', array(&$rhino_instance, 'PreparePostPageOptions'), 1);
				add_action('wp_insert_post', array(&$rhino_instance, 'SavePostPage'));
				
				//comments
				add_filter( 'comment_row_actions', array(&$rhino_instance, 'rhino_comment_row_actions'), 10, 2 );
			}

            // create custom plugin settings menu
            add_action( 'admin_menu', array(&$rhino_instance, 'rhino_create_menu') );

            //call register settings function
            add_action( 'admin_init', array(&$rhino_instance, 'rhino_register_setting') );
            add_action('init', array(&$rhino_instance, 'load_query'));
			add_action('init', array(&$rhino_instance, 'init'));

            add_action('plugins_loaded', array(&$rhino_instance, 'translate'));

            register_activation_hook(__FILE__, array(&$rhino_instance, 'rhino_activate'));
            register_deactivation_hook(__FILE__, array(&$rhino_instance, 'rhino_deactivate'));
}