<?php
error_reporting(0);

defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends CI_Controller {

    public function __construct() {
        parent::__construct();
		sessionExist();
		if(!$this->session->userdata('is_logged_in'))
		{
			redirect('Login/index','refresh');
		}
			
        $this->load->model('inventory_model');		
	}
	
	public function index (){
			redirect(base_url('user/dashboard'));
		}
		
	public function initial_stock_form(){
		//$postedArr=$this->security->xss_clean($_POST);
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		if($office_operation_type == "store"){
			$page_id = '18';
		}
		else if($office_operation_type == "showroom"){
			redirect(base_url('user/dashboard'));
		}		
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		if($add_value=='1' || $edit_value == '2')
		{
			if($_POST)
			{
					$errors=array();
					if(isset($_POST['initial_stock_id']) && $_POST['initial_stock_id']==0){
				//	if(empty($_POST['initial_stock_starting_store_date'])) {
					//$errors['initial_stock_starting_store_date'] = 'Initial Stock Date & Time of Starting Store is required';
				//	}
					}
						
					foreach($_POST['initial_stock_serial_no'] as $key=>$value)
					{
					  if(empty($value)){
						$errors['initial_stock_starting_serial_popup'.$key] = 'Initial Stock Starting Serial No. is required'; 
					  }else{
						if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i', $value)){
							//print_r($value);exit;
							$errors['initial_stock_starting_serial_popup'.$key] = 'Initial Stock Starting Serial No. accept only combination of (letter or number)';
						   }else{
								$ar =  array_diff_key($_POST['initial_stock_serial_no'],array_unique($_POST['initial_stock_serial_no']));
								foreach($ar as $k=>$v){
								$myval = $k;
								$errors['initial_stock_starting_serial_popup'.$k] = "Serial Number is duplicate.";
								}
							   
							   /* $check_exit_serial = $this->db->get_where('serial_number_master',array('product_id'=>$product_id,'serial_number'=>$value))->row();
                                if(empty($check_exit_serial)){
								
								}else{	
								$errors['initial_stock_starting_serial_popup'.$key] = 'Serial No. already exit';	
							   } */
						}
						
					}
					  
					}
					
				$table_name_initial='inventory_'.$office_operation_type.'_initial_stock_'.$office_id;				
				$table_name_initial_product_serial='inventory_'.$office_operation_type.'_initial_stock_product_serial_number_'.$office_id;
				$product_id = $_POST['product_id'];
				
				$initialStock_data = $this->base_model->get_record_by_id($table_name_initial,array('product_id'=>$product_id));
				$initialStock_serialData = array();
				if(!empty($initialStock_data))
				{
					$initialStock_serialData = $this->db->get_where($table_name_initial_product_serial,array('initial_stock_id'=>$initialStock_data->initial_stock_id))->result();
				}
				
				if(!empty($initialStock_serialData))
				{
					foreach($initialStock_serialData as $serial){
						$this->base_model->delete_record_by_id('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$serial->initial_product_serial_number));
					}
				}
				
				$arr_serial_count = $this->db->select('*')->where(array('product_id'=>$product_id))->where_in('serial_number',$serialNumber_Array)->get('serial_number_master');
				$serialMasterArray = $this->db->select('*')->where(array('product_id'=>$product_id))->get('serial_number_master')->result();
				$serial_master_array = array();
				foreach($serialMasterArray as $serial){
					array_push($serial_master_array,$serial->serial_number);
				}
				$ar =  array_intersect($initialStock_serialData,$serial_master_array);
				foreach($ar as $k=>$v){
				$myval = $k;
				$errors['initial_stock_starting_serial_popup'.$k] = "Serial number is already exist.";
				}
				
					  
					if(empty($_POST['initial_stock_quantity'])) {
					$errors['initial_stock_quantity'] = 'Initial Stock Quantity is required';
					}else{
					if(!preg_match('/^[0-9]*$/', $_POST['initial_stock_quantity'])) {
						$errors['initial_stock_quantity'] = 'Initial Stock Quantity accept only number';
					}
					}
					
					if(!empty($initialStock_serialData))
					{
						foreach($initialStock_serialData as $serial){
						$this->base_model->insert_one_row('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$serial->initial_product_serial_number,'createdOn'=>date('Y-m-d H:i:s')));
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
				}else{
				// if($_POST['is_submit']=='1')
				// {
				$postedArr=$this->security->xss_clean($_POST);
			
				//$i_s_date=strtotime(str_replace("/","-",$postedArr['initial_stock_starting_store_date']));
				$initial_stock_starting_store_date= date("Y-m-d H:i:s");
				$initial_stock_quantity=$postedArr['initial_stock_quantity'];
				$initial_stock_serial_no=$postedArr['initial_stock_serial_no'][0];
				$initial_stock_serial_number=$postedArr['initial_stock_serial_no'];
				$product_id=$postedArr['product_id'];
				$initial_stock_edit_id=$postedArr['initial_stock_id'];
				$createdOn=date('Y-m-d H:i:s');
				$table_name_initial='inventory_'.$office_operation_type.'_initial_stock_'.$office_id;				
				$table_name_initial_product_serial='inventory_'.$office_operation_type.'_initial_stock_product_serial_number_'.$office_id;
				
				$product_current_stock_table='product_current_stock_'.$office_id;
				
				$current_stock_serial_number_table = 'product_current_stock_serial_number_'.$office_id;
				
				$history_table = 'inventory_office_history_'.$office_id;
				$initial_stock_data=$this->db->get_where($table_name_initial,array('product_id'=>$product_id))->row();
				if(!empty($initial_stock_data))
				{
					$initial_stock_edit_id=$initial_stock_data->initial_stock_id;
				}
				if(isset($initial_stock_edit_id) && $initial_stock_edit_id==0 && empty($initial_stock_data)){
				
				$newData=array('product_id'=>$product_id,'initial_stock_quantity'=>$initial_stock_quantity,'initial_stock_serial_number'=>$initial_stock_serial_no,'initial_stock_starting_store_date'=>$initial_stock_starting_store_date,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				$this->base_model->insert_one_row($table_name_initial,$newData);
				$initial_stock_id=$this->base_model->get_last_insert_id();
				
				$current_exist=$this->db->get_where($product_current_stock_table,array('product_id'=>$product_id))->row();
				if(empty($current_exist))
				{
				$newData1=array('initial_stock_id'=>$initial_stock_id,'product_id'=>$product_id,'product_current_stock'=>$initial_stock_quantity,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				$this->base_model->insert_one_row($product_current_stock_table,$newData1);				
				$current_stock_id = $this->db->insert_id();
				}
				else
				{
				
				$newData1=array('product_id'=>$product_id,'product_current_stock'=>$initial_stock_quantity,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				$this->db->update($product_current_stock_table,$newData1,array('product_id'=>$product_id));		
				$current_stock_id = $current_exist->current_stock_id;	
				}
				
				$historyData = array('current_stock'=>$initial_stock_quantity,'product_id'=>$product_id,'net_stock'=>$initial_stock_quantity,'type_value'=>'initial','creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				$this->base_model->insert_one_row($history_table,$historyData);
				
				
				if(!empty($initial_stock_serial_number)){
					foreach($initial_stock_serial_number as $key=>$value)
					  {
						if(!empty($value) && $value!=''){
						$i_s_product_serial[]=array('initial_stock_id'=>$initial_stock_id,'product_id'=>$product_id,'initial_product_serial_number'=>$value,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
						
						$current_stock_product_serial[] = array('current_stock_id'=>$current_stock_id,'product_id'=>$product_id,'product_serial_number'=>$value,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
						
						$this->db->insert('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$value));
						}					
					  }
					 $this->base_model->insert_multiple_row($table_name_initial_product_serial,$i_s_product_serial);
					 $this->base_model->insert_multiple_row($current_stock_serial_number_table,$current_stock_product_serial);
					 
					 
					  }
				}else{
				$newUpdateData=array('initial_stock_quantity'=>$initial_stock_quantity,'initial_stock_serial_number'=>$initial_stock_serial_no);
				
				$res=$this->base_model->update_record_by_id($table_name_initial,$newUpdateData,array('initial_stock_id'=>$initial_stock_edit_id));
				
				$newData1=array('product_id'=>$product_id,'product_current_stock'=>$initial_stock_quantity,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				$this->db->update($product_current_stock_table,$newData1,array('product_id'=>$product_id));		
				$current_stock_id = $current_exist->current_stock_id;	
				
				// $newUpdateDataCurrent=array('product_current_stock'=>$initial_stock_quantity);
				
				// $res_current = $this->base_model->update_record_by_id($product_current_stock_table,$newUpdateDataCurrent,array('initial_stock_id'=>$initial_stock_edit_id));
				
				$historyData = array('current_stock'=>$initial_stock_quantity,'net_stock'=>$initial_stock_quantity); 
				$his_data=$this->db->get_where($history_table,array('type_value'=>'initial','product_id'=>$product_id))->row();
				
				$res_his=$this->base_model->update_record_by_id($history_table,$historyData,array('history_id'=>$his_data->history_id));
				
				
				$arr_exist_current=$this->db->get_where($current_stock_serial_number_table,array('product_id'=>$product_id))->result();
				foreach($arr_exist_current as $serial_number)
				{
					
					$this->base_model->delete_record_by_id('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$serial_number->product_serial_number));
				}
				
				
				$this->base_model->delete_record_by_id($table_name_initial_product_serial,array('product_id'=>$product_id));
				$this->base_model->delete_record_by_id($current_stock_serial_number_table,array('product_id'=>$product_id));
				 if(!empty($initial_stock_serial_number)){
					foreach($initial_stock_serial_number as $key=>$value)
					  {
						if(!empty($value) && $value!=''){
						$i_s_product_serial[]=array('initial_stock_id'=>$initial_stock_edit_id,'product_id'=>$product_id,'initial_product_serial_number'=>$value,'createdOn'=>$createdOn);
						
						$current_stock_product_serial[] = array('current_stock_id'=>$current_stock_id,'product_id'=>$product_id,'product_serial_number'=>$value,'createdOn'=>$createdOn);
						//$this->db->insert('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$value,'createdOn'=>date('Y-m-d H:i:s')));
						$seriaMasterData = $this->db->get_where('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$value));
						if($seriaMasterData->num_rows() > 0 )
						{
							//$this->base_model->update_record_by_id('serial_number_master',array('createdOn'=>date('Y-m-d H:i:s')),array('product_id'=>$product_id,'serial_number'=>$value));
						}
						else
						{
							$this->base_model->insert_one_row('serial_number_master', array('product_id'=>$product_id,'serial_number'=>$value,'createdOn'=>date('Y-m-d H:i:s')));
						}
						
						}					
					  }
					 $this->base_model->insert_multiple_row($table_name_initial_product_serial,$i_s_product_serial);
					 $this->base_model->insert_multiple_row($current_stock_serial_number_table,$current_stock_product_serial);
					}	
				}
				if(isset($initial_stock_edit_id) && $initial_stock_edit_id==0){
				$this->session->set_flashdata("SuccessMessage", 'Initial Stock has been added successfully.');
				if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
					
					$errors['done']='success';
					$errors['MSG']='Initial Stock has been added successfully.';
					echo json_encode($errors);
					exit;
				 }
				}else{
					
					$this->session->set_flashdata("SuccessMessage", 'Initial Stock has been updated successfully.');
					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						$errors['done']='success';
						$errors['MSG']='Initial Stock has been updated successfully.';
						echo json_encode($errors);
						exit;
					 }
				
				}
				// }
				// else{
					// echo json_encode(array('done'=>'success','set_submit'=>'1'));
					// exit;
				// }
			
			
			}
		}
		}
		$data=array();
		$data['page_id'] = '18';
		$table_name_initial='inventory_'.$office_operation_type.'_initial_stock_'.$office_id;
		$data['initial_stock_product_master']=$this->inventory_model->_get_all_record_of_inventory_initial_stock_product_by_join($table_name_initial,$office_id,$office_operation_type,$this->session->userdata('user_id'),'show');
		$header['title'] = "Initial Stock";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/initial_stock_form',$data);
		$this->load->view('includes/_footer');
    }
	
	public function AjaxInitialStockForm(){
	    $data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$postedArr=$this->security->xss_clean($_POST);
		$initial_stock_id=$postedArr['initial_stock_id'];	
		$product_id=$postedArr['product_id'];
		$initial_stock_serial_number=$postedArr['initial_stock_serial_number'];
		$table_name_initial='inventory_'.$office_operation_type.'_initial_stock_'.$office_id;
		$table_name_initial_product_serial='inventory_'.$office_operation_type.'_initial_stock_product_serial_number_'.$office_id;
		$data['inventory_initial_stock_srno']=$this->inventory_model->_get_all_record_of_inventory_initial_stock_product_by_join($table_name_initial,$office_id,$office_operation_type,$this->session->userdata('user_id'),'edit',$initial_stock_id,$product_id,$initial_stock_serial_number,$table_name_initial_product_serial);
        $data['pageName']="inventory_initial_stock_serial_no_form";
        echo json_encode($data);		
		
	}
	
	public function product_stock_receipt_inventory(){
	    $data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d',strtotime("-1 months")).' 00:00:00';
		}
		if(isset($postedArr['right_to']))
		{
			$tdate = explode('/',$postedArr['right_to']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		$tableNamePRODUCTSTOCKRECEIPT='inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id;
		$data['product_stock_receipt_details']=$this->inventory_model->_get_all_record_product_stock_transfer_details_to_by_join($tableNamePRODUCTSTOCKRECEIPT,$office_operation_type,$office_id,$fromDate,$toDate);
		$data['product_master'] = $this->db->get('product_master')->result();
		if($office_operation_type == "store"){
			$page_id = "20";
		}
		else if($office_operation_type == "showroom"){
			redirect(base_url('user/dashboard'));
		}
		$page_access = $this->role_model->get_page_permission($page_id);
		$view_value=$page_access->view_value;
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
		//echo count($page_access); die;
		if(count($page_access) > 0){
		$data['page_id'] = "20";
		$header['title'] = "Stock Receipt From Vendor Inventory";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/product_stock_receipt_inventory',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function product_stock_receipt_form(){

		$product_stock_receipt_id = ($this->input->get('product_stock_receipt_id')) ? base64_decode($this->input->get('product_stock_receipt_id')) : '';
 
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
	//	$user_access_type = $this->session->userdata('user_access_type');
		if(isset($_POST['product_stock_receipt_id']) && $_POST['product_stock_receipt_id'] !='')
		{
			$inventory_product_stock_receipt='inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id;
			$checkData = $this->db->get_where($inventory_product_stock_receipt,array('product_stock_receipt_id'=> $_POST['product_stock_receipt_id']))->row();
			if($checkData->access_level_status =='1')
			{
				redirect(base_url('inventory/product_stock_receipt_inventory'));
			}
		}
		if($_POST['my_page_type'] == "addData")
		{
			$user_access_type = 'Maker';
		}
		else if( $_POST['my_page_type'] == "editData" && $_POST['product_stock_receipt_id'] !='')
		{
			$user_access_type = 'Maker';
		}
		else if($_POST['my_page_type'] == "authorizeData"){
			$user_access_type = "Authorizer";
		}
//	print_r($_POST); echo "<br/>".$user_access_type; die;
			//print_r($user_access_type);die;
		if($office_operation_type == "store"){
			$page_id = '20';
		}
		else if($office_operation_type == "showroom"){
			redirect(base_url('user/dashboard'));
		}		
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		if($view_value==0)
		{
			
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
//		print_r($_POST); die;
		if($add_value=='1' || $edit_value == '2')
		{
		if($_POST){
			$errors=array();
			if($user_access_type =='Maker'){
			    
				if(empty($_POST['product_stock_receipt_date'])) {
				$errors['product_stock_receipt_date'] = 'Date and time of Receipt is required';
				}
				
				if(empty($_POST['product_stock_receipt_number'])) {
				$errors['product_stock_receipt_number'] = 'Receipt # is required';
				}else{
				if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i', $_POST['product_stock_receipt_number'])) {
					$errors['product_stock_receipt_number'] = 'Receipt # accept only letter or number';
				}
				}
				
				if(empty($_POST['product_stock_receipt_work_order_no'])) {
				$errors['product_stock_receipt_work_order_no'] = 'Work order # is required';
				}else{
				if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i', $_POST['product_stock_receipt_work_order_no'])) {
					$errors['product_stock_receipt_work_order_no'] = 'Work order # accept only letter or number';
				}
				}
				
				
				if(empty($_POST['vendor_id'])) {
				$errors['vendor_id_chosen'] = 'Name of Vendor is required';
				}
				
				$first_stockProductId = (isset($_POST['stock_product_id'])) ? $_POST['stock_product_id'] : '';
				
				foreach($_POST['product_id'] as $key=>$value)
				{
					$$serialStockArray = array();
					if($first_stockProductId !=''){
						$inventory_stock_product_receipt='inventory_'.$office_operation_type.'_product_stock_receipt_product_'.$office_id;
						$table_product_stock_reciept_serial='inventory_'.$office_operation_type.'_product_stock_receipt_p_s_n_'.$office_id;
						$stock_product_id = $first_stockProductId[$key];
						$stockProductData = $this->db->get_where($inventory_stock_product_receipt,array('stock_product_id'=>$stock_product_id,'product_id'=>$value))->row();
						$serialStockArray = $this->db->get_where($table_product_stock_reciept_serial,array('stock_product_id'=>$stock_product_id))->result();
						foreach($serialStockArray as $serial){
							$this->base_model->delete_record_by_id('serial_number_master', array('product_id'=>$value,'serial_number'=>$serial->stock_product_serial_number));
						}
					}
					//echo $this->db->last_query(); die;
					  if(empty($value)){
						$errors['product_id'.$key.'_chosen'] = 'Product Name is required';				
					  }
					  if(empty($_POST['stock_product_quantity'][$key])){
						$errors['stock_product_quantity'.$key] = 'Quantity Ordered is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_product_quantity'][$key])) {
							$errors['stock_product_quantity'.$key] = 'Quantity Ordered accept only number';
						}
						}
					  
					  if(empty($_POST['stock_product_qty_received'][$key])){
						$errors['stock_product_qty_received'.$key] = 'Qty Received is required with serial number.';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_product_qty_received'][$key])) {
							$errors['stock_product_qty_received'.$key] = 'Qty Received accept only number';
						}
						}
						
						if(!empty($_POST['stock_product_quantity'][$key]) && !empty($_POST['stock_product_qty_received'][$key])){
							
							if($_POST['stock_product_qty_received'][$key]>$_POST['stock_product_quantity'][$key]){
							  $errors['stock_product_qty_received'.$key] = 'Qty Received should not be greater than Qty Ordered';		
							}
						
						}
						/* 
					   if(empty($_POST['stock_product_weight'][$key])){
						$errors['stock_product_weight'.$key] = 'Weight(in gm) is required';				
					  }else{
						if(!preg_match('/^[0-9\.]+$/i', $_POST['stock_product_weight'][$key])) {
							$errors['stock_product_weight'.$key] = 'Weight(in gm) accept only number';
						}
						} */
						$div_Number = $_POST['div_number'][$key];
						$serialNumber_Array = array();
						$serialNumber_Array = $_POST['pop_up_serial_number'.$div_Number];
						if(!empty($serialNumber_Array)){
							
						$stock_product_qty_received=$_POST['stock_product_qty_received'][$key];
						if($stock_product_qty_received != count(array_unique($serialNumber_Array)))
						{
							$errors['heading'.$key] = 'Serial numbers are not unique.';
							$ar =  array_diff_key($serialNumber_Array,array_unique($serialNumber_Array));
							foreach($ar as $k=>$v){
							$myval = $k;
							$errors['initial_stock_starting_serial_popup'.$div_Number.'_'.$k] = "Serial number is duplicate.";
							}
						}
						else
						{
							$arr_serial_count = $this->db->select('*')->where(array('product_id'=>$value))->where_in('serial_number',$serialNumber_Array)->get('serial_number_master');
							$serialMasterArray = $this->db->select('*')->where(array('product_id'=>$value))->get('serial_number_master')->result();
							$serial_master_array = array();
							foreach($serialMasterArray as $serial){
								array_push($serial_master_array,$serial->serial_number);
							}
							if($arr_serial_count->num_rows() > 0){
								$errors['heading'.$key] = 'Some serial number are already exist.';
							}
							$ar =  array_intersect($serialNumber_Array,$serial_master_array);
							foreach($ar as $k=>$v){
							$myval = $k;
							$errors['initial_stock_starting_serial_popup'.$div_Number.'_'.$k] = "Serial number is already exist.";
							}
						}
						}
					/* 	
					div_number heading
						$stock_product_qty_received=$_POST['stock_product_qty_received'][$key];
			//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
						$serial_number=explode(",",$_POST['pop_up_serial_number'][$key]);
						if($stock_product_qty_received!=count($serial_number))
						{
						$errors['stock_product_qty_received'.$key] = 'Please enter '.$stock_product_qty_received.' serial number';
						}
						 */
					  /*
					  if(empty($_POST['stock_product_serial_number'][$key])){
						$errors['stock_product_serial_number'.$key] = 'Serial No. is required';					
					  }else{
						if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i',$_POST['stock_product_serial_number'][$key])) {
							$errors['stock_product_serial_number'.$key] = 'Serial No. accept only combination of (letter or number)';
						}
						}
						*/
					  /* 
					  if(empty($_POST['stock_product_net'][$key])){
						$errors['stock_product_net'.$key] = 'Net stock is required';				
					  }else{
						if(!preg_match('/^[0-9\.]+$/i', $_POST['stock_product_net'][$key])) {
							$errors['stock_product_net'.$key] = 'Net stock accept only number';
						}
						} */
					  
					  
					  /* if(empty($_POST['stock_product_remarks'][$key])){
						$errors['stock_product_remarks'.$key] = 'Remarks is required';					
					  } */
					  
					if(!empty($serialStockArray)){
						foreach($serialStockArray as $serial){
							$this->base_model->insert_one_row('serial_number_master', array('product_id'=>$value,'serial_number'=>$serial->stock_product_serial_number,'createdOn'=>date('Y-m-d H:i:s')));
						}
					}
					  
				}
				
				if($_POST['product_stock_receipt_work_order_status']=='close'){
				if(empty($_POST['reason_for_closing_workorder'])) {
				$errors['reason_for_closing_workorder'] = 'Reason for closing work order is required';
				}
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
				else{
				if($_POST['is_submit']=='1')
				{
				$postedArr=$this->security->xss_clean($_POST);
			//	$p_s_receipt_date=strtotime(str_replace("/","-",$postedArr['product_stock_receipt_date']));
				//$product_stock_receipt_date= $postedArr['product_stock_receipt_date'];
                $product_stock_receipt_date=$postedArr['product_stock_receipt_date'];
				$product_stock_receipt_number= $postedArr['product_stock_receipt_number'];
				$productStockReceiptId = ($postedArr['product_stock_receipt_id']) ? $postedArr['product_stock_receipt_id'] : '';
				$product_stock_receipt_work_order_no=$postedArr['product_stock_receipt_work_order_no'];
				$product_stock_receipt_work_order_status=$postedArr['product_stock_receipt_work_order_status'];
				$reason_for_closing_workorder=$postedArr['reason_for_closing_workorder'];
				$vendor_id=$postedArr['vendor_id'];
				$product_id=$postedArr['product_id'];
				$stock_product_quantity=$postedArr['stock_product_quantity'];
				$stock_product_weight=$postedArr['stock_product_weight'];
				$stock_product_qty_received=$postedArr['stock_product_qty_received'];
			//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
			//	$serial_number=$postedArr['pop_up_serial_number'];
				$stock_product_net=$postedArr['stock_product_net'];
				$stock_product_remarks=$postedArr['stock_product_remarks'];
				$access_level = (isset($postedArr['access_level'])) ? $postedArr['access_level'] : '0';
				//echo '<pre>'; print_r($postedArr); die;
				$createdOn=date('Y-m-d H:i:s');
				
				$inventory_product_stock_receipt='inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id;
				
				$product_stock_receipt_id = $productStockReceiptId;
				$inventory_stock_product_receipt='inventory_'.$office_operation_type.'_product_stock_receipt_product_'.$office_id;
				$table_product_stock_reciept_serial='inventory_'.$office_operation_type.'_product_stock_receipt_p_s_n_'.$office_id;
				
				if($user_access_type == "Maker"){
				if($productStockReceiptId == ''){ 
				$newData=array('vendor_id'=>$vendor_id,'product_stock_receipt_date'=>$product_stock_receipt_date,'product_stock_receipt_number'=>$product_stock_receipt_number,'product_stock_receipt_work_order_no'=>$product_stock_receipt_work_order_no,'product_stock_receipt_work_order_status'=>$product_stock_receipt_work_order_status,'reason_for_closing_workorder'=>$reason_for_closing_workorder,'added_by'=>$this->session->userdata('user_id'),'added_date'=>date('Y-m-d H:i:s'),'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				
				$this->base_model->insert_one_row($inventory_product_stock_receipt,$newData);
				
				$product_stock_receipt_id=$this->base_model->get_last_insert_id();
				}
				else{
					// $updateData = array('added_by'=>$this->session->userdata('user_id'),'added_date'=>date('Y-m-d H:i:s'));
					// $this->base_model->update_record_by_id($inventory_product_stock_receipt,$updateData,array('product_stock_receipt_id'=>$productStockReceiptId));
				}
				
				if(!empty($product_id)){
					$stockProductIds = $_POST['stock_product_id'];
					foreach($product_id as $key=>$value)
					  {
						$div_Number = $_POST['div_number'][$key];
						
						  $serialNumberArray = array();
						  $serialNumberArray = $_POST['pop_up_serial_number'.$div_Number];
						if(!empty($value) && $value!=''){
							$this->base_model->set_initial_product($value);
							if($stockProductIds[$key]!= ''){
								
								$stock_product_id = $stockProductIds[$key];
								$i_p_stock_updateArray = array('stock_product_quantity'=>$stock_product_quantity[$key],'stock_product_weight'=>$stock_product_weight[$key],'stock_product_qty_received'=>$stock_product_qty_received[$key],'stock_product_serial_number'=>'','stock_product_net'=>$currentStock + ($stock_product_qty_received[$key])  ,'stock_product_remarks'=>$stock_product_remarks[$key]);
								
								$this->base_model->update_record_by_id($inventory_stock_product_receipt,$i_p_stock_updateArray,array('stock_product_id'=>$stock_product_id));
							}
							else{
								$i_p_stock_receipt_product=array('product_stock_receipt_id'=>$product_stock_receipt_id,'product_id'=>$value,'stock_product_quantity'=>$stock_product_quantity[$key],'stock_product_weight'=>$stock_product_weight[$key],'stock_product_qty_received'=>$stock_product_qty_received[$key],'stock_product_serial_number'=>'','stock_product_net'=>$currentStock + ($stock_product_qty_received[$key]),'stock_product_remarks'=>$stock_product_remarks[$key],'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
							
							
							
								$this->base_model->insert_one_row($inventory_stock_product_receipt,$i_p_stock_receipt_product);
						 
								$stock_product_id=$this->db->insert_id();
							}
						
							$this->db->where(array('stock_product_id'=>$stock_product_id));
							$this->db->delete($table_product_stock_reciept_serial);
					
							$insertSerialData2=array();
							foreach($serialNumberArray as $serialNumber){
								$insertSerialData2[] = array('product_stock_receipt_id'=>$product_stock_receipt_id,'stock_product_id'=>$stock_product_id,'stock_product_serial_number'=>$serialNumber,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
								$seriaMasterData = $this->db->get_where('serial_number_master', array('product_id'=>$value,'serial_number'=>$serialNumber));
								if($seriaMasterData->num_rows() > 0 )
								{
									
								//	$this->base_model->update_record_by_id('serial_number_master',array('createdOn'=>date('Y-m-d H:i:s')),array('product_id'=>$value,'serial_number'=>$serialNumber));
								}
								else
								{
									$this->base_model->insert_one_row('serial_number_master', array('product_id'=>$value,'serial_number'=>$serialNumber,'createdOn'=>date('Y-m-d H:i:s')));
								}
							}
							$this->base_model->insert_multiple_row($table_product_stock_reciept_serial,$insertSerialData2);

							/* if(!empty($serial_number[$key])){
								$serialNumberArray = explode(',',$serial_number[$key]);
								$insertSerialData2=array();
								foreach($serialNumberArray as $serialNumber){
									$insertSerialData2[] = array('product_stock_receipt_id'=>$product_stock_receipt_id,'stock_product_id'=>$stock_product_id,'stock_product_serial_number'=>$serialNumber,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
								}
								$this->base_model->insert_multiple_row($table_product_stock_reciept_serial,$insertSerialData2);
							} */
					
						}					
					  }
					
					}
					
					if($_POST['my_page_type'] == "addData")
					{
						
							$action = "Add";
							$activity = "Product stock receipt from vendor (".$product_stock_receipt_number.") has been added.";
							$page_name = "initial_stock";
							$this->base_model->insertActivity($action,$activity,$page_name);
							
						$this->session->set_flashdata("SuccessMessage", 'Product Stock Receipt been added successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							
							$errors['done']='success';
							$errors['MSG']='Product Stock Receipt has been added successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							exit;
						 }
					}
					else if( $_POST['my_page_type'] == "editData")
					{
						$action = "Update";
							$activity = "Product stock receipt from vendor (".$product_stock_receipt_number.") has been updated.";
							$page_name = "initial_stock";
							$this->base_model->insertActivity($action,$activity,$page_name);
							
						$this->session->set_flashdata("SuccessMessage", 'Product Stock Receipt been added successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							
							
							$errors['done']='success';
							$errors['MSG']='Product Stock Receipt has been added successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							exit;
						 }
					}
					
				}
				
				if($user_access_type == "Authorizer" && $access_level == '1'){
					
					$where_stockReceipt = array('product_stock_receipt_id'=>$productStockReceiptId);
					
					$stockReceiptProductData = $this->db->get_where($inventory_stock_product_receipt,$where_stockReceipt)->result();
					

					// echo '<pre>';
					// print_r($_POST);
					// print_r($stockReceiptProductData);
					// echo '</pre>';
					// die;
					$this->base_model->update_record_by_id($inventory_product_stock_receipt,array('access_level_status'=>$access_level,'product_stock_receipt_work_order_status'=>$product_stock_receipt_work_order_status,'reason_for_closing_workorder'=>$reason_for_closing_workorder,'authorized_by'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s')),$where_stockReceipt);
					
					
					$tableNameSTOCKRECEIPTproductCurrentStock='product_current_stock_'.$office_id;
					$currentStockSerialNumber = 'product_current_stock_serial_number_'.$office_id;
					
					
			
					foreach($stockReceiptProductData as $key=>$receiptProductData){
						$value = $receiptProductData->product_id;
						$stock_product_id = $receiptProductData->stock_product_id;
						$stock_product_qty_received = 0;
						$stock_product_qty_received = $receiptProductData->stock_product_qty_received;
						
						$serialNumberData = $this->db->get_where($table_product_stock_reciept_serial,array('stock_product_id'=>$stock_product_id))->result();
						
						$serialNumberArray = array();
						$insertSerialData = array();
						foreach($serialNumberData as $serialData){
							$serialNumberArray[] = $serialData->stock_product_serial_number;
						}
						
						$current_stock_check=$this->base_model->get_record_by_id($tableNameSTOCKRECEIPTproductCurrentStock,array('product_id'=>$value));
						$currentStock = (isset($current_stock_check->product_current_stock)) ? $current_stock_check->product_current_stock : '0';
		
						if(empty($current_stock_check)){
							
							$product_current_stock=array('product_id'=>$value,'product_current_stock'=>$stock_product_qty_received,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn,'current_stock_status'=>'0');
							
							$this->base_model->insert_one_row($tableNameSTOCKRECEIPTproductCurrentStock,$product_current_stock);
							
							$current_stock_id = $this->db->insert_id();
						
							foreach($serialNumberArray as $serialNumber){
								$insertSerialData[] = array('current_stock_id'=>$current_stock_id,'product_id'=>$value,'product_serial_number'=>$serialNumber,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
							}
							$this->base_model->insert_multiple_row($currentStockSerialNumber,$insertSerialData);
						
							$history_table = 'inventory_office_history_'.$office_id;
							$historyData = array('current_stock'=>'0','product_id'=>$value,'received_stock'=>$stock_product_qty_received,'net_stock'=>$stock_product_qty_received,'type_value'=>'vendor','received_from'=>$vendor_id,'authorized_by'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s'),'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
							$this->base_model->insert_one_row($history_table,$historyData);
							
						}
						else{
							$new_stock=($current_stock_check->product_current_stock)+($stock_product_qty_received);
							$product_current_stock=array('product_current_stock'=>$new_stock,'current_stock_status'=>'0');
							
							$this->base_model->update_record_by_id($tableNameSTOCKRECEIPTproductCurrentStock,$product_current_stock,array('product_id'=>$value));
							
							$current_stock_id = $current_stock_check->current_stock_id;
							
							foreach($serialNumberArray as $serialNumber){
								$insertSerialData[] = array('current_stock_id'=>$current_stock_id,'product_id'=>$value,'product_serial_number'=>$serialNumber,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
							}
							
							$this->base_model->insert_multiple_row($currentStockSerialNumber,$insertSerialData);
							$history_table = 'inventory_office_history_'.$office_id;
							$this->db->select('*');
							$this->db->order_by('history_id','desc')->limit('1');
							$arr_his_data=$this->db->get_where($history_table,array('product_id'=>$value))->row();
							
							$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value,'received_stock'=>$stock_product_qty_received,'net_stock'=>($arr_his_data->net_stock+$stock_product_qty_received),'type_value'=>'vendor','authorized_by'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s'),'received_from'=>$vendor_id,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
							$this->base_model->insert_one_row($history_table,$historyData);
						}
					
					}
					$this->session->set_flashdata("SuccessMessage", 'Product Stock Receipt been authorized successfully.');
					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						$errors['done']='success';
						$errors['MSG']='Product Stock Receipt has been added successfully.';
						echo json_encode($errors);
						//redirect('masterForm/raw_material_master');
						exit;
					}
				}
				

				}
				else{
					echo json_encode(array('done'=>'success','set_submit'=>'1'));
					exit;
				}
			}	
		}
		
		
		$data=array();
		
		$inventory_product_stock_receipt='inventory_'.$office_operation_type.'_product_stock_receipt_product_'.$office_id;
		
		$data['summary_of_product_received']=$this->inventory_model->_get_all_record_of_inventory_product_stock_receipt_by($inventory_product_stock_receipt);
		
		$data['vendor_master']=$this->inventory_model->getAllVendorsWithStore($office_id);
		$data['product_master']=$this->base_model->get_all_records('product_master');
		
		$product_stock_receipt_id = ($this->input->get('product_stock_receipt_id')) ? base64_decode($this->input->get('product_stock_receipt_id')) : '';
		$data['view_page']='0';
		if($this->input->get('product_stock_receipt_id')!='')
		{
		
		$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_product_receipt_by_stock_transfer_id($product_stock_receipt_id);
		$data['view_page']='1';
		}
		// echo '<pre>';
		// print_r($data);
		
		$header['title'] = "Stock Receipt From Vendor Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/product_stock_receipt_form',$data);
		$this->load->view('includes/_footer');
		
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
	}
	
	public function product_stock_receipt_form_view(){
		
		// echo '<pre>';
// print_r($_POST);
 // die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		if($office_operation_type == "store"){
			$page_id = '20';
		}
		else if($office_operation_type == "showroom"){
			redirect(base_url('user/dashboard'));
		}		
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
		if($add_value=='1' || $edit_value == '2')
		{
		$office_id=$this->session->userdata('office_id');
		//$user_access_type = $this->session->userdata('user_access_type');
		
		$data=array();
		$inventory_stock_product_receipt='inventory_'.$office_operation_type.'_stock_product_receipt_'.$office_id;
		$data['summary_of_product_received']=$this->inventory_model->_get_all_record_of_inventory_product_stock_receipt_by($inventory_stock_product_receipt,$office_id,$office_operation_type,$this->session->userdata('user_id'),'show');
		$data['vendor_master']=$this->base_model->get_all_records('vendor_master');
		$data['product_master']=$this->base_model->get_all_records('product_master');
		
		$product_stock_receipt_id = ($this->input->get('product_stock_receipt_id')) ? base64_decode($this->input->get('product_stock_receipt_id')) : '';
		$checkData = $this->base_model->get_record_by_id('inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id,array('product_stock_receipt_id'=>$product_stock_receipt_id));
		if(!empty($checkData)){
		$data['view_page']='';
		if($this->input->get('product_stock_receipt_id')!='')
		{
		
		$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_product_receipt_by_stock_transfer_id($product_stock_receipt_id);
		}
		// echo '<pre>';
		// print_r($data); die;
		$header['title'] = "Stock Receipt From Vendor View";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/product_stock_receipt_form_view',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
	}
	
	public function product_stock_receipt_form_authorizer(){
		
		// echo '<pre>';
// print_r($_POST);
 // die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
	//	$user_access_type = $this->session->userdata('user_access_type');
		$data=array();
		$inventory_stock_product_receipt='inventory_'.$office_operation_type.'_stock_product_receipt_'.$office_id;
		$data['summary_of_product_received']=$this->inventory_model->_get_all_record_of_inventory_product_stock_receipt_by($inventory_stock_product_receipt,$office_id,$office_operation_type,$this->session->userdata('user_id'),'show');
		$data['vendor_master']=$this->base_model->get_all_records('vendor_master');
		$data['product_master']=$this->base_model->get_all_records('product_master');
		
		$product_stock_receipt_id = ($this->input->get('product_stock_receipt_id')) ? base64_decode($this->input->get('product_stock_receipt_id')) : '';
		$checkData = $this->base_model->get_record_by_id('inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id,array('product_stock_receipt_id'=>$product_stock_receipt_id));
		
		if(!empty($checkData) && $checkData->added_by != $this->session->userdata('user_id')){
		$data['view_page']='';
		if($this->input->get('product_stock_receipt_id')!='')
		{
		
		$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_product_receipt_by_stock_transfer_id($product_stock_receipt_id);
		}
		// echo '<pre>';
		// print_r($data); die;
		$header['title'] = "Stock Receipt From Vendor Authorizer";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/product_stock_receipt_form_authorizer',$data);
		$this->load->view('includes/_footer');
		}
		else
		{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
	}
	
	public function AjaxStockInitial(){
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$id=$postedArr['product_id'];
		$page_type=$postedArr['pageName'];
		switch($page_type){
			
		case 'product_stock_receipt_initial_form':
		      if(empty($office_id) && $office_id==0){
					
					$table_name='product_current_stock';
					
				}else{
					
					$table_name='product_current_stock_'.$office_id;
				}
		      $product_current_stock=$this->base_model->get_record_by_id($table_name,array('product_id'=>$id));
			  $initialStock = isset($product_current_stock->product_current_stock) ? $product_current_stock->product_current_stock : '0';
			  $productData = $this->base_model->get_record_by_id('product_master',array('product_id'=>$id));
			  $weightPerItem = isset($productData->product_weight) ? $productData->product_weight : '0';
			  $jsonArray=array('initial_stock_quantity'=>$initialStock,'weightPerItem'=>$weightPerItem);
				/* $inventory_stock_product_receipt=$this->base_model->get_all_record_by_id('inventory_stock_product_receipt',array('product_id'=>$id));
				if(empty($inventory_stock_product_receipt)){
				$inventory_initial_stock=$this->base_model->get_record_by_id('inventory_initial_stock',array('product_id'=>$id));
				$jsonArray=array('initial_stock_quantity'=>$inventory_initial_stock->initial_stock_quantity);
				}else{
				$stock_product_net=0;
				$row=$this->db->query('SELECT stock_product_net FROM inventory_stock_product_receipt where product_id="'.$id.'"')->result();
				foreach($row as $Row){
					if(count($Row->stock_product_net) > 0){
						$stock_product_net=$Row->stock_product_net; 
					}
				}
				$jsonArray=array('initial_stock_quantity'=>$stock_product_net);	
				} */
			echo json_encode($jsonArray);
			
			break;
	}
	}
	
	public function stock_transfer_inventory(){
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		// $toDate = date('Y-m-d',strtotime('now'));
		// $fromDate = date('Y-m-d',strtotime("-1 months"));
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d',strtotime("-1 months")).' 00:00:00';
		}
		if(isset($postedArr['right_to']))
		{
			$tdate = explode('/',$postedArr['right_to']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		if($office_operation_type == "store"){
			$data['page_id'] = "21";
			$page_id = "21";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "25";
			$page_id = "25";
		}	
		/* if(empty($office_id) && $office_id==0){

		  $table_name='inventory_stock_transfer';
		  $table_name='product_current_stock';
				
		}else{ */
		 // $tableNamePRODUCTSTOCKRECEIPT='inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id;	
		 // $tableNamePRODUCTSTOCKRECEIPT='inventory_store_product_stock_receipt_'.$office_id;	
		  /* $table_name='product_current_stock_'.$office_id; */
		/* } */
      /*  $data['product_stock_receipt_details']=$this->inventory_model->_get_all_record_product_stock_transfer_details_to_by_join($tableNamePRODUCTSTOCKRECEIPT,$office_operation_type,$office_id);*/
	  
	    $tableNameSTOCKRECEIPT='inventory_'.$office_operation_type.'_stock_transfer_'.$office_id;	
		 $data['stock_receipt_details']=$this->inventory_model->_get_all_record_stock_transfer_details_to_by_join($tableNameSTOCKRECEIPT,$office_operation_type,$office_id,$fromDate,$toDate);
	   //print_r($data['stock_receipt_details']);die;
	   $page_access = $this->role_model->get_page_permission($page_id);
	   $view_value=$page_access->view_value;
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view .");
			redirect(base_url('user/dashboard'));
		}
		if(count($page_access) > 0){
		$header['title'] = "Stock Transfer Inventory";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_transfer_inventory',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function stock_transfer_form(){
		//print_r($_POST); die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$user_access_type = $this->session->userdata('user_access_type');

		if($_POST['my_page_type'] == "addData")
		{
			$user_access_type = 'Maker';
		}
		else if( $_POST['my_page_type'] == "editData" && $_POST['stock_transfer_id'] !='')
		{
			$user_access_type = 'Maker';
		}
		else if($_POST['my_page_type'] == "authorizeData"){
			$user_access_type = "Authorizer";
		}
		
		if(isset($_POST['stock_transfer_id']) && $_POST['stock_transfer_id'] !='')
		{
			$inventory_stock_transfer_table ='inventory_'.$office_operation_type.'_stock_transfer_'.$office_id;
			$checkData = $this->db->get_where($inventory_stock_transfer_table,array('stock_transfer_id'=> $_POST['stock_transfer_id']))->row();
			if($checkData->access_level_status =='1')
			{
				redirect(base_url('inventory/stock_transfer_inventory'));
			}
		}
		
		if($office_operation_type == "store"){
			$data['page_id'] = "21";
			$page_id = "21";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "25";
			$page_id = "25";
		}
		
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		if($add_value == "1" || $edit_value == "2")
		{
		if($_POST){
		
			     $errors=array();
				if($user_access_type == "Maker"){
					if(empty($_POST['stock_transfer_date'])) {
					$errors['stock_transfer_date'] = 'Date of Transfer is required';
					}
					
					//if(empty($_POST['stock_transfer_number'])) {
					//$errors['stock_transfer_number'] = 'Stock Transfer Number is required';
					//}
					
					if(empty($_POST['stock_transfer_to_office_id'])) {
					$errors['stock_transfer_to_office_id_chosen'] = 'Transfer To is required';
					}
					
					//if(empty($_POST['stock_transfer_narration'])) {
					//$errors['stock_transfer_narration'] = 'Narration is required';
					//}
					
					if(empty($_POST['stock_transfer_mode'])) {
					$errors['stock_transfer_mode_chosen'] = 'Mode To is required';
					}
					
					/* if(empty($_POST['stock_transferStatus'])) {
					$errors['stock_transferStatus_chosen'] = 'Status To is required';
					} */
					
					foreach($_POST['product_id'] as $key=>$value)
					{
					  if(empty($value)){
						$errors['product_id'.$key.'_chosen'] = 'Product Name is required';				
					  }
					 /*  
					  if(empty($_POST['stock_transfer_product_serial_number'][$key])){
						$errors['stock_transfer_product_serial_number'.$key] = 'Serial No. is required';				
					  }else{
						if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i',$_POST['stock_transfer_product_serial_number'][$key])) {
							$errors['stock_transfer_product_serial_number'.$key] = 'Serial No. accept only combination of (letter or number)';
						}
						} */
					  
					  //if(empty($_POST['stock_transfer_product_weight'][$key])){
						//$errors['stock_transfer_product_weight'.$key] = 'Weight(in gm) is required';				
					  //}else{
						//if(!preg_match('/^[0-9\.]+$/i', $_POST['stock_transfer_product_weight'][$key])) {
							//$errors['stock_transfer_product_weight'.$key] = 'Weight(in gm) accept only number';
						//}
						//}
					  
					  if(empty($_POST['stock_transfer_product_quantity'][$key])){
						$errors['stock_transfer_product_quantity'.$key] = 'Quantity is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_transfer_product_quantity'][$key])) {
							$errors['stock_transfer_product_quantity'.$key] = 'Quantity accept only number';
						}
						}
						  
						 $stock_transfer_product_quantity=$_POST['stock_transfer_product_quantity'][$key];
			//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
						$serial_number=$_POST['stock_transfer_product_serial_number_'.$key];
						
						if($stock_transfer_product_quantity!=count($serial_number))
						{
						$errors['stock_transfer_product_serial_number'.$key.'_chosen'] = 'Please select '.$stock_transfer_product_quantity.' serial numbers';
						} 
						  /* if(empty($_POST['stock_transfer_product_remarks'][$key])){
                            $errors['stock_transfer_product_remarks'.$key] = 'Remarks is required';				
						  } */
						 
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
				else{
				if($_POST['is_submit']=='1')
				{
				$office_operation_type=$this->session->userdata('office_operation_type');
		        $office_id=$this->session->userdata('office_id');
				$postedArr=$this->security->xss_clean($_POST);
				//$s_trans_date=strtotime($postedArr['stock_transfer_date']);
				$stock_transfer_date=$postedArr['stock_transfer_date'];
				//$stock_transfer_date= date("m/d/Y",$s_trans_date);
				$stock_transfer_number= $postedArr['stock_transfer_number'];
				$stock_transfer_id= (isset($postedArr['stock_transfer_id'])) ? $postedArr['stock_transfer_id'] : '';
				$stock_transfer_to_office_id=$postedArr['stock_transfer_to_office_id'];
				$stock_transfer_narration=$postedArr['stock_transfer_narration'];
				$stock_transfer_mode=$postedArr['stock_transfer_mode'];
				$stock_transfer_mode_number=$postedArr['stock_transfer_mode_number'];
				$stock_transferStatus=(empty($postedArr['stock_transferStatus']))?'':$postedArr['stock_transferStatus'];
				$product_id=$postedArr['product_id'];
			//	$stock_transfer_product_serial_number=$postedArr['stock_transfer_product_serial_number'];
				$stock_transfer_product_weight=$postedArr['stock_transfer_product_weight'];
				$stock_transfer_product_quantity=$postedArr['stock_transfer_product_quantity'];
				$stock_transfer_product_remarks=$postedArr['stock_transfer_product_remarks'];
				$access_level = (isset($postedArr['access_level'])) ? $postedArr['access_level'] : '0';
				$createdOn=date('Y-m-d H:i:s');
				$office_name=(isset($office_id) && $office_id==0)?'Head Office':$this->session->userdata('office_name');
				
				$table_inventory_stock_transfer_product='inventory_'.$office_operation_type.'_stock_transfer_product_'.$office_id;
						
				$table_inventory_stock_receipt_product='inventory_'.getOfficeOperationType($stock_transfer_to_office_id).'_stock_receipt_product_'.$stock_transfer_to_office_id;
				
				$table_name_current_stock='product_current_stock_'.$office_id;
				
				$table_inventory_stock_transfer_product_serial='inventory_'.$office_operation_type.'_stock_transfer_product_serial_number_'.$office_id;
					
				$table_inventory_stock_receipt_product_serial='inventory_'.getOfficeOperationType($stock_transfer_to_office_id).'_stock_receipt_product_serial_number_'.$stock_transfer_to_office_id;
					
				if(empty($stock_transfer_number))
				{
					$myTranferTableNameNew = 'inventory_'.$office_operation_type.'_stock_transfer_'.$office_id;
					$financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
					$autoGenerateNumber = 1;
					$financialSecondYear = $financialFirstYear+1;
					$financialYear = $financialFirstYear.'-'.$financialSecondYear;
					$myoffficeDataNew = $this->db->get_where('office_master',array("office_id"=>$office_id))->row();
					// $getTableNameData = $this->db->select_max('stock_transfer_id')->from($tableName)->get()->row();
					
					$getTableNameData = $this->db->select('stock_transfer_id')->from($myTranferTableNameNew)->like('stock_transfer_number',"$financialYear","before")->get();
					
					if(!empty($getTableNameData)){
						$autoGenerateNumber = $getTableNameData->num_rows() + 1;
					}
					$autoGenerateNumber = str_pad($autoGenerateNumber,6,"0", STR_PAD_LEFT);
					$stock_transfer_number = strtoupper($myoffficeDataNew->office_short_code)."/STI/".$autoGenerateNumber."/".$financialYear;
				}
				
				if($user_access_type == "Maker"){
				if($stock_transfer_id == ''){
					$newData=array('stock_transfer_date'=>$stock_transfer_date,'stock_transfer_number'=>$stock_transfer_number,'stock_transfer_to_office_id'=>$stock_transfer_to_office_id,'stock_transfer_from'=>$office_name,'stock_transfer_narration'=>$stock_transfer_narration,'stock_transfer_mode'=>$stock_transfer_mode,'stock_transfer_mode_number'=>$stock_transfer_mode_number,'added_by'=>$this->session->userdata('user_id'),'added_date'=>date('Y-m-d H:i:s'),'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
					$this->base_model->insert_one_row('inventory_'.$office_operation_type.'_stock_transfer_'.$office_id,$newData);
					$stock_transfer_id = $this->base_model->get_last_insert_id();
				}
				else{
					$this->base_model->update_record_by_id('inventory_'.$office_operation_type.'_stock_transfer_'.$office_id,array('stock_transfer_narration'=>$stock_transfer_narration,'stock_transfer_mode'=>$stock_transfer_mode,'stock_transfer_mode_number'=>$stock_transfer_mode_number),array('stock_transfer_id'=>$stock_transfer_id));
					
				}
				
				if(!empty($product_id)){
					$stockTransferProductIds = (isset($_POST['stock_transferProduct_id'])) ? $_POST['stock_transferProduct_id'] : '';
					
						
					foreach($product_id as $key=>$value)
					  {
						$this->base_model->set_initial_product($value);
						$stock_transfer_product_serial_number=array();
						$stock_transfer_product_serial_number=$_POST['stock_transfer_product_serial_number_'.$key];
						
						if(!empty($value) && $value!=''){
						
						
						// print_r($stockTransferProductIds);
						// echo '<br/>'.$stockTransferProductIds[$key];
						// die; exit;
						if($stockTransferProductIds[$key]!= ''){
							
							$stock_transfer_product_id = $stockTransferProductIds[$key];
							
							$i_p_stock_updateArray = array('stock_transfer_product_serial_number'=>'','stock_transfer_product_weight'=>$stock_transfer_product_weight[$key],'stock_transfer_product_quantity'=>$stock_transfer_product_quantity[$key],'stock_transfer_product_remarks'=>$stock_transfer_product_remarks[$key]);
								
							$this->base_model->update_record_by_id($table_inventory_stock_transfer_product,$i_p_stock_updateArray,array('stock_transfer_product_id'=>$stock_transfer_product_id));
							
							$productSerialData = $this->base_model->get_all_record_by_id($table_inventory_stock_transfer_product_serial,array('stock_transfer_product_id'=>$stock_transfer_product_id));
							
							foreach($productSerialData as $oldSerial){
								$this->db->where('product_serial_number',$oldSerial->stock_transfer_product_serial_number);
								$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'0'));
							}
							
							$this->db->where('stock_transfer_product_id',$stock_transfer_product_id);
							$this->db->delete($table_inventory_stock_transfer_product_serial);
							
							foreach($stock_transfer_product_serial_number as $serials)
							{
								$this->db->where('product_serial_number',$serials);
								$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'2'));
								
								$insert_serial=array('stock_transfer_id'=>$stock_transfer_id,'stock_transfer_product_id'=>$stock_transfer_product_id,'stock_transfer_product_serial_number'=>$serials,'creator_id'=>$this->session->userdata('user_id'),'stock_transfer_product_serial_number_status'=>'2','createdOn'=>$createdOn);
								$this->base_model->insert_one_row($table_inventory_stock_transfer_product_serial,$insert_serial);
							}
						}
						else{
							$i_stock_trans_product=array('stock_transfer_id'=>$stock_transfer_id,'product_id'=>$value,'stock_transfer_product_serial_number'=>'','stock_transfer_product_weight'=>$stock_transfer_product_weight[$key],'stock_transfer_product_quantity'=>$stock_transfer_product_quantity[$key],'stock_transfer_product_remarks'=>$stock_transfer_product_remarks[$key],'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
						
							$this->base_model->insert_one_row($table_inventory_stock_transfer_product,$i_stock_trans_product);
							$stock_transfer_product_id=$this->db->insert_id();
						 
							
							$productSerialData = $this->base_model->get_all_record_by_id($table_inventory_stock_transfer_product_serial,array('stock_transfer_product_id'=>$stock_transfer_product_id));
							
							foreach($productSerialData as $oldSerial){
								$this->db->where('product_serial_number',$oldSerial->stock_transfer_product_serial_number);
								$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'0'));
							}
							
							$this->db->where('stock_transfer_product_id',$stock_transfer_product_id);
							$this->db->delete($table_inventory_stock_transfer_product_serial);
							
							foreach($stock_transfer_product_serial_number as $serials)
							{
								$this->db->where('product_serial_number',$serials);
								$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'2'));
								
								$insert_serial=array('stock_transfer_id'=>$stock_transfer_id,'stock_transfer_product_id'=>$stock_transfer_product_id,'stock_transfer_product_serial_number'=>$serials,'creator_id'=>$this->session->userdata('user_id'),'stock_transfer_product_serial_number_status'=>'2','createdOn'=>$createdOn);
								$this->base_model->insert_one_row($table_inventory_stock_transfer_product_serial,$insert_serial);
							}
						}
						
						}					
					  }
					}
					if($_POST['my_page_type'] == "addData")
					{
						$action = "Add";
						$activity = "Stock transfer (".$stock_transfer_number.") has been added.";
						$page_name = "stock_transfer_inventory";
						$this->base_model->insertActivity($action,$activity,$page_name);
					
						$this->session->set_flashdata("SuccessMessage", 'Stock has been transferred successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							$errors['done']='success';
							$errors['MSG']='Stock has been transfered successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							
							exit;
						 }
					}
					else if( $_POST['my_page_type'] == "editData")
					{
						$action = "Update";
						$activity = "Stock transfer (".$stock_transfer_number.") has been updated.";
						$page_name = "stock_transfer_inventory";
						$this->base_model->insertActivity($action,$activity,$page_name);
						
						$this->session->set_flashdata("SuccessMessage", 'Stock has been transferred successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							$errors['done']='success';
							$errors['MSG']='Stock has been transfered successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							
							exit;
						 }
					}
				
				}
					// code insertion and updation by authorizer
					
				if($user_access_type == "Authorizer" && $access_level == '1'){
					
					// update in transfer table
					$where_stockTransfer_id = array('stock_transfer_id'=>$stock_transfer_id);
					$productData = $this->db->get_where($table_inventory_stock_transfer_product,$where_stockTransfer_id)->result();
					
					$this->base_model->update_record_by_id('inventory_'.$office_operation_type.'_stock_transfer_'.$office_id,array('access_level_status'=>$access_level,'authorized_by'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s')),$where_stockTransfer_id);
					
					// insert in the receipt table of receiver
					$stock_receiptData = array('stock_transfer_id'=>$stock_transfer_id,'stock_transfer_number'=>$stock_transfer_number,'stock_receipt_from'=>$office_id,'stock_transfer_date'=>$stock_transfer_date,'stock_transfer_status'=>'0','creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
					$this->base_model->insert_one_row('inventory_'.getOfficeOperationType($stock_transfer_to_office_id).'_stock_receipt_'.$stock_transfer_to_office_id,$stock_receiptData);
					
					$stock_receipt_id = $this->db->insert_id();
					
					
					
					foreach($productData as $key=>$product_data){
						// in the receipt table of receiver
						$value = $product_data->product_id;
						$stock_transfer_product_id = $product_data->stock_transfer_product_id;
						$stock_transfer_product_quantity = 0;
						$stock_transfer_product_weight = 0;
						$stock_transfer_product_quantity = $product_data->stock_transfer_product_quantity;
						$stock_transfer_product_weight = $product_data->stock_transfer_product_weight;
						
						$product_current_stocks=$this->base_model->get_record_by_id($table_name_current_stock,array('product_id'=>$value));
						$currentStock = (isset($product_current_stocks->product_current_stock)) ? $product_current_stocks->product_current_stock : '0';
						
						$stock_receipt_productData = array('stock_receipt_id'=>$stock_receipt_id,'product_id'=>$value,'serial_number'=>'','weight'=>$stock_transfer_product_weight,'stock_transferred'=>$stock_transfer_product_quantity,'stock_pending'=>$stock_transfer_product_quantity,'stock_transferStatus'=>'0','creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);

						$this->base_model->insert_one_row($table_inventory_stock_receipt_product,$stock_receipt_productData);
						$stock_receipt_product_id=$this->db->insert_id();
						
						$where_stockTrasfer_serial = array('stock_transfer_product_id'=>$stock_transfer_product_id,'stock_transfer_product_serial_number_status'=>'2');
						
						$serialNumberData = $this->db->get_where($table_inventory_stock_transfer_product_serial,$where_stockTrasfer_serial)->result();
						$stock_transfer_product_serial_number = array();
						
						foreach($serialNumberData as $serialData){
							$stock_transfer_product_serial_number[] = $serialData->stock_transfer_product_serial_number;
						}
						
						foreach($stock_transfer_product_serial_number as $serials)
						{
							$this->db->where('product_serial_number',$serials);
							$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'1'));
						
							$insert_serial_receipt=array('stock_receipt_id'=>$stock_receipt_id,'stock_receipt_product_id'=>$stock_receipt_product_id,'serial_number'=>$serials,'creator_id'=>$this->session->userdata('user_id'),'stock_receipt_product_serial_number_status'=>'3','createdOn'=>$createdOn);
						
							$this->base_model->insert_one_row($table_inventory_stock_receipt_product_serial,$insert_serial_receipt);
					
						}
						
						$new_stock=($product_current_stocks->product_current_stock)-($stock_transfer_product_quantity);
						$product_current_stock=array('product_current_stock'=>$new_stock);
						
						$this->base_model->update_record_by_id($table_name_current_stock,$product_current_stock,array('product_id'=>$value));
						
						$history_table = 'inventory_office_history_'.$office_id;
						$this->db->select('*');
						$this->db->order_by('history_id','desc')->limit('1');
						$arr_his_data=$this->db->get_where($history_table,array('product_id'=>$value))->row();
						
						
						$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value,'transfer_stock'=>$stock_transfer_product_quantity,'net_stock'=>($arr_his_data->net_stock-$stock_transfer_product_quantity),'type_value'=>getOfficeOperationType($stock_transfer_to_office_id),'transfer_to'=>$stock_transfer_to_office_id,'transaction_number'=>$stock_transfer_number,'authorized_by'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s'),'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
						$this->base_model->insert_one_row($history_table,$historyData);
						
					}
					$action = "Authorize";
					$activity = "Stock transfer (".$stock_transfer_number.") has been authorized.";
					$page_name = "stock_transfer_inventory";
					$this->base_model->insertActivity($action,$activity,$page_name);
					$this->session->set_flashdata("SuccessMessage", 'Stock has been authorized successfully.');
					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						$errors['done']='success';
						$errors['MSG']='Stock has been transfered successfully.';
						echo json_encode($errors);
						//redirect('masterForm/raw_material_master');
						
						exit;
					}
					
				}	
				}
				else{
					echo json_encode(array('done'=>'success','set_submit'=>'1'));
					exit;
				}
				
			}	
		}
		
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$data['summary_of_order_transferred']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by($office_operation_type,$office_id);
        
        if(empty($office_id) && $office_id==0){
			
			$office_operation_type='show';
			$data['product_master']=$this->base_model->get_all_records('product_master');
			
		}else{
			$office_operation_type=$this->session->userdata('office_operation_type');
			if(isset($office_operation_type) && $office_operation_type=='showroom'){
				
				 $data['product_master']=$this->inventory_model->getAllProductsByOfficeId($office_id);
				 
			}else{
				
				$data['product_master']=$this->base_model->get_all_records('product_master');
			}
		}	
		$data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'), $office_operation_type);
		$stock_transfer_id = ($this->input->get('stock_transfer_id')) ? base64_decode($this->input->get('stock_transfer_id')) : '';
		$data['view_page']='0';
		if($this->input->get('stock_transfer_id')!='')
		{
		$data['stock_transfer_id'] = $stock_transfer_id;
		$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by_stock_transfer_id($stock_transfer_id);
		$data['view_page']='1';
		}
		$header['title'] = "Stock Transfer Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_transfer_form',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('inventory/stock_transfer_inventory'));
		}
    }
	
	public function stock_transfer_form_view(){
		
		// echo '<pre>';
// print_r($_POST);
 // die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		if($office_operation_type == "store"){
			$data['page_id'] = "21";
			$page_id = "21";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "25";
			$page_id = "25";
		}	
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$view_value = $page_permission_array->view_value;
		if($view_value == "3")
		{
		$data=array();
		$data['summary_of_order_transferred']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by($this->session->userdata('user_id'),'show');
        $office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
        if(empty($office_id) && $office_id==0){
			
			$office_operation_type='show';
			$data['product_master']=$this->base_model->get_all_records('product_master');
			
		}else{
			$office_operation_type=$this->session->userdata('office_operation_type');
			if(isset($office_operation_type) && $office_operation_type=='showroom'){
				
				 $data['product_master']=$this->inventory_model->getAllProductsByOfficeId($office_id);
				 
			}else{
				
				$data['product_master']=$this->base_model->get_all_records('product_master');
			}
		}
		
		$data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'), $office_operation_type);
			$stock_transfer_id = ($this->input->get('stock_transfer_id')) ? base64_decode($this->input->get('stock_transfer_id')) : '';
			$checkData = $this->base_model->get_record_by_id('inventory_'.$office_operation_type.'_stock_transfer_'.$office_id,array('stock_transfer_id'=>$stock_transfer_id));
		if(!empty($checkData)){
			$data['view_page']='0';
			if($this->input->get('stock_transfer_id')!='')
			{
			
			$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by_stock_transfer_id($stock_transfer_id);
			$data['view_page']='1';
			}
		// echo '<pre>';
		// print_r($data); die;
		$header['title'] = "Stock Transfer View";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_transfer_form_view',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
		}
		else{
			redirect(base_url('inventory/stock_transfer_inventory'));
		}
		
	}
	
	public function stock_transfer_form_authorizer(){
		
		// echo '<pre>';
// print_r($_POST);
 // die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		//$user_access_type = $this->session->userdata('user_access_type');
			
		$data=array();
		$data['summary_of_order_transferred']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by($this->session->userdata('user_id'),'show');
        $office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
        if(empty($office_id) && $office_id==0){
			
			$office_operation_type='show';
			$data['product_master']=$this->base_model->get_all_records('product_master');
			
		}else{
			$office_operation_type=$this->session->userdata('office_operation_type');
			if(isset($office_operation_type) && $office_operation_type=='showroom'){
				
				$data['product_master']=$this->inventory_model->getAllProductsByOfficeId($office_id);
				
			}else{
				
				$data['product_master']=$this->base_model->get_all_records('product_master');
			}
		}
		
		$data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'), $office_operation_type);
			$stock_transfer_id = ($this->input->get('stock_transfer_id')) ? base64_decode($this->input->get('stock_transfer_id')) : '';
			$checkData = $this->base_model->get_record_by_id('inventory_'.$office_operation_type.'_stock_transfer_'.$office_id,array('stock_transfer_id'=>$stock_transfer_id));
		
		if(!empty($checkData) && $checkData->added_by != $this->session->userdata('user_id')){
			$data['view_page']='0';
			if($this->input->get('stock_transfer_id')!='')
			{
			
			$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_transfer_by_stock_transfer_id($stock_transfer_id);
			$data['view_page']='1';
			}
		// echo '<pre>';
		// print_r($data); die;
		$header['title'] = "Stock Transfer Authorizer";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_transfer_form_authorizer',$data);
		$this->load->view('includes/_footer');
		}
		else
		{
			redirect(base_url('inventory/stock_transfer_inventory'));
		}
		
	}
	
	public function ajaxGenerateStockTransferNo(){
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		$id=$postedArr['id'];
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$page_type=$postedArr['pageName'];
		switch($page_type){
			
		case 'stock_transfer_form':
		
			
			$tableName = 'inventory_'.$office_operation_type.'_stock_transfer_'.$office_id;
            $financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            $autoGenerateNumber = 1;
            $financialSecondYear = $financialFirstYear+1;
            $financialYear = $financialFirstYear.'-'.$financialSecondYear;
            
            // $getTableNameData = $this->db->select_max('stock_transfer_id')->from($tableName)->get()->row();
			
			// $getTableNameData = $this->db->select_max('stock_transfer_id')->from($tableName)->like('stock_transfer_number',"$financialYear","before")->get()->row();
		
			$getTableNameData = $this->db->select('stock_transfer_id')->from($tableName)->like('stock_transfer_number',"$financialYear","before")->get();
			
            if(!empty($getTableNameData)){
                $autoGenerateNumber = $getTableNameData->num_rows() + 1;
            }
            $autoGenerateNumber = str_pad($autoGenerateNumber,6,"0", STR_PAD_LEFT);
			
		    /* $maxid='1';
			$row=$this->db->query('SELECT MAX(stock_transfer_id) AS maxid FROM inventory_'.$office_operation_type.'_stock_transfer_'.$office_id)->row();
			if(count($row->maxid) > 0){
				$maxid=$row->maxid+1; 
			}	 */
		//	$office_master=$this->base_model->get_record_by_id('office_master',array('office_id'=>$id));
			$office_master=$this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			$officeReceiverData=$this->base_model->get_record_by_id('office_master',array('office_id'=>$id));
/*		
		$jsonArray=array('auto_id'=>'000'.$maxid,'office_short_code'=>$office_master->office_short_code,'office_operation_type'=>$office_master->office_operation_type,'office_reciever_operation_type'=>$officeReceiverData->office_operation_type);
			 
	*/		$jsonArray = array('auto_id'=>$autoGenerateNumber,
								'office_short_code'=>$office_master->office_short_code,
								'office_operation_type'=>$office_master->office_operation_type,
								'office_reciever_operation_type'=>$officeReceiverData->office_operation_type);
								
			echo json_encode($jsonArray);
			
			break;
			
		case 'ProductSubCategory':
		
			$product_sub_category_master=$this->base_model->get_all_record_by_id('product_sub_category_master',array('product_category_id'=>$id,'creator_id'=>$this->session->userdata('user_id')));
			echo json_encode($product_sub_category_master);
			
			break;
			
		case 'ProductType':
		
			$product_type_master=$this->base_model->get_all_record_by_id('product_type_master',array('product_sub_category_id'=>$id,'creator_id'=>$this->session->userdata('user_id')));
			echo json_encode($product_type_master);
			
			break;
		case 'stock_receipt_form':
		    $maxid='1';
			$row=$this->db->query("SELECT COUNT(stock_receipt_number) AS maxid FROM inventory_".$office_operation_type."_stock_receipt_".$office_id." where stock_receipt_number!=''")->row();
			if(count($row->maxid) > 0){
				$maxid=$row->maxid+1; 
			}	
		//	$office_master=$this->base_model->get_record_by_id('office_master',array('office_id'=>$id));
			$office_master=$this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			$jsonArray=array('auto_id'=>'00000'.$maxid,'office_short_code'=>$office_master->office_short_code,'office_operation_type'=>$office_master->office_operation_type);
			echo json_encode($jsonArray);
			
			break;
			
		}
		
		
		
		
	}
	
	public function stock_receipt_inventory(){
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		if($office_operation_type == "store"){
			$data['page_id'] = "22";
			$page_id = "22";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "26";
			$page_id = "26";
		}

		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d',strtotime("-1 months")).' 00:00:00';
		}
		if(isset($postedArr['right_to']))
		{
			$tdate = explode('/',$postedArr['right_to']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));		
		/* if(empty($office_id) && $office_id==0){

		  $table_name='inventory_stock_transfer';
		  $table_name='product_current_stock';
				
		}else{ */
		  $tableNameSTOCKRECEIPT='inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;	
		  /* $table_name='product_current_stock_'.$office_id; */
		/* } */
        $data['stock_receipt_details']=$this->inventory_model->_get_all_record_stock_receipt_details_to_by_join($tableNameSTOCKRECEIPT,$office_operation_type,$office_id,$fromDate,$toDate);
		// echo $this->db->last_query();
		// echo '<pre>';
		// print_r($data); die;
		$page_access = $this->role_model->get_page_permission($page_id);
		$view_value=$page_access->view_value;
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
		if(count($page_access) > 0){
			
		$header['title'] = "Stock Receipt Inventory";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_receipt_inventory',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('user/dashboard'));
		}
	}
	
	public function stock_receipt_form(){
		
	//	print_r($_POST);die;
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$user_access_type = $this->session->userdata('user_access_type');
		if($_POST['my_page_type'] == "addData")
		{
			$user_access_type = 'Maker';
		}
		else if( $_POST['my_page_type'] == "editData" && $_POST['stock_receiptId'] !='')
		{
			$user_access_type = 'Maker';
		}
		else if($_POST['my_page_type'] == "authorizeData"){
			$user_access_type = "Authorizer";
		}
		
		if($office_operation_type == "store"){
			$data['page_id'] = "22";
			$page_id = "22";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "26";
			$page_id = "26";
		}	
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		if($add_value == "1" || $edit_value == "2")
		{
		if($_POST){
			     $errors=array();
			      if($user_access_type == "Authorizer"){
					   if($_POST['stock_transferStatus']=='No'){
				    if(empty($_POST['narration_recipt'])) {
							$errors['narration_recipt'] = 'Narration is required';
						}
					}
				  }
				 if($user_access_type == "Maker"){
					if(empty($_POST['stock_receipt_date'])) {
					$errors['stock_receipt_date'] = 'Date of Transfer is required';
					}
					
					if(empty($_POST['stock_transfer_number'])) {
					$errors['stock_transfer_number_chosen'] = 'Stock Transfer Number is required';
					}
					//if(empty($_POST['stock_receipt_number'])) {
					//$errors['stock_receipt_number'] = 'Stock Receipt Number is required';
					//}
					
					//if(empty($_POST['stock_receipt_from'])) {
				    //$errors['stock_receipt_from'] = 'Transfer To is required';
					//}
					
				    if($_POST['stock_transferStatus']=='No'){
						if(empty($_POST['narration_recipt'])) {
							$errors['narration_recipt'] = 'Narration is required';
						}
					}
					
					
					
					if(!empty($_POST['product_id'])){
					foreach($_POST['product_id'] as $key=>$value)
					{
					  if(empty($value)){
						$errors['product_name'.$key] = 'Product Name is required';				
					  }
					/*   
					  if(empty($_POST['serial_number'][$key])){
						$errors['serial_number'.$key] = 'Serial No. is required';				
					  }else{
						if(!preg_match('/^[0-9a-zA-Z\-\/\#]+$/i',$_POST['serial_number'][$key])) {
							$errors['serial_number'.$key] = 'Serial No. accept only combination of (letter or number)';
						}
						} */
					  
					  if(empty($_POST['weight'][$key])){
						$errors['weight'.$key] = 'Weight(in gm) is required';				
					  }else{
						if(!preg_match('/^[0-9\.]+$/i', $_POST['weight'][$key])) {
							$errors['weight'.$key] = 'Weight(in gm) accept only number';
						}
						}
					  
					  if(empty($_POST['stock_transferred'][$key])){
						$errors['stock_transferred'.$key] = 'Stock Transferred is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_transferred'][$key])) {
							$errors['stock_transferred'.$key] = 'Stock Transferred accept only number';
						}
						}
						
/* 
						if(empty($_POST['stock_received'][$key])){
						$errors['stock_received'.$key] = 'Stock Received is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_received'][$key])) {
							$errors['stock_received'.$key] = 'Stock Received accept only number';
						}
						} */
                          $stock_received=$_POST['stock_received'][$key];
			//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
						$serial_number=$_POST['serial_number_'.$key];
						
						if($stock_received!=count($serial_number))
						{
						$errors['serial_number'.$key.'_chosen'] = 'Please select '.$stock_received.' serial numbers';
						} 
                        /* if($_POST['stock_pending'][$key]!=''){
						$errors['stock_pending'.$key] = 'Stock Pending is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['stock_pending'][$key])) {
							$errors['stock_pending'.$key] = 'Stock Pending accept only number';
						}
						} */
						
						  /* if(empty($_POST['stock_transfer_product_remarks'][$key])){
                            $errors['stock_transfer_product_remarks'.$key] = 'Remarks is required';				
						  } */
						 
					}
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
				if($_POST['is_submit']=='1')
				{
				$postedArr=$this->security->xss_clean($_POST);
				$stock_receipt_date=$postedArr['stock_receipt_date'];
				//$stock_receipt_date= date("d/m/Y",$s_receipt_date);
				$stock_transfer_number= $postedArr['stock_transfer_number'];
				$stock_receipt_number=$_POST['stock_receipt_number'];
				if($stock_receipt_number == ''){
					$stock_receipt_number= (isset($postedArr['stock_receiptNumber'])) ? $postedArr['stock_receiptNumber'] : '';
				}
				//$stock_receipt_from=$postedArr['stock_receipt_from'];
				$stock_receipt_id=$postedArr['stock_receipt_id'];
				$stock_receiptId = (isset($postedArr['stock_receiptId'])) ? $postedArr['stock_receiptId'] : '';
				if($stock_receipt_id == ''){
					$stock_receipt_id = (isset($postedArr['stock_receiptId'])) ? $postedArr['stock_receiptId'] : '';
				}
				$stock_transfer_date=$postedArr['stock_transfer_date'];
				$stock_transfer_id=$postedArr['stock_transfer_id'];
				$stock_transferId= (isset($postedArr['stock_transferId'])) ? $postedArr['stock_transferId'] : '';
				$stock_transferFrom= (isset($postedArr['stock_transferFrom'])) ? $postedArr['stock_transferFrom'] : '';
				if($stock_transferId != ''){
					$stock_transfer_id = $stock_transferId;
				}
				$stock_transferStatus=$postedArr['stock_transferStatus'] == 'Yes' ? '1' : '0';
				$product_id=$postedArr['product_id'];
			//	$serial_number=$postedArr['serial_number'];
				$weight=$postedArr['weight'];
				$stock_transferred=$postedArr['stock_transferred'];
				$stock_received=$postedArr['stock_received'];
				$stock_pending=$postedArr['stock_pending'];
				$access_level = (isset($postedArr['access_level'])) ? $postedArr['access_level'] : '';
				//$narration = (isset($postedArr['narration'])) ? $postedArr['narration'] : '';
				$narration = (isset($_POST['narration_recipt'])) ? $_POST['narration_recipt'] : '';
				
				$createdOn=date('Y-m-d H:i:s');
				$office_operation_type=$this->session->userdata('office_operation_type');
		        $office_id=$this->session->userdata('office_id');
				$stock_receipt_product_ids = $_POST['stock_receipt_product_id'];
				
			/* 	if(empty($office_id) && $office_id==0){
					
					$tableNameSTOCKRECEIPTproductCurrentStock='product_current_stock';
					
				}else{ */
					
					
				// }
				//$newData=array('stock_transfer_id'=>$stock_transfer_id,'stock_receipt_date'=>$stock_receipt_date,'stock_transfer_number'=>$stock_transfer_number,'stock_receipt_from'=>$stock_receipt_from,'stock_transfer_date'=>$stock_transfer_date,'stock_transferStatus'=>$stock_transferStatus,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn); 
				
				$tableNameSTOCKRECEIPT='inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;
				$tableNameSTOCKRECEIPTproduct='inventory_'.$office_operation_type.'_stock_receipt_product_'.$office_id;
				
				$table_inventory_stock_receipt_product_serial='inventory_'.$office_operation_type.'_stock_receipt_product_serial_number_'.$office_id; // logged-in-user
				
				$checkData = $this->db->get_where($tableNameSTOCKRECEIPT,array('stock_receipt_id '=> $stock_receipt_id ))->row();
				if($checkData->access_level_status =='1')
				{
					redirect(base_url('inventory/stock_receipt_inventory'));
				}
				
				$tableNameSTOCKRECEIPTproductCurrentStock='product_current_stock_'.$office_id;
				$currentStockSerialNumberTable='product_current_stock_serial_number_'.$office_id;
					
				if($stock_transferFrom == ''){
				$sender_data=$this->db->get_where($tableNameSTOCKRECEIPT,array('stock_receipt_id'=>$stock_receipt_id))->row();
				$sender_office_id=$sender_data->stock_receipt_from;
				}
				else {
					$sender_office_id = $stock_transferFrom;
				}
				
				$table_inventory_stock_transfer_product_serial='inventory_'.getOfficeOperationType($sender_office_id).'_stock_transfer_product_serial_number_'.$sender_office_id;
				
				if($user_access_type == "Maker"){
					
					$checkData = $this->db->get_where($tableNameSTOCKRECEIPT,array('stock_receipt_id'=>$stock_receipt_id))->row();
					if($checkData->added_by == '' || $checkData->added_by == '0' ){
						$addedBy = $this->session->userdata('user_id');
						$addedDate = date('Y-m-d H:i:s');
					}
					else{
						$addedBy = $checkData->added_by;
						$addedDate = $checkData->added_date;
					}
					
					$newUpdateDataOld=array('stock_receipt_date'=>$stock_receipt_date,'stock_receipt_number'=>$stock_receipt_number,'stock_transfer_status'=>$stock_transferStatus,'narration'=>$narration,'added_by'=>$addedBy,'added_date'=>$addedDate);
					
					/* $newUpdateDataOld=array('stock_receipt_date'=>$stock_receipt_date,'stock_receipt_number'=>$stock_receipt_number,'stock_transfer_status'=>$stock_transferStatus,'narration'=>$narration,'access_level_status'=>'0','added_by'=>$addedBy,'added_date'=>$addedDate);
					 */
					$this->db->where('stock_receipt_id',$stock_receipt_id);
					$this->db->update($tableNameSTOCKRECEIPT,$newUpdateDataOld);
					
				
				// $stock_receipt_id=$this->base_model->get_last_insert_id();
				$flagStatus = array();
				$stock_receipt_product_ids = $_POST['stock_receipt_product_id'];
				
				if(!empty($stock_receipt_product_ids)){
					
					foreach($stock_receipt_product_ids as $key=>$value)
					  { 
						
						if(!empty($value) && $value!=''){
						$stock_receipt_product_data=$this->base_model->get_record_by_id($tableNameSTOCKRECEIPTproduct,array('stock_receipt_product_id'=>$value));
						$product_id=$stock_receipt_product_data->product_id;
						$this->base_model->set_initial_product($product_id);
						$current_stock_check=$this->base_model->get_record_by_id($tableNameSTOCKRECEIPTproductCurrentStock,array('product_id'=>$product_id));
						
						$stockReceiptProductData = $this->db->get_where($tableNameSTOCKRECEIPTproduct,array('stock_receipt_product_id'=>$value))->row();
						if($stock_receiptId == ''){
						$stockReceived = $stockReceiptProductData->stock_received + $stock_received[$key];
						}
						else{
							$stockReceived = $stock_received[$key] /*  + $_POST['total_authorized'][$key] */;
						}
						//serialNumber array
						$stock_receipt_product_serial_number=$_POST['serial_number_'.$key];
						
						$i_stock_receipt_product=array('stock_receipt_id'=>$stock_receipt_id,'product_id'=>$product_id,'serial_number'=>'','weight'=>$weight[$key],'stock_received'=>$stockReceived,'stock_pending'=>$stock_pending[$key],'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
						
						$this->base_model->update_record_by_id($tableNameSTOCKRECEIPTproduct,$i_stock_receipt_product,array('stock_receipt_product_id'=>$value));
						
						if($stock_receiptId != ''){
							$update_serial_in_receiptold =array('stock_receipt_product_serial_number_status'=>'3');
							
							$this->base_model->update_record_by_id($table_inventory_stock_receipt_product_serial,$update_serial_in_receiptold,array('stock_receipt_product_id'=>$value,'stock_receipt_product_serial_number_status'=>'2'));
						}
						
						foreach($stock_receipt_product_serial_number as $serials)
						{
							$update_serial_in_receipt=array('stock_receipt_product_serial_number_status'=>'2');
							$this->base_model->update_record_by_id($table_inventory_stock_receipt_product_serial,$update_serial_in_receipt,array('serial_number'=>$serials));
						}
						
						}					
					  }
					  
					  if($stock_transferStatus=='1'){
						  $this->base_model->update_record_by_id('inventory_'.getOfficeOperationType($sender_office_id).'_stock_transfer_'.$sender_office_id,array('stock_transfer_status'=>'1'),array('stock_transfer_id'=>$stock_transfer_id));
 //$this->base_model->update_record_by_id('inventory_'.getOfficeOperationType($office_id).'_stock_receipt_'.$office_id,array('stock_transfer_status'=>'1'),array('stock_transfer_id'=>$stock_transfer_id));
                            
					  }
					 
					}
					if($_POST['my_page_type'] == "addData")
					{
						$action = "Add";
						$activity = "Stock receipt (".$stock_receipt_number.") has been added.";
						$page_name = "stock_receipt_inventory";
						$this->base_model->insertActivity($action,$activity,$page_name);
						$this->session->set_flashdata("SuccessMessage", 'Stock Receipt has been added successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							$errors['done']='success';
							$errors['MSG']='Stock Receipt has been added successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							exit;
						 }
					}
					else if( $_POST['my_page_type'] == "editData" )
					{
						
						$action = "Update";
						$activity = "Stock receipt (".$stock_receipt_number.") has been updated.";
						$page_name = "stock_receipt_inventory";
						$this->base_model->insertActivity($action,$activity,$page_name);
						
						$this->session->set_flashdata("SuccessMessage", 'Stock Receipt has been updated successfully.');
						if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
							$errors['done']='success';
							$errors['MSG']='Stock Receipt has been added successfully.';
							echo json_encode($errors);
							//redirect('masterForm/raw_material_master');
							exit;
						 }
					}
				
				}
				
				if($user_access_type == "Authorizer" && $access_level == "1"){
					
					$stock_receipt_productData = $this->db->get_where($tableNameSTOCKRECEIPTproduct,array('stock_receipt_id'=>$stock_receipt_id))->result();
					
					
					$newUpdateData=array('stock_receipt_date'=>$stock_receipt_date,'narration'=>$_POST['narration_recipt'],'stock_receipt_number'=>$stock_receipt_number,'stock_transfer_status'=>$stock_transferStatus,'authorized_by'=>$this->session->userdata('user_id'),'access_level_status'=>$access_level,'authorized_date'=>date('Y-m-d H:i:s'));
					$this->db->where('stock_receipt_id',$stock_receipt_id);
					$this->db->update($tableNameSTOCKRECEIPT,$newUpdateData);
					
					
					
					// $stock_receipt_product_ids = array();
					// foreach($stock_receipt_productData as $s_r_productData){
						// $stock_receipt_product_ids[] = $s_r_productData->stock_receipt_product_id;
					// }
					
					$stock_receipt_product_ids = $_POST['stock_receipt_product_id'];
					
					foreach($stock_receipt_product_ids as $key=>$value){
						if($stock_pending[$key]==0)
                        {
                            $flagStatus[] = '1';
                        }
                        else{
                            $flagStatus[] = '0';
                        }
						$stock_receipt_product_data=$this->base_model->get_record_by_id($tableNameSTOCKRECEIPTproduct,array('stock_receipt_product_id'=>$value));
						
							$newUpdateProductData=array('total_stock_received'=>($stock_receipt_product_data->total_stock_received + $stock_receipt_product_data->stock_received));
							$this->db->where('stock_receipt_product_id',$value);
							$this->db->update($tableNameSTOCKRECEIPTproduct,$newUpdateProductData);
						
						$product_id=$stock_receipt_product_data->product_id;
						
						$current_stock_check=$this->base_model->get_record_by_id($tableNameSTOCKRECEIPTproductCurrentStock,array('product_id'=>$product_id));
						
						$where_status = array('stock_receipt_product_id'=>$value,'stock_receipt_product_serial_number_status'=>'2');
						$serialDataArray = $this->db->get_where($table_inventory_stock_receipt_product_serial,$where_status)->result();
						
						$stock_receipt_product_serial_number = array();
						foreach($serialDataArray as $serialData){
							$stock_receipt_product_serial_number[] = $serialData->serial_number;
						}
						
						if(empty($current_stock_check)){
							
							$product_current_stock=array('product_id'=>$product_id,'product_current_stock'=>$stock_received[$key],'creator_id'=>$this->session->userdata('user_id'),'current_stock_status'=>'0','createdOn'=>$createdOn);
							
							$this->base_model->insert_one_row($tableNameSTOCKRECEIPTproductCurrentStock,$product_current_stock);
							
							if($stock_receipt_product_data->stock_received != '' && $stock_receipt_product_data->stock_received != '0'){
								$history_table = 'inventory_office_history_'.$office_id;
								$historyData = array('current_stock'=>'0','product_id'=>$product_id,'received_stock'=>$stock_received[$key],'net_stock'=>$stock_received[$key],'type_value'=>getOfficeOperationType($sender_office_id),'received_from'=>$sender_office_id,'authorized_by'=>$this->session->userdata('user_id'),'creator_id'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s'),'transaction_number'=>$stock_receipt_number,'createdOn'=>$createdOn); 
								$this->base_model->insert_one_row($history_table,$historyData);
								$current_stock_id=$this->db->insert_id();
							}
						}
						else{
							$new_stock=($current_stock_check->product_current_stock)+($stock_received[$key]);
							$product_current_stock=array('product_current_stock'=>$new_stock);
							
							$this->base_model->update_record_by_id($tableNameSTOCKRECEIPTproductCurrentStock,$product_current_stock,array('product_id'=>$product_id));
							$current_stock_id=$current_stock_check->current_stock_id;
							
							if($stock_receipt_product_data->stock_received != '' && $stock_receipt_product_data->stock_received != '0'){
								$history_table = 'inventory_office_history_'.$office_id;
								$this->db->select('*');
								$this->db->order_by('history_id','desc')->limit('1');
								$arr_his_data=$this->db->get_where($history_table,array('product_id'=>$product_id))->row();
								
								$current_net_stock = (isset($arr_his_data->net_stock) && $arr_his_data->net_stock !='') ? $arr_his_data->net_stock : '0';
								
								$historyData = array('current_stock'=>$current_net_stock,'product_id'=>$product_id,'received_stock'=>$stock_received[$key],'net_stock'=>$new_stock,'type_value'=>getOfficeOperationType($sender_office_id),'received_from'=>$sender_office_id,'authorized_by'=>$this->session->userdata('user_id'),'creator_id'=>$this->session->userdata('user_id'),'authorized_date'=>date('Y-m-d H:i:s'),'transaction_number'=>$stock_receipt_number,'createdOn'=>$createdOn); 
								$this->base_model->insert_one_row($history_table,$historyData);
							}
						}
						
						foreach($stock_receipt_product_serial_number as $serials)
						{
							//Current Stock serial number table
							$serialNumberData = $this->base_model->get_record_by_id($currentStockSerialNumberTable,array('product_serial_number'=>$serials));
				
							if(!empty($serialNumberData)){
								$this->base_model->update_record_by_id($currentStockSerialNumberTable,array('current_stock_status' =>'0'),array('product_serial_number'=>strtoupper($serials)));
							}
							else{
								$serial_insert_array=array('current_stock_id'=>$current_stock_id,'product_id'=>$product_id,'product_serial_number'=>$serials,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>$createdOn);
								$this->base_model->insert_one_row($currentStockSerialNumberTable,$serial_insert_array);
							}
						
						$update_serial_in_transfer=array('stock_transfer_product_serial_number_status'=>'1');
						$this->base_model->update_record_by_id($table_inventory_stock_transfer_product_serial,$update_serial_in_transfer,array('stock_transfer_product_serial_number'=>$serials));
						
						$update_serial_in_receipt=array('stock_receipt_product_serial_number_status'=>'1');
						$this->base_model->update_record_by_id($table_inventory_stock_receipt_product_serial,$update_serial_in_receipt,array('serial_number'=>$serials));
						
						}
						
					}
					
					if(in_array('0',$flagStatus)){
						$oldStockReceiptData = $this->db->get_where($tableNameSTOCKRECEIPT,array('stock_receipt_id'=>$stock_receipt_id))->row();
					
					// insert in the receipt table of receiver ( new entry for chunks)
					$stock_receiptData = array('stock_transfer_id'=>$oldStockReceiptData->stock_transfer_id,'stock_transfer_number'=>$oldStockReceiptData->stock_transfer_number,'stock_receipt_from'=>$oldStockReceiptData->stock_receipt_from,'stock_transfer_date'=>$oldStockReceiptData->stock_transfer_date,'stock_transfer_status'=>'0','creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s'));
					$this->base_model->insert_one_row($tableNameSTOCKRECEIPT,$stock_receiptData);
					
					$new_stock_receipt_id = $this->db->insert_id();
					}
					else{
						$this->base_model->update_record_by_id('inventory_'.getOfficeOperationType($sender_office_id).'_stock_transfer_'.$sender_office_id,array('stock_transfer_status'=>'1'),array('stock_transfer_id'=>$stock_transfer_id));

						$this->base_model->update_record_by_id('inventory_'.getOfficeOperationType($office_id).'_stock_receipt_'.$office_id,array('stock_transfer_status'=>'1'),array('stock_transfer_id'=>$stock_transfer_id));
					}
					
					
					if($new_stock_receipt_id !=''){
						$oldStockProductReceiptData = $this->db->get_where($tableNameSTOCKRECEIPTproduct,array('stock_receipt_id'=>$stock_receipt_id))->result();
						
						foreach($oldStockProductReceiptData as $oldStockProduct)
						{
							if($oldStockProduct->stock_pending !='0'){
								$new_stock_receipt_productData = array('stock_receipt_id'=>$new_stock_receipt_id,'product_id'=>$oldStockProduct->product_id,'serial_number'=>'','weight'=>$oldStockProduct->weight,'stock_transferred'=>$oldStockProduct->stock_transferred,'total_stock_received'=>$oldStockProduct->total_stock_received,'stock_pending'=>($oldStockProduct->stock_transferred - $oldStockProduct->total_stock_received) ,'stock_transferStatus'=>'0','creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s'));

								$this->base_model->insert_one_row($tableNameSTOCKRECEIPTproduct,$new_stock_receipt_productData);
								$new_stock_receipt_product_id=$this->db->insert_id();
								
								$this->db->where(array('stock_receipt_product_id'=>$oldStockProduct->stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'3'));
								
								$this->db->update($table_inventory_stock_receipt_product_serial,array('stock_receipt_product_id'=>$new_stock_receipt_product_id,'stock_receipt_id'=>$new_stock_receipt_id));
							}
						}
					}
					$action = "Authorize";
					$activity = "Stock receipt (".$stock_receipt_number.") has been authorized.";
					$page_name = "stock_receipt_inventory";
					$this->base_model->insertActivity($action,$activity,$page_name);
					$this->session->set_flashdata("SuccessMessage", 'Stock Receipt has been authorized successfully.');
					if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
						$errors['done']='success';
						$errors['MSG']='Stock Receipt has been added successfully.';
						echo json_encode($errors);
						//redirect('masterForm/raw_material_master');
						exit;
					}
				}
				}
				else{
					echo json_encode(array('done'=>'success','set_submit'=>'1'));
					exit;
				}
			}	
		}
		$data=array();
		
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$data['summary_of_order_received']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by($office_operation_type,$office_id);
		
		$inventory_receipt_table = 'inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;
		//$data['stockTranferNumber'] = $this->base_model->get_all_records($inventory_receipt_table);
		$data['stockTranferNumber'] = $this->db->get_where($inventory_receipt_table,array('stock_transfer_status'=>'0'))->result();
		$stock_receipt_id = ($this->input->get('stock_receipt_id')) ? base64_decode($this->input->get('stock_receipt_id')) : '';
		$data['view_page']='0';
		if($this->input->get('stock_receipt_id')!='')
		{
		
		$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by_stock_transfer_id($stock_receipt_id);
		$data['view_page']='1';
		}
		 //echo "<pre>";
		 //print_r($data);
		 //echo "</pre>";
		$header['title'] = "Stock Receipt Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_receipt_form',$data);
		$this->load->view('includes/_footer');
		
		}
		else{
			redirect(base_url('inventory/stock_receipt_inventory'));
		}
    }
	
	public function stock_receipt_form_view()
	{
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		if($office_operation_type == "store"){
			$data['page_id'] = "22";
			$page_id = "22";
		}
		else if($office_operation_type == "showroom"){
			$data['page_id'] = "26";
			$page_id = "26";
		}	
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$view_value = $page_permission_array->view_value;
		if($view_value == "3")
		{
		$data['summary_of_order_received']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by($office_operation_type,$office_id);
		
		$inventory_receipt_table = 'inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;
		$data['stockTranferNumber'] = $this->base_model->get_all_records($inventory_receipt_table);
		
		$stock_receipt_id = ($this->input->get('stock_receipt_id')) ? base64_decode($this->input->get('stock_receipt_id')) : '';
		
		
		$checkData = $this->base_model->get_record_by_id($inventory_receipt_table,array('stock_receipt_id'=>$stock_receipt_id));
		if(!empty($checkData)){
			$data['view_page']='0';
			if($this->input->get('stock_receipt_id')!='')
			{
			$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by_stock_transfer_id($stock_receipt_id);
			$data['view_page']='1';
			}
		// echo '<pre>';
		// print_r($data); die;
		$data['stockReceiptId'] = $stock_receipt_id;
		
		$header['title'] = "Stock Receipt View";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_receipt_form_view',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
		}
		else{
			redirect(base_url('inventory/product_stock_receipt_inventory'));
		}
	}
	
	public function stock_receipt_form_authorizer()
	{
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$data['summary_of_order_received']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by($office_operation_type,$office_id);
		
		$inventory_receipt_table = 'inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;
		$data['stockTranferNumber'] = $this->base_model->get_all_records($inventory_receipt_table);
		
		$stock_receipt_id = ($this->input->get('stock_receipt_id')) ? base64_decode($this->input->get('stock_receipt_id')) : '';
		
		
		$checkData = $this->base_model->get_record_by_id($inventory_receipt_table,array('stock_receipt_id'=>$stock_receipt_id));
		if(!empty($checkData) && $checkData->added_by != $this->session->userdata('user_id')){
			$data['view_page']='0';
			if($this->input->get('stock_receipt_id')!='')
			{
			$data['view_data']=$this->inventory_model->_get_all_record_of_inventory_stock_receipt_by_stock_transfer_id($stock_receipt_id);
			$data['view_page']='1';
			}
		// echo '<pre>';
		// print_r($data); die;
		$data['stockReceiptId'] = $stock_receipt_id;
		
		$header['title'] = "Stock Receipt Authorizer";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_receipt_form_authorizer',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('inventory/stock_receipt_inventory'));
		}
	}
	
	public function ajaxStockTranferProductReceived()
	{
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		$office_id=$this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$search_value=$postedArr['search_value'];
		//if(isset($office_id)){
			//inventory_showroom_stock_receipt_2
			
			$receiptTable = 'inventory_'.$office_operation_type.'_stock_receipt_'.$office_id;
			
			$dataReceipt =  $this->db->get_where($receiptTable,array('stock_transfer_number'=>$search_value,'stock_transfer_status'=>'0'))->row();
			//$data['query'] = $this->db->last_query();
			$sentFrom = $dataReceipt->stock_receipt_from;
			$stock_receipt_id = $dataReceipt->stock_receipt_id;
			//$senderOperationType= getOfficeOperationType($sentFrom);
			
			$senderData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$sentFrom));
		    $stock_transfer_id = $dataReceipt->stock_transfer_id;
			$data['inventory_stock_transfer'] = $dataReceipt;
			$data['office_name']=ucfirst($senderData->office_name);
			$data['stock_receipt_id'] = $stock_receipt_id;
			
			
			$data['inventory_stock_transfer_product'] = $this->inventory_model->_get_all_record_of_inventory_stock_transfer_receipt_by($stock_receipt_id,$office_operation_type,$office_id);
			
		echo json_encode($data);
			
			/* $officeData = $this->db->get_where('office_master',array('office_id'=>$office_id))->row();
			$regionalId = $officeData->regional_store_id;
			$regionOffices = $this->base_model->get_all_record_by_id('office_master',array('regional_store_id'=>$regionalId));
			
			foreach($regionOffices as $regionOffice){
				if($regionOffice->office_id != $office_id){
					$table_inventory_received_from['inventory_'.$regionOffice->office_operation_type.'_stock_transfer_'.$regionOffice->office_id]=$regionOffice->office_operation_type."_".$regionOffice->office_id;
				$transferData[] = $this->db->get_where('inventory_'.$regionOffice->office_operation_type.'_stock_transfer_'.$regionOffice->office_id,array('stock_transfer_number'=>$search_value,'stock_transfer_to_office_id'=>$office_id))->row();
				}
			} */
			//echo $this->db->last_query();
			
			// $data =  $this->base_model->get_record_by_id($receiptTable,array('stock_transfer_number'=>$search_value,'stock_transfer_status !='=>'0'));
		    // $stock_transfer_id = $this->db->last_query();//$data['inventory_stock_transfer']->stock_transfer_id;
		/* }else{
			
		   //$data['office_name']=$this->session->userdata('office_name');
		   $data['inventory_stock_transfer']=$this->base_model->get_record_by_id($receiptTable,array('stock_transfer_number'=>$search_value,'stock_transfer_to_office_id'=>$office_id,'stock_transfer_status !='=>'Yes'));
		   $data['office_name']=$data['inventory_stock_transfer']->stock_transfer_from;//$this->session->userdata('office_name');
		   $stock_transfer_id=$data['inventory_stock_transfer']->stock_transfer_id;
		
		}
		$data['inventory_stock_transfer_product'] = $this->inventory_model->_get_all_record_of_inventory_stock_transfer_receipt_by($stock_transfer_id);
		
		/* if(empty($data['inventory_stock_transfer_product'])){
		   $data=1;
		} */
			
	}
	
	public function ajaxGetProductBYofficeId()
	{
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		$id=$postedArr['id'];
		$pageName=$postedArr['pageName'];
		$office_operation_types=$postedArr['office_operation_type'];
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		switch($pageName){
			case 'stock_transfer_form_product':
			if(isset($office_id) && $office_id==0){
			  if($office_operation_types=='showroom'){
				  
			  $data['product_master']=$this->inventory_model->getAllProductsByOfficeId($id);
			  }else{
			  $data['product_master']=$this->base_model->get_all_records('product_master');
			  }
			}
            if(isset($office_id) && $office_id!=0){
			if(isset($office_operation_type) && $office_operation_type=='showroom'){
				
				 $data['product_master']=$this->inventory_model->getAllProductsByOfficeId($office_id);
				
				
			}else{
				
			 if($office_operation_types=='showroom'){
				 
			  $data['product_master']=$this->inventory_model->getAllProductsByOfficeId($id);
			  
			  }else{
				  
			  $data['product_master']=$this->base_model->get_all_records('product_master');
			  }
			}
			}

            $current_stock = $this->db->get('product_current_stock_'.$office_id)->result();
			$currentStock = array();
			foreach($current_stock as $cs){
				if($cs->product_current_stock > 0){
					array_push($currentStock,$cs->product_id);
				}
			}
			
            $data['current_stock'] = $currentStock;
			  echo json_encode($data);	
			break;
			
			case 'product_stock_receipt_form':

			$data['product_master']=$this->base_model->get_all_records('product_master');
				
			break;
			
		}	
	}
	
	public function AjaxAddNewDivCommon()
	{
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		switch($postedArr['pageName']){
			case 'stock_transfer_form':

			$data['product_master']=$this->base_model->get_all_records('product_master');	
				
			break;
			
			case 'product_stock_receipt_form':

			//$data['product_master']=$this->base_model->get_all_records('product_master');
			$this->db->where('vendor_id',$postedArr['vendorId']);
			$this->db->select('product_master.*')->from('product_master');
			$this->db->join('vendor_charges','product_master.product_id = vendor_charges.product_id');
			$data['product_master'] = $this->db->get()->result();
				
			break;
			
		}
		$data['divSize']=$postedArr['divSize'];
		$data['pageName']=$postedArr['pageName'];
		$this->load->view('includes/_AjaxAddNewDivCommon',$data);	
	}
	
	public function getProductByVendor()
	{
		if($this->input->is_ajax_request()){
			$this->db->where('vendor_id',$_POST['vendorId']);
			$this->db->select('product_master.*')->from('product_master');
			$this->db->join('vendor_charges','product_master.product_id = vendor_charges.product_id');
			$data['product_master'] = $this->db->get()->result();
			
			echo $this->load->view('includes/_product_stock_receipt_form-productList',$data,true);
		}
	}
	
	public function testdata()
	{
	$office_operation_type=$this->session->userdata('office_operation_type');
	$office_id=$this->session->userdata('office_id');
	$data['list']=$this->db->query("select iss.*,pm.product_name from inventory_store_initial_stock_$office_id as iss left join product_master as pm on iss.product_id=pm.product_id ")->result();
	echo $this->load->view('includes/viewproductListwithnetstok',$data,true);
	}
	
	public function getSerialNumberSeries()
	{
		if($this->input->is_ajax_request()){
			$quantity = $this->input->post('quantity');
			$office_operation_type = $this->session->userdata('office_operation_type');
			$office_id = $this->session->userdata('office_id');
			$product_id = $this->input->post('product_id');
			$table_name = $this->input->post('table_name');
			$fieldName = $this->input->post('fieldName');
			$pageName = $this->input->post('pageName');
			$stock_receipt_product_id = $this->input->post('stock_receipt_product_id');
			$stock_transfer_product_id = $this->input->post('stock_transfer_product_id');
			$net_stock_id = $this->input->post('net_stock_id');
			if($pageName == "stock_transfer_form"){
				if($stock_transfer_product_id=='' || $stock_transfer_product_id == 'undefined'){
					$data['serialMaster'] = $this->db->select($fieldName.' as serial_number')->get_where($table_name,array('product_id'=>$product_id,'current_stock_status'=>'0'))->result();
				}
				else{
					$status_where = "( current_stock_status = '0' OR current_stock_status = '2' )";
					$this->db->where(array('product_id'=>$product_id));
					$this->db->where($status_where);
					$this->db->select($fieldName.' as serial_number');
					$data['serialMaster'] = $this->db->get($table_name)->result();
				}
				
				$data['query'] = $this->db->last_query();
				$data['select_id'] = "stock_transfer_product_serial_number".$net_stock_id;
				$data['select_name'] = "stock_transfer_product_serial_number_".$net_stock_id;
			}
			else if($pageName == "stock_receipt_form"){
				$formType = $this->input->post('formType');
				if($formType=='' || $formType == 'undefined' || $formType == '0'){
					$data['serialMaster'] = $this->db->select($fieldName.' as serial_number')->get_where($table_name,array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'3'))->result();
				}
				else{
					$status_where = "( stock_receipt_product_serial_number_status = '2' OR stock_receipt_product_serial_number_status = '3' )";
					$this->db->where(array('stock_receipt_product_id'=>$stock_receipt_product_id));
					$this->db->where($status_where);
					$this->db->select($fieldName.' as serial_number');
					$this->db->order_by('stock_receipt_product_serial_number_status','asc');
					$data['serialMaster'] = $this->db->get($table_name)->result();
				}
				//serial status = 3 used for recieved from not for sale and transfer
				
				$data['select_id'] = "serial_number".$net_stock_id;
				$data['select_name'] = "serial_number_".$net_stock_id;
			}
			$data['net_stock_id'] = $net_stock_id;
			$data['product_id'] = $product_id;
			$data['quantity'] = $quantity;
			echo $this->load->view('includes/_product_serial_number_list',$data,true);
		}
	}
	
	public function getProductStockReceiptNumber()
    {
        $autoGenerateNumber = 1;
        if($this->input->is_ajax_request()){
            $office_id = $this->input->post('office_id');
            $officeData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
            
            $office_operation_type = $officeData->office_operation_type;
            $tableName = "inventory_".$office_operation_type."_product_stock_receipt_".$office_id;
            
            $financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
            
            $financialSecondYear = $financialFirstYear+1;
            $financialYear = $financialFirstYear.'-'.$financialSecondYear;
            
            // $getTableNameData = $this->db->select_max('product_stock_receipt_id')->from($tableName)->get()->row();
			
			// $getTableNameData = $this->db->select_max('product_stock_receipt_id')->from($tableName)->like('product_stock_receipt_number',"$financialYear","before")->get()->row();
            
			$getTableNameData = $this->db->select('product_stock_receipt_id')->from($tableName)->like('product_stock_receipt_number',"$financialYear","before")->get();
			
            if(!empty($getTableNameData)){
                //$autoGenerateNumber = $getTableNameData->product_stock_receipt_id + 1;
                $autoGenerateNumber = $getTableNameData->num_rows() + 1;
            }
            $autoGenerateNumber = str_pad($autoGenerateNumber,6,"0", STR_PAD_LEFT);
            
            $autoGenerateNumberNew = $officeData->office_short_code.'/SRV/'.$autoGenerateNumber.'/'.$financialYear;
            
            echo trim($autoGenerateNumberNew);
        }
        
    }
	public function product_current_stock()
    {
		$office_operation_type = $this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		if($office_operation_type == "store"){
			$page_id = '19';
		}
		else if($office_operation_type == "showroom"){
			$page_id = '24';
		}		
		$page_access = $this->role_model->get_page_permission($page_id);
		
		if(count($page_access) > 0)
		{
			$table2=trim("product_current_stock_".$office_id);
			$join_data=$this->db->select('t1.product_current_stock ,t2.product_name')->from("$table2 as t1")->join('product_master as t2', 't1.product_id = t2.product_id', 'LEFT')->get();
			$data['current_stoke']=$join_data->result_array();
			$header['title'] = "Current Stock";
			$this->load->view("includes/_header",$header);
			$this->load->view("includes/_top_menu");
			$this->load->view('inventory/product_current_stock',$data);
			$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('user/dashboard'));
		}
    }
		
	public function showSerialNumberByStockId()
	{
		if($this->input->is_ajax_request()){
			$office_id = $this->session->userdata('office_id');
			$office_operation_type = $this->session->userdata('office_operation_type');
            $stock_product_id = $this->input->post('stock_product_id');
            $quantity = $this->input->post('quantity');
            $product_id = $this->input->post('product_id');
            $net_stock_id = $this->input->post('net_stock_id');
            $serialNumbers = $this->input->post('serialNumbers');
			// echo $product_id.' and '.$stock_product_id;
			$productReceiptSerialNumberTable = "inventory_".$office_operation_type."_product_stock_receipt_p_s_n_".$office_id;
			if($serialNumbers == '' || $serialNumbers == 'undefined'){
			
				$serialNumberData = $this->base_model->get_all_record_by_id($productReceiptSerialNumberTable,array('stock_product_id'=>$stock_product_id));
				$divToAppend = '';
				
				for($i=1;$i<$quantity;$i++){
					$serials = (isset($serialNumberData[$i]->stock_product_serial_number))? $serialNumberData[$i]->stock_product_serial_number : '';
					$divToAppend .= '<div class="row" id="my_id-'.$i.'"><div class="form-group col-lg-4"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" name="initial_stock_serial_no[]" value="'.$serials.'" /><input type="hidden" name="initial_product_serial_id[]" value="" /></div><div class="form-group col-lg-1" onclick="removeSrNo('.$i.','.$net_stock_id.')"><a href="javascript:void(0);" class="btn btn-round btn-default"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
					
				}
				$first_serial_number = $serialNumberData['0']->stock_product_serial_number;
				$data = array('divToAppend'=>$divToAppend,'first_serial_number'=>$first_serial_number);
			}
			else{
				$serialsArray = explode(',',$serialNumbers);
				if($quantity < count($serialsArray)){
					$totalQuanity = count($serialsArray);
				}
				else{
					$totalQuanity = $quantity;
				}
				$divToAppend = '';
				
				for($i=1;$i<$totalQuanity;$i++){
					$serials = (isset($serialsArray[$i])) ? $serialsArray[$i] : '';
					$divToAppend .= '<div class="row" id="my_id-'.$i.'"><div class="form-group col-lg-4"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" name="initial_stock_serial_no[]" value="'.$serials.'" /><input type="hidden" name="initial_product_serial_id[]" value="" /></div><div class="form-group col-lg-1" onclick="removeSrNo('.$i.','.$net_stock_id.')"><a href="javascript:void(0);" class="btn btn-round btn-default"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
					
				}
				$first_serial_number = $serialsArray['0'];
				$data = array('divToAppend'=>$divToAppend,'first_serial_number'=>$first_serial_number);
			}
			echo json_encode($data);
		}
	}
	
		
	public function makeSerialNumbers()
	{
		$firstSerialNumber = $this->input->post('firstSerialNumber');
		$limit = $this->input->post('limit'); // it is used for last serial number..
		$divNumber = $this->input->post('divNumber');
		$rangeDivNumber = $this->input->post('rangeDivNumber');
		$product_id = $this->input->post('product_id');
		
		$firstSerialNumberArray = $this->input->post('firstSerialNumbers');
		$lastSerialNumberArray = $this->input->post('lastSerialNumbers'); // it is used for last serial number..
		$divNumberArray = $this->input->post('divIds');
		$productIdArray = $this->input->post('productIds');
		
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$table_name = "product_current_stock_serial_number_".$office_id;
		
	//	print_r($_POST);
		
		$alreadyUsedSerials = array();
		if(in_array($product_id,$productIdArray)){
			if(count($firstSerialNumberArray)>0){
				foreach($productIdArray as $key=>$value){
					if($divNumberArray[$key] != $divNumber){
						if($product_id == $value){
							$serialList = array();
							$serialList = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$value,'current_stock_status'=>'0','product_serial_number BETWEEN "'.$firstSerialNumberArray[$key].'" AND '=>$lastSerialNumberArray[$key]))->order_by('current_stock_serial_number_id','ASC')->get($table_name);
							foreach($serialList->result() as $serials){
								array_push($alreadyUsedSerials,$serials->serial_number);
							}
						}
					}
				}
			}
		}
		
	//	echo $this->db->last_query();
		
		if(count($alreadyUsedSerials)>0){
			$alreadyUsedSerials = array_unique($alreadyUsedSerials);
		}
		
		//print_r($alreadyUsedSerials); // die;
		$tableDataByFirst = $this->db->select('current_stock_serial_number_id')->get_where($table_name,array('product_serial_number'=>$firstSerialNumber,'product_id'=>$product_id));
		$tableDataByLast = $this->db->select('current_stock_serial_number_id')->get_where($table_name,array('product_serial_number'=>$limit,'product_id'=>$product_id));
		
		if($tableDataByFirst->num_rows() > 0 && $tableDataByLast->num_rows() > 0 ){
			$firstValue = $tableDataByFirst->row()->current_stock_serial_number_id;
			$lastValue = $tableDataByLast->row()->current_stock_serial_number_id;
			if($firstValue <= $lastValue){
			}
			else{
				$thirdValue = $lastValue;
				$lastValue = $firstValue;
				$firstValue = $thirdValue;
			}
			if(count($alreadyUsedSerials)>0){
			$serialMaster = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$product_id,'current_stock_status'=>'0','current_stock_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->where_not_in('product_serial_number',$alreadyUsedSerials)->order_by('current_stock_serial_number_id','ASC')->get($table_name);	
			}
			else{
			$serialMaster = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$product_id,'current_stock_status'=>'0','current_stock_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->order_by('current_stock_serial_number_id','ASC')->get($table_name);	
			}
			
		//	echo $this->db->last_query();
			// if($serialMaster->num_rows() < $limit){
				// echo json_encode(array('msgType'=>'error','msg'=>'Range is more than available serial number.','limit'=>$serialMaster->num_rows()));
			// }
			// else{
				$divToAppend = '';
				$nnew = array();
				$i=0;
				foreach($serialMaster->result() as $key=>$serialNumber){
					if($alreadyUsedSerials[$key] == $serialNumber->serial_number)
					{
						continue;
					}
					else{
						$divToAppend .= '<div class="my_id-'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" readonly name="stock_transfer_product_serial_number_'.$divNumber.'[]" value="'.$serialNumber->serial_number.'" /><a onclick="removeAddSrNo('.$divNumber.','.$rangeDivNumber.','.$i.')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
					}
					
					
					$i++;
				}
			
				//echo $divToAppend;
				echo json_encode(array('msgType'=>'success','msg'=>$divToAppend,'quantity'=>$i));
			//}
		}
		else{
			echo json_encode(array('msgType'=>'error','msg'=>'Either serial number is not available or is used.','limit'=>'0'));
		}
	}

	
	public function updateMakeSerialNumbers()
	{
		$firstSerialNumber = $this->input->post('firstSerialNumber');
		$limit = $this->input->post('limit'); // it is used for last serial number..
		$divNumber = $this->input->post('divNumber');
		$rangeDivNumber = $this->input->post('rangeDivNumber');
		$product_id = $this->input->post('product_id');
		
		$firstSerialNumberArray = $this->input->post('firstSerialNumbers');
		$lastSerialNumberArray = $this->input->post('lastSerialNumbers'); // it is used for last serial number..
		$divNumberArray = $this->input->post('divIds');
		$productIdArray = $this->input->post('productIds');
		
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$table_name = "product_current_stock_serial_number_".$office_id;
		
		$alreadyUsedSerials = array();
		if(in_array($product_id,$productIdArray)){
			if(count($firstSerialNumberArray)>0){
				foreach($productIdArray as $key=>$value){
					if($divNumberArray[$key] != $divNumber){
						if($product_id == $value){
							$serialList = array();
							$serialList = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$value,'current_stock_status'=>'0','product_serial_number BETWEEN "'.$firstSerialNumberArray[$key].'" AND '=>$lastSerialNumberArray[$key]))->order_by('current_stock_serial_number_id','ASC')->get($table_name);
							foreach($serialList->result() as $serials){
								array_push($alreadyUsedSerials,$serials->serial_number);
							}
						}
					}
				}
			}
		}
		
		if(count($alreadyUsedSerials)>0){
			$alreadyUsedSerials = array_unique($alreadyUsedSerials);
		}
		
	//	print_r($alreadyUsedSerials); die;
		$tableDataByFirst = $this->db->select('current_stock_serial_number_id')->get_where($table_name,array('product_serial_number'=>$firstSerialNumber,'product_id'=>$product_id));
		$tableDataByLast = $this->db->select('current_stock_serial_number_id')->get_where($table_name,array('product_serial_number'=>$limit,'product_id'=>$product_id));
		
		if($tableDataByFirst->num_rows() > 0 && $tableDataByLast->num_rows() > 0 ){
			$firstValue = $tableDataByFirst->row()->current_stock_serial_number_id;
			$lastValue = $tableDataByLast->row()->current_stock_serial_number_id;
			$status_where = "( current_stock_status = '0' OR current_stock_status = '2' )";
			if($firstValue <= $lastValue){
			}
			else{
				$thirdValue = $lastValue;
				$lastValue = $firstValue;
				$firstValue = $thirdValue;
			}
			$this->db->where($status_where);
			if(count($alreadyUsedSerials)>0){
			$serialMaster = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$product_id,'current_stock_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->where_not_in('product_serial_number',$alreadyUsedSerials)->order_by('current_stock_serial_number_id','ASC')->get($table_name);	
			}
			else{
			$serialMaster = $this->db->select('product_serial_number as serial_number')->where(array('product_id'=>$product_id,'current_stock_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->order_by('current_stock_serial_number_id','ASC')->get($table_name);	
			}
			
			// echo $this->db->last_query(); die;
			if($serialMaster->num_rows() < $limit){
				echo json_encode(array('msgType'=>'error','msg'=>'Range is more than available serial number.','limit'=>$serialMaster->num_rows()));
			}
			else{
				$divToAppend = '';
				$nnew = array();
				$i=0;
				foreach($serialMaster->result() as $key=>$serialNumber){
					if($alreadyUsedSerials[$key] == $serialNumber->serial_number)
					{
						continue;
					}
					else{
						$divToAppend .= '<div class="my_id-'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" readonly name="stock_transfer_product_serial_number_'.$divNumber.'[]" value="'.$serialNumber->serial_number.'" /><a onclick="removeAddSrNo('.$divNumber.','.$rangeDivNumber.','.$i.')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
					}
					
					
					$i++;
				}
			
				//echo $divToAppend;
				echo json_encode(array('msgType'=>'success','msg'=>$divToAppend,'quantity'=>$i));
			}
		}
		else{
			echo json_encode(array('msgType'=>'error','msg'=>'Either serial number is not available or is used.','limit'=>'0'));
		}
	}

	public function getSerialNumberAutoCompleteList()
	{
		$product_id = $this->uri->segment(3);
		$searchString = $this->input->get_post('term');
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "product_current_stock_serial_number_".$office_id;
		
        $SerialMaster = $this->db->select('*')->from($table_name)->where(array('product_serial_number like '=>'%'.$searchString.'%','current_stock_status'=>'0','product_id'=>$product_id))->order_by('current_stock_serial_number_id','ASC')->get()->result();

        $serialArray = array();

        foreach($SerialMaster as $serials){

            $label = $serials->product_serial_number;

            array_push($serialArray, array("serial_id" => $serials->product_serial_number, "label" => $label));

        }

        echo json_encode($serialArray);
	}
	
	public function getSerialNumberAutoCompleteListOnUpdate()
	{
		$product_id = $this->uri->segment(3);
		$searchString = $this->input->get_post('term');
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "product_current_stock_serial_number_".$office_id;
		
		$status_where = "( current_stock_status = '0' OR current_stock_status = '2' )";
		$this->db->where($status_where);
		
       // $SerialMaster = $this->db->get_where($table_name,array('product_serial_number like '=>'%'.$searchString.'%','product_id'=>$product_id))->result();
		
		$SerialMaster = $this->db->select('*')->from($table_name)->where(array('product_serial_number like '=>'%'.$searchString.'%','product_id'=>$product_id))->order_by('current_stock_serial_number_id','ASC')->get()->result();

        $serialArray = array();

        foreach($SerialMaster as $serials){

            $label = $serials->product_serial_number;

            array_push($serialArray, array("serial_id" => $serials->product_serial_number, "label" => $label));

        }

        echo json_encode($serialArray);
	}
	
	// to make serial number divs on stock receipt form-control
	public function getReceiptSerialNumber()
	{
		$stock_receipt_product_id = $this->input->post('stock_receipt_product_id');
		$divNumber = $this->input->post('divNumber');
		$rangeDivNumber = $this->input->post('rangeDivNumber');
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		$serialMaster = $this->db->select('serial_number')->get_where($table_name,array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'3'))->result();
		
		$divToAppend = '';
		$i=0;
		foreach($serialMaster as $key=>$serialNumber){
			$divToAppend .= '<div class="my_id-'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" readonly name="serial_number_'.$divNumber.'[]" value="'.$serialNumber->serial_number.'" /><a onclick="removeAddSrNoReceiptForm('.$divNumber.','.$rangeDivNumber.','.$i.')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
			$i++;
		}
		$lastValue = count($serialMaster)-1;
		$firstSerialNumber = $serialMaster[0]->serial_number;
		$lastSerialNumber = $serialMaster[$lastValue]->serial_number;
		
		echo json_encode(array('firstSerialNumber'=>$firstSerialNumber,'lastSerialNumber'=>$lastSerialNumber,'msg'=>$divToAppend));
		
	}
	
	public function makeSerialNumbersReceiptForm()
	{
		$firstSerialNumber = $this->input->post('firstSerialNumber');
		$limit = $this->input->post('limit'); // it is used for last serial number..
		$divNumber = $this->input->post('divNumber');
		$rangeDivNumber = $this->input->post('rangeDivNumber');
		$stock_receipt_product_id = $this->input->post('stock_receipt_product_id');
		
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		
		$tableDataByFirst = $this->db->select('stock_receipt_serial_number_id')->get_where($table_name,array('serial_number'=>$firstSerialNumber,'stock_receipt_product_id'=>$stock_receipt_product_id));
		$tableDataByLast = $this->db->select('stock_receipt_serial_number_id')->get_where($table_name,array('serial_number'=>$limit,'stock_receipt_product_id'=>$stock_receipt_product_id));
		
		if($tableDataByFirst->num_rows() > 0 && $tableDataByLast->num_rows() > 0 ){
			$firstValue = $tableDataByFirst->row()->stock_receipt_serial_number_id;
			$lastValue = $tableDataByLast->row()->stock_receipt_serial_number_id;
			if($firstValue <= $lastValue){
			$serialMaster = $this->db->select('serial_number as serial_number')->where(array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'3','stock_receipt_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->get($table_name);	
			}
			else{
				$serialMaster = $this->db->select('serial_number as serial_number')->where(array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'3','stock_receipt_serial_number_id BETWEEN "'.$lastValue.'" AND '=>$firstValue))->get($table_name);
			}
			$divToAppend = '';
			$nnew = array();
			$i=0;
			foreach($serialMaster->result() as $key=>$serialNumber){
				if($alreadyUsedSerials[$key] == $serialNumber->serial_number)
				{
					continue;
				}
				else{
					$divToAppend .= '<div class="my_id-'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" readonly name="serial_number_'.$divNumber.'[]" value="'.$serialNumber->serial_number.'" /><a onclick="removeAddSrNoReceiptForm('.$divNumber.','.$rangeDivNumber.','.$i.')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
				}
				
				$i++;
			}
			
			//echo $divToAppend;
			echo json_encode(array('msgType'=>'success','msg'=>$divToAppend,'quantity'=>$i));
			
		}
		else{
			echo json_encode(array('msgType'=>'error','msg'=>'Either serial number is not available or is used.','limit'=>'0'));
		}
	}
	
	public function updateMakeSerialNumbersReceiptForm()
	{
		$firstSerialNumber = $this->input->post('firstSerialNumber');
		$limit = $this->input->post('limit'); // it is used for last serial number..
		$divNumber = $this->input->post('divNumber');
		$rangeDivNumber = $this->input->post('rangeDivNumber');
		$stock_receipt_product_id = $this->input->post('stock_receipt_product_id');
		
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		
		$tableDataByFirst = $this->db->select('stock_receipt_serial_number_id')->get_where($table_name,array('serial_number'=>$firstSerialNumber,'stock_receipt_product_id'=>$stock_receipt_product_id));
		$tableDataByLast = $this->db->select('stock_receipt_serial_number_id')->get_where($table_name,array('serial_number'=>$limit,'stock_receipt_product_id'=>$stock_receipt_product_id));
		
		if($tableDataByFirst->num_rows() > 0 && $tableDataByLast->num_rows() > 0 ){
			$firstValue = $tableDataByFirst->row()->stock_receipt_serial_number_id;
			$lastValue = $tableDataByLast->row()->stock_receipt_serial_number_id;
			
			$where_status = '(stock_receipt_product_serial_number_status = 3 or stock_receipt_product_serial_number_status = 2)';
			$this->db->where($where_status);
			if($firstValue <= $lastValue){
				$serialMaster = $this->db->select('serial_number as serial_number')->where(array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_serial_number_id BETWEEN "'.$firstValue.'" AND '=>$lastValue))->get($table_name);	
			}
			else{
				$serialMaster = $this->db->select('serial_number as serial_number')->where(array('stock_receipt_product_id'=>$stock_receipt_product_id,'stock_receipt_serial_number_id BETWEEN "'.$lastValue.'" AND '=>$firstValue))->get($table_name);	
			}
			$divToAppend = '';
			$nnew = array();
			$i=0;
			foreach($serialMaster->result() as $key=>$serialNumber){
				if($alreadyUsedSerials[$key] == $serialNumber->serial_number)
				{
					continue;
				}
				else{
					$divToAppend .= '<div class="my_id-'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$i.'" readonly name="serial_number_'.$divNumber.'[]" value="'.$serialNumber->serial_number.'" /><a onclick="removeAddSrNoReceiptForm('.$divNumber.','.$rangeDivNumber.','.$i.')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
				}
				
				$i++;
			}
			
			//echo $divToAppend;
			echo json_encode(array('msgType'=>'success','msg'=>$divToAppend,'quantity'=>$i));
			
		}
		else{
			echo json_encode(array('msgType'=>'error','msg'=>'Either serial number is not available or is used.','limit'=>'0'));
		}
	}
	
	public function getSerialNumberAutoCompleteListReceipt()
	{
		$product_id = $this->uri->segment(3);
		$searchString = $this->input->get_post('term');
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		
        $SerialMaster = $this->db->get_where($table_name,array('serial_number like '=>'%'.$searchString.'%','stock_receipt_product_serial_number_status'=>'3','stock_receipt_product_id'=>$product_id))->result();

		//echo $this->db->last_query();
        $serialArray = array();

        foreach($SerialMaster as $serials){

            $label = $serials->serial_number;

            array_push($serialArray, array("serial_id" => $serials->serial_number, "label" => $label));
        }
        echo json_encode($serialArray);
	}
	
	public function getUpdateSerialNumberAutoCompleteListReceipt()
	{
		$product_id = $this->uri->segment(3);
		$searchString = $this->input->get_post('term');
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		
		$table_name = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		
		$where_status = '(stock_receipt_product_serial_number_status = 3 or stock_receipt_product_serial_number_status = 2)';
		$this->db->where($where_status);
		
        $SerialMaster = $this->db->get_where($table_name,array('serial_number like '=>'%'.$searchString.'%','stock_receipt_product_id'=>$product_id))->result();

		//echo $this->db->last_query();
        $serialArray = array();

        foreach($SerialMaster as $serials){

            $label = $serials->serial_number;

            array_push($serialArray, array("serial_id" => $serials->serial_number, "label" => $label));
        }
        echo json_encode($serialArray);
	}
	
	public function makeSerialNumberFromVendor()
	{
		$radioValue = $this->input->post('radioValue');
		$startingValue = $this->input->post('startingValue');
		$quantity = $this->input->post('quantity');
		$divNumber = $this->input->post('divNumber');

		preg_match('/^(.*[^\d])(\d+)$/', $startingValue, $match);
	//	print_r($match); die;
		// echo $match[2]; // 12345
		$divToAdd ='';
		if($radioValue == "Manual" || $radioValue == ""){
			$divToAdd .= '<div id="rowsrno_0"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$divNumber.'_'.$i.'" name="pop_up_serial_number'.$divNumber.'[]" value="'.$startingValue.'" onblur="checkDuplicateSerialNumber('.$divNumber.',0);" /><a onclick="removeNewSrNoNew('.$divNumber.',' .$i. ')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
			for($i=1;$i<$quantity; $i++){
				$divToAdd .= '<div id="rowsrno_'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$divNumber.'_'.$i.'" name="pop_up_serial_number'.$divNumber.'[]" value="" onblur="checkDuplicateSerialNumber('.$divNumber.','.$i.');" /><a onclick="removeNewSrNoNew('.$divNumber.',' .$i. ')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
			}
		}
		else if($radioValue == "Automatic"){
			for($i=0;$i<$quantity; $i++){
				$newValue = $match[2] + $i;
				//if(strlen($match[2])<= 4){
					$myValue = str_pad($newValue,strlen($match[2]),"0", STR_PAD_LEFT);
				// }
				// else{
					// $myValue = $newValue;
				// }
				
				$currentValue = $match[1].($myValue);
				$divToAdd .= '<div id="rowsrno_'.$i.'"><div class="form-group col-lg-3"><input type="text" class="form-control" id="initial_stock_starting_serial_popup'.$divNumber.'_'.$i.'" name="pop_up_serial_number'.$divNumber.'[]" value="'.$currentValue.'" /><a onclick="removeNewSrNoNew('.$divNumber.',' .$i. ')" href="javascript:void(0);" class="btn btn-round btn-default my-btn-round"><i class="glyphicon glyphicon-remove"></i></a></div></div>';
			}
		}
		
		echo json_encode(array('divToAdd'=>$divToAdd,'receiveQuantity'=>$i));
	}
	
	public function deleteProductStockReceipt()
	{
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$productStockReceiptSRTable = "inventory_".$office_operation_type."_product_stock_receipt_p_s_n_".$office_id;
		$productStockReceiptProductTable = "inventory_".$office_operation_type."_product_stock_receipt_product_".$office_id;
		$productStockReceiptTable = "inventory_".$office_operation_type."_product_stock_receipt_".$office_id;
		$product_stock_receipt_id = base64_decode($this->input->post('product_stock_receipt_id'));
		$checkData = $this->db->get_where($productStockReceiptTable,array('product_stock_receipt_id'=>$product_stock_receipt_id))->row();
		
		$serialList = array();
		if($checkData->access_level_status == '0'){
			
			$stockProductIds = $this->db->get_where($productStockReceiptProductTable,array('product_stock_receipt_id'=>$product_stock_receipt_id))->result();
			foreach($stockProductIds as $stock)
			{
				$product_id = $stock->product_id;
				$stock_product_id = $stock->stock_product_id;
				$this->db->select("ps_sr.stock_product_serial_number,ps_sr.stock_product_serial_number_id")->from($productStockReceiptSRTable." as ps_sr");
				$this->db->where(array('stock_product_id'=>$stock_product_id));
				$serials = $this->db->get()->result();
				
				foreach($serials as $serial){
					
					$serialList[] = array('product_id'=>$product_id,'serial_number'=>$serial->stock_product_serial_number);
					$this->db->where(array('product_id'=>$product_id,'serial_number'=>$serial->stock_product_serial_number));
					$this->db->delete('serial_number_master');
					$this->db->where(array('stock_product_serial_number_id'=>$serial->stock_product_serial_number_id));
					$this->db->delete($productStockReceiptSRTable);
				}
				
				$this->db->where(array('stock_product_id'=>$stock_product_id));
				$this->db->delete($productStockReceiptProductTable);
			}
		
			foreach($serialList as $serialData){
				//print_r(array('product_id'=>$serialData->product_id,'serial_number'=>$serialData->serial_number));
				
				
			}
			
	$this->db->where(array('product_stock_receipt_id'=>$product_stock_receipt_id));
	$this->db->delete($productStockReceiptTable);
			
		}
		
	}
	
	public function deleteStockReceipt()
	{
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$stockReceiptSRTable = "inventory_".$office_operation_type."_stock_receipt_product_serial_number_".$office_id;
		$stockReceiptProductTable = "inventory_".$office_operation_type."_stock_receipt_product_".$office_id;
		$stockReceiptTable = "inventory_".$office_operation_type."_stock_receipt_".$office_id;
		$stock_receipt_id = base64_decode($this->input->post('stock_receipt_id'));
		$checkData = $this->db->get_where($stockReceiptTable,array('stock_receipt_id'=>$stock_receipt_id))->row();
		
		if($checkData->stock_receipt_number !='' && $checkData->access_level_status == '0')
		{
			$stock_receiptData = array('stock_receipt_number'=>'','stock_receipt_date'=>'','added_by'=>'','added_date'=>'');
			$this->db->where(array('stock_receipt_id'=>$stock_receipt_id));
			$this->db->update($stockReceiptTable,$stock_receiptData);
			
			$oldStockProductReceiptData = $this->db->get_where($stockReceiptProductTable,array('stock_receipt_id'=>$stock_receipt_id))->result();
			
			foreach($oldStockProductReceiptData as $oldStockProduct)
			{
				$new_stock_receipt_productData = array('stock_received'=>'0','stock_pending'=>($oldStockProduct->stock_pending + $oldStockProduct->stock_received));

				$this->db->where(array('stock_receipt_product_id'=>$oldStockProduct->stock_receipt_product_id));
				$this->db->update($stockReceiptProductTable,$new_stock_receipt_productData);
				
				$this->db->where(array('stock_receipt_product_id'=>$oldStockProduct->stock_receipt_product_id,'stock_receipt_product_serial_number_status'=>'2'));
				
				$this->db->update($stockReceiptSRTable,array('stock_receipt_product_serial_number_status'=>'3'));
			}
		}
	}
	
	public function deletStockTransfer()
	{
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		$stockTransferSRTable = "inventory_".$office_operation_type."_stock_transfer_product_serial_number_".$office_id;
		$stockTransferProductTable = "inventory_".$office_operation_type."_stock_transfer_product_".$office_id;
		$stockTransferTable = "inventory_".$office_operation_type."_stock_transfer_".$office_id;
		$stock_transfer_id = base64_decode($this->input->post('stock_transfer_id'));
		$checkData = $this->db->get_where($stockTransferTable,array('stock_transfer_id'=>$stock_transfer_id))->row();
		
		if($checkData->stock_transfer_id !='' && $checkData->access_level_status == '0')
		{
			$oldProductDatas = $this->db->get_where($stockTransferProductTable,array('stock_transfer_id'=>$stock_transfer_id))->result();
			foreach($oldProductDatas as $oldProductData)
			{
				$stock_transfer_product_id = $oldProductData->stock_transfer_product_id;
				$value = $oldProductData->product_id;
				
				$productSerialData = $this->base_model->get_all_record_by_id($stockTransferSRTable,array('stock_transfer_product_id'=>$stock_transfer_product_id));
				
				foreach($productSerialData as $oldSerial){
					$this->db->where(array('product_serial_number'=>$oldSerial->stock_transfer_product_serial_number,'current_stock_status'=>'2'));
					$this->db->update('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status'=>'0'));
				}
				
				$this->db->where('stock_transfer_product_id',$stock_transfer_product_id);
				$this->db->delete($stockTransferSRTable);
				
				$this->db->where('stock_transfer_product_id',$stock_transfer_product_id);
				$this->db->delete($stockTransferProductTable);
			}
			
			$this->db->where('stock_transfer_id',$stock_transfer_id);
			$this->db->delete($stockTransferTable);
		}
	}

}
