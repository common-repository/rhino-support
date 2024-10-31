<?php
if (!defined('ABSPATH')) die();

if (!class_exists('Rhino_Pluginmethods')) {

    class Rhino_Pluginmethods {
		
		var $RhinoAdminNotices;

        function Constructor( $pluginfile )
        { 
			require_once(ABSPATH . '/wp-admin/includes/plugin.php');
            global $wpdb;
			
            $this->pluginURL=get_bloginfo( 'wpurl' ).'/'.PLUGINDIR.'/'.dirname( plugin_basename( $pluginfile ) );
			$this->PluginInfo = (object) get_plugin_data($pluginfile);
			$this->Version = $this->PluginInfo->Version;
			
			$wlsc[] = array('title'=>"Create Ticket Form",'value'=>"[rhinosupport_create]");
			$wlsc[] = array('title'=>"List Tickets",'value'=>"[rhinosupport_listtickets]");
			
			global $RhinoTinyMCEPluginInstance;
			global $rhino_tinymce_override;
			if(!isset($RhinoTinyMCEPluginInstance)){ //instantiate the class only once
				$RhinoTinyMCEPluginInstance = new RhinoTinyMCEPlugin();
				add_action('admin_init',array(&$RhinoTinyMCEPluginInstance,'TNMCE_PluginJS'));
			}
		
			$RhinoTinyMCEPluginInstance->RegisterShortcodes("Rhino Support",$wlsc,$wlmc,0,null,(array)$special_codes);
        }
        
		/**
		 * Get rhino data through curl.
		 * 
		 * @param string $method.
		 * @param string $email (optional).
		 * @param int $id (optional).
		 * @return curl result base on method passed.
		 */
        
        function fetch_data_from_api( $method, $email = null, $id = null, $limit = null, $offset = null ) {

            if ( $method == 'Website' )
               $request = curl_init('https://api.rhinosupport.com/API/Website/?apiCode='.get_option('wprhinosupport_key').'&responseType=json&masterColor='.ltrim (get_option('wprhinosupport_scroller_color'),'#').'&onlineText='.str_replace ( ' ', '%20', get_option('wprhinosupport_scroller_online')).'&offlineText='.str_replace ( ' ', '%20', get_option('wprhinosupport_scroller_offline')).'&offset='.get_option('wprhinosupport_scroller_pixel').'&departmentList='.$id);
            elseif ( $method=='Ticket' )
                $request = curl_init('http://www.rhinosupport.com/API/Ticket/?apiCode='.get_option('wprhinosupport_key').'&emailAddress='.$email.'&responseType=json');
            elseif ( $method=='Department' )
                $request = curl_init('http://www.rhinosupport.com/API/Department/?apiCode='.get_option('wprhinosupport_key').'&id='.$id.'&responseType=json');
			elseif ( $method=='Message' )
                $request = curl_init('http://www.rhinosupport.com/API/Message/?apiCode='.get_option('wprhinosupport_key').'&ticketID='.$id.'&responseType=json');
            elseif ( $method == 'User' )
               $request = curl_init('https://api.rhinosupport.com/API/User/?apiCode='.get_option('wprhinosupport_key').'&emailAddress='.$email.'&responseType=json');

            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($request);
            curl_close($request);

            return($result);
        }
        
		// Check if API saved is correct.
		// Returns true if correct and false along with the reason of failure if failed.
		function IsAPILegit() {
			
			$request = curl_init('https://api.rhinosupport.com/API/Website/?apiCode='.get_option('wprhinosupport_key').'&responseType=json');
			
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($request);
           
			$http_status = curl_getinfo($request, CURLINFO_HTTP_CODE);
			curl_close($request);

			if($http_status == 200) {
				return array(
					'status' => true,
				);
			} elseif ($http_status == 500) {
				return array(
					'status' => false,
						'reason' => '[API Error] - Invalid API Key'
				);
			} else  {
				return array(
					'status' => false,
					'reason' => 'Can not connect to Rhino Server.'	
				);
			}
         
		}
		
		// Get Ticket Form Information
		// This is to sync the ticket form fields from the Rhino Account Settings
		// to your Rhino plugin Create ticket form
		function get_ticket_form() {
			
			$request = curl_init('http://www.rhinosupport.com/API/TicketForm/?apiCode='.get_option('wprhinosupport_key').'&responseType=json');
			
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($request);
           $json_obj = json_decode( $result ); 

		   return $json_obj;
         
		}
		
       function get_latest_message( $id ) {
		      $data = $this->fetch_data_from_api( 'Message', '', $id );
            $json_obj = json_decode( $data ); 
			$lastjson_obj = end($json_obj);
			return $lastjson_obj;
	   }
       
	   /**
		* Get website info base on the $type passed.
		* 
		* @param string $type.
		*/
	   
       function get_website_info( $type, $deptids = null) {
            $data = $this->fetch_data_from_api( 'Website', null, $deptids  );
            $json_obj = json_decode( $data ); 
		
            switch ( $type ) {
                case 'supportURL':
                    return $json_obj[0]->supportURL;
                    break;
                 case 'sideCode':
                    return $json_obj[0]->sideCode;
                    break;
            }
        }
        
		/**
		* Get Department name through department ID. This will return the Default Department
		* if the id of the department that is passed is PRIVATE.
		* @param int $id.
		*/
		
        function get_department( $id ) {
            $data = $this->fetch_data_from_api( 'Department', '', $id );
            $json_obj = json_decode( $data ); 
			if($json_obj[0]->private) {
				$data = $this->fetch_data_from_api( 'Department');
				$json_obj = json_decode( $data ); 
				foreach ($json_obj as $val):
					if($val->default):
						return $val->name;
					endif;
				endforeach;
			} else {
				return $json_obj[0]->name;
			}
        }
        
        function get_user_info( $email ) {
            $data = $this->fetch_data_from_api( 'User', $email );
            $json_obj = json_decode( $data ); 
            return $json_obj[0]->id;
        }
        
		function get_domain() {
			$parsedUrl = parse_url($this->get_website_info('supportURL'));
			$host = explode('.', $parsedUrl['host']);
			return $subdomain = $host[0];
		}
		
        function create_ticket() {

			if (is_email(trim($_POST['txtuseremail']))) {
				
				if((trim($_POST['txtdisplayname'])!= "")) {
					
					if((trim($_POST['txtdepartment']) != "")) {
					
						if((trim($_POST['txtsubject']) != "")) {
							
							if((trim($_POST['txtmessage']) != "")) {
								
								$message = stripslashes($_POST['txtmessage']);
								
								if(isset($_POST['txtcustom'])) {
									foreach($_POST['txtcustom'] as $custom_text) {
										$custom = $custom .'<br>'.$custom_text;
									}
								}
								$message =  $custom . '<br><br> Your Message = ' . $message;
								$message = nl2br($message);
								
								$request = curl_init ('https://www.rhinosupport.com/API/Ticket/');
								$postFields = array();
								$postFields['apiCode'] = get_option( 'wprhinosupport_key' );
								$postFields['name'] = sanitize_text_field(stripslashes($_POST['txtsubject']));
								$postFields['emailTo'] = trim($_POST['txtuseremail']);
								$postFields['body'] = $message;
								$postFields['ticketFromCustomer'] = '1';
								$postFields['firstName'] = sanitize_text_field(stripslashes($_POST['txtdisplayname']));
								//$postFields['companyName'] = '[companyName]';
								//$postFields['websiteURL'] = '[websiteURL]';
								//$postFields['phoneNumber'] = '[phoneNumber]';
								$postFields['ticketStatus'] = 'open';
								$postFields['department'] = $_POST['txtdepartment'];
								
								if(!empty($_FILES['file']['tmp_name'])) {
									$postFields['attachmentName'] = $_FILES['file']['name'];
									$postFields['attachment'] = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
								}
								
								$postFields['cc'] =  $_POST['txtcc'];
								$postFields['bcc'] =  $_POST['txtbcc'];

								curl_setopt ($request, CURLOPT_POST, true);
								curl_setopt ($request, CURLOPT_POSTFIELDS, http_build_query($postFields));
								curl_setopt ($request, CURLOPT_RETURNTRANSFER, true); 

								$result = curl_exec ($request);
								
								$http_status = curl_getinfo($request, CURLINFO_HTTP_CODE);
								
								curl_close ($request);
								
								return $http_status;
							
							}
							else {
								echo '<p class="rhinowarning">Please Input Message.</p>';
							}							
						}
						else {
							echo '<p class="rhinowarning">Please Input Subject.</p>';
						}					
					}
					else {
						echo '<p class="rhinowarning">Please Select Department.</p>';
					}					
				}
				else {
					echo '<p class="rhinowarning">Please Input your Name</p>';
				}
			}	
			else {
				echo '<p class="rhinowarning">Invalid Email</p>';
			}
			
        }
		
		//Convert comments into tickets
		function convert_ticket() {
			update_comment_meta( $_POST['txtcommentid'], 'rhino-ticket', 1 );

			$curlstatus = $this->create_ticket();
			
				//check if curl was  successful
				if($curlstatus == 200) {
					$this->RhinoAdminNotices = array(
						'message' => 'Successfully Created the Ticket!',
						'status' => 'updated'
					) ;

				} else { //if it's not successful, we echo the http status and redirect it back to the create ticket page with the post data
					$this->RhinoAdminNotices = array(
						'message' => 'Sorry, we were unable to submit your ticket. The curl request returned a '. $curlstatus .' HTTP status.',
						'status' => 'error'
					) ;
				}
				add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		function save_api_key() {
			update_option( 'wprhinosupport_key', trim($_POST['wprhinosupport_key']) );
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated API Key.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		function save_department() {
			
			// This is only use when WLM is activated so we check if the hidden field text box 
			// generated when WLM is active is there.
			if(isset($_POST['txtiswlmactivated'])) {
				$serializedata = '';
				//then check if there's a dept passed on any level
				if(isset($_POST['wprhinosupport_wlm_members'])) {
					$data = array();
					foreach($_POST['wprhinosupport_wlm_members'] as $levelval){
						$data[key($levelval)] = array_merge((array)$data[key($levelval)], (array)$levelval[key($levelval)]);
					}
					$serializedata = serialize($data);
					update_option( 'wprhinosupport_wlm_members_departments', $serializedata );
				} else {
					update_option( 'wprhinosupport_wlm_members_departments', $serializedata );
				}
			}
			
			if(is_null($_POST['wprhinosupport_non_logged_departments']))
				$non_logged_depts = '';
			else
				$non_logged_depts = implode(",", $_POST['wprhinosupport_non_logged_departments']);
			
			update_option( 'wprhinosupport_non_logged_departments', $non_logged_depts );

			if(is_null($_POST['wprhinosupport_logged_departments']))
				$logged_depts = '';
			else
				$logged_depts = implode(",", $_POST['wprhinosupport_logged_departments']);
			
			update_option( 'wprhinosupport_logged_departments', $logged_depts );
			
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated Department Access.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		//save create ticket redirect
		function save_create_redirect() {
			if($_POST['radiocreate'] == 'custom') {
				update_option( 'wprhinosupport_create_thankyou', $_POST['radiocreate']);
				update_option( 'wprhinosupport_create_thankyou_value1', $_POST['txtcustommessage']);
			} elseif($_POST['radiocreate'] == 'redirect') {
				update_option( 'wprhinosupport_create_thankyou', $_POST['radiocreate']);
				update_option( 'wprhinosupport_create_thankyou_value2', $_POST['rhinocreateoption2redirect']);
			}
			
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated Redirect when creating a ticket.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		// submit ticket redirection url
		function save_redirection() {
			update_option( 'wprhinosupport_create_thankyou', 'redirect');

			if($_POST['radioredirection'] == 'external') {
				update_option( 'wprhinosupport_external_internal', 'external');
				update_option( 'wprhinosupport_create_thankyou_value2', $_POST['txtexternal']);
			} elseif ($_POST['radioredirection'] == 'internal') {
				update_option( 'wprhinosupport_external_internal', 'internal');
				update_option( 'wprhinosupport_create_thankyou_value2', $_POST['txtinternal']);
			}
			
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated the Redirect URL.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		//Save scrolling tab section
		function save_scroller_section() {
			update_option( 'wprhinosupport_scroller_tab', $_POST['radioscroller']);

			//save scroller code
			update_option('wprhinosupport_scroller_pixel', $_POST['txtPixelOffset']);
			update_option('wprhinosupport_scroller_color', $_POST['hiddenScrollerColorPicker']);
			update_option('wprhinosupport_scroller_online', $_POST['txtonline']);
			update_option('wprhinosupport_scroller_offline', $_POST['txtoffline']);
			
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated Scrolling Tab Settings.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		//save specific post/page id for applying scrolling tab
		function save_post_page_checkbox() {
			update_option( 'wprhinosupport_scroller_tab', 'specific');
			
			if(empty($_POST['chkpostpages']))
				$ids = '';
			else
				$ids = implode(",", $_POST['chkpostpages']);
			
			
			update_option('wprhinosupport_supporttab_ids', $ids);
			
			$this->RhinoAdminNotices = array(
						'message' => 'Successfully Updated Scrolling Tab Settings.',
						'status' => 'updated'
					) ;
			add_action( 'admin_notices', array(&$this, 'rhino_admin_notice') );
		}
		
		function print_pre($value) { 
			echo "<pre>",print_r($value, true),"</pre>";
		}
    }
}

