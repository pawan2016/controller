<?php
error_reporting(1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends CI_Controller {
	
	public function __construct() {
        parent::__construct();
        sessionExist();
		$this->load->model('inventory_model');
		$this->load->model('Report_model');
		$this->load->model('base_model');
		error_reporting(0);
		
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
	}
	
	public function price_report()
	{   
		$header['title'] = "Price Report";
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('report/price_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function loadPriceTableList()
	{
		if($this->input->is_ajax_request()){
			$searchDate = $this->input->post('searchDate');
			$this->db->where('price_master.price_date',$searchDate);
			$this->db->select('product_master.*,price_master.price_rate,price_master.price_date,price_master.buy_back_price')->from('product_master');
			$this->db->join('price_master','price_master.product_id = product_master.product_id','left');
			$query = $this->db->get();
			$data['price_master'] = $query->result();
			echo $this->load->view('includes/_priceTableList',$data,true);
		}
	}

	public function allreport()
	{  
		$header['title'] = "All Report";
		 $postedArr  = $this->security->xss_clean($_POST);
		 //print_r($postedArr);die;
	    $office_id=$this->session->userdata('office_id');
	    $office_operation_type=$this->session->userdata('office_operation_type');
		$data['product_master'] = $this->base_model->get_all_records('product_master');
		$data['vendor_master']=$this->base_model->get_all_records('vendor_master');
		if($office_operation_type=='showroom')
		{
		   //print_r($data['product_master']);die;
		  $data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'showroom');
		}
		if($office_operation_type=='store')
		{
		  $data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'store');
		}
	    $data['get_all_record']=$this->Report_model->_get_all_report($office_operation_type,$office_id);
	    if($this->input->post('submit'))
		{
			$from=$this->input->post('access_right_from');
			$to=$this->input->post('access_right_to');
			if(!empty($form)){
			$from="$from 00:00:00";
		    }if(!empty($to)){
			$to="$to 23:59:59";
		     }
			$product_id="";
			$transaction_name="";
			if($this->input->post('product_id'))
			{
			 $product_id=$this->input->post('product_id');
			 $data['selectedProduct']=$product_id;
			}
			if($this->input->post('transaction_name'))
			{
			 $transaction_name=$this->input->post('transaction_name');
			 $data['selectedtransaction_name']=$transaction_name;
			}
			if($this->input->post('office_location'))
			{
			 $teansfer_recive_office_location=$this->input->post('office_location');
			 $data['teansfer_recive_office_location']=$teansfer_recive_office_location;
			}
			if($this->input->post('vendor_id'))
			{
			 $vendor_id=$this->input->post('vendor_id');
			 $data['vendor_id']= $vendor_id;
			}
			$data['get_all_record']=$this->Report_model->_get_all_report_between_date($office_operation_type,$office_id,$from,$to,$product_id,$transaction_name,$teansfer_recive_office_location,$vendor_id);
		    //echo $this->db->last_query();die;
		  
		}
	   
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/allreport',$data);
		$this->load->view('includes/_footer');
	}
	
	public function transactionreport()
	{
		$header['title'] = "Transaction Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 //print_r($postedArr);die;
	    $office_id=$this->session->userdata('office_id');
	    $office_operation_type=$this->session->userdata('office_operation_type');

		
	   
	   $data=$this->Report_model->_get_transaction_report($office_operation_type,$office_id);
	  
		$this->load->view("includes/_header");
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/transactionreport',$data);
		$this->load->view('includes/_footer');
	}
	
	public function tax_report()
	{
		$header['title'] = "Tax Report";
		$data['tax_details'] = $this->db->get_where('tax_master',array('office_id'=>$this->session->userdata('office_id')))->row();
		$this->load->view("includes/_header");
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/tax_report',$data);
		$this->load->view('includes/_footer');
	}
	
	
	public function admin_allreport()
	{  
		$header['title'] = "All Report";
		 $postedArr  = $this->security->xss_clean($_POST);
		 //print_r($postedArr);die;
	    /* $office_id=$this->session->userdata('office_id');
	    $office_operation_type=$this->session->userdata('office_operation_type');
		$data['product_master'] = $this->base_model->get_all_records('product_master');
		$data['vendor_master']=$this->base_model->get_all_records('vendor_master');
		if($office_operation_type=='showroom')
		{
		   //print_r($data['product_master']);die;
		  $data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'showroom');
		}
		if($office_operation_type=='store')
		{
		  $data['transfer_to']=$this->inventory_model->_get_all_record_of_transfer_to_by_join($this->session->userdata('user_id'),'store');
		} */
	    
	    if($this->input->post('submit'))
		{
			
			$region_id=$this->input->post('region_id');
		$office_location=$this->input->post('office_location');
		if($this->input->post('transaction_name'))
		{
			 $transaction_name=$this->input->post('transaction_name');
			 $data['transaction_name']=$transaction_name;
		}
		
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		if(!empty($region_id))
		{
		  $this->db->where_in("office_master.regional_store_id",$region_id);
		}
		if(!empty($office_location) && isset($office_location))
		{
		  $this->db->where_in("office_master.office_id",$office_location);

		}
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		$data['all_location']=$query->result();
			
			
		/* echo '<pre>';	
		print_r($data['all_location']);die;	 */
			
		  
		}
	    $data['region_master']= $this->base_model->get_all_records('regional_store_master');
	   // $data['transfer_to']= $this->db->where('office_id !=',1)->get('office_master')->result();
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		if(!empty($region_id))
		{
		  $this->db->where_in("office_master.regional_store_id",$region_id);
		}
		$query = $this->db->get();
		$data['transfer_to']=$query->result();
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/admin_allreport',$data);
		$this->load->view('includes/_footer');
	}
	
   function getRegionlocation()
   {
		$regionvalue=$_POST['resionvalue'];
		//$regionvalue=implode(',',$regionvalue);
		
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		$this->db->where_in("office_master.regional_store_id",$regionvalue);
		$query = $this->db->get();
		// echo $this->db->last_query();die;
		$data['transfer_to']=$query->result();
		echo $this->load->view('includes/_location',$data);

   }
   
   
   	
   function getsearchdata()
   {
		$region_id=$_POST['region_id'];
		$office_location=$_POST['office_location'];
		if($this->input->post('transaction_name'))
		{
			 $transaction_name=$this->input->post('transaction_name');
			 $data['transaction_name']=$transaction_name;
		}
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		if(!empty($region_id))
		{
		  $this->db->where_in("office_master.regional_store_id",$region_id);
		}
		if(!empty($office_location) && isset($office_location))
		{
		  $this->db->where_in("office_master.office_id",$office_location);

		}
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		$data['all_location']=$query->result();
		//echo "<pre>";
		//print_r($data['all_location']);die;
		echo $this->load->view('includes/_getsearchdata',$data);

   }
   
	public function admin_transactionreport()
	{  
		$header['title'] = "Transaction Report";

		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		 //print_r($postedArr);die;
	   if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$_POST['region_id']=$user_role_data->regional_store_id;
			$_POST['submit']=true;
			
			if($this->input->post('access_right_from')=='' && $this->input->post('access_right_to')=='')
			{
				$_POST['access_right_from']=date('d/m/Y',strtotime('now'));
				$_POST['access_right_to']=date('d/m/Y',strtotime('now'));
			}
			
		
		}
				$postedArr  = $this->security->xss_clean($_POST);
	    $fromDate1 = date('d/m/Y',strtotime('now'));
		$toDate1 = date('d/m/Y',strtotime('now'));
		
	    $fromDateWhere = date('Y-m-d H:i:s',strtotime('now'));
		$toDateWhere = date('Y-m-d H:i:s',strtotime('now'));
		
		$region_names = array();
		$office_names = array();
		
		if($this->input->post('submit'))
		{
			$type_transaction=$this->input->post('type_transaction');
			$data['type_transaction']=$type_transaction;
			 
			$fromDate1 = $this->input->post('access_right_from');
			$toDate1 = $this->input->post('access_right_to');
			
			$from2 = explode('/',$fromDate1);
			$to2 = explode('/',$toDate1);
			$fromDate = $from2[2].'-'.$from2[1].'-'.$from2[0];
			$toDate = $to2[2].'-'.$to2[1].'-'.$to2[0];
			$str_where=array();
			$str_ids_pur='';
		
			if(!empty($fromDate))
			{
				$fromDateWhere = "$fromDate 00:00:00";
			}if(!empty($toDate))
			{
				$toDateWhere = "$toDate 23:59:59";
			}
			
			
			if($this->input->post('payment_mode'))
			{
				$payment_mode=$this->input->post('payment_mode');
				$data['payment_mode']=$payment_mode;
			}
			if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
			{
				$postedArr['region_id']=$user_role_data->regional_store_id;
				$where_others=array('regional_store_id'=>$user_role_data->regional_store_id);
			}
			$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
			$region_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : "";
			if(!empty($office_ids)){
				foreach($office_ids as $office_id)
				{
					$office_names[] = getOfficeLocation($office_id);
				}
			}
			if(isset($postedArr['region_id']))
			{
				foreach($region_ids as $region_id)
				{
					$region_names[] = getRegionLocation($region_id);
				}
			}
			
			$data['printPaymentMode'] = (isset($postedArr['payment_mode'])) ? implode(',',$postedArr['payment_mode']) : 'All';
			$data['printTransactionType'] = (isset($postedArr['type_transaction']) && $postedArr['type_transaction'] !='') ? $postedArr['type_transaction'] : 'All';
			$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
			$data['printRegion'] = (isset($postedArr['region_id'])) ? implode(',',$region_names) : "All";
		}
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
		$data['region_master']= $this->db->select('*')->from('regional_store_master')->where(array('regional_store_id'=>$user_role_data->regional_store_id))->get()->result();
		}
		else
		{
	    $data['region_master']= $this->base_model->get_all_records('regional_store_master');
		}
	   // $data['transfer_to']= $this->db->where('office_id !=',1)->get('office_master')->result();
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		$this->db->where("office_master.office_operation_type",'showroom');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
		$this->db->where("regional_store_master.regional_store_id",$user_role_data->regional_store_id);
		}
		$query = $this->db->get();
		$data['transfer_to']=$query->result();
		
		$data['fromDate'] = $fromDate1;
		$data['toDate'] = $toDate1;
		
		$data['fromDateWhere'] = $fromDateWhere;
		$data['toDateWhere'] = $toDateWhere;
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/admin_region_transactionreport',$data);
			
		}
	    else
		{
	    $this->load->view('report/admin_transactionreport',$data);
		}
		$this->load->view('includes/_footer');
	}
	
   function getRegionShowroom()
   {
		$regionvalue=$_POST['resionvalue'];
		//$regionvalue=implode(',',$regionvalue);
		
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		$this->db->where("office_master.office_operation_type",'showroom');
		if(!empty($regionvalue)){
		$this->db->where_in("office_master.regional_store_id",$regionvalue);
		}
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		$data['transfer_to']=$query->result();
		echo $this->load->view('includes/_location',$data);

   }
   
   
	public function scheduleReport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		$header['title'] = "Schedule Report";
		$postedArr  = $this->security->xss_clean($_POST);
		
		 // print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$where_others=array('regional_store_type'=>'others');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
		}
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where($where_others)->get()->result();
		}
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		
		// $data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		$data['transfer_to']=$this->inventory_model->schedule_office_location_list($regional_ids);
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
		}
		if(isset($postedArr['right_to']))
		{
			$tdate = explode('/',$postedArr['right_to']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		
		$office_id = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		$type = (isset($postedArr['region_type'])) ? $postedArr['region_type'] : 'all';
		
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? getOfficeLocation($office_id) : 'All';
		$data['printRegion'] = (isset($postedArr['region_type'])) ? ucwords($type) : "All";
		
		$data['get_all_record']=$this->Report_model->_get_schedule_report($fromDate,$toDate,$office_id,$type);
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/schedule_region_report',$data);
		}
	    else
		{
			$this->load->view('report/schedule_report',$data);
		}
		$this->load->view('includes/_footer');
	}
	
	public function getOfficeLocationByRegionType()
	{
		$region_type = $this->input->post('region_type');
		if($region_type == "all"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($region_type == "mmtc"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($region_type == "others"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'others'))->get()->result();
		}
		
		$regional_ids = array();
		foreach($regionalStoreData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		
		// $data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		$data['transfer_to']=$this->inventory_model->schedule_office_location_list($regional_ids);
		
		echo $this->load->view('includes/_location_single_value',$data,true);
	}
	
	function getRegionList()
	{
		$region_type = $this->input->post('region_type');
		if($region_type == "all"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($region_type == "mmtc"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($region_type == "others"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'others'))->get()->result();
		}
		$data['regionLists']=$regionalStoreData;
		echo $this->load->view('includes/_regions',$data);

	}
	
	public function getMultiRegionList()
	{
		$region_type = $this->input->post('region_type');
		if($region_type == "all"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($region_type == "mmtc"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($region_type == "others"){
			$regionalStoreData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'others'))->get()->result();
		}
		$data['regionLists']=$regionalStoreData;
		echo $this->load->view('includes/_regions_multiple',$data);

	}
   
   public function salesReport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		$header['title'] = "Sales Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
	//	$regionData = $this->db->get('regional_store_master')->result();
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$where_others=array('regional_store_type'=>'others');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
		}
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where($where_others)->get()->result();
		}
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		
		$region_names = array();
		$office_names = array();
		
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
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
		
		$office_ids = (isset($postedArr['office_location']) && !empty($postedArr['office_location'][0])) ? $postedArr['office_location'] : '';

		if(!empty($office_ids[0])){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		if(!empty($regional_ids[0])){
			foreach($regional_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		$data['printRegionType'] = (isset($postedArr['region_type']) ) ? $postedArr['region_type'] : "All";
		
		
		
		$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/sales_region_report',$data);
		}
	    else
		{
	    $this->load->view('report/sales_report',$data);
		}
		$this->load->view('includes/_footer');
	}
	
   
	public function salesReport_old()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sales Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'others'))->get()->result();
		}
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		
		$region_names = array();
		$office_names = array();
		
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
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
		
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		$region_ids = (isset($postedArr['region_id'])) ? $postedArr['region_id'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		if(!empty($region_ids)){
			foreach($region_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		$data['printRegionType'] = (isset($postedArr['region_type']) ) ? $postedArr['region_type'] : "All";
		
		$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/sales_report',$data);
		$this->load->view('includes/_footer');
	}
	
	
	public function salesReport2()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sales Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$region_names = array();
		$office_names = array();
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
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
		
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		if(isset($postedArr['region_id']))
		{
			$region_names[] = getRegionLocation($postedArr['region_id']);
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		
		$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/sales_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function downloadTransaction()
	{
		$regionIds = $this->input->post('regionIds');
		$type_transaction = $this->input->post('type_transaction');
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		$officeIds = $this->input->post('officeIds');
		$payment_mode = $this->input->post('payment_mode');
		print_r($_POST);
	}

	public function paymentModeReport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		$header['title'] = "Payment Mode Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		 if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$regionData = $this->db->get_where('regional_store_master',array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id))->result();	
		}
		else
		{
			$regionData = $this->db->get('regional_store_master')->result();
		}
		$regional_ids = array();
		$region_names = array();
		$office_names = array();
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
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
				
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		if(isset($postedArr['region_id']))
		{
			$region_names[] = getRegionLocation($postedArr['region_id']);
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		
		
		$data['get_all_record']=$this->Report_model->_get_payment_mode_report($fromDate,$toDate,$office_ids,$regional_ids);
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/payment_type_region_report',$data);
		}
		else
		{
			$this->load->view('report/payment_type_report',$data);
		}
		$this->load->view('includes/_footer');
	}
	
	public function officeScheduleReport()
	{
		$header['title'] = "Schedule Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		$office_id = $this->session->userdata('office_id');
		$officeData = $this->db->get_where('office_master',array('office_id'=>$office_id))->row();
		 // print_r($postedArr);
		$regionData = $this->db->get_where('regional_store_master',array('regional_store_id'=>$officeData->regional_store_id))->result();
		
		$regional_ids = array();
		
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		
		// $data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		$data['transfer_to']=$this->inventory_model->schedule_office_location_list($regional_ids);
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
		}
		if(isset($postedArr['right_to']))
		{
			$tdate = explode('/',$postedArr['right_to']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		
		$office_id = $office_id;
		$type = $regionData[0]->regional_store_type;
		
		$data['get_all_record']=$this->Report_model->_get_schedule_report($fromDate,$toDate,$office_id,$type);
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/office_schedule_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function officeSalesReport()
	{
		if($this->session->userdata('role_id') != '3')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sales Report";
		$postedArr  = $this->security->xss_clean($_POST);
		$office_id = $this->session->userdata('office_id');
		$officeData = $this->db->get_where('office_master',array('office_id'=>$office_id))->row();
		 // print_r($postedArr);
		$regionData = $this->db->get_where('regional_store_master',array('regional_store_id'=>$officeData->regional_store_id))->result();
		$regional_ids = array();
		foreach($regionData as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		
		// echo $this->session->userdata('role_id');
		// echo $this->db->last_query();
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['right_from']))
		{
			$fdate = explode('/',$postedArr['right_from']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
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
				
		$office_ids[] = $office_id;
		
		$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/office_sales_report',$data);
		$this->load->view('includes/_footer');
	}
	
	
	public function dailyStatementReport()
	{
		redirect(base_url('user/dashboard'));
		
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Daily Statement Report";
		$data['regionLists']= $this->base_model->get_all_records('regional_store_master');
		$postedArr  = $this->security->xss_clean($_POST);

		$regional_ids = array();
		foreach($data['regionLists'] as $region)
		{
			$regional_ids [] = 	$region->regional_store_id;
		}
		$regional_ids = (isset($postedArr['region_id'])) ? $postedArr['region_id'] : $regional_ids;
		
		if(isset($postedArr['reportDate']))
		{
			$fdate = explode('/',$postedArr['reportDate']);
			$reportDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0];
		}
		else{
			$reportDate = date('Y-m-d',strtotime('now'));
		}
		$report_Date = date( 'Y-m-d', strtotime( $reportDate . ' -1 day' ) );
		
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		$data['get_all_record']=$this->Report_model->_get_daily_statement_report($report_Date,$regional_ids);
		$data['reportDate'] = date('d/m/Y',strtotime($reportDate));
		$data['tbreportDate'] = date('d/m/Y',strtotime($reportDate  . ' -1 day'));
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/daily_statement_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function productStockReceiptReport()
	{
		if($this->session->userdata('role_id') > '2')
		{
			redirect(base_url('user/dashboard'));
		}
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
		$header['title'] = "Stock Receipt From Vendor Report";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('report/product_stock_receipt_report',$data);
		$this->load->view('includes/_footer');
		}
		else{
			redirect(base_url('user/dashboard'));
		}
	}

	public function soldInventoryReport()
	{
		if($this->session->userdata('role_id') != '3')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sold Inventory Report";
		$postedArr  = $this->security->xss_clean($_POST);
		$office_id = $this->session->userdata('office_id');
		$data['all_products'] = $this->db->get('product_master')->result();
		$product_ids = array();
		$product_names = array();
		if(isset($postedArr['product_id']) && $postedArr['product_id'] != '')
		{
			$product_ids = $postedArr['product_id'];
		}
		else{
			foreach($data['all_products'] as $product)
			{
				$product_ids[] = $product->product_id;
			}
		}
		
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['fromDate']))
		{
			$fdate = explode('/',$postedArr['fromDate']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
		}
		if(isset($postedArr['toDate']))
		{
			$tdate = explode('/',$postedArr['toDate']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		foreach($product_ids as $p)
		{
			$product_names[] = getProductName($p);
		}
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		$data['printProduct'] = (isset($postedArr['product_id']) && $postedArr['product_id'] != '') ? implode(',',$product_names) : 'All';
		$data['productIds'] = (isset($postedArr['product_id']) && $postedArr['product_id'] != '') ? $postedArr['product_id'] : '';
		$data['product_Ids'] = $product_ids;
		
		$all_records = $this->Report_model->_get_sold_inventory($fromDate,$toDate,$office_id,$product_ids);
		$data['get_all_record'] = $all_records['invoiceData'];
		$data['totalWeight'] = $all_records['totalWeight'];
		$data['totalQty'] = $all_records['totalQty'];
		
		// echo '<pre>';
		// print_r($product_names);
		// echo '</pre>';
		// die;
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/sold_inventory',$data);
		$this->load->view('includes/_footer');
	}
	
	public function invoiceView()
	{
		$invoiceId =  base64_decode($this->input->get('invoice_id'));
		$office_id = $this->session->userdata('office_id');

		$data['invoiceDetails'] = $this->db->get_where('invoice_showroom_'.$office_id,array('invoice_id'=>$invoiceId))->row();
		$custId = $data['invoiceDetails']->customer_id;
		$data['customerDetails'] = $this->db->get_where('customer_master',array('customer_id'=>$custId))->row();
		
		$this->db->select('showroom.*,product_master.product_name,product_master.product_purity')->from('invoice_showroom_product_'.$office_id.' as showroom');
		$this->db->join('product_master','showroom.product_id=product_master.product_id')->where('invoice_id',$invoiceId);
		$data['productDetails'] = $this->db->get()->result();
		$data['office_location'] = $this->db->get_where('office_master',array('office_id'=>$office_id))->row();
		$data['paymenttype_details'] = $this->db->get_where('invoice_showroom_payment_mode_'.$office_id,array('invoice_id'=>$invoiceId))->result();
		
		//$this->load->view('inventory/sales_invoice_receipt',$data);
		$this->load->view('report/view_invoice_report',$data);
	}
	public function currentInventoryReport()
	{
		if($this->session->userdata('role_id') < '2')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Current Inventory Report";
		$postedArr  = $this->security->xss_clean($_POST);
		$office_id = $this->session->userdata('office_id');
		$data['all_products'] = $this->db->get('product_master')->result();
		$product_ids = array();
		$product_names = array();
		if(isset($postedArr['product_id']) && $postedArr['product_id'] != '')
		{
			$product_ids = $postedArr['product_id'];
		}
		else{
			foreach($data['all_products'] as $product)
			{
				$product_ids[] = $product->product_id;
			}
		}
		
		foreach($product_ids as $p)
		{
			$product_names[] = getProductName($p);
		}
		
		$data['printProduct'] = (isset($postedArr['product_id']) && $postedArr['product_id'] != '') ? implode(',',$product_names) : 'All';
		$data['productIds'] = (isset($postedArr['product_id']) && $postedArr['product_id'] != '') ? $postedArr['product_id'] : '';
		$data['product_Ids'] = $product_ids;
		
		$all_records = $this->Report_model->_get_current_inventory($office_id,$product_ids);
		$data['get_all_record'] = $all_records['result'];
		$data['totalWeight'] = $all_records['totalWeight'];
		$data['totalQty'] = $all_records['totalQty'];
		
		// echo '<pre>';
		// print_r($data);
		// echo '</pre>';
		// die;
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/current_inventory_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function searchCurrentSerialNumber()
	{
		if($this->input->is_ajax_request())
		{
			$office_id = $this->session->userdata('office_id');
			$serialNumber = $this->input->post('serialNumber');
			$product_ids = $this->input->post('product_ids');
			$this->Report_model->searchCurrentSerialNumber($office_id,$serialNumber,$product_ids);
		}
	}
	
	public function saleInventoryReport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sale & Inventory Report";
		$postedArr  = $this->security->xss_clean($_POST);
		//print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'others'))->get()->result();
		}
		// print_r($regionData);
		foreach($regionData as $region)
		{
			$regional_ids[] = 	$region->regional_store_id;
		}
		
		$region_names = array();
		$office_names = array();
		
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['fromDate']))
		{
			$fdate = explode('/',$postedArr['fromDate']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d', strtotime( ' -1 day' )).' 00:00:00';
		}
		if(isset($postedArr['toDate']))
		{
			$tdate = explode('/',$postedArr['toDate']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d', strtotime( ' -1 day' )).' 23:59:59';
		}
		
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		
		if(!empty($regional_ids)){
			foreach($regional_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		
	//	$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		
		if(isset($postedArr['toDate']))
		{
			$fdate = explode('/',$postedArr['toDate']);
			$reportDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0];
		}
		else{
			$reportDate = date('Y-m-d',strtotime('now'));
		}
		$report_Date = date( 'Y-m-d', strtotime( $reportDate . ' -1 day' ) );
		$report_Date2 = date( 'Y-m-d', strtotime( $reportDate ) );
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		$data['all_products'] = $productLists = $this->db->get('product_master')->result();
		$data['get_all_record']=$this->Report_model->_get_sold_and_inventory_report($report_Date,$fromDate,$toDate,$office_ids,$regional_ids);
		$data['reportDate'] = date('d/m/Y',strtotime($reportDate));
		$data['tbreportDate'] = date('d/m/Y',strtotime($reportDate));
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('report/sold_and_inventory_report',$data);
		$this->load->view('includes/_footer');
	}
	
	public function adminTransactionTaxReport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Tax Transaction Report";
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$_POST['region_id']=$user_role_data->regional_store_id;
			$_POST['submit']=true;
			if($this->input->post('access_right_from')=='' && $this->input->post('access_right_to')=='')
			{
				$_POST['access_right_from']=date('d/m/Y',strtotime('now'));
				$_POST['access_right_to']=date('d/m/Y',strtotime('now'));
			}
		}
		$postedArr  = $this->security->xss_clean($_POST);
		 //print_r($postedArr);die;
	   
	    $fromDate1 = date('d/m/Y',strtotime('now'));
		$toDate1 = date('d/m/Y',strtotime('now'));
		$region_names = array();
		$office_names = array();
		
		if($this->input->post('submit'))
		{
			 $type_transaction=$this->input->post('type_transaction');
			 $data['type_transaction']=$type_transaction;
			 
			$fromDate1 = $this->input->post('access_right_from');
			$toDate1 = $this->input->post('access_right_to');
			
			if($this->input->post('payment_mode'))
			{
			 $payment_mode=$this->input->post('payment_mode');
			 $data['payment_mode']=$payment_mode;
			}
			
			$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
			$region_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : "";
			$regions = array();
			if(!empty($office_ids)){
				foreach($office_ids as $office_id)
				{
					$office_names[] = getOfficeLocation($office_id);
				}
			}
			if(isset($postedArr['region_id']))
			{
				foreach($region_ids as $region_id)
				{
					$region_names[] = getRegionLocation($region_id);
					$regions[] = $region_id;
				}
			}
			
			$data['printPaymentMode'] = (isset($postedArr['payment_mode'])) ? implode(',',$postedArr['payment_mode']) : 'All';
			$data['printTransactionType'] = (isset($postedArr['type_transaction']) && $postedArr['type_transaction'] !='') ? $postedArr['type_transaction'] : 'All';
			$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
			$data['printRegion'] = (isset($postedArr['region_id'])) ? implode(',',$region_names) : "All";
		}
	    //$data['region_master']= $this->base_model->get_all_records('regional_store_master');
		$where_others=array();
		
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
			
		}
	    $data['region_master']= $this->db->get_where('regional_store_master',$where_others)->result();
	   // $data['transfer_to']= $this->db->where('office_id !=',1)->get('office_master')->result();
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		$this->db->where("office_master.office_operation_type",'showroom');
		
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$this->db->where('regional_store_master.regional_store_type','others');
			$this->db->where('regional_store_master.regional_store_id',$user_role_data->regional_store_id);
		}
		if(!empty($regions))
		{
			$this->db->where_in('office_master.regional_store_id',$regions);
		}
		$query = $this->db->get();
		$data['transfer_to']=$query->result();
		
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$data['fromDate'] = $fromDate1;
		$data['toDate'] = $toDate1;
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/admin_transaction_tax_region_report',$data);
		}
	    else
		{
			$this->load->view('report/admin_transaction_tax_report',$data);
		}
		$this->load->view('includes/_footer');
	}
	
	public function transactionTaxReport()
	{  
		if($this->session->userdata('role_id') != '3')
		{
			redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Transaction Report";
		$postedArr  = $this->security->xss_clean($_POST);
		 //print_r($postedArr);die;
	   
	    $fromDate1 = date('d/m/Y',strtotime('now'));
		$toDate1 = date('d/m/Y',strtotime('now'));
		$region_names = array();
		$office_names = array();
		
		if($this->input->post('submit'))
		{
			 $type_transaction=$this->input->post('type_transaction');
			 $data['type_transaction']=$type_transaction;
			 
			$fromDate1 = $this->input->post('access_right_from');
			$toDate1 = $this->input->post('access_right_to');
			
			if($this->input->post('payment_mode'))
			{
			 $payment_mode=$this->input->post('payment_mode');
			 $data['payment_mode']=$payment_mode;
			}
			
			$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
			$region_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : "";
			if(!empty($office_ids)){
				foreach($office_ids as $office_id)
				{
					$office_names[] = getOfficeLocation($office_id);
				}
			}
			if(isset($postedArr['region_id']))
			{
				foreach($region_ids as $region_id)
				{
					$region_names[] = getRegionLocation($region_id);
				}
			}
			
			$data['printPaymentMode'] = (isset($postedArr['payment_mode'])) ? implode(',',$postedArr['payment_mode']) : 'All';
			$data['printTransactionType'] = (isset($postedArr['type_transaction']) && $postedArr['type_transaction'] !='') ? $postedArr['type_transaction'] : 'All';
			$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
			$data['printRegion'] = (isset($postedArr['region_id'])) ? implode(',',$region_names) : "All";
		}
	    $data['region_master']= $this->base_model->get_all_records('regional_store_master');
	   // $data['transfer_to']= $this->db->where('office_id !=',1)->get('office_master')->result();
		$this->db->select("office_master.*,regional_store_master.regional_store_type");
		$this->db->from("office_master ","regional_store_master");
		$this->db->join('regional_store_master',"office_master.regional_store_id=regional_store_master.regional_store_id","LEFT");
		$this->db->where("office_master.office_id !=",1);
		$this->db->where("office_master.office_operation_type",'showroom');
		$query = $this->db->get();
		$data['transfer_to']=$query->result();
		
		$data['fromDate'] = $fromDate1;
		$data['toDate'] = $toDate1;
		
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
	    $this->load->view('report/transaction_tax_report',$data);
		$this->load->view('includes/_footer');
	}
	public function stock_transfer_recieve_inventory(){
if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
		
		$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id_cur=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id_cur))->row();
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		//$office_id=$this->session->userdata('office_id');
		// $toDate = date('Y-m-d',strtotime('now'));
		// $fromDate = date('Y-m-d',strtotime("-1 months"));
		$postedArr  = $this->security->xss_clean($_POST);
		 // print_r($postedArr);
		 
		 $fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['fromDate']))
		{
			$fdate = explode('/',$postedArr['fromDate']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d').' 00:00:00';
		}
		if(isset($postedArr['toDate']))
		{
			$tdate = explode('/',$postedArr['toDate']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d').' 23:59:59';
		}
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		 
		 
		 
		 $regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$where_others=array('regional_store_type'=>'others');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
		}
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where($where_others)->get()->result();
		}
		// print_r($regionData);
		foreach($regionData as $region)
		{
			$regional_ids[] = 	$region->regional_store_id;
		}
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
	
		
		if($postedArr['region_type'] == "all")
		{
			if($office_id == ''){
				//$officeDatas = $this->db->select('office_id,office_operation_type')->from('office_master')->where('office_operation_type','showroom')->get()->result();
				$officeDatas = $this->db->select('office_id,office_operation_type,office_name')->from('office_master')->where('office_id >','1')->get()->result();
			}
			else{
				$officeDatas[] = (object)array('office_id'=>$office_id,'office_operation_type'=>getOfficeOperationType($office_id));
			}
		}
		else if($postedArr['region_type'] == "mmtc")
		{
			if($office_id == ''){
				//$officeDatas = $this->db->select('office_id,office_operation_type')->from('office_master as om')->join('regional_store_master as reg','om.regional_store_id=reg.regional_store_id')->where(array('om.office_operation_type'=>'showroom','reg.regional_store_type'=>'mmtc'))->get()->result(); 
				
				$this->db->select('office_id,office_operation_type,office_name')->from('office_master as om')->join('regional_store_master as reg','om.regional_store_id=reg.regional_store_id')->where(array('om.office_id >'=>'1','reg.regional_store_type'=>'mmtc'));
				if(count($regional_ids)>0)
				{
					$this->db->where_in('om.regional_store_id',$regional_ids);
				}
				
				$officeDatas = $this->db->get()->result();
			}
			else{
				$officeDatas[] = (object)array('office_id'=>$office_id,'office_operation_type'=>getOfficeOperationType($office_id));
			}
		}
		else if($postedArr['region_type'] == "others")
		{
			if($office_id == ''){
				// $officeDatas = $this->db->select('office_id,office_operation_type')->from('office_master as om')->join('regional_store_master as reg','om.regional_store_id=reg.regional_store_id')->where(array('om.office_operation_type'=>'showroom','reg.regional_store_type'=>'others'))->get()->result();
				$this->db->select('office_id,office_operation_type,office_name')->from('office_master as om')->join('regional_store_master as reg','om.regional_store_id=reg.regional_store_id')->where(array('om.office_id >'=>'1','reg.regional_store_type'=>'others'));
				if(count($regional_ids)>0)
				{
					$this->db->where_in('om.regional_store_id',$regional_ids);
				}
				$officeDatas = $this->db->get()->result();
			}
			else{
				$officeDatas[] = (object)array('office_id'=>$office_id,'office_operation_type'=>getOfficeOperationType($office_id));
			}
		}
		
		
	//print_r($officeDatas);
		$region_names = array();
		$office_names = array();
		
		
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->schedule_office_location_list($regional_ids);
		 
		 $office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
				
			}
		}
		
		if(!empty($regional_ids)){
			foreach($regional_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
				
		//print_r($officeDatas);
		foreach($officeDatas as $of_data)
		{
			//echo $of_data->office_operation_type." ".$of_data->office_id;
			$tableNameSTOCKRECEIPT='inventory_'.$of_data->office_operation_type.'_stock_transfer_'.$of_data->office_id;	
		 $data['stock_receipt_details'][$of_data->office_id]=$this->Report_model->_get_all_record_stock_transfer_details_to_by_join($tableNameSTOCKRECEIPT,$of_data->office_operation_type,$of_data->office_id,$fromDate,$toDate);
		
		}
		
		$data['ofcDatas'] = $officeDatas;
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		 //print_r($data['stock_receipt_details']);
		
		
	
		/* if(empty($office_id) && $office_id==0){

		  $table_name='inventory_stock_transfer';
		  $table_name='product_current_stock';
				
		}else{ */
		 // $tableNamePRODUCTSTOCKRECEIPT='inventory_'.$office_operation_type.'_product_stock_receipt_'.$office_id;	
		 // $tableNamePRODUCTSTOCKRECEIPT='inventory_store_product_stock_receipt_'.$office_id;	
		  /* $table_name='product_current_stock_'.$office_id; */
		/* } */
      /*  $data['product_stock_receipt_details']=$this->inventory_model->_get_all_record_product_stock_transfer_details_to_by_join($tableNamePRODUCTSTOCKRECEIPT,$office_operation_type,$office_id);*/
	  
	    
	   //print_r($data['stock_receipt_details']);die;
	   
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$header['title'] = "Stock Transfer Inventory";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/stock_transfer_receive_inventory_region',$data);
		}
	    else
		{
		$this->load->view('report/stock_transfer_receive_inventory',$data);
		}
		$this->load->view('includes/_footer');
		
		
	}
	public function saleInventoryReportdetail()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
			$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		
		$header['title'] = "Sale & Inventory Report";
		$postedArr  = $this->security->xss_clean($_POST);
		// print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$where_others=array('regional_store_type'=>'others');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
		}
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where($where_others)->get()->result();
		}
		// print_r($regionData);
		foreach($regionData as $region)
		{
			$regional_ids[] = 	$region->regional_store_id;
		}
		
		$region_names = array();
		$office_names = array();
		
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->schedule_office_location_list($regional_ids);
		
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['fromDate']))
		{
			$fdate = explode('/',$postedArr['fromDate']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d', strtotime( ' -1 day' )).' 00:00:00';
		}
		if(isset($postedArr['toDate']))
		{
			$tdate = explode('/',$postedArr['toDate']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d', strtotime( ' -1 day' )).' 23:59:59';
		}
		
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		
		if(!empty($regional_ids)){
			foreach($regional_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		
	//	$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		
		if(isset($postedArr['toDate']))
		{
			$fdate = explode('/',$postedArr['toDate']);
			$reportDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0];
		}
		else{
			$reportDate = date('Y-m-d',strtotime('now'));
		}
		$report_Date = date( 'Y-m-d', strtotime( $reportDate . ' -1 day' ) );
		//$report_Date = date( 'Y-m-d', strtotime( $reportDate) );
		$report_Date2 = date( 'Y-m-d', strtotime( $reportDate ) );
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		$data['all_products'] = $productLists = $this->db->get_where('product_master',array('product_type_id'=>'1'))->result();
		$data['get_all_record']=$this->Report_model->_get_detail_sold_and_inventory_report($report_Date,$fromDate,$toDate,$office_ids,$regional_ids);
		
		$data['get_all_record_vendor']=$this->Report_model->_get_detail_sold_and_inventory_vendor_report($report_Date,$fromDate,$toDate,$office_ids,$regional_ids);
		$data['reportDate'] = date('d/m/Y',strtotime($reportDate));
		$data['tbreportDate'] = date('d/m/Y',strtotime($reportDate));
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$data['region_type_post']=$postedArr['region_type'];
		$data['region_id_post']=$postedArr['region_id'];
		$data['office_location_post']=$postedArr['office_location'];
		//echo $data['region_type_post']." ".$data['region_id_post']." ".$data['office_location_post'];
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/detail_sold_and_inventory_region_report',$data);
		}
	    else
		{
			$this->load->view('report/detail_sold_and_inventory_report',$data);
		}
		$this->load->view('includes/_footer');
	}
	public function salesallreport()
	{
		if($this->session->userdata('role_id') > '1')
		{
			redirect(base_url('user/dashboard'));
		}
			$user_id=$this->session->userdata('user_id');
			$role_permission_id=$this->session->userdata('role_permission_id');
			$office_id=$this->session->userdata('office_id');
			$user_role_data=$this->db->get_where('user_role_permission_master',array('user_id'=>$user_id,'role_permission_id'=>$role_permission_id,'office_id'=>$office_id))->row();
		
		$header['title'] = "Sale All Report";
		$postedArr  = $this->security->xss_clean($_POST);
		// print_r($postedArr);
		$regionData = $this->db->get('regional_store_master')->result();
		$regional_ids = array();
		$where_others=array('regional_store_type'=>'others');
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$postedArr['region_type']="others";
			$where_others=array('regional_store_type'=>'others','regional_store_id'=>$user_role_data->regional_store_id);
		}
		if($postedArr['region_type'] == "all"){
			$regionData = $this->db->select('*')->from('regional_store_master')->get()->result();
		}
		else if($postedArr['region_type'] == "mmtc"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where(array('regional_store_type'=>'mmtc'))->get()->result();
		}
		else if($postedArr['region_type'] == "others"){
			$regionData = $this->db->select('*')->from('regional_store_master')->where($where_others)->get()->result();
		}
		 //print_r($regionData);
		foreach($regionData as $region)
		{
			$regional_ids[] = 	$region->regional_store_id;
		}
		
		$region_names = array();
		$office_names = array();
		
		$regional_ids = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? $postedArr['region_id'] : $regional_ids;
		$data['regionLists'] = $regionData;
		$data['transfer_to']=$this->inventory_model->showroom_office_location_list($regional_ids);
		
		$fromDate = '';
		$toDate = '';
		
		if(isset($postedArr['fromDate']))
		{
			$fdate = explode('/',$postedArr['fromDate']);
			$fromDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0].' 00:00:00';
		}
		else{
			$fromDate = date('Y-m-d', strtotime( ' -1 day' )).' 00:00:00';
		}
		if(isset($postedArr['toDate']))
		{
			$tdate = explode('/',$postedArr['toDate']);
			$toDate = $tdate[2].'-'.$tdate[1].'-'.$tdate[0].' 23:59:59';
		}
		else{
			$toDate = date('Y-m-d', strtotime( ' -1 day' )).' 23:59:59';
		}
		
		$office_ids = (isset($postedArr['office_location'])) ? $postedArr['office_location'] : '';
		if(!empty($office_ids)){
			foreach($office_ids as $office_id)
			{
				$office_names[] = getOfficeLocation($office_id);
			}
		}
		
		if(!empty($regional_ids)){
			foreach($regional_ids as $region_id)
			{
				$region_names[] = getRegionLocation($region_id);
			}
		}
		
		$data['printOffice'] = (isset($postedArr['office_location'])) ? implode(',',$office_names) : 'All';
		$data['printRegion'] = (isset($postedArr['region_id']) && $postedArr['region_id'] !='') ? implode(',',$region_names) : "All";
		
	//	$data['get_all_record']=$this->Report_model->_get_sales_report($fromDate,$toDate,$office_ids,$regional_ids);
		
		if(isset($postedArr['toDate']))
		{
			$fdate = explode('/',$postedArr['toDate']);
			$reportDate = $fdate[2].'-'.$fdate[1].'-'.$fdate[0];
		}
		else{
			$reportDate = date('Y-m-d',strtotime('now'));
		}
		$report_Date = date( 'Y-m-d', strtotime( $reportDate . ' -1 day' ) );
		//$report_Date = date( 'Y-m-d', strtotime( $reportDate) );
		$report_Date2 = date( 'Y-m-d', strtotime( $reportDate ) );
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		$data['all_products'] = $productLists = $this->db->get_where('product_master',array('product_type_id'=>'1'))->result();
		$data['get_all_record']=$this->Report_model->_get_sales_all_report($report_Date,$fromDate,$toDate,$office_ids,$regional_ids);
		$data['reportDate'] = date('d/m/Y',strtotime($reportDate));
		$data['tbreportDate'] = date('d/m/Y',strtotime($reportDate));
		// echo '<pre>';
		// print_r($data['get_all_record']);
		// echo '</pre>';
		$data['regional_store_id'] = $user_role_data->regional_store_id;
		$this->load->view("includes/_header",$header);
        $this->load->view("includes/_top_menu");
		if(!empty($user_role_data) && $user_role_data->regional_store_id>0)
		{
			$this->load->view('report/sales_all_region_report',$data);
		}
	    else
		{
		$this->load->view('report/sales_all_report',$data);
		}
		$this->load->view('includes/_footer');
	}
}
