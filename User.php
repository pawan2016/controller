<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	
	public function __construct() {
        parent::__construct();
        sessionExist();
		$this->load->model('inventory_model');
		error_reporting(0);
		
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
	}
	
	public function dashboard()
	{   
	//print_r($this->session->all_userdata());
		$header['title'] = "Dashboard";
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('user/dashboard');
		$this->load->view('includes/_footer');
	}
	
	public function role_master()
	{ 
		$header['title'] = "Role Master";
    $check_super_admin=$this->session->all_userdata();	
	if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		$data['role_master'] = $this->db->get('role_permission_master')->result();
	    $this->load->view('user/role_master',$data);
		$this->load->view('includes/_footer');
		}else{
			$this->session->set_flashdata("error_message","You don't have access role master.");
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function add_role_master()
	{
		//print_r($_POST); die;
		$header['title'] = "Add Role Form";
		if($_POST){
			$rolePermissionId = (isset($_POST['role_permission_id'])) ? $_POST['role_permission_id'] : '0';
			$errors=array();
			if(empty($_POST['access_on'])){
				$errors['access_on_chosen'] = 'Access level of role is required';
			}
			if(empty($_POST['role_name'])){
				$errors['role_name'] = 'Role name is required';
			}
			else{
				if(!preg_match ("/^[a-zA-Z0-9_-]+$/",$_POST['role_name'])) {
					$errors['role_name'] = "Role name must only contain alpha-numeric characters and underscores!";
				}
				else{
					//already exist
					if(isset($rolePermissionId) && $rolePermissionId=='0'){
					$role_master_check=$this->base_model->get_record_by_id('role_permission_master',array('role_name'=>$_POST['role_name']));
					}
					else{
					$role_master_check=$this->base_model->get_record_by_id('role_permission_master',array('role_name'=>$_POST['role_name'],'role_permission_id !='=>$rolePermissionId));
					}
					if(count($role_master_check)>0){
					$errors['role_name'] = 'Already exist.'; 
					}
				}
			}
			if(isset($rolePermissionId) && $rolePermissionId=='0'){
				if(count($_POST) <= 4){
					$errors['eror_msg'] = "Please select any page for performing an action.";
				}
			}
			else{
				if(count($_POST) <= 5){
					$errors['eror_msg'] = "Please select any page for performing an action.";
				}
			}
			
			
			if(count($errors) > 0){
				//This is for ajax requests:
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				echo json_encode($errors);
				exit;
				}
				//This is when Javascript is turned off:
				echo "<ul>";
				foreach($errors as $key => $value){
				echo "<li>" . $value . "</li>";
				}
				echo "</ul>";exit;
			}
			else
			{
				$role_name = $_POST['role_name'];
				$access_on = $_POST['access_on'];
				$creator_id = $this->session->userdata('user_id');
				$createdOn = date('Y-m-d H:i:s');
				if(isset($rolePermissionId) && $rolePermissionId=='0'){
					$this->base_model->insert_one_row('role_permission_master',array('role_name'=>$role_name,'access_on'=>$access_on,'creator_id'=>$creator_id,'createdOn'=>$createdOn));
					$role_permission_id = $this->db->insert_id();
				}
				else{
					$this->db->where('role_permission_id',$rolePermissionId);
					$this->db->update('role_permission_master',array('role_name'=>$role_name));
					$role_permission_id = $rolePermissionId;
				}
				$insertData = array();			
				foreach($_POST['page_name'] as $key=>$page){
					$addValue = isset($_POST[$page.'_add']) ? $_POST[$page.'_add'] : "0";
					$editValue = isset($_POST[$page.'_edit']) ? $_POST[$page.'_edit'] : "0";
					$viewValue = isset($_POST[$page.'_view']) ? $_POST[$page.'_view'] : "0";
					$deleteValue = isset($_POST[$page.'_delete']) ? $_POST[$page.'_delete'] : "0";
					$authorizeValue = isset($_POST[$page.'_authorize']) ? $_POST[$page.'_authorize'] : "0";
					if($addValue == '0' && $editValue == '0' && $viewValue == '0' && $deleteValue == '0' && $authorizeValue == '0'){
						$pageStatus = '0';
					}
					else{
						$pageStatus = '1';
					}
					$insertData[] = array('role_permission_id'=>$role_permission_id,'page_id'=>$_POST['page_id'][$key],'add_value'=>$addValue,'edit_value'=>$editValue,'view_value'=>$viewValue,'delete_value'=>$deleteValue,'authorize_value'=>$authorizeValue,'page_status'=>$pageStatus,'creator_id'=>$creator_id,'createdOn'=>$createdOn);
				}
				//print_r($insertData); die;
					$this->db->where('role_permission_id',$role_permission_id);
					$this->db->delete('role_page_access_master');
				
					$this->base_model->insert_multiple_row('role_page_access_master',$insertData);
					if(isset($rolePermissionId) && $rolePermissionId=='0'){
						$this->session->set_flashdata("success_message", 'Role has been inserted successfully.');
						$action = "Add";
						$activity = "Role (".$role_name.") has been added.";
						$page_name = "add_role_master";
						$this->base_model->insertActivity($action,$activity,$page_name);
					}
					else{
						$action = "Edit";
						$activity = "Role (".$role_name.") has been updated.";
						$page_name = "add_role_master";
						$this->base_model->insertActivity($action,$activity,$page_name);
						$this->session->set_flashdata("success_message", 'Role has been updated successfully.');
					}
				
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					$errors['done']='success';
					echo json_encode($errors);
					//redirect('masterForm/raw_material_master');
					exit;
				 }
			}
		}
		$check_super_admin=$this->session->all_userdata();	
	    if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('user/add_role_master');
		$this->load->view('includes/_footer');
		}else{
			$this->session->set_flashdata("error_message","You don't have access role master.");
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function edit_role_master()
	{
		$check_super_admin=$this->session->all_userdata();	
		$header['title'] = "Edit Role Form";
	    if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$role_permission_id = $this->input->get('role_id');
		$data['page_lists'] = $this->db->select('rpm.role_permission_id,rpm.role_name,rpm.access_on,ram.page_id,ram.add_value,ram.edit_value,ram.delete_value,ram.view_value,plm.short_name,plm.page_name,ram.authorize_value')->from('role_permission_master as rpm')->join('role_page_access_master as ram','rpm.role_permission_id = ram.role_permission_id','left')->join('page_listing_master as plm','plm.page_id = ram.page_id','left')->where(array('rpm.role_permission_id'=>$role_permission_id,'plm.page_hide'=>'1'))->get()->result();
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('user/edit_role_master',$data);
		$this->load->view('includes/_footer');
		}else{
			$this->session->set_flashdata("error_message","You don't have access role master.");
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function user_master()
	{   
		$header['title'] = "Users Master";
		$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		$delete_value = 4;
		}else
		{

		$page_id = 1;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		//print_r($page_permission_array);die;
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		$delete_value = $page_permission_array->delete_value;
		}
		if($view_value==3)
		{
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		$data['users_master'] = $this->base_model->get_all_records('users_master');
	    $this->load->view('user/user_master',$data);
		$this->load->view('includes/_footer');
		}else{
			$this->session->set_flashdata("error_message","You don't have permission to view user master.");
			redirect(base_url('user/dashboard'));
		}
	}
	
	
	public function add_user_master()
	{  
        $header['title'] = "Add User Form";
        $check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		$delete_value = 4;
		}else
		{

		$page_id = 1;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		//print_r($page_permission_array);die;
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		$delete_value = $page_permission_array->delete_value;
		}			
	//print_r($_FILES); die;
		
		$userId = ($this->input->get('user_id')) ? base64_decode($this->input->get('user_id')) : '';
		$data['editUrl'] = '';
		
		$data_reg=$this->db->select('*')->get_where('regional_store_master',array('regional_store_type'=>'others'));
		$data['all_regional']=$data_reg->result();
		
		if($userId != ''){
			if($edit_value == '0'){
				$this->session->set_flashdata('error_message',"You don't have permission to edit user.");
				redirect(base_url('user/dashboard'));
			}
			$data['user_master_data'] = $this->base_model->get_record_by_id('users_master',array('user_id'=>$userId));
			$data['editUrl'] = '?user_id='.base64_encode($userId);
			$data['user_role_permission_master']= $this->base_model->get_all_record_by_condition('user_role_permission_master',array('user_id'=>$userId));
			//print_r($data['user_role_permission_master']);die;
			$header['title'] = "Edit User Form";

		}
		
		$this->form_validation->set_rules('user_name','User name', 'required|alpha_dash_dot');
		
		
		$this->form_validation->set_rules('user_mobile_number','User Mobile', 'required|numeric|exact_length[10]');
		$this->form_validation->set_rules('office_id[]','Office location', 'required');
		if($userId ==''){
			if($add_value == "0"){
						$this->session->set_flashdata("error_message","You don't have permission to add user .");
						redirect(base_url('user/dashboard'));
					}
			$this->form_validation->set_message('alpha_numaric_special', 'User password may only contain least one lowercase letter,and at least one uppercase letter,and at least a special character,One numeric ');
			$this->form_validation->set_message('alpha_dash_dot_username', 'User ID may only contain alpha numeric charectors, underscore , dash and .');
			$this->form_validation->set_rules('user_password','User password', 'required|matches[user_confirm_password]|min_length[8]|alpha_numaric_special');
			$this->form_validation->set_rules('user_confirm_password','User confirm password', 'required');
			$this->form_validation->set_rules('user_username','User userid', 'required|alpha_dash_dot_username|is_unique[users_master.user_username]');
			$this->form_validation->set_rules('user_email_id','User email-id', 'required|valid_email');
		}
		else{
		if($this->input->post('user_password')!='')
		{
			$this->form_validation->set_message('alpha_numaric_special', 'User password may only contain least one lowercase letter,and at least one uppercase letter,and at least a special character,One numeric ');
			$this->form_validation->set_message('alpha_dash_dot_username', 'User ID may only contain alpha numeric charectors, underscore , dash and .');
			$this->form_validation->set_rules('user_password','User password', 'required|matches[user_confirm_password]|min_length[8]|alpha_numaric_special');
			$this->form_validation->set_rules('user_confirm_password','User confirm password', 'required');
		}
			$this->form_validation->set_rules('user_email_id','User email-id', 'required|valid_email|callback_userEmail_check['.$userId.']');
			$this->form_validation->set_rules('user_username','User userid', 'required|alpha_dash_dot_username|callback_userUsername_check['.$userId.']');
		}
		$this->form_validation->set_rules('user_address','User address', 'required');
		$this->form_validation->set_rules('state_id','State', 'required');
		$this->form_validation->set_rules('district_id','District', 'required');
		$this->form_validation->set_rules('city_id','City', 'required');
		$this->form_validation->set_rules('user_pincode','Pincode', 'required|is_numeric|exact_length[6]');
		$this->form_validation->set_rules('access_right_from','Access right from date', 'required');
		
		if ($this->form_validation->run() == TRUE)
		{
			//echo "<pre>";
			//print_r($_POST);die;
			if($this->input->post() !=''){
				$table = 'users_master';
				$user_name = strtoupper($this->input->post('user_name'));
				$user_username = $this->input->post('user_username');
				$user_password = md5($this->input->post('user_password'));
				$user_mobile_number = $this->input->post('user_mobile_number');
				$office_id = $this->input->post('office_id');
				$user_email_id = $this->input->post('user_email_id');
				$user_address = $this->input->post('user_address');
				$state_id = $this->input->post('state_id');
				$district_id = $this->input->post('district_id');
				$city_id = $this->input->post('city_id');
				$user_pincode = $this->input->post('user_pincode');
				$access_right_from = $this->input->post('access_right_from');
				$access_right_to = $this->input->post('access_right_to');
				$user_access_type = "Maker"; //$this->input->post('user_access_type');
				$officeData = $this->db->select('*')->get_where('office_master',array('office_id'=>$office_id))->row();
				if($officeData->office_operation_type == "showroom"){
					$role_id = '3';
				}
				else if($officeData->office_operation_type == "store"){
					$role_id = '2';
				}
				/* if($userId !=''){
					if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0')
					{
						$usersData = $this->db->select('*')->get_where('users_master',array('user_id'=>$userId))->row();
						$imageName = $usersData->user_image;
						unlink(base_url('/uploads/userImage/'.$imageName));
					}
				} */
				
				/* if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0')
				{
					$ext = pathinfo($_FILES["user_image"]["name"], PATHINFO_EXTENSION);
					$userImage = time().'.'.$ext;
					move_uploaded_file($_FILES["user_image"]["tmp_name"],"/opt/lampp/htdocs/mmtc/uploads/userImage/".$userImage);
				} */
				
				if($userId !=''){
					
						$usersData = $this->db->select('*')->get_where('users_master',array('user_id'=>$userId))->row();
						$imageName = $usersData->user_image;
						$userImage=$usersData->user_image;
						
				}
				
				if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0' && !empty($_FILES["user_image"]["name"]))
				{
					$config['upload_path'] = './uploads/userImage/';
					$config['allowed_types'] = 'gif|jpg|png';
					

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload('user_image'))
					{
						$error = array('error' => $this->upload->display_errors());
                        redirect('user/user_master?error=0');
						//$this->load->view('upload_form', $error);
					}
					else
					{
						
						$data = array('upload_data' => $this->upload->data());
						if(!empty($data))
						{
							$userImage=$data['upload_data']['file_name'];
						}
						
					}
				}
					
				 
				$createdOn = date('Y-m-d h:i:s');
				if($userId == ''){
					$insertData = array('user_name' => $user_name,
										'user_username' => $user_username,
										'user_password' => $user_password,
										'user_mobile_number' => $user_mobile_number,
										'office_id' => $office_id,
										'user_email_id' => $user_email_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
										'user_access_type' => $user_access_type,
										'createdOn' => $createdOn,
									);
					
					$this->base_model->insert_one_row($table,$insertData);
					$this->session->set_flashdata('success_message',"Record has been added successfully.");
					$action = "Add";
					$activity = "User Master (".$user_name.") has been added.";
					$page_name = "add_user_master";
					$this->base_model->insertActivity($action,$activity,$page_name);
					
				}
				else{
				$arr_update_password=array();
				if($this->input->post('user_password')!='')
				{
				$updateData = array('user_name' => $user_name,
											'user_username' => $user_username,
											'user_password' => $user_password,
										'user_mobile_number' => $user_mobile_number,
										'user_email_id' => $user_email_id,
										'office_id' => $office_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
									//	'user_access_type' => $user_access_type,
									);
								
				}	
				else
				{
				$updateData = array('user_name' => $user_name,
										'user_mobile_number' => $user_mobile_number,
										'user_username' => $user_username,
										'user_email_id' => $user_email_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'office_id' => $office_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
									//	'user_access_type' => $user_access_type,
									);
								
				}		
					
					$where = array('user_id'=>$userId);
					
					$this->base_model->update_record_by_id($table, $updateData, $where);

					$this->session->set_flashdata('success_message',"Record has been updated successfully.");
					$action = "Edit";
					$activity = "User Master (".$user_name.") has been updated.";
					$page_name = "add_user_master";
					$this->base_model->insertActivity($action,$activity,$page_name);
				}
				redirect('user/user_master?succ=1');
			}
			
		}
		
		$data['state_master'] = $this->base_model->get_all_records('state_master');
		if(!empty($data['state_master']))
		{
			$stateId = $data['state_master'][0]->state_id;
			
			$data['district_master'] = $this->base_model->get_all_record_by_condition('district_master',array('state_id'=>$stateId));
			$districtId = (isset($data['district_master'][0]->district_id)) ? $data['district_master'][0]->district_id : '';
			
			$data['city_master'] = $this->base_model->get_all_record_by_condition('city_master',array('district_id'=>$districtId));
		}
		//$data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'show');
		
		//$mytransfer = $this->inventory_model->office_location_list();
		$data['transfer_to'] = $this->inventory_model->office_location_list();
		
		/* $transfer_to=array();
									$transfer_to[0] ->office_id= "H";
									$transfer_to[0] ->office_name="MMTC";
									$transfer_to[0] ->regional_store_type="HEAD OFFICE";
									$data['transfer_to'] = array_merge($transfer_to,$mytransfer); */
									// echo "<pre>";
									// print_r($data['transfer_to']); die;
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('user/add_user_master',$data);
		$this->load->view('includes/_footer');
	}
	
	
	public function ajax_add_user_master()
	{
		
		if($_POST){
			//print_r($_POST);
			$check_super_admin=$this->session->all_userdata();
			if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
			{
			$add_value = 1;
			$edit_value = 2;
			$view_value = 3;
			$delete_value = 4;
			}else
			{

			$page_id = 1;
			$page_permission_array = $this->role_model->get_page_permission($page_id);
			//print_r($page_permission_array);die;
			$add_value = $page_permission_array->add_value;
			$edit_value = $page_permission_array->edit_value;
			$view_value = $page_permission_array->view_value;
			$delete_value = $page_permission_array->delete_value;
			}
			if($userId == ''){
			if($add_value == '0'){
				//$this->session->set_flashdata('error_message',"You don't have permission to add user.");
				$errors['permission_error'] = "You don't have permission to add user.";
				//redirect(base_url('user/dashboard'));
			   }

			}
			$userId = ($this->input->get('user_id')) ? base64_decode($this->input->get('user_id')) : '';
			$data['editUrl'] = '';
			if($userId != ''){
				if($edit_value == '0'){
				$errors['permission_error'] = "You don't have permission to edit user.";
			   }
			$data['user_master_data'] = $this->base_model->get_record_by_id('users_master',array('user_id'=>$userId));
			$data['editUrl'] = '?user_id='.base64_encode($userId);
			}
			   $table = 'users_master';
				$user_name = strtoupper($this->input->post('user_name'));
				$user_username = $this->input->post('user_username');
				$user_password = md5($this->input->post('user_password'));
				$user_mobile_number = $this->input->post('user_mobile_number');
				$office_id = $this->input->post('office_id');
				$user_email_id = $this->input->post('user_email_id');
				$user_address = $this->input->post('user_address');
				$state_id = $this->input->post('state_id');
				$district_id = $this->input->post('district_id');
				$city_id = $this->input->post('city_id');
				$user_pincode = $this->input->post('user_pincode');
				$access_right_from = $this->input->post('access_right_from');
				$access_right_to = $this->input->post('access_right_to');
				$user_access_type = "Maker"; //$this->input->post('user_access_type');
				$confirm_pass=$_POST['user_confirm_password'];
				
			if(empty($_POST['user_name'])){
				$errors['user_name'] = 'User Name Required';
			}
			else{
				if(!preg_match ('/^[a-z0-9_ .]+$/i',$_POST['user_name'])) {
					$errors['user_name'] = "User name may only contain alpha-numeric characters, underscores, dashes and dots.";
				}
			}
			if(empty($_POST['user_mobile_number'])){
				$errors['user_mobile_number'] = 'User Mobile No. Is Required';
			}
			else{
				if(!preg_match ('/^[\-+]?[0-9]*\.?[0-9]+$/',$_POST['user_mobile_number'])) {
					$errors['user_mobile_number'] = "User Mobile No. must contain only numbers.";
				}
				else{
					//already exist
					if(strlen($user_mobile_number)!='10')
					{
					  $errors['user_mobile_number'] = "User Mobile No. must be exactly 10 characters in length..";
				    }
					else{
						if($userId == ''){
						$check_mobile=$this->base_model->get_record_by_id($table,array('user_mobile_number'=>$user_mobile_number));
						}
						else{
							$check_mobile=$this->base_model->get_record_by_id($table,array('user_mobile_number'=>$user_mobile_number,'user_id !='=>$userId));
						}
						
						if(count($check_mobile)>0){
						$errors['user_mobile_number'] = 'Already exist.'; 
						}
					
					}
				}
			}
		if($userId == ''){	
			if(empty($_POST['user_password']))
			{
				$errors['user_password']="User Password is required";
			}else{
				if(!preg_match('/^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',trim($_POST['user_password'])))
				{
				  $errors['user_password']="User password may contain atleast one lowercase letter, one uppercase letter,one special character & one numeric.";
				}else{
					if(trim($_POST['user_password'])!=trim($confirm_pass))
					{
						 $errors['user_password']='User Password Not Match To Confirm Password';
					}
				}
			}
			
			if(empty($_POST['user_confirm_password']))
			{
			  $errors['user_confirm_password']="Confirm Password is required";
			}
		}else{
			if($this->input->post('user_password')!='')
				{
					if(empty($_POST['user_password']))
					{
					$errors['user_password']="User Password is required";
					}else{
					if(!preg_match('/^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/',trim($_POST['user_password'])))
					{
					$errors['user_password']="User password may contain atleast one lowercase letter, one uppercase letter,one special character & one numeric.";
					}else{
					if(trim($_POST['user_password'])!=trim($confirm_pass))
					{
						 $errors['user_password']='User Password Not Match To Confirm Password';
					}
					}
					}

					if(empty($_POST['user_confirm_password']))
					{
					$errors['user_confirm_password']="Confirm Password is required";
					}
					
				}
		}
			
			
			if(empty($user_username))
			{
				$errors['user_username']="User Id is required";
			}else{
				if(!preg_match('/^[a-z0-9_.]+$/i',$user_username))
				{
				  $errors['user_username']="User ID may only contain alpha numeric characters, underscore and .";
				}else{
					if($userId == ''){
						$check_username=$this->base_model->get_record_by_id('users_master',array('user_username'=>$user_username));
					}
					else{
						$check_username=$this->base_model->get_record_by_id('users_master',array('user_username'=>$user_username,'user_id !='=>$userId));
					}
					
					if(count($check_username)>0){
					$errors['user_username'] = 'Already exist.'; 
					}
				}
			}
			
			if(empty($_POST['user_email_id']))
			{
				//$errors['user_email_id']="Email Id is required";
			}else{
				if(!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$_POST['user_email_id']))
				{
					$errors['user_email_id']="Please enter valid email";
				}
				else{
					if($userId==' '){
						
						$check_useremail=$this->base_model->get_record_by_id('users_master',array('user_email_id'=>$user_email_id));
					}
					else{
						$check_useremail=$this->base_model->get_record_by_id('users_master',array('user_email_id'=>$user_email_id,'user_id !='=>$userId));
					}
					
					if(count($check_useremail)>0){
					$errors['user_email_id'] = 'Already exist.'; 
					}
					
				}
			}			
			if(empty($_POST['state_id']))
			{
				$errors['state_id_chosen']="State is required";
			}
			if(empty($_POST['district_id']))
			{
				$errors['district_id_chosen']="District is required.";
			}
			if(empty($_POST['city_id']))
			{
				$errors['city_id_chosen']="City is required.";
			}
			if(empty($_POST['user_pincode']))
			{
				$errors['user_pincode']="Pincode is required.";
			}else{
				if(strlen($_POST['user_pincode'])!='6')
				{
					$errors['user_pincode']="Pincode length 6  is required.";
				}
			}
			if(empty($_POST['user_address']))
			{
				$errors['user_address']="User address is required.";
			}
			if(empty($_POST['access_right_from']))
			{
				$errors['access_right_from']="ss right from date is required.";
			}
			if(empty($_POST['office_id']))
			{
				$office_array=$_POST['selected_location_val'];
				//$errors['office_id_chosen']="office location is required";
			}
			
			if(empty($_POST['selected_location_val']))
			{				
				$errors['office_id-0']="office location is required";
			}
			else{
				$office_array= explode(',',$_POST['selected_location_val']);
				$selected_divs_ids= explode(',',$_POST['selected_divs_ids']);
				//print_r($_POST['role_permission_id']);
				foreach($_POST['role_permission_id'] as $key=>$role)
				{
					if($role == ''){
						$errors['role_id-'.$selected_divs_ids[$key]]="Role is required";
					}
					if(empty($office_array[$key]))
					{
						//$errors['office_id-'.$selected_divs_ids[$key]]=implode(',',$office_array)."office location is required.".$office_array[$selected_divs_ids[$key]]." and ".implode(',',$selected_divs_ids)." and ".$office_array[$key];
						$errors['office_id-'.$selected_divs_ids[$key]]= "office location is required.";
					}
				}
				
			}
			
			
			if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0' && !empty($_FILES["user_image"]["name"]))
				{
					$config['upload_path'] = './uploads/userImage/';
					$config['allowed_types'] = 'gif|jpg|png';
					

					$this->load->library('upload', $config);

					if ( ! $this->upload->do_upload('user_image'))
					{
						$errors['user_image']="Image not uploadded";
						//$error = array('error' => $this->upload->display_errors());
                        //redirect('user/user_master?error=0');
						//$this->load->view('upload_form', $error);
					}
					else
					{
						
						$data = array('upload_data' => $this->upload->data());
						if(!empty($data))
						{
							$userImage=$data['upload_data']['file_name'];
						}
						
					}
				}
			
			
			//print_r($userImage);die;
			if(count($errors) > 0){
				//This is for ajax requests:
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				echo json_encode($errors);
				exit;
				}
				//This is when Javascript is turned off:
				echo "<ul>";
				foreach($errors as $key => $value){
				echo "<li>" . $value . "</li>";
				}
				echo "</ul>";exit;
			}
			else
			{
				
				
				if($this->input->post() !=''){
				//print_r($_POST);die;
				$table = 'users_master';
				$user_name = strtoupper($this->input->post('user_name'));
				$user_username = $this->input->post('user_username');
				$user_password = md5($this->input->post('user_password'));
				$user_mobile_number = $this->input->post('user_mobile_number');
				$office_id = $this->input->post('office_id');
				$role_permission_id = $this->input->post('role_permission_id');
				$region_id = $this->input->post('region_id');
				$user_email_id = $this->input->post('user_email_id');
				$user_address = $this->input->post('user_address');
				$state_id = $this->input->post('state_id');
				$district_id = $this->input->post('district_id');
				$city_id = $this->input->post('city_id');
				$user_pincode = $this->input->post('user_pincode');
				$access_right_from = $this->input->post('access_right_from');
				$access_right_to = $this->input->post('access_right_to');
				$user_access_type = "Maker"; //$this->input->post('user_access_type');
				$office_array= explode(',',$_POST['selected_location_val']);
				$creator_id = $this->session->userdata('user_id');
				$createdOn = date('Y-m-d H:i:s');	
				$office_id=$office_array[0];
				
				$officeData = $this->db->select('*')->get_where('office_master',array('office_id'=>$office_id))->row();
				if($officeData->office_operation_type == "showroom"){
					$role_id = '3';
				}
				else if($officeData->office_operation_type == "store"){
					$role_id = '2';
				}
				else{
					$role_id = '1';
				}
				
				/* if($userId !=''){
					if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0')
					{
						$usersData = $this->db->select('*')->get_where('users_master',array('user_id'=>$userId))->row();
						$imageName = $usersData->user_image;
						unlink(base_url('/uploads/userImage/'.$imageName));
					}
				} */
				
				/* if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0')
				{
					$ext = pathinfo($_FILES["user_image"]["name"], PATHINFO_EXTENSION);
					$userImage = time().'.'.$ext;
					move_uploaded_file($_FILES["user_image"]["tmp_name"],"/opt/lampp/htdocs/mmtc/uploads/userImage/".$userImage);
				} */
				
				if($userId !=''){
					
						$usersData = $this->db->select('*')->get_where('users_master',array('user_id'=>$userId))->row();
						$imageName = $usersData->user_image;
						$userImage=$usersData->user_image;
						
				}
				
				
					
				 
				$createdOn = date('Y-m-d h:i:s');
				if($userId == ''){
					$insertData = array('user_name' => $user_name,
										'user_username' => $user_username,
										'user_password' => $user_password,
										'user_mobile_number' => $user_mobile_number,
										'office_id' => $office_id,
										'user_email_id' => $user_email_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
										'user_access_type' => $user_access_type,
										'createdOn' => $createdOn,
									);
					
					$this->base_model->insert_one_row($table,$insertData);
					$user_id_last=$this->db->insert_id();
					foreach($office_array as $key=>$user_location)
					{
						$ofice_id_data=$user_location;
						$rolepremisstionid=$role_permission_id[$key];
						$regional_store_id='0';
						if($region_id[$key]!='' && $ofice_id_data=='1')
						{
							$regional_store_id=$region_id[$key];
						}
						$insertarray=array('user_id'=>$user_id_last,'office_id'=>$ofice_id_data,'role_permission_id'=>$rolepremisstionid,'regional_store_id'=>$regional_store_id,'creator_id'=>$creator_id,'createdOn'=>$createdOn);
						$this->db->insert('user_role_permission_master',$insertarray);
						
					}
					$this->session->set_flashdata('success_message',"Record has been added successfully.");
					$action = "Add";
					$activity = "User Master (".$user_name.") has been added.";
					$page_name = "add_user_master";
					$this->base_model->insertActivity($action,$activity,$page_name);
					
				}
				else{
				$arr_update_password=array();
				if($this->input->post('user_password')!='')
				{
				$updateData = array('user_name' => $user_name,
											'user_username' => $user_username,
											'user_password' => $user_password,
										'user_mobile_number' => $user_mobile_number,
										'user_email_id' => $user_email_id,
										'office_id' => $office_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
									//	'user_access_type' => $user_access_type,
									);
								
				}	
				else
				{
				$updateData = array('user_name' => $user_name,
										'user_mobile_number' => $user_mobile_number,
										'user_username' => $user_username,
										'user_email_id' => $user_email_id,
										'user_address' => $user_address,
										'state_id' => $state_id,
										'office_id' => $office_id,
										'district_id' => $district_id,
										'city_id' => $city_id,
										'user_pincode' => $user_pincode,
										'access_right_from' => $access_right_from,
										'access_right_to' => $access_right_to,
										'role_id' => $role_id,
										'user_image' => $userImage,
									//	'user_access_type' => $user_access_type,
									);
								
				}		
					
					$where = array('user_id'=>$userId);
					
					$this->base_model->update_record_by_id($table, $updateData, $where);
					
					
					$this->db->where('user_id',$userId);
					$this->db->delete('user_role_permission_master');
					
					foreach($office_array as $key=>$user_location)
					{
						$ofice_id_data=$user_location;
						$rolepremisstionid=$role_permission_id[$key];
						$regional_store_id='0';
						if($region_id[$key]!='' && $ofice_id_data=='1')
						{
							$regional_store_id=$region_id[$key];
						}
						$insertarray=array('user_id'=>$userId,'office_id'=>$ofice_id_data,'role_permission_id'=>$rolepremisstionid,'regional_store_id'=>$regional_store_id,'creator_id'=>$creator_id,'createdOn'=>$createdOn);
						$this->db->insert('user_role_permission_master',$insertarray);
						
					}

					$this->session->set_flashdata('success_message',"Record has been updated successfully.");
					$action = "Edit";
					$activity = "User Master (".$user_name.") has been updated.";
					$page_name = "add_user_master";
					$this->base_model->insertActivity($action,$activity,$page_name);
				}
				
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					$errors['done']='success';
					if($userId=="")
					{
					$errors['success_message']='Record has been added successfully.';
					}else{
						$errors['success_message']='Record has been updated successfully.';
					}
					echo json_encode($errors);
					//redirect('masterForm/raw_material_master');
					exit;
				 }
				
				//redirect('user/user_master?succ=1');
			}
					$data['state_master'] = $this->base_model->get_all_records('state_master');
					if(!empty($data['state_master']))
					{
					$stateId = $data['state_master'][0]->state_id;

					$data['district_master'] = $this->base_model->get_all_record_by_condition('district_master',array('state_id'=>$stateId));
					$districtId = (isset($data['district_master'][0]->district_id)) ? $data['district_master'][0]->district_id : '';

					$data['city_master'] = $this->base_model->get_all_record_by_condition('city_master',array('district_id'=>$districtId));
					}
					//$data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'show');

					//$mytransfer = $this->inventory_model->office_location_list();
					$data['transfer_to'] = $this->inventory_model->office_location_list();

					// $transfer_to=array();
					// $transfer_to[0] ->office_id= "H";
					// $transfer_to[0] ->office_name="MMTC";
					// $transfer_to[0] ->regional_store_type="HEAD OFFICE";
					// $data['transfer_to'] = array_merge($transfer_to,$mytransfer);

			}
		}	
	}
	public function userEmail_check($email_id,$user_id){
		$usersData = $this->db->select('*')->get_where('users_master',array('user_email_id'=>$email_id))->row();
		
		if ($usersData->user_id != $user_id && count($usersData)>0) {
			 $this->form_validation->set_message('userEmail_check', 'The %s field should be unique.');
			 return FALSE;
		}
		else {
		return TRUE;
		}
	}
		public function AjaxAddNewDivCommon(){

		$data=array();

		$postedArr=$this->security->xss_clean($_POST);

		$office_id = $this->session->userdata('office_id');

		switch($postedArr['pageName']){

			case 'add_user_master':
					$data['transfer_to'] = $this->inventory_model->office_location_list();
					// $mytransfer = $this->inventory_model->office_location_list();
				
					// $transfer_to=array();
					// $transfer_to[0] ->office_id= "H";
					// $transfer_to[0] ->office_name="MMTC";
					// $transfer_to[0] ->regional_store_type="HEAD OFFICE";
					// $data['transfer_to'] = array_merge($transfer_to,$mytransfer);
			
			break;
			case 'location_change':
					$data['transfer_to'] = $this->inventory_model->office_location_list();
					// $mytransfer = $this->inventory_model->office_location_list();

					// $transfer_to=array();
					// $transfer_to[0] ->office_id= "H";
					// $transfer_to[0] ->office_name="MMTC";
					// $transfer_to[0] ->regional_store_type="HEAD OFFICE";
					// $data['transfer_to'] = array_merge($transfer_to,$mytransfer);
			
			break;
			
		}

		$data['divSize']=$postedArr['divSize'];
		$data['pageName']=$postedArr['pageName'];
		$data['already_location']=$postedArr['already_location'];
		$data['select_location']=$postedArr['select_location'];
		$this->load->view('includes/_AjaxAddNewDivCommon_user',$data);	

	}
	public function userUsername_check($user_username,$user_id){
		$usersData = $this->db->select('*')->get_where('users_master',array('user_username'=>$user_username))->row();
		
		if ($usersData->user_id != $user_id && count($usersData)>0) {
			 $this->form_validation->set_message('userUsername_check', 'The %s field should be unique.');
			 return FALSE;
		}
		else {
		return TRUE;
		}
	}
	public function change_password(){
		$header['title'] = "Change Password";
		if($this->session->userdata('is_logged_in'))
		{
			if($this->input->post('Submit'))
			{						
				$data = array();
				$l_data = array();
				$l_data['page_title'] = 'Change password';
				$session_data=$_SESSION['user_id'];
				$current_pass=md5($this->input->post('current_pass'));
				$new_pass= md5($this->input->post('new_pass'));		
				$confirm_pass= md5($this->input->post('confirm_pass'));	
				$this->form_validation->set_rules('current_pass','Current password', 'required');
				$this->form_validation->set_rules('new_pass','New password', 'required|alpha_numaric_special');
				$this->form_validation->set_rules('confirm_pass','Confirm password', 'required|matches[new_pass]');
				$this->form_validation->set_message('alpha_numaric_special', 'User password may only contain least one lowercase letter,and at least one uppercase letter,and at least a special character,One numeric ');
				if($this->form_validation->run() == FALSE)
				{
					$this->load->view("includes/_header",$header);
					$this->load->view("includes/_top_menu");
					$this->load->view('user/change_password');
					$this->load->view('includes/_footer');
				} 
				else 
				{
					$qry=$this->db->query('select * from users_master  where user_id="'.$session_data.'" and user_password="'.$current_pass.'"');
					$check_password=$qry->result_array();
					if(empty($check_password[0]['user_id']))
					{
						$l_data['error']="Please enter valid current password";	
						$this->load->view("includes/_header",$header);
						$this->load->view("includes/_top_menu");
						$this->load->view('user/change_password',$l_data);
						$this->load->view('includes/_footer');
						return false;
					}
					else
					{
						$this->load->model('User_model');
						$data= array('user_password'=>$new_pass);
						$id['updat']=$this->User_model->change($session_data,$data);
						if($id)
						{
							$action = "Update";
							$activity = "Password (".$check_password[0]['user_name'].") has been updated.";
							$page_name = "change_password";
							$this->base_model->insertActivity($action,$activity,$page_name);
							
							$l_data['succ']="Password Change Successfully";
							$this->load->view("includes/_header",$header);
							$this->load->view("includes/_top_menu");	
							$this->load->view('user/change_password',$l_data);
							$this->load->view('includes/_footer');
							return false;
						}
					}
				}
			}
			else{
				$this->load->view("includes/_header",$header);
				$this->load->view("includes/_top_menu");
				$this->load->view('user/change_password');
				$this->load->view('includes/_footer');
			}
		}
		else
		{
		redirect(base_url().'mmtc_new/login', 'refresh');
		}
	}
	
	public function getFormPageList()
	{
		$access_on = $this->input->post('access_on');
		$data['access_on'] = $access_on;
		$data['page_lists'] = $this->db->get_where('page_listing_master',array('access_on'=>$access_on,'page_hide'=>'1'))->result();
		echo $this->load->view('includes/_role_list_table',$data,true);
	}
	
   public function roleAjax()
   {
	    $data=array();

		$postedArr=$this->security->xss_clean($_POST);
	
	   $usersData = $this->db->select('*')->get_where('office_master',array('office_id'=>$postedArr['office_id']))->row();
	   	if($usersData->office_operation_type=="store"){
			$access_on=2;
		}elseif($usersData->office_operation_type=="showroom"){
			$access_on=3;
		}
		else{
			$access_on=1;
		}
		$data=$this->db->select('*')->get_where('role_permission_master',array('access_on'=>$access_on));
		$data1['all_role']=$data->result();
		$data1['pageName']=$postedArr['pageName'];
		$data1['divSize1']=$postedArr['divSize1'];
		
		echo $this->load->view('includes/_AjaxAddNewDivCommon',$data1,true);
		
   }
   
	public function user_activity()
	{
		$header['title'] = "Users Activity";
		$check_super_admin=$this->session->all_userdata();	
	    if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$data['activityLists'] = $this->db->select('uam.createdOn,uam.activity,uam.action,um.user_name')->from('users_activity_master as uam')->join('users_master as um','um.user_id=uam.user_id','left')->order_by('uam.createdOn','DESC')->get()->result();
		$this->load->view('user/user_activity',$data);
		$this->load->view('includes/_footer');
		}else{
			$this->session->set_flashdata("error_message","You don't have access user activity.");
			redirect(base_url('user/dashboard'));
		}
	}

	public function activateAccountStatus()
	{
		$userId = $this->input->post("userId");
		$user_status = $this->input->post("status");
		$this->db->where(array("user_id"=>$userId));
		$this->db->update('users_master',array('user_status'=>$user_status));
		$show_status = "";
		if($user_status == '1'){ 
			$show_status = "Active";
		} if($user_status == '0') { 
			$show_status = "Deactive";
		} 
		echo $show_status;
	}
	public function regionAjax()
   {
	    $data=array();

		$postedArr=$this->security->xss_clean($_POST);
	
	  
		$data=$this->db->select('*')->get_where('regional_store_master',array('regional_store_type'=>'others'));
		$data1['all_regional']=$data->result();
		$data1['pageName']=$postedArr['pageName'];
		$data1['divSize']=$postedArr['divSize'];
		
		echo $this->load->view('includes/_AjaxAddNewDivCommon',$data1,true);
		
   }
}
