<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
         $this->form_validation->set_error_delimiters('<span class="error">', '</span>');
        $this->load->library('form_validation');
	}

    protected function my_gc()
	{
		$mytime = time();
		
		$sessionDatas = $this->db->get_where('ci_sessions')->result();
		
		foreach($sessionDatas as $sdata)
		{
			if( (int)($mytime - $sdata->timestamp ) >= 600){
				$this->db->where('session_id',$sdata->id);
				$this->db->update('users_master',array('session_id'=>''));
				
				$this->db->where('id',$sdata->id);
				$this->db->delete('ci_sessions');
			}
		}
	}
	
	public function index() {
		$this->my_gc();
				
		if($this->session->userdata('is_logged_in'))
		{
			//redirect('user/dashboard','refresh');
			redirect('login/selectLocation','refresh');
		}
		$this->load->helper('captcha');
	    $random_number = substr(number_format(time() * rand(),0,'',''),0,6);
		// setting up captcha config
		$capache_config = array(
				 'word' => $random_number,
				 'img_path' => './captcha/',
				 'img_url' => base_url().'captcha/',
				 'img_width' => 150,
                 'img_height' => 30,
                 'font_size' => 100,
				 'font_path'=> base_url().'captcha/font/Verdana.ttf',
				 'expiration' => 7200
		  );
		$data['captcha'] = create_captcha($capache_config);
		if ($data['captcha'] !== FALSE) {
			///echo $captcha['image'];
		} else {
			die('No captcha was created');
		}
		$this->session->set_userdata('captchaWord',$data['captcha']['word']);
		$this->load->view('includes/_login_header');		
		$this->load->view('login/index',$data);
    }
	
	public function check_login(){
        if($_POST){
			//	$this->my_gc();
		
		$userCaptcha = $this->input->post('userCaptcha');
		$this->form_validation->set_error_delimiters('<span class="error1">', '</span>');
		$this->form_validation->set_rules('username_tmp', 'Username', 'trim|required');
		$this->form_validation->set_rules('password_tmp', 'Password', 'trim|required');
		$this->form_validation->set_rules('userCaptcha', 'Captcha', 'trim|required|callback_check_captcha['.$userCaptcha.']');
		if($this->form_validation->run() == FALSE)
			  {
				  $userData = $this->db->get_where('users_master',array('user_username'=>$this->base_model->escape_str($_POST['username_tmp'])))->row();
				  if(empty($userData)){
					  $this->session->set_userdata("SuccessMessage", 'Invalid user.');
					  redirect('login');
				  }
				  else if(($this->session->set_userdata('captchaWord')!=$this->input->post('userCaptcha')) && $this->input->post('password_tmp')!='')
				  {
					  $this->session->set_userdata("SuccessMessage", 'Please Enter Valid Captcha.');
					  redirect('login');
				  }
				  else{
					$this->session->set_userdata("SuccessMessage", 'Please enter Username, Password.');
					//$data['userCaptcha']=$userCaptcha;
					//$this->load->view('includes/_login_header');		
					//$this->load->view('login/index',$data);
					redirect('login');
				  }
			  }  
		else 
			  {
				$postedArr = $this->security->xss_clean($_POST);
				//print_r($postedArr);die;
				$username=$this->base_model->escape_str($postedArr['username_tmp']);
				$password=$this->base_model->escape_str($postedArr['password_tmp']);
				$row=$this->user_model->get_login_data('users_master',$username,$password);
				
				$this->db->where('id',$row->session_id);
				$dataSession = $this->db->get('ci_sessions')->row();
				
				if(!empty($dataSession))
				{
					$this->session->set_userdata("SuccessMessage", 'You have already logged-in.');
					redirect('login');					
				}
				
				if(count($row)>0){
					if($row->user_status == '1') {
						if(empty($dataSession) || ($row->session_id == $dataSession->id )){
							if( strtotime($row->access_right_from) <= strtotime(date('Y-m-d',strtotime('now'))) && ($row->access_right_to == '' || strtotime($row->access_right_to) >= strtotime(date('Y-m-d',strtotime('now'))) || $row->access_right_to == '0000-00-00')){
								//find office opearation type
								$office_details=$this->base_model->get_record_by_id('office_master',array('office_id'=>$row->office_id));
								$newData=array('user_id'=>$row->user_id,'user_name'=>$row->user_name,'user_username'=>$row->user_username,'user_email_id'=>$row->user_email_id,'user_phone_number'=>$row->user_phone_number,'user_mobile_number'=>$row->user_mobile_number,'user_address'=>$row->user_address,'user_pincode'=>$row->user_pincode,'user_image'=>$row->user_image,'user_status'=>$row->user_status,'user_access_level'=>$row->user_access_level,'user_access_type'=>$row->user_access_type,'city_id'=>$row->city_id,'district_id'=>$row->district_id,'state_id'=>$row->state_id,'role_id'=>$row->role_id,'role_type_id'=>$row->role_type_id,'office_id'=>$row->office_id,'office_operation_type'=>$office_details->office_operation_type,'office_name'=>$office_details->office_name,'access_right_from'=>$row->access_right_from,'access_right_to'=>$row->access_right_to,'createdOn'=>$row->createdOn,'updateOn'=>$row->updateOn,'is_logged_in'=>'1');
								$this->session->set_userdata($newData);
								$check_super_admin=$this->session->all_userdata();
								$this->db->where('user_id',$row->user_id);
								$this->db->update('users_master',array('session_id'=> get_cookie('ci_session')));
								
								if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
								{//print_r($check_super_admin);die;
								   redirect('user/dashboard');
								}
								redirect('login/selectLocation');
							}
							else{
								$this->session->set_userdata("SuccessMessage", 'Your access rights has been expired.');
								redirect('login');
							}
						}
						else{
							$this->session->set_userdata("SuccessMessage", 'You have already logged-in.');
							redirect('login');
						}
					}else{
						
					  $this->session->set_userdata("SuccessMessage", 'Your activation is not completed.');
                      redirect('login');
					}
				
				} else {
					
                    $this->session->set_userdata('SuccessMessage', 'Please login with your correct Username and Password.');
                    redirect('login');
                }
			  }					
	          	
	}
	    redirect('login');
	}
	
	public function check_captcha($str=null){
		$word = $this->session->userdata('captchaWord');
		if(strcmp(strtoupper($str),strtoupper($word)) == 0){
		  return true;
		}
		else{
			//$this->session->unset_userdata('SuccessMessage');
			$this->session->set_userdata('SuccessMessage', 'Please enter correct words!');
		  return false;
		}
	 }
	
	
	public function forgot_password(){
		$this->load->view('includes/_login_header');		
		$this->load->view('login/forgot_password');
		if($_POST){
		$config = array(array(
						'field'=>'mobilenumber',
						'label'=>'Mobile Number',
						'rules'=>'required|trim'
						)
				);
		$this->form_validation->set_rules($config);
		if($this->form_validation->run() == false)
		{
				  $this->session->set_flashdata("success_message", 'Please Enter mobile number.');
					redirect(base_url('login/forgot_password'));
		}else{
				$user_mobile_number=$this->input->post('mobilenumber');
				$result=$this->db->get_where('users_master',array('user_mobile_number'=>$user_mobile_number))->row();
				if(!empty($result))
				{
				$datetime = date('Y-m-d H:i:s');
				$otpattempt=0;
				$otpNumber = rand(111111,999999);
				$message="Dear user,OTP For Your Change Password towards mmtc ".$otpNumber;
				$phone_number = $result->user_mobile_number;
				$userId=$result->user_id;
				$this->opt_send_new_sms($message,$phone_number);
				if(isset($phone_number) && !empty($otpNumber)){
					$opt_data = array('user_otp_number'=>$otpNumber,'user_otp_time'=>$datetime,'user_otp_attemp'=>$otpattempt);
					$insert_opt = array('table'=>'users_master','data' => $opt_data,'where'=>$userId);		
					$res_opt = $this->base_model->update_data($insert_opt);
					if(isset($res_opt) && !empty($res_opt)){
					$this->session->set_flashdata("SuccessMessage", 'OTP sent on your mobile number.');
					redirect('login/firstStepToSetPassword');
					}	
				}else{
					
					$this->session->set_flashdata("success_message", 'Please Enter Valid mobile number.');
					redirect(base_url('login/forgot_password'));
				}
			}else{
					$res=$this->db->get_where('users_master',array('user_mobile_number'=>$user_mobile_number))->row();
					if(empty($res)){
					  $this->session->set_userdata("success_message", 'Please Enter Valid Mobile Number.');
					 redirect(base_url('login/forgot_password'));
				  }
				  }
			
		
			}
			
		}
	}
	// added by anil chauhan
	function opt_send_new_sms($message, $phone_number) {
   $data = array (
       "username" => "DIGITALINDIA-DEV", // type your assigned username here(for example:"username" => "CDACMUMBAI")
       "password" => '$DI2014', // type your password
       "senderid" => "DIPROG", // type your senderID
		"smsservicetype" => "singlemsg", // *Note* for single sms enter singlemsg , for bulk enter bulkmsg
       "mobileno" => trim($phone_number), // enter the mobile number
       "bulkmobno" => "", // enter mobile numbers separated by commas for bulk sms otherwise leave it blank
       "content" => $message
   ) // type the message.
  ;
   // post_to_url("http://msdgweb.mgov.gov.in/esms/sendsmsrequest", $data);
 
   $url = "http://msdgweb.mgov.gov.in/esms/sendsmsrequest";
   $fields = '';
   foreach ( $data as $key => $value ) {
     $fields .= $key . '=' . $value . '&';
   }
   rtrim ( $fields, '&' );
   $post = curl_init ();
   curl_setopt ( $post, CURLOPT_URL, $url );
   curl_setopt ( $post, CURLOPT_POST, count ( $data ) );
   curl_setopt ( $post, CURLOPT_POSTFIELDS, $fields );
   curl_setopt ( $post, CURLOPT_RETURNTRANSFER, 1 );
   $result = curl_exec ( $post );
   if ($result) {
	//redirect('login/firstStepToSetPassword');
	//echo"<script>alert('send otp on your mobile number')</script>";
	print_r( 'SMS Sent Sucessfully' ) ;
   }else {
	print_r ( 'unable to send sms' ) ;
   }
   curl_close ( $post );
}
	// ending the msg code
	public function firstStepToSetPassword(){
	  $this->load->view('includes/_login_header');		
	  $this->load->view('login/firstStepToSetPassword');
	  if($_POST){
		$config = array(array(
						'field'=>'optnumber',
						'label'=>'OTP Number',
						'rules'=>'required|trim'
						)
				);
		$this->form_validation->set_rules($config);
		$this->otpexpire();
		if($this->form_validation->run() == false)
		{
			$this->session->set_flashdata("success_message", 'Please enter OTP number.');
			redirect(base_url('login/firstStepToSetPassword'));
		}
		else{
			$otp_number=$this->input->post('optnumber');
			$result=$this->base_model->get_record_by_id('users_master',array('user_otp_number'=>$otp_number));
			$userId=$result->user_id;
			$user_mobile=$result->user_mobile_number;
			$new_otp= $result->user_otp_number;
			if($otp_number == $new_otp)
			{
					//$this->session->set_userdata('token_value',$new_otp);
					$this->session->set_userdata('token_value',$user_mobile);
					$user_id = base64_encode($userId);
					if(isset($user_id)){
					//$this->session->set_flashdata("SuccessMessage", 'Successfuly Enter OTP Number.');
					redirect('login/reset_password?token='.$user_id);
				}
			}else{
					
					$this->session->set_flashdata("success_message", 'Incorrect OTP Number, Please Try Again.');
					redirect(base_url('login/firstStepToSetPassword'));
				
				}
				
		}
		$this->session->set_flashdata("success_message", 'Incorrect OTP Number, Please enter correct OTP number.');
		redirect(base_url('login/firstStepToSetPassword'));
	}
}
	public function reset_password(){
		
		
		$this->load->view('includes/_login_header');		
		//$token=$this->input->get('token'); //user mobile number
		//$token_decode=base64_decode($token);
		//$otp=$this->session->userdata('token_value'); // generated otp number
		$usermobile =$this->session->userdata('token_value'); // generated otp number
		if($_POST){
			
			$this->form_validation->set_rules('newpassword','New password', 'trim|required|matches[confirmpassword]|min_length[8]|alpha_numaric_special');
			$this->form_validation->set_message('alpha_numaric_special','User password may contain atleast one lowercase letter, one uppercase letter,one special character & one numeric.');
			$this->form_validation->set_rules('confirmpassword',' confirm password', 'required|trim');
			$this->otpexpire();
			if($this->form_validation->run()== TRUE)
			{		
		
				$newpassword=$this->input->post('newpassword');
				$confirmpassword=$this->input->post('confirmpassword');
				//$result=$this->base_model->get_record_by_id('users_master',array('user_otp_number'=>$otp));
				$result=$this->base_model->get_record_by_id('users_master',array('user_mobile_number'=>$usermobile));
				$user_mobile=$result->user_mobile_number;
				$userId=$result->user_id;
				$otpnumber=$result->user_otp_number;
				//if($username && $usernameupdate && $otpnumber)
				//{
				if($otpnumber){
					$newpassword=md5($newpassword);
					$newpwd = array('user_password'=>$newpassword);
					$updatepwd = array('table'=>'users_master',
										'data' => $newpwd,
										'where'=>$userId,
										);	
					$res_pwd = $this->base_model->update_data($updatepwd);	
					if(isset($res_pwd))
					{
						$otpupdate="";
						$remove_otp =array('user_otp_number'=>$otpupdate);	
						$update_otp_data = array('table'=>'users_master','data' =>$remove_otp,'where'=>$userId);		
						$res_otp_data = $this->base_model->update_data($update_otp_data);
						if($res_otp_data == true)
						{
							$this->session->set_flashdata("success_message", 'Your password has been changed successfully.');
							redirect(base_url('login'));
						}
					}
					
				}
						
			}	
					
				
		}
			$this->load->view('login/reset_password');
	}
	
	function otpexpire()
	{
		$token=$this->input->get('token'); //user id 
        $token_decode=base64_decode($token);
        $mobile=$this->session->userdata('token_value'); // mobile number
        if($_POST){
				$otpnew="";
               // $username=$this->input->post('username');
                $newpassword=$this->input->post('newpassword');
                $confirmpassword=$this->input->post('confirmpassword');
                $result=$this->base_model->get_record_by_id('users_master',array('user_mobile_number'=>$mobile));
                $user_mobile=$result->user_mobile_number;
                $count=$result->user_otp_attemp;
                if($username && $usernameupdate && $otp)
                {
                        //go to reset password method 
				}else{
					$add=$count+1;
					if($add<=3){
						
						$this->db->where(array('user_id'=>$result->user_id));
						$this->db->update('users_master',array('user_otp_attemp' =>$add));
						
					}else{
						//$result=$this->base_model->get_record_by_id('users_master',array('user_otp_number'=>$otp));
						$result=$this->base_model->get_record_by_id('users_master',array('user_mobile_number'=>$mobile));
						$counttime=$result->user_otp_attemp;
						if($counttime==3){
							$rmoveotp="";
							$new_otp=array('user_otp_number'=>$rmoveotp);
							$add_otp= array('table'=>'users_master','data' => $new_otp,'where'=>$result->user_id);
							$res_otp = $this->base_model->update_data($add_otp);
							if($res_otp == true){
								$this->session->set_flashdata("success_message", 'OTP has been expired, Please generate new OTP.');
								redirect(base_url('login/forgot_password'));
							}
						}
					}
				}
		}
	}
	function otpduration()
	{
		$data = $this->db->select('user_id,user_otp_time,TIMESTAMPDIFF(MINUTE,user_otp_time, now()) as timeDiffer')->from('users_master')->get()->result();
		
			foreach($data as $value)
			{
				if($value->timeDiffer >15)
				{
						$rmoveotp="";
						//$datetime = date('Y-m-d H:i:s');
						$res_otp=$this->db->update('users_master',array('user_otp_number' => $rmoveotp));
						if($res_otp == true)
						{
							$this->session->set_flashdata("success_message", 'OTP has been expired, Please generate new OTP.');
							redirect(base_url('login'));
					}
				}
			}
			
	}
	
	public function logout()
	{
		$this->db->where('user_id',$this->session->userdata('user_id'));
		$this->db->update('users_master',array('session_id'=>''));
		
		$this->db->where('id',get_cookie('ci_session'));
		$this->db->delete('ci_sessions');
		
		$this->session->sess_destroy();
		redirect('login');
	}
		
		
    public function get_captcha1()
{
    $this->load->helper('captcha');

    $random_number = substr(number_format(time() * rand(),0,'',''),0,6);

    $capache_config = array(
				 'word' => $random_number,
				 'img_path' => './captcha/',
				 'img_url' => base_url().'captcha/',
				 'img_width' => 150,
                 'img_height' => 30,
                 'font_size' => 100,
				 'font_path'=> base_url().'captcha/font/Verdana.ttf',
				 'expiration' => 7200
		  );

    $data['captcha'] = create_captcha($capache_config );
	$this->session->set_userdata('captchaWord',$data['captcha']['word']);
    //print_r($cap);die;
    //$this->session->set_userdata($cap['word']);

    return $data['captcha']['image'];
}
		
    public function get_captcha()
    {
		//$this->load->helper('captcha');
        $new_captcha = $this->get_captcha1();
        echo "".$new_captcha;
    }
	
	public function selectLocation()
	{
		$user_id = $this->session->userdata('user_id');
		
		$user_id = $this->session->userdata('user_id');
		if($user_id=='' || $user_id=='1')
		{
			redirect(base_url('login/logout'));
		}
		
		if($_POST){
		//	print_r($_POST); die;
			$office_id = $this->input->post('office_id');
			$office_id = ($office_id == "H") ? "0" : $office_id;
			if($office_id == "0")
			{
				$this->db->select('upm.role_permission_id')->from('user_role_permission_master as upm');
				$this->db->where(array('upm.user_id'=>$user_id,'upm.office_id'=>$office_id));
				$location = $this->db->get()->row();
				$location->office_id = "H";
				$location->office_name = "HEAD OFFICE";
				$location->office_operation_type = "";
				
				
			}
			else
			{
				$this->db->select('om.office_id,om.office_name,om.office_operation_type,upm.role_permission_id')->from('user_role_permission_master as upm');
				$this->db->join('office_master as om','upm.office_id=om.office_id');
				$this->db->where(array('upm.user_id'=>$user_id,'upm.office_id'=>$office_id));
				$location = $this->db->get()->row();
			}
			
			// echo $this->db->last_query();
			// print_r($location); die;
			$role_id = '0';
			if($location->office_operation_type == "showroom"){
				$role_id= '3';
			}
			else if($location->office_operation_type == "store"){
				$role_id= '2';
			}
			else{
				$role_id = '1';
			}
			$newData = array('office_id'=>$location->office_id,'office_operation_type'=>$location->office_operation_type,'office_name'=>$location->office_name,'role_id'=>$role_id,'role_permission_id'=>$location->role_permission_id);
			$this->session->set_userdata($newData);
			redirect('user/dashboard');
		}
	     
		$this->load->view('includes/_login_header');		
		$this->load->view('login/select_location');
	}
	
	public function updateSession()
	{
		$sessionId = get_cookie('ci_session');

		$this->db->where('id',$sessionId);
		$this->db->update('ci_sessions',array('timestamp'=>time()));
	}

	public function generateExcelFile()
	{
		$this->load->library('excel');
		// activate worksheet number 1
		$this->excel->setActiveSheetIndex(0);
		//name the worksheet
		$this->excel->getActiveSheet()->setTitle('test worksheet');
		//set cell A1 content with some text
		$this->excel->getActiveSheet()->setCellValue('A1', 'This is just some text value');
		//change the font size
		$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(20);
		//make the font become bold
		$this->excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		//merge cell A1 until D1
		$this->excel->getActiveSheet()->mergeCells('A1:D1');
		//set aligment to center for that merged cell (A1 to D1)
		$this->excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		 
		$filename='just_some_random_name.xls'; //save our workbook as this file name
		header('Content-Type: application/vnd.ms-excel'); //mime type
		header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
		header('Cache-Control: max-age=0'); //no cache
					 
		//save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
		//if you want to save it as .XLSX Excel 2007 format
		$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
		//force user to download the Excel file without writing it to server's HD
		$objWriter->save('php://output');
	}
	
	function sendSms() 
	{
		$phone_number = "9711059687";
		$message = "Testing";
		
	/* 
		

https://smsgw.sms.gov.in/failsafe/HttpLink?username=xxxxxx&pin=xxxxxxx&message=message&mnumber=91XXXXXXXXXX&signature=SENDERID  
 */
		$data = array (
				   "username" => "igc.sms", // type your assigned username here(for example:"username" => "CDACMUMBAI")
				   "pin" => 'Yr*6bN#2xA', // type your password
				   "signature" => "NICSMS", // type your senderID
				   "mnumber" => '91'.trim($phone_number), // enter the mobile number
				   "message" =>  urlencode($message)
				);
   
   $url = "https://smsgw.sms.gov.in/failsafe/HttpLink";
   $fields = '';
   foreach ( $data as $key => $value ) {
     $fields .= $key . '=' . $value . '&';
   }
   rtrim ( $fields, '&' );
   $post = curl_init ();
   curl_setopt ( $post, CURLOPT_URL, $url );
   curl_setopt ( $post, CURLOPT_POST, count ( $data ) );
   curl_setopt ( $post, CURLOPT_POSTFIELDS, $fields );
   curl_setopt ( $post, CURLOPT_RETURNTRANSFER, 1 );
   $result = curl_exec ( $post );
   if ($result) {
	print_r( 'SMS Sent Sucessfully' ) ;
   }else {
	print_r ( 'unable to send sms' ) ;
   }
   curl_close ( $post );
}
	
}
