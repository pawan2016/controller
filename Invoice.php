<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
//require_once('tcpdf/tcpdf.php');
class Invoice extends CI_Controller {
    public function __construct() {
	
       parent::__construct();
	   sessionExist();
		if(!$this->session->userdata('is_logged_in'))
		{
			redirect('Login/index','refresh');
		}
        $this->load->model('inventory_model');	
		
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');		
	}
public function saveInvoiceData()
{
if($_POST){

				$office_id = $this->session->userdata('office_id');
			    $errors=array();
				if(empty($_POST['invoice_date'])) {
				$errors['invoice_date'] = 'Date and time of Invoice is required';
				}
				
				if(empty($_POST['invoice_type'])) {
				$errors['invoice_type_chosen'] = 'Please select invoice type';
				}
				
				/*if(empty($_POST['customer_code'])) {
				$errors['customer_code'] = 'Customer Code required';
				}*/
				/*if(empty($_POST['customer_phone_number'])) {
				$errors['customer_phone_number'] = 'Customer Phone required';
				}
				*/
				 $invoice_id=base64_decode($_POST['invoice_id']);
				 if($_POST['received_amount'] < $_POST['total_net_amount'] && ($_POST['invoice_type']!='advance' or $invoice_id!=''))
					{
						$errors['received_amount'] = 'Received amount should not less than Net Amount';
					}
		      if(!isset($_POST['round_off']) || $_POST['round_off']=="")
				 {
					if($_POST['received_amount'] < $_POST['total_net_amount'] && ($_POST['invoice_type']!='advance' or $invoice_id!=''))
					{
						$errors['received_amount'] = 'Received amount should not less than Net Amount';
					}
				 }
				 else{
					 if(($_POST['round_off']>="-10") && ($_POST['round_off']<=10))
					 {
						 
					 }else{
						 $errors['round_off'] = 'Round off value should be greter than -10 and less then 10';
					 }
					 
				 } 
				 
				 /* foreach($_POST['count_check'] as $key_total=>$value_check_total)
				  {
				 	foreach($_POST['payment_mode_hidden'] as $key=>$value_check)
				  {
				
					  if($value_check=='cheque'){
					 if($_POST['cheque_relese'][$key] =='select'){
						$errors['cheque_relese_'.($value_check_total)] = 'Cheque Realization is required';		
						
					  }
				  }
					  
				  }
				}  */
				 
				foreach($_POST['payment_mode'] as $key=>$value)
				{
					//$value=='cash' &&
					if( $_POST['payment_mode_amount'][$key]>=200000){
				//if(empty($_POST['customer_pan_number']) && $_POST['total_net_amount']>=200000) 
					if(empty($_POST['customer_pan_number']))
					{
				     $errors['customer_pan_number'] = 'PAN Number required';
					}
					else{
						if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $_POST['customer_pan_number'])) {
						   $errors['customer_pan_number'] = "Invalid pan number";
						}
					}
				  }else{
					  
					  //&& $value=='cash' 
					if(empty($_POST['id_proof']) && isset($_POST['id_proof']) &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof'] = 'ID Proof required';
					}
					//&& $value=='cash'
					if(empty($_POST['id_proof_number'])  &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof_number'] = 'ID Proof Number required';
					}
				  }
				}
				
				
				//$arr_selected_product_divs=explode(",",$_POST['selected_products_divs']);
				//print_r($arr_selected_product_divs);
				$flag_exist_serial=0;
				foreach($_POST['product_id'] as $key=>$value)
				{
					//$err_div=$arr_selected_product_divs[$key];
					  if(empty($value)){
						$errors['product_id_'.$key.'_chosen'] = 'Product Name is required';		
						
					  }
					  if(empty($_POST['quantity'][$key])){
						$errors['qty-'.$key] = 'Qty is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['quantity'][$key])) {
							$errors['qty-'.$key] = 'Qty accept only number';
						}
						}
						if($_POST['received_amount'] >= $_POST['total_net_amount'])
						{
							$quantity=$_POST['quantity'][$key];
				//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
							$serial_number=$_POST['serial_number_'.$key];
							if($quantity!=count($serial_number))
							{
							$errors['serial_number_'.$key.'_chosen'] = 'Please select '.$quantity.' serial number';
							}
						}
						$invoice_product_serial_number=$_POST['serial_number_'.$key];
						foreach($invoice_product_serial_number as $serial_number){
							
							$arr_serial=$this->db->get_where('product_current_stock_serial_number_'.$office_id,array('product_id'=>$value,'current_stock_status!='=>'0','product_serial_number'=>$serial_number))->result();
							if(count($arr_serial)>0)
							{
							$flag_exist_serial=1;
							break;
							}
						
						}
						if($flag_exist_serial==1)
						{
							if($errors['serial_number_'.$key.'_chosen']=='')
							{
								$errors['serial_number_'.$key.'_chosen'] = 'Exists|||'.$key;
							}
							
						}
						
					  
				}
			//	$arr_selected_payment_divs=explode(",",$_POST['selected_payment_divs']);
				//print_r($arr_selected_payment_divs);
				$already_paid=0;
				if(isset($_POST['already_paid']) && $_POST['already_paid']!='')
				{
					$already_paid=$_POST['already_paid'];
				}
				//print_r($_POST['payment_mode']);die;
				foreach($_POST['payment_mode'] as $key=>$value)
				{
					//$err_div=$arr_selected_payment_divs[$key];
					  if(empty($value)){
						$errors['payment_mode_'.($key+$already_paid).'_chosen'] = 'Payment mode is required';		
						
					  }
					   if($value=='credit card' || $value=='debit card')
					   {
						  if(empty($_POST['card_check_name'][$key]))
							  {
								 
								   $errors['card_check_name_'.($key+$already_paid)] = 'Card name is required';				
								   			
							  }
							  
							     if(empty($_POST['card_issuing_bank'][$key]))
							  {
								 
								 $errors['card_issuing_bank_'.($key+$already_paid)] = 'Issuing bank number is required';	
							  }
					   }
					  if(empty($_POST['payment_mode_amount'][$key])){
						$errors['payment_mode_amount_'.($key+$already_paid)] = 'Amount is required';				
					  }else{
						/* if(!preg_match('/^[0-9]+$/i', $_POST['payment_mode_amount'][$key])) {
							$errors['payment_mode_amount_'.$key] = 'Amount accept only number';
						} */
						}
					  if($value=='credit card' || $value=='debit card' || $value=='cheque')
					  {
					  if(empty($_POST['card_check_number'][$key])){
						$errors['card_check_number_'.($key+$already_paid)] = '  '.$value.' number is required';				
					  }
					   if(empty($_POST['card_issuing_bank'][$key])){
						$errors['card_issuing_bank_'.($key+$already_paid)] = 'Issuing bank number is required';				
					  }
					  
					  
					  elseif($value=='credit card' || $value=='debit card')
					  {
						  if(strlen($_POST['card_check_number'][$key])>4)
						  {
							  $errors['card_check_number_'.($key+$already_paid)] = 'Please enter last 4 digit of '.$value.' number';				
						  }
					  }
					}
					 if($value=='cheque'){
					 if($_POST['cheque_relese'][$key] =='select'){
						$errors['cheque_relese_'.($key+$already_paid)] = 'Cheque Realization is required';		
						
					  }
				  }
				}
				
				
				if(count($errors) > 0){
				//This is for ajax requests:
				$errors['msg']='error';
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
		$office_id = $this->session->userdata('office_id');
		//$invoice_number = $this->input->post('invoice_number');
		$invoice_date = $this->input->post('invoice_date');
		$customer_code = $this->input->post('customer_code');
		
		$customer_name = $this->input->post('customer_name');
		$customer_address = $this->input->post('customer_address');
		$customer_phone_number = $this->input->post('customer_phone_number');
		$customer_email_id = $this->input->post('customer_email_id');
		$customer_pan_number = $this->input->post('customer_pan_number');
		$customer_transaction_id = $this->input->post('customer_transaction_id');
		$id_proof = $this->input->post('id_proof');
		$id_proof_number = $this->input->post('id_proof_number');
		$state = $this->input->post('state');
			$district = $this->input->post('district');
			$city = $this->input->post('city');
			$modal_customer_pincode = $this->input->post('modal_customer_pincode');
		//$customerData = $this->base_model->get_record_by_id('customer_master',array('modal_customer_short_name'=>$customer_code));
if($invoice_id=='')
{
		
$customerData = $this->base_model->get_record_by_id('customer_master',array('modal_customer_phone_number'=>$customer_phone_number,'modal_customer_phone_number!='=>''));
$customerInsertData = array('modal_customer_name'=>$customer_name,
									'modal_customer_address' => $customer_address,
									'modal_customer_phone_number' => $customer_phone_number,
									'modal_customer_email_id' => $customer_email_id,
									'modal_customer_pan_number' => $customer_pan_number,
									'id_proof' => $id_proof,
									'state' => $state,
									'district' => $district,
									'city' => $city,
									'modal_customer_pincode' => $modal_customer_pincode,
									'id_proof_number' => $id_proof_number);
		if(empty($customerData)){
			
			$customerData_mob = $this->base_model->get_record_by_id('customer_master',array('modal_customer_mobile_number'=>$customer_phone_number,'modal_customer_mobile_number!='=>''));
			if(empty($customerData_mob)){
				if(!empty($customer_name) || !empty($customer_address) || !empty($customer_phone_number) || !empty($customer_email_id) || !empty($customer_pan_number) || !empty($id_proof) || !empty($id_proof_number))
				{
				$customerInsertData['createdOn'] = date('Y-m-d H:i:s');
				$this->base_model->insert_one_row('customer_master',$customerInsertData);
				$customer_id  = $this->db->insert_id();
				}
				else
				{
				$customer_id='0';	
				}
		
				}
				else
				{
					$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData_mob->customer_id));
					$customer_id = $customerData_mob->customer_id;
				}
				}
		else
		{
		$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData->customer_id));
		$customer_id = (isset($customerData->customer_id)) ? $customerData->customer_id : '0';
		}
}
		$product_id = $this->input->post('product_id');
		//$serial_number = $this->input->post('serial_number');
		$quantity = $this->input->post('quantity');
		$invoice_type = $this->input->post('invoice_type');
		$showrom_invoice_narrative = $this->input->post('showrom_invoice_narrative');
		$customer_transaction_id = $this->input->post('customer_transaction_id');
		$weight = $this->input->post('weight');
		$rate_per_quantity = $this->input->post('rate_per_quantity');
		$discount_percent = $this->input->post('discount_percent');
		$tax = $this->input->post('tax');
		$entryTax = $this->input->post('entry_tax');
		$net_amount = $this->input->post('net_amount');
		$total_amount = $this->input->post('total_amount');
		$surcharge_on_vat = ($this->input->post('surcharge_on_vat')) ? $this->input->post('surcharge_on_vat') : '0';
		$total_net_amount = $this->input->post('total_net_amount');
		$received_amount = $this->input->post('received_amount');
		$round_off=($this->input->post('round_off')) ? $this->input->post('round_off') : '0';
		$amount_refunded = ($this->input->post('amount_refunded')) ? $this->input->post('amount_refunded') : '0';
$invoiceId=$invoice_id;
	if($invoice_id=='')
{	
		$invoiceInsertData = array(
									'invoice_date'=>$invoice_date,
									'invoice_type'=>$invoice_type,
									'showrom_invoice_narrative'=>$showrom_invoice_narrative,
									'customer_transaction_id'=>$customer_transaction_id,
									'customer_id' => $customer_id,
									'total_amount' => $total_amount,
									'surcharge_on_vat' => $surcharge_on_vat,
									'amount_received' => $received_amount,
                                    'adjustment'=>$round_off,
									'amount_refunded' =>$amount_refunded,
									'creator_id' => $this->session->userdata('user_id'),
									'createdOn' => date('Y-m-d H:i:s'),									
									);
		$this->base_model->insert_one_row('invoice_showroom_'.$office_id,$invoiceInsertData);
		$invoiceId = $this->db->insert_id();
		$invoice_number = 1;
		$tableName = "inventory_".$office_operation_type."_product_stock_receipt_".$office_id;
		
		$financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
		
		$financialSecondYear = $financialFirstYear+1;
		$financialYear = $financialFirstYear.'-'.$financialSecondYear;
		
		$getTableNameData = $this->db->select('invoice_id')->from('invoice_showroom_'.$office_id)->like('invoice_number',"$financialYear","before")->get();
		
		if(!empty($getTableNameData)){
			//$autoGenerateNumber = $getTableNameData->product_stock_receipt_id + 1;
			$invoice_number = $getTableNameData->num_rows() + 1;
		}
		$invoice_number = str_pad($invoice_number,6,"0", STR_PAD_LEFT);
		
		$officeData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			
			// office_short_code
			
			$officeUniqueName = $officeData->office_short_code;
			// $data = $this->db->select_max('invoice_id')->get('invoice_showroom_'.$office_id)->row();
			// if(!empty($data)){
				// $invoice_number = $data->invoice_id + 1;
			// }
		//	$invoice_number = str_pad($invoiceId,6,"0", STR_PAD_LEFT);
			//$financialYear = date('y').'-'.(date('y') + 1);
			// $financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
			// $financialSecondYear = $financialFirstYear+1;
			// $financialYear = $financialFirstYear.'-'.$financialSecondYear;
			//$invoiceNumber = $officeUniqueName.'/INV/'.$invoice_number.'/'.$financialYear;
			if($_POST['invoice_type']=='advance')
			{
			 $invoiceNumber = $officeUniqueName.'/AP/'.$invoice_number.'/'.$financialYear;
			}else{
				 $invoiceNumber = $officeUniqueName.'/INV/'.$invoice_number.'/'.$financialYear;
			}
			
			$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('invoice_number'=>$invoiceNumber),array('invoice_id'=>$invoiceId));
		
			$totalProduct = count($product_id);
			
			$invoice_product_id=array();
			foreach($product_id as $key=>$value_product)
			{
				//implode(',',$invoice_product_serial_number)
				
					$productInsertData = array('invoice_id' =>$invoiceId,
											'product_id' =>$value_product,
											'serial_number' => '',
											'weight' => $weight[$key],
											'qunatity' => $quantity[$key],
											'rate' => $rate_per_quantity[$key],
											'discount' => $discount_percent[$key],
											'tax' => $tax[$key],
											'entry_tax' => $entryTax,
											'net_amount' => $net_amount[$key],
											'creator_id' => $this->session->userdata('user_id'),
											'createdOn' => date('Y-m-d H:i:s'),
										);
				$this->base_model->insert_one_row('invoice_showroom_product_'.$office_id,$productInsertData);
				
				$invoice_product_id[$value_product] = $this->db->insert_id();
			}
}
else{
	$arr_result_product=$this->db->get_where('invoice_showroom_product_'.$office_id,array('invoice_id'=>$invoiceId))->result();
	foreach($arr_result_product as $pro_primary)
	{
		$invoice_product_id[$pro_primary->product_id] = $pro_primary->invoice_product_id;
	}
	
	//$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('amount_received'=>$received_amount,'surcharge_on_vat' => $surcharge_on_vat),array('invoice_id'=>$invoiceId));
	$round_off=($this->input->post('round_off')) ? $this->input->post('round_off') : '0';
    $amount_refunded = ($this->input->post('amount_refunded')) ? $this->input->post('amount_refunded') : '0';
	$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('adjustment'=>$round_off,'amount_refunded' =>$amount_refunded,'showrom_invoice_narrative'=>$showrom_invoice_narrative,'customer_transaction_id'=>$customer_transaction_id),array('invoice_id'=>$invoiceId));
}
//condition for advance mode
	
if($received_amount>=$total_net_amount) 
{
		
        $payment_mode_array=$this->input->post("payment_mode");
	    $cheque_relese=$this->input->post("cheque_relese");
	    $payment_mode_hidden=$this->input->post("payment_mode_hidden");
		 $invoice_payment_id=$this->input->post("invoice_payment_id");
		
		$conbine_array=array_combine($invoice_payment_id,$cheque_relese);
		$payment_mode_count=0;
        $cheque_relese_count=0;
		foreach($payment_mode_hidden as $payment_hidden)
		{
			if($payment_hidden=='cheque')
			{
				$payment_mode_count++;
			}
			
		}
		foreach($payment_mode_array as $payment_mode)
		{
			if($payment_mode=='cheque')
			{
				$payment_mode_count++;
			}
		}
		foreach($cheque_relese as $cheque_relese_data)
		{
			if($cheque_relese_data=="1")
			{
			  $cheque_relese_count++;
			}
		}
		//echo ($payment_mode_count);
		//echo $cheque_relese_count;die;
		if($payment_mode_count==$cheque_relese_count){
			foreach($product_id as $key=>$value_product)
			{
				
					
				$table_name_product_current='product_current_stock_'.$office_id;
				
				$invoice_product_serial_number = $_POST['serial_number_'.$key];
				
				$inventory_stock_product_current_stock=$this->base_model->get_record_by_id($table_name_product_current,array('product_id'=>$value_product));
				if(!empty($inventory_stock_product_current_stock)){
					
					$new_stock=($inventory_stock_product_current_stock->product_current_stock)-($quantity[$key]);
					$product_current_stock=array('product_current_stock'=>$new_stock);
					$this->base_model->update_record_by_id($table_name_product_current,$product_current_stock,array('product_id'=>$value_product));
					
				}else{
					
					$product_current_stock=array('product_id'=>$value_product,'product_current_stock'=>$quantity[$key],'createdOn'=>date('Y-m-d H:i:s'));		
					$this->base_model->insert_one_row($table_name_product_current,$product_current_stock);
					
				}
				
				$arr_invoice_data=$this->db->get_where('invoice_showroom_'.$office_id,array('invoice_id'=>$invoiceId))->row();
	
		
							$history_table = 'inventory_office_history_'.$office_id;
							$this->db->select('*');
							$this->db->order_by('history_id','desc')->limit('1');
							$arr_his_data=$this->db->get_where($history_table,array('product_id'=>$value_product))->row();
							
							
							$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value_product,'transfer_stock'=>$quantity[$key],'net_stock'=>($arr_his_data->net_stock-$quantity[$key]),'type_value'=>'customer','transfer_to'=>$arr_invoice_data->customer_id,'transaction_number'=>$arr_invoice_data->invoice_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s')); 
							//print_r($historyData);die;
							$this->base_model->insert_one_row($history_table,$historyData);
			
				
				foreach($invoice_product_serial_number as $serial_number){
					$insert_invoice_serial_data = array('invoice_id'=>$invoiceId,'invoice_product_id'=>$invoice_product_id[$value_product],'serial_number'=>$serial_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn' => date('Y-m-d H:i:s'));

					$this->base_model->insert_one_row('invoice_showroom_product_serial_number_'.$office_id,$insert_invoice_serial_data);

					$update_current_stock_serial_data = array('current_stock_status'=>'1');
					$this->base_model->update_record_by_id('product_current_stock_serial_number_'.$office_id,$update_current_stock_serial_data,array('product_serial_number'=>$serial_number));
				}
			}
			if($invoice_id!='')
				{
					foreach($conbine_array as $keydata=>$valuedata)
					{
					 $this->base_model->update_record_by_id('invoice_showroom_payment_mode_'.$office_id,array('cheque_release'=>$valuedata),array('invoice_payment_id'=>$keydata));	
					}
				}	
			$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('transaction'=>'completed'),array('invoice_id'=>$invoiceId));
		}else{
			
			if($invoice_id!='')
				{
					foreach($conbine_array as $keydata=>$valuedata)
					{
					 $this->base_model->update_record_by_id('invoice_showroom_payment_mode_'.$office_id,array('cheque_release'=>$valuedata),array('invoice_payment_id'=>$keydata));	
					}
				}	
			$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('transaction'=>'incomplete'),array('invoice_id'=>$invoiceId));
		}
		
}
else{
	if($invoice_id!='')
				{
					foreach($conbine_array as $keydata=>$valuedata)
					{
					 $this->base_model->update_record_by_id('invoice_showroom_payment_mode_'.$office_id,array('cheque_release'=>$valuedata),array('invoice_payment_id'=>$keydata));	
					}
				}	
	$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('transaction'=>'incomplete'),array('invoice_id'=>$invoiceId));
}
		$payment_mode_amount = $this->input->post('payment_mode_amount');
		$payment_mode = $this->input->post('payment_mode');
		$card_check_number = $this->input->post('card_check_number');
		$card_check_name = $this->input->post('card_check_name');
		$card_issuing_bank = $this->input->post('card_issuing_bank');
		
		$cheque_relese=($this->input->post("cheque_relese"));
		//print_r($cheque_relese); die;
		foreach($payment_mode as $key=>$pm)
		{
            //$cheque_release_value = ($cheque_relese[$key] == '') ? '0' : $cheque_relese[$key];
		$invoicePaymentModeData = array('invoice_id'=>$invoiceId,
									'payment_type' => $pm,
									'payment_amount' => $payment_mode_amount[$key],
									'card_cheque_number' => $card_check_number[$key],
									'bank_name'=>$card_check_name[$key],
									'card_issuing_bank'=>$card_issuing_bank[$key],
									'cheque_release'=>$cheque_relese[$key],
									'creator_id' => $this->session->userdata('user_id'),
									'createdOn' => date('Y-m-d H:i:s'),									
									);
		$this->base_model->insert_one_row('invoice_showroom_payment_mode_'.$office_id,$invoicePaymentModeData);
		
		}
         if($invoiceId!="")
		 {
			 $data['paymenttype_details'] = $this->db->get_where('invoice_showroom_payment_mode_'.$office_id,array('invoice_id'=>$invoiceId))->result();
			$total_received_till=0;
			foreach($data['paymenttype_details'] as $payment_types) 
			{
			  $total_received_till=$total_received_till+$payment_types->payment_amount;
			}
			$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('amount_received'=>$total_received_till,'surcharge_on_vat' => $surcharge_on_vat),array('invoice_id'=>$invoiceId));
			 
		 }
		echo json_encode(array('msg'=>'success','invoice_id'=>base64_encode($invoiceId)));
		
		exit;
		/*sales_invoice_receipt(base64_encode($invoiceId));*/
		}
		//redirect(base_url('invoice/sales_invoice_receipt/'.$invoiceId));
	}
	
	}
	public function sales_invoice_edit()
	{
		
		$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		}else
		{

		$page_id = 27;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		//print_r($page_permission_array);die;
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		}
		if($view_value == "0")
		{
				$this->session->set_flashdata("error_message","You don't have permission to view.");
				redirect(base_url('user/dashboard'));
		}
		if($add_value == "0")
		{
				$this->session->set_flashdata("error_message","You don't have permission to create invoice.");
				redirect(base_url('user/dashboard'));
		}
	
		$header['title'] = "Edit Sales Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$office_id = $this->session->userdata('office_id');
		$invoice_id = ($this->input->get('invoice_id')) ? base64_decode($this->input->get('invoice_id')) : '';
		
		
		$this->db->select('showroom.*,cust.modal_customer_name,cust.modal_customer_email_id,cust.modal_customer_pan_number,cust.modal_customer_address,cust.id_proof,cust.id_proof_number,cust.modal_customer_phone_number')->from('invoice_showroom_'.$office_id.' as showroom');
		$this->db->join('customer_master as cust','showroom.customer_id=cust.customer_id','left')->where('invoice_id',$invoice_id);
		$data['invoice_details']=$this->db->get()->row();
		
		if(empty($data['invoice_details']))
		{
			$data['heading']='Invoice Not Found';
			$data['message']='No Record Found';
			$this->load->view('errors/html/error_404',$data);
		}
		else
		{
			$this->db->select('showroom_pro.*,product_master.product_name')->from('invoice_showroom_product_'.$office_id.' as showroom_pro');
			$this->db->join('product_master','showroom_pro.product_id=product_master.product_id')->where('invoice_id',$invoice_id);
			$data['invoice_products']=$this->db->get()->result();
			
			$data['invoice_payment']=$this->db->get_where('invoice_showroom_payment_mode_'.$office_id,array('invoice_id'=>$invoice_id))->result();
			$this->load->view('inventory/sales_invoice_edit',$data);
		}
		$this->load->view('includes/_footer');
	}
	
    public function sales_invoice_form(){
		
		
		$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		}else
		{

		$page_id = 27;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		//print_r($page_permission_array);die;
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		}
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
		if($add_value == "0")
		{
				$this->session->set_flashdata("error_message","You don't have permission to create invoice.");
				redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Sales Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		
		$office_id = $this->session->userdata('office_id');
		$data['state_master'] = $this->base_model->get_all_records('state_master');
		$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
		$product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>date('d/m/Y')));
		$todaysProducts = array();

		foreach($product_today_price_master as $todaysProduct){
			$todaysProducts[] = $todaysProduct->product_id;
		}
		$data['todaysProducts'] = $todaysProducts;
		
		$this->load->view('inventory/sales_invoice_form',$data);
		$this->load->view('includes/_footer');
    }
	
	public function back_date_invoice_form(){
		$header['title'] = "Back Date Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/back_date_invoice_form');
		$this->load->view('includes/_footer');
    }
	
	public function sales_return_form(){
		$this->load->view("includes/_header");
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/sales_return_form');
		$this->load->view('includes/_footer');
    }
	
	public function add_customer_info(){
		// print_r($_POST);
		//$this->form_validation->set_rules('modal_customer_name','Customer name', 'required|alpha_dash_dot');
		//$this->form_validation->set_rules('modal_customer_short_name','Customer short name', 'required|alpha_numeric|is_unique[customer_master.modal_customer_short_name]');
		//$this->form_validation->set_rules('modal_customer_email_id','Customer email-id', 'required|is_unique[customer_master.modal_customer_email_id]|valid_email');
		
		/*$this->form_validation->set_rules('modal_customer_phone_number','Customer phone', 'required');
		$this->form_validation->set_rules('modal_customer_mobile_number','Customer mobile', 'required');*/
		//$this->form_validation->set_rules('modal_customer_pan_number','Customer pan number', 'required|alpha_dash_slash');
		$errors=array();
		if(empty($_POST['modal_customer_phone_number']) && empty($_POST['modal_customer_mobile_number']))
		{
		$this->form_validation->set_rules('modal_customer_mobile_number','Customer mobile', 'required');
		$errors['Error']=array('modal_customer_mobile_number'=>'Customer mobile is Required');
		}
		
		
		//$this->form_validation->set_rules('modal_customer_address','Customer address', 'required');
		/*$this->form_validation->set_rules('state_id','Customer state location', 'required');*/
		/*$this->form_validation->set_rules('district_id','Customer district location', 'required');*/
		/*$this->form_validation->set_rules('city_id','Customer city location', 'required');
		$this->form_validation->set_rules('modal_customer_pincode','Customer pincode', 'required|is_numeric|exact_length[6]');*/
		
		if (empty($errors))
		{
			//print_r($_POST);die;
			$table = 'customer_master';
			$modal_customer_name = $this->input->post('modal_customer_name');
			//$modal_customer_short_name = $this->input->post('modal_customer_short_name');
			$id_proof = $this->input->post('id_proof');
			$modal_customer_short_name = $this->input->post('modal_customer_short_name');
			$modal_customer_phone_number = $this->input->post('modal_customer_phone_number');
			$modal_customer_mobile_number = $this->input->post('modal_customer_mobile_number');
			$modal_customer_pan_number = $this->input->post('modal_customer_pan_number');
			$modal_customer_email_id = $this->input->post('modal_customer_email_id');
			$modal_customer_address = $this->input->post('modal_customer_address');
			$state_id = $this->input->post('state_id');
			$district_id = $this->input->post('district_id');
			$city_id = $this->input->post('city_id');
			
			$state = $this->input->post('state');
			$district = $this->input->post('district');
			$city = $this->input->post('city');
			$id_proof = $this->input->post('proof_data');
			$id_proof_number = $this->input->post('id_number');
			$user_id = $this->session->userdata('user_id');
			$modal_customer_pincode = $this->input->post('modal_customer_pincode');
			
			$createdOn = date('Y-m-d h:i:s');
/*'modal_customer_short_name' => $modal_customer_short_name,*/
				$insertData = array('modal_customer_name' => $modal_customer_name,
									
									'modal_customer_pan_number' => $modal_customer_pan_number,
									'modal_customer_phone_number' => $modal_customer_phone_number,
									'modal_customer_mobile_number' => $modal_customer_mobile_number,
									'modal_customer_email_id' => $modal_customer_email_id,
									'modal_customer_address' => $modal_customer_address,
									'state' => $state,
									'district' => $district,
									'city' => $city,
									'id_proof' => $id_proof,
									'id_proof_number' => $id_proof_number,
									'modal_customer_pincode' => $modal_customer_pincode,
									'creator_id' => $user_id,
									'createdOn' => $createdOn,
								);
				
				$this->base_model->insert_one_row($table,$insertData);
				echo '|||1';
		}
		else{
		/*	$errors['Error'] = array(
									 'modal_customer_name' => form_error('modal_customer_name'),
									'modal_customer_short_name' => 'Customer code is required.',
									'modal_customer_email_id' => form_error('modal_customer_email_id'),
									'modal_customer_phone_number' => form_error('modal_customer_phone_number'),
									
									 'modal_customer_mobile_number' => form_error('modal_customer_mobile_number'),
									 'modal_customer_pan_number' => form_error('modal_customer_pan_number'),
									'modal_customer_address' => form_error('modal_customer_address'),
									'state_id' => form_error('state_id'),
									'district_id' => form_error('district_id'),
									'city_id' => form_error('city_id'),
									'modal_customer_pincode' => form_error('modal_customer_pincode'), 
									);*/
			echo '|||'.json_encode($errors);
		}
	
	}

	public function getInvoiceNumber()
	{
		$invoice_number = 1;
		if($this->input->is_ajax_request()){
			$office_id = $this->input->post('office_id');
			$officeData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			
			// office_short_code
			
			$officeUniqueName = $officeData->office_short_code;
			$data = $this->db->select_max('invoice_id')->get('invoice_showroom_'.$office_id)->row();
			if(!empty($data)){
				$invoice_number = $data->invoice_id + 1;
			}
			$invoice_number = str_pad($invoice_number,6,"0", STR_PAD_LEFT);
			//$financialYear = date('y').'-'.(date('y') + 1);
			$financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');
			$financialSecondYear = $financialFirstYear+1;
			$financialYear = $financialFirstYear.'-'.$financialSecondYear;
			$invoiceNumber = $officeUniqueName.'/SI/'.$invoice_number.'/'.$financialYear;
		}
		echo $invoiceNumber;
	}
	
	public function getCustomerAutoSearch()
	{
		$term=$_GET['term'];
		$customer_master = $this->db->get_where('customer_master',array('modal_customer_phone_number like '=>'%'.$term.'%'))->result();
		$customers = array();
		foreach($customer_master as $customer){
			$label = $customer->modal_customer_phone_number;
			array_push($customers, array("customer_id" => $customer->customer_id, "label" => $label, "custome_name" => $customer->modal_customer_name));
		}
		$customer_master = $this->db->get_where('customer_master',array('modal_customer_mobile_number like '=>'%'.$term.'%'))->result();
		foreach($customer_master as $customer){
			$label = $customer->modal_customer_mobile_number;
			array_push($customers, array("customer_id" => $customer->customer_id, "label" => $label, "custome_name" => $customer->modal_customer_name));
		}
		echo json_encode($customers);
	}
	
	public function getCustomerInfo()
	{
		if($this->input->is_ajax_request()){
			$customer_id = $this->input->post('customer_id');
			$customerData = $this->base_model->get_record_by_id('customer_master',array('customer_id'=>$customer_id));
		}
		echo json_encode($customerData);
	}

	public function getPriceByProduct()
	{
		$price_rate = '';
		$office_id = $this->session->userdata('office_id');
		$data = '';
		if($this->input->is_ajax_request()){
			$product_id = trim($this->input->post('product_id'));
			$price_data = $this->db->get_where('price_master',array('product_id'=>$product_id,'price_date'=>date('d/m/Y')))->row();
			$price_rate = $price_data->price_rate;
			$myTable = 'product_current_stock_'.$office_id;
			$product_current_stock_data = array();
			$product_current_stock_data = $this->db->query("select * from ".$myTable." where product_id='".$product_id."'")->row();

			$myquery = $this->db->last_query();
			$product_current_stock = (isset($product_current_stock_data->product_current_stock)) ? $product_current_stock_data->product_current_stock : '0' ;
						
			$product_data = $this->base_model->get_record_by_id('product_master',array('product_id'=>$product_id));
			$product_mount_status = $product_data->product_mount_status;
			
			
		
			// $officeData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			// $state_id = $officeData->state_id;
			$taxData = $this->db->select('surcharge_on_vat,entry_tax')->get_where('tax_master',array('office_id'=>$office_id))->row();
			$surcharge = $taxData->surcharge_on_vat;
			$entry_tax = $taxData->entry_tax;
			$taxValue = '0';
			$taxData = array();
			if($product_mount_status == "Mounted"){
				$taxData = $this->db->select('vat_on_mounted_products')->get_where('tax_master',array('office_id'=>$office_id))->row();
				if(!empty($taxData))
				{
				$taxValue = $taxData->vat_on_mounted_products;
				}
			}
			else if($product_mount_status == "Unmounted"){
				$taxData = $this->db->select('vat_on_unmounted_products')->get_where('tax_master',array('office_id'=>$office_id))->row();
				if(!empty($taxData))
				{
				$taxValue = $taxData->vat_on_unmounted_products;
				}
			}
			else if($product_mount_status == "Bar"){
				$taxData = $this->db->select('vat_on_bars')->get_where('tax_master',array('office_id'=>$office_id))->row();
				if(!empty($taxData))
				{
				$taxValue = $taxData->vat_on_bars;
				}
			}
			else if($product_mount_status == "Medallion"){
				$taxData = $this->db->select('vat_on_medallions')->get_where('tax_master',array('office_id'=>$office_id))->row();
				if(!empty($taxData))
				{
				$taxValue = $taxData->vat_on_medallions;
				}
			}
			
			
			$data = array('price_rate'=>$price_rate,'tax'=>$taxValue,'surcharge'=>$surcharge,'weight'=>$product_data->product_weight,'product_current_stock'=>$product_current_stock,'entry_tax'=>$entry_tax);
		}
		echo json_encode($data);
	}
	
	// add new div 
	public function AjaxAddNewDivCommon(){
		$data=array();
		$postedArr=$this->security->xss_clean($_POST);
		$office_id = $this->session->userdata('office_id');
		switch($postedArr['pageName']){
			case 'sales_invoice_form':
			$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
			$date=date('d/m/Y');
			break;
			case 'sales_invoice_change_products':
			$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
			$date=date('d/m/Y');
			break;
			case 'back_date_sales_invoice_productsdiv':
			$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
			$date1=$this->input->post('invoice_date_value');
			$date=date('d/m/Y',strtotime($date1));
			break;
			
		}
		$product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>$date));
		$todaysProducts = array();

		foreach($product_today_price_master as $todaysProduct){
			$todaysProducts[] = $todaysProduct->product_id;
		}
		$data['todaysProducts'] = $todaysProducts;
		$data['divSize']=$postedArr['divSize'];
		$data['pageName']=$postedArr['pageName'];
		$data['already_products']=$postedArr['already_products'];
		$data['select_product']=$postedArr['select_product'];
		$this->load->view('includes/_AjaxAddNewDivCommon',$data);	
	}
	


	public function stock_transfer_inventory(){
		$this->load->view("includes/_header");
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_transfer_inventory');
		$this->load->view('includes/_footer');
	}
	
	public function stock_receipt_inventory(){
		$this->load->view("includes/_header");
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/stock_receipt_inventory');
		$this->load->view('includes/_footer');
	}
	
	public function product_stock_receipt_inventory(){
		$this->load->view("includes/_header");
		$this->load->view("includes/_top_menu");
		$this->load->view('inventory/product_stock_receipt_inventory');
		$this->load->view('includes/_footer');
	}


	public function invoice_inventory()
	{
		$office_id = $this->session->userdata('office_id');
		$header['title'] = "Invoice Inventory";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		
		$data['invoice_master'] = $this->db->get_where('invoice_showroom_'.$office_id)->result();
		
		$this->load->view('inventory/invoice_inventory',$data);
		$this->load->view('includes/_footer');
	}
	
	public function sales_invoice_receipt()
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
		
	//		echo "<pre>";print_r($data['paymenttype_details']); die;
		//$this->load->view('inventory/sales_invoice_receipt',$data);
		$this->load->view('inventory/view_invoice',$data);
	}
	
	public function invoiceview()
	{
		$this->load->view('inventory/sales_invoice_view');
	}
	
	public function getSerialNumberSeries()
	{
		if($this->input->is_ajax_request()){
			$quantity = $this->input->post('quantity');
			$product_id = $this->input->post('product_id');
			$table_name = "product_current_stock_serial_number_".$this->session->userdata('office_id');//$this->input->post('table_name');
			$net_stock_id = $this->input->post('net_stock_id');
			$pageName = $this->input->post('pageName');
			$fieldName = $this->input->post('fieldName');
			
			$office_id=$this->session->userdata('office_id');
			$table_invoice_name = "invoice_showroom_product_serial_number_".$office_id;
			$table_invoice_product_name = "invoice_showroom_product_".$office_id;
			if($pageName == "sales_invoice_form"){
			if($this->input->post('invoice_id')!='')
				{
					$invoice_id=base64_decode($this->input->post('invoice_id'));
					$data['serialMaster1'] = $this->db->select('in_p_sn.serial_number as serial_number')->from($table_invoice_name.' as in_p_sn')->join($table_invoice_product_name.' as in_p','in_p.invoice_product_id = in_p_sn.invoice_product_id')->where(array('in_p.invoice_id'=>$invoice_id,'in_p.product_id'=>$product_id))->get()->result();
					//$data['serialMaster1'] = $this->db->select('serial_number')->get_where($table_invoice_name,array('invoice_id'=>$invoice_id))->result();
				}
				$data['serialMaster'] = $this->db->select($fieldName.' as serial_number')->get_where($table_name,array('product_id'=>$product_id,'current_stock_status'=>'0'))->result();
				$data['query'] = $this->db->last_query();
				$data['select_id'] = "serial_number-".$net_stock_id;
				$data['select_name'] = "serial_number_".$net_stock_id;
				if(!empty($data['serialMaster1']))
				{
					$data['serialMaster']=array_merge($data['serialMaster1'],$data['serialMaster']);
				}
			}
			$data['net_stock_id'] = $net_stock_id;
			$data['product_id'] = $product_id;
			$data['quantity'] = $quantity;
			echo $this->load->view('includes/_product_serial_number_list',$data,true);
		}
	}
	/* public function _get_all_record_Sales_to_by_join($tableNameINVOICE,$office_operation_type,$office_id){
		
		$table_payment='invoice_showroom_payment_mode_'.$office_id;
		$this->db->select('invoice.*,customer_master.modal_customer_name,customer_master.modal_customer_pan_number')->from($tableNameINVOICE.' as invoice');
		$this->db->join('customer_master as customer_master','invoice.customer_id=customer_master.customer_id','left');
		$check_back_date_data = array("backdatesale","backdatecredit","backdateproforma","backdatecorporate","backdateadvance");
		$this->db->where_not_in("invoice.invoice_type",$check_back_date_data);
		$this->db->order_by("invoice.invoice_id","desc");
		$data = $this->db->get()->result();
		return $data;
		
	} */
	
	public function _get_all_record_Sales_to_by_join($tableNameINVOICE,$office_operation_type,$office_id,$fromDate,$toDate){
		
		$table_payment='invoice_showroom_payment_mode_'.$office_id;
		$this->db->select('invoice.*,customer_master.modal_customer_name,customer_master.modal_customer_pan_number')->from($tableNameINVOICE.' as invoice');
		$this->db->join('customer_master as customer_master','invoice.customer_id=customer_master.customer_id','left');
		$check_back_date_data = array("backdatesale","backdatecredit","backdateproforma","backdatecorporate","backdateadvance");
		$this->db->where_not_in("invoice.invoice_type",$check_back_date_data);
		$this->db->where(array('invoice.createdOn >= '=>$fromDate,'invoice.createdOn <='=>$toDate));
		$this->db->order_by("invoice.invoice_id","desc");
		$data = $this->db->get()->result();
		return $data;
		
	}
	
	
		public function sales_invoice_details(){
		$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		$delete_value = 4;
		}else
		{

		$page_id = 27;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		//print_r($page_permission_array);die;
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		$delete_value = $page_permission_array->delete_value;
		}
		if($view_value==0)
		{
			$this->session->set_flashdata("error_message","You don't have permission to view.");
			redirect(base_url('user/dashboard'));
		}
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$fromDate1 = date('Y-m-d',strtotime('now'))." 00:00:00";
		$toDate1 = date('Y-m-d',strtotime('now'))." 23:59:59";
			
		if($this->input->post('submit') !=''){
			$fromDate2 = explode('/',$this->input->post('fromDate'));
			$toDate2 = explode('/',$this->input->post('toDate'));
			$fromDate1 = $fromDate2[2].'-'.$fromDate2[1].'-'.$fromDate2[0].' 00:00:00';
			$toDate1 = $toDate2[2].'-'.$toDate2[1].'-'.$toDate2[0].' 23:59:59';
		}
		
		$fromDate = $fromDate1;
		$toDate = $toDate1;
		
	    $tableNameINVOICE='invoice_'.$office_operation_type.'_'.$office_id;	
		
		$header['title'] = "Sales Invoice Details";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		
		$data['invoice_details']=$this->_get_all_record_Sales_to_by_join($tableNameINVOICE,$office_operation_type,$office_id,$fromDate,$toDate);
		
		$data['fromDate'] = date('d/m/Y',strtotime($fromDate));
		$data['toDate'] = date('d/m/Y',strtotime($toDate));
		
		$this->load->view('inventory/sales_invoice_details',$data);
		$this->load->view('includes/_footer');
	}
public function getdate_time()
{
	echo date('Y-m-d H:i:s');
}
public function delete_invoice_data()
{
	$check_super_admin=$this->session->all_userdata();
			if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
			{
			$add_value = 1;
			$edit_value = 2;
			$view_value = 3;
			$delete_value = 4;
			}else
			{

			$page_id = 27;
			$page_permission_array = $this->role_model->get_page_permission($page_id);
			//print_r($page_permission_array);die;
			$add_value = $page_permission_array->add_value;
			$edit_value = $page_permission_array->edit_value;
			$view_value = $page_permission_array->view_value;
			$delete_value = $page_permission_array->delete_value;
			}
			if($view_value == "0")
		    {
				$this->session->set_flashdata("error_message","You don't have permission to view.");
				redirect(base_url('user/dashboard'));
		    }
	    if($delete_value==4)
		{
	if(!empty($_POST['invoice_number']))
	{
		
		$invoice_id=base64_decode($_POST['invoice_number']);
		$reason=$_POST['reason'];
		//$data=array('reason'=>$reason);
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		$table_invoice='invoice_'.$office_operation_type.'_'.$office_id;
		//$this->db->where('invoice_id',$invoice_id);
	    //$this->db->update($table_invoice,$data);
		$table_productinvoice='invoice_'.$office_operation_type.'_product_'.$office_id;
		$table_productserialinvoice='invoice_'.$office_operation_type.'_product_serial_number_'.$office_id;
		$table_history='inventory_office_history_'.$office_id;
		$table_current='product_current_stock_'.$office_id;
		$table_current_serials='product_current_stock_serial_number_'.$office_id;
		$invoice_data=$this->db->get_where($table_invoice,array('invoice_id'=>$invoice_id))->row();
		if($invoice_data->is_deleted == '0')
		{
			//$this->db->delete($table_history,array('transaction_number'=>$invoice_data->invoice_number));
		
		 $delete_data = array('is_deleted'=>'1',
			'delete_by_user'=>$this->session->userdata('user_id'),
			'deleted_date'=>date('Y-m-d H:i:s')
			 );
		//$this->db->delete('invoice_'.$office_operation_type.'_payment_mode_'.$office_id,array('invoice_id'=>$invoice_data->invoice_id));
		 $this->db->update('invoice_'.$office_operation_type.'_payment_mode_'.$office_id,$delete_data,array('invoice_id'=>$invoice_data->invoice_id));
	
		$invoice_product_data=$this->db->get_where($table_productinvoice,array('invoice_id'=>$invoice_id))->result();
		foreach($invoice_product_data as $value)
		{
			
			$invoice_serials_data=$this->db->get_where($table_productserialinvoice,array('invoice_product_id'=>$value->invoice_product_id))->result();
			$flag=0;
				foreach($invoice_serials_data as $value_serials)
				{
					$flag=1;
					$this->db->update($table_current_serials,array('current_stock_status'=>'0'),array('product_serial_number'=>$value_serials->serial_number));
				}
			//	$this->db->delete($table_productserialinvoice,array('invoice_product_id'=>$value->invoice_product_id));
			
			 $delete_data = array('is_deleted'=>'1',
			'delete_by_user'=>$this->session->userdata('user_id'),
			'deleted_date'=>date('Y-m-d H:i:s'),
			 );
				$this->db->update($table_productserialinvoice,$delete_data,array('invoice_product_id'=>$value->invoice_product_id));
				if($flag==1)
				{
					$arr_current_value=$this->db->get_where($table_current,array('product_id'=>$value->product_id))->row();
					$cur_stock=$arr_current_value->product_current_stock+$value->qunatity;
					$this->db->update($table_current,array('product_current_stock'=>$cur_stock),array('product_id'=>$value->product_id));
					
					
					$this->db->select('*');
					$this->db->order_by('history_id','desc')->limit('1');
					$arr_his_data=$this->db->get_where($table_history,array('transaction_number'=>$invoice_data->invoice_number,'product_id'=>$value->product_id))->row();
					//$arr_his_data=$this->db->get_where($table_history,array('product_id'=>$value->product_id))->row();
					
					$this->db->select('*');
					$this->db->order_by('history_id','desc')->limit('1');
					$arr_his_netstock_latest=$this->db->get_where($table_history,array('product_id'=>$value->product_id))->row();
					
					
					$historyData = array('current_stock'=>$arr_his_netstock_latest->net_stock,'product_id'=>$value->product_id,'received_stock'=>$arr_his_data->transfer_stock,'net_stock'=>($arr_his_netstock_latest->net_stock+$arr_his_data->transfer_stock),'type_value'=>'Invoice Deleted','received_from'=>$arr_his_data->transfer_to,'transaction_number'=>$arr_his_data->transaction_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s'),'extra_field_1'=>$reason); 
					
					$this->base_model->insert_one_row($table_history,$historyData);
				}
		}
		     $delete_data=array('is_deleted'=>'1',
			'delete_by_user'=>$this->session->userdata('user_id'),
			'deleted_date'=>date('Y-m-d H:i:s')
			
			
			);
		//$this->db->delete($table_productinvoice,array('invoice_id'=>$invoice_id));
			$this->db->update($table_productinvoice,$delete_data,array('invoice_id'=>$invoice_id));
			//$this->db->delete($table_invoice,array('invoice_id'=>$invoice_id));
			$delete_data=array('is_deleted'=>'1',
			'delete_by_user'=>$this->session->userdata('user_id'),
			'deleted_date'=>date('Y-m-d H:i:s')
			
			
			);
			$this->db->update($table_invoice,$delete_data,array('invoice_id'=>$invoice_id));

			echo '1';	
			
		}
		else{
			$this->session->set_flashdata('SuccessMessage',DELETE_MSG_COMMON_PERMISSION_ERROR);
			echo '2';
		}
	}
}
else{
	  $this->session->set_flashdata('SuccessMessage',DELETE_MSG_COMMON_PERMISSION_ERROR);
	  echo '2';
  }
	
}
public function sales_invoice_edit_form()
	{
		$header['title'] = "Edit Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$office_id = $this->session->userdata('office_id');
		$invoice_id = ($this->input->get('invoice_id')) ? base64_decode($this->input->get('invoice_id')) : '';
		
		
		$this->db->select('showroom.*,cust.modal_customer_name,cust.modal_customer_email_id,cust.modal_customer_pan_number,cust.modal_customer_address,cust.id_proof,cust.id_proof_number,cust.modal_customer_phone_number')->from('invoice_showroom_'.$office_id.' as showroom');
		$this->db->join('customer_master as cust','showroom.customer_id=cust.customer_id','left')->where('invoice_id',$invoice_id);
		$data['invoice_details']=$this->db->get()->row();
		
		if(empty($data['invoice_details']))
		{
			$data['heading']='Invoice Not Found';
			$data['message']='No Record Found';
			$this->load->view('errors/html/error_404',$data);
		}
		else
		{
			$this->db->select('showroom_pro.*,product_master.product_name')->from('invoice_showroom_product_'.$office_id.' as showroom_pro');
			$this->db->join('product_master','showroom_pro.product_id=product_master.product_id')->where('invoice_id',$invoice_id);
			$data['invoice_products']=$this->db->get()->result();
			
			$data['invoice_payment']=$this->db->get_where('invoice_showroom_payment_mode_'.$office_id,array('invoice_id'=>$invoice_id))->result();
			$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
			$product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>date('d/m/Y')));
			$todaysProducts = array();
			foreach($product_today_price_master as $todaysProduct){
				$todaysProducts[] = $todaysProduct->product_id;
			}
			$data['todaysProducts'] = $todaysProducts;
			
			$this->load->view('inventory/sales_invoice_edit_form',$data);
		}
		$this->load->view('includes/_footer');
	}
public function saveInvoiceDataEdit()
{
if($_POST){
	// echo "<pre>";
// print_r($_POST);die;
$invoiceId = base64_decode($_POST['invoice_id']);
			    $errors=array();
				if(empty($_POST['invoice_date'])) {
				$errors['invoice_date'] = 'Date and time of Invoice is required';
				}
				
				if(empty($_POST['invoice_type'])) {
				$errors['invoice_type_chosen'] = 'Please select invoice type';
				}
				
				/*if(empty($_POST['customer_code'])) {
				$errors['customer_code'] = 'Customer Code required';
				}*/
				/*if(empty($_POST['customer_phone_number'])) {
				$errors['customer_phone_number'] = 'Customer Phone required';
				}
				*/
				 $invoice_id=base64_decode($_POST['invoice_id']);
				 
				if($_POST['received_amount'] < $_POST['total_net_amount'] && ($_POST['invoice_type']!='advance' or $invoice_id!=''))
				{
					$errors['received_amount'] = 'Received amount should not less than Net Amount';
				}
				
				foreach($_POST['payment_mode'] as $key=>$value)
				{
					// && $value=='cash'
					/*if(empty($_POST['customer_pan_number']) && $_POST['payment_mode_amount'][$key]>=200000){
				//if(empty($_POST['customer_pan_number']) && $_POST['total_net_amount']>=200000) 
				$errors['customer_pan_number'] = 'PAN Number required';
				}
				//&& $value=='cash'
				if(empty($_POST['id_proof']) && isset($_POST['id_proof']) &&  $_POST['payment_mode_amount'][$key]>=50000) {
				$errors['id_proof'] = 'ID Proof required';
				}
				//&& $value=='cash'
				if(empty($_POST['id_proof_number']) && $_POST['payment_mode_amount'][$key]>=50000) {
				$errors['id_proof_number'] = 'ID Proof Number required';
				}*/
				
				
				if( $_POST['payment_mode_amount'][$key]>=200000){
				//if(empty($_POST['customer_pan_number']) && $_POST['total_net_amount']>=200000) 
					if(empty($_POST['customer_pan_number']))
					{
				     $errors['customer_pan_number'] = 'PAN Number required';
					}
					else{
						if (!preg_match("/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/", $_POST['customer_pan_number'])) {
						   $errors['customer_pan_number'] = "Invalid pan number";
						}
					}
				  }else{
					  
					  //&& $value=='cash' 
					if(empty($_POST['id_proof']) && isset($_POST['id_proof']) &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof'] = 'ID Proof required';
					}
					//&& $value=='cash'
					if(empty($_POST['id_proof_number'])  &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof_number'] = 'ID Proof Number required';
					}
				  }
				
				}
				//$arr_selected_product_divs=explode(",",$_POST['selected_products_divs']);
				//print_r($arr_selected_product_divs);
				foreach($_POST['product_id'] as $key=>$value)
				{
					//$err_div=$arr_selected_product_divs[$key];
					  if(empty($value)){
						$errors['product_id_'.$key.'_chosen'] = 'Product Name is required';		
						
					  }
					  if(empty($_POST['quantity'][$key])){
						$errors['qty-'.$key] = 'Qty is required';				
					  }else{
						if(!preg_match('/^[0-9]+$/i', $_POST['quantity'][$key])) {
							$errors['qty-'.$key] = 'Qty accept only number';
						}
						}
						/* if($_POST['received_amount'] >= $_POST['total_net_amount'])
						{
							$quantity=$_POST['quantity'][$key];
				//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
							$serial_number=$_POST['serial_number_'.$key];
							if($quantity!=count($serial_number))
							{
							$errors['serial_number_'.$key.'_chosen'] = 'Please select '.$quantity.' serial number';
							}
						} */
					  
				}
			//	$arr_selected_payment_divs=explode(",",$_POST['selected_payment_divs']);
				//print_r($arr_selected_payment_divs);
				foreach($_POST['payment_mode'] as $key=>$value)
				{
					//$err_div=$arr_selected_payment_divs[$key];
					  if(empty($value)){
						$errors['payment_mode_'.$key.'_chosen'] = 'Payment mode is required';		
						
					  }
					  if($value=='credit card' || $value=='debit card')
					   {
						  if(empty($_POST['card_check_name'][$key]))
							  {
								 
								   $errors['card_check_name_'.$key] = 'Card name is required';	
								   			
							  }
							  
							   if(empty($_POST['card_issuing_bank'][$key]))
							  {
								 
								  $errors['card_issuing_bank_'.$key] = 'Issuing bank number is required';
							  }
					   }
					  if(empty($_POST['payment_mode_amount'][$key])){
						$errors['payment_mode_amount_'.$key] = 'Amount is required';				
					  }else{
						/* if(!preg_match('/^[0-9]+$/i', $_POST['payment_mode_amount'][$key])) {
							$errors['payment_mode_amount_'.$key] = 'Amount accept only number';
						} */
						}
					  if($value=='credit card' || $value=='debit card' || $value=='cheque')
					  {
					  if(empty($_POST['card_check_number'][$key])){
						$errors['card_check_number_'.$key] = 'Please enter '.$value.' is required';				
					  }
					  if(empty($_POST['card_issuing_bank'][$key])){
						  $errors['card_issuing_bank_'.$key] = 'Issuing bank number is required';
						  
						 }
					  elseif($value=='credit card' || $value=='debit card')
					  {
						  if(strlen($_POST['card_check_number'][$key])>4)
						  {
							  $errors['card_check_number_'.$key] = 'Please enter last 4 digit of '.$value.' number';				
						  }
					  }
						  
					  
					  }
					
					  if($value=='cheque'){
					 if($_POST['cheque_relese'][$key] =='select'){
						$errors['cheque_relese_'.($key+$already_paid)] = 'Cheque Realization is required';		
						
					  }
				   }
					
					  
				}
				
				
				if(count($errors) > 0){
				//This is for ajax requests:
				$errors['msg']='error';
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
		$office_id = $this->session->userdata('office_id');
		$office_operation_type = $this->session->userdata('office_operation_type');
		//$invoice_number = $this->input->post('invoice_number');
		$invoice_date = $this->input->post('invoice_date');
		$customer_code = $this->input->post('customer_code');
		
		$customer_name = $this->input->post('customer_name');
		$customer_address = $this->input->post('customer_address');
		$customer_phone_number = $this->input->post('customer_phone_number');
		$customer_email_id = $this->input->post('customer_email_id');
		$customer_pan_number = $this->input->post('customer_pan_number');
		$id_proof = $this->input->post('id_proof');
		$id_proof_number = $this->input->post('id_proof_number');
		$showrom_invoice_narrative = $this->input->post('showrom_invoice_narrative');
		$customer_transaction_id = $this->input->post('customer_transaction_id');
		
		
		$table_invoice='invoice_'.$office_operation_type.'_'.$office_id;
		$table_productinvoice='invoice_'.$office_operation_type.'_product_'.$office_id;
		$table_productserialinvoice='invoice_'.$office_operation_type.'_product_serial_number_'.$office_id;
		$table_history='inventory_office_history_'.$office_id;
		$table_current='product_current_stock_'.$office_id;
		$table_current_serials='product_current_stock_serial_number_'.$office_id;
		$invoice_data=$this->db->get_where($table_invoice,array('invoice_id'=>$invoice_id))->row();
		
		
	//	$this->db->delete($table_history,array('transaction_number'=>$invoice_data->invoice_number));
	
		
		$this->db->delete('invoice_'.$office_operation_type.'_payment_mode_'.$office_id,array('invoice_id'=>$invoice_data->invoice_id));
		 
	
		/* $invoice_product_data=$this->db->get_where($table_productinvoice,array('invoice_id'=>$invoice_id))->result();
		foreach($invoice_product_data as $value)
		{
			
			$invoice_serials_data=$this->db->get_where($table_productserialinvoice,array('invoice_product_id'=>$value->invoice_product_id))->result();
			$flag=0;
				foreach($invoice_serials_data as $value_serials)
				{
					$flag=1;
					$this->db->update($table_current_serials,array('current_stock_status'=>'0'),array('product_serial_number'=>$value_serials->serial_number));
				}
				$this->db->delete($table_productserialinvoice,array('invoice_product_id'=>$value->invoice_product_id));
				if($flag==1)
				{
					$arr_current_value=$this->db->get_where($table_current,array('product_id'=>$value->product_id))->row();
					$cur_stock=$arr_current_value->product_current_stock+$value->qunatity;
					$this->db->update($table_current,array('product_current_stock'=>$cur_stock),array('product_id'=>$value->product_id));
					
					$this->db->select('*');
					$this->db->order_by('history_id','desc')->limit('1');
					$arr_his_data=$this->db->get_where($table_history,array('transaction_number'=>$invoice_data->invoice_number,'product_id'=>$value->product_id))->row();
					
					
					$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value->product_id,'received_stock'=>$arr_his_data->transfer_stock,'net_stock'=>($arr_his_data->net_stock+$arr_his_data->transfer_stock),'type_value'=>'Invoice Edited','received_from'=>$arr_his_data->transfer_to,'transaction_number'=>$arr_his_data->transaction_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s')); 
					
					$this->base_model->insert_one_row($table_history,$historyData);
					
					
				}
				
	
				
				
		}
		
		$this->db->delete($table_productinvoice,array('invoice_id'=>$invoice_id));
		 */
		
		
		//$customerData = $this->base_model->get_record_by_id('customer_master',array('modal_customer_short_name'=>$customer_code));
	
$customerData = $this->base_model->get_record_by_id('customer_master',array('modal_customer_phone_number'=>$customer_phone_number,'modal_customer_phone_number!='=>''));
$customerInsertData = array('modal_customer_name'=>$customer_name,
									'modal_customer_address' => $customer_address,
									'modal_customer_phone_number' => $customer_phone_number,
									'modal_customer_email_id' => $customer_email_id,
									'modal_customer_pan_number' => $customer_pan_number,
									'id_proof' => $id_proof,
									'id_proof_number' => $id_proof_number);
		if(empty($customerData)){
			
			$customerData_mob = $this->base_model->get_record_by_id('customer_master',array('modal_customer_mobile_number'=>$customer_phone_number,'modal_customer_mobile_number!='=>''));
			if(empty($customerData_mob)){
				if(!empty($customer_name) || !empty($customer_address) || !empty($customer_phone_number) || !empty($customer_email_id) || !empty($customer_pan_number) || !empty($id_proof) || !empty($id_proof_number))
				{
				$customerInsertData['createdOn'] = date('Y-m-d H:i:s');
				$this->base_model->insert_one_row('customer_master',$customerInsertData);
				$customer_id  = $this->db->insert_id();
				}
				else
				{
				$customer_id='0';	
				}
		
				}
				else
				{
					$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData_mob->customer_id));
					$customer_id = $customerData_mob->customer_id;
				}
		}
		else
		{
		$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData->customer_id));
		$customer_id = (isset($customerData->customer_id)) ? $customerData->customer_id : '0';
		}
		$product_id = $this->input->post('product_id');
		//$serial_number = $this->input->post('serial_number');
        
		$quantity = $this->input->post('quantity');
		$invoice_type = $this->input->post('invoice_type');
		$showrom_invoice_narrative = $this->input->post('showrom_invoice_narrative');
		$customer_transaction_id = $this->input->post('customer_transaction_id');
		$weight = $this->input->post('weight');
		$rate_per_quantity = $this->input->post('rate_per_quantity');
		$discount_percent = $this->input->post('discount_percent');
		$tax = $this->input->post('tax');
		$entryTax = $this->input->post('entry_tax');
		$net_amount = $this->input->post('net_amount');
		$total_amount = $this->input->post('total_amount');
		$surcharge_on_vat = ($this->input->post('surcharge_on_vat')) ? $this->input->post('surcharge_on_vat') : '0';
		$total_net_amount = $this->input->post('total_net_amount');
		$received_amount = $this->input->post('received_amount');
		//$round_off=($this->input->post('round_off')) ? $this->input->post('round_off') : '0';
       //'adjustment'=>$round_off,
		$amount_refunded = ($this->input->post('amount_refunded')) ? $this->input->post('amount_refunded') : '0';
	
		$invoiceInsertData = array(
		                            'invoice_type'=>$invoice_type,
		                            'showrom_invoice_narrative'=>$showrom_invoice_narrative,
									'customer_id' => $customer_id,
									'total_amount' => $total_amount,
									'surcharge_on_vat' => $surcharge_on_vat,
									'amount_received' => $received_amount,
									'amount_refunded' =>$amount_refunded,
									'creator_id' => $this->session->userdata('user_id'),
									);
         
		$this->db->update('invoice_showroom_'.$office_id,$invoiceInsertData,array('invoice_id'=>$invoice_id));
		$invoiceId = $invoice_id;
		
		$officeData = $this->base_model->get_record_by_id('office_master',array('office_id'=>$office_id));
			
			// office_short_code
			
			$officeUniqueName = $officeData->office_short_code;
			
		
			$totalProduct = count($product_id);
			
			$invoice_product_id=array();
			foreach($product_id as $key=>$value_product)
			{
				//implode(',',$invoice_product_serial_number)
				
					$productInsertData = array('invoice_id' =>$invoiceId,
											'product_id' =>$value_product,
											'serial_number' => '',
											'weight' => $weight[$key],
											'qunatity' => $quantity[$key],
											'rate' => $rate_per_quantity[$key],
											'discount' => $discount_percent[$key],
											'tax' => $tax[$key],
											'entry_tax' => $entryTax,
											'net_amount' => $net_amount[$key],
											'creator_id' => $this->session->userdata('user_id'),
											'createdOn' => date('Y-m-d H:i:s'),
										);
			//	$this->base_model->insert_one_row('invoice_showroom_product_'.$office_id,$productInsertData);
				
				$invoice_product_id[$value_product] = $this->db->insert_id();
			}

//condition for advance mode
	
if($received_amount>=$total_net_amount) 
{
			
			foreach($product_id as $key=>$value_product)
			{
				
					
				$table_name_product_current='product_current_stock_'.$office_id;
				
				$invoice_product_serial_number = $_POST['serial_number_'.$key];
				
/* 
				$inventory_stock_product_current_stock=$this->base_model->get_record_by_id($table_name_product_current,array('product_id'=>$value_product));
				if(!empty($inventory_stock_product_current_stock)){
					
					$new_stock=($inventory_stock_product_current_stock->product_current_stock)-($quantity[$key]);
					$product_current_stock=array('product_current_stock'=>$new_stock);
					$this->base_model->update_record_by_id($table_name_product_current,$product_current_stock,array('product_id'=>$value_product));
				}else{
					$product_current_stock=array('product_id'=>$value_product,'product_current_stock'=>$quantity[$key],'createdOn'=>date('Y-m-d H:i:s'));
					$this->base_model->insert_one_row($table_name_product_current,$product_current_stock);
				}
 */
				
			/* 	$arr_invoice_data=$this->db->get_where('invoice_showroom_'.$office_id,array('invoice_id'=>$invoiceId))->row();
	
		
							$history_table = 'inventory_office_history_'.$office_id;
							$this->db->select('*');
							$this->db->order_by('history_id','desc')->limit('1');
							$arr_his_data=$this->db->get_where($history_table,array('product_id'=>$value_product))->row();
							
							
							$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value_product,'transfer_stock'=>$quantity[$key],'net_stock'=>($arr_his_data->net_stock-$quantity[$key]),'type_value'=>'customer','transfer_to'=>$arr_invoice_data->customer_id,'transaction_number'=>$arr_invoice_data->invoice_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s')); 
							$this->base_model->insert_one_row($history_table,$historyData);
 */
			
				
/* 
				foreach($invoice_product_serial_number as $serial_number){
					$insert_invoice_serial_data = array('invoice_id'=>$invoiceId,'invoice_product_id'=>$invoice_product_id[$value_product],'serial_number'=>$serial_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn' => date('Y-m-d H:i:s'));

					$this->base_model->insert_one_row('invoice_showroom_product_serial_number_'.$office_id,$insert_invoice_serial_data);

					$update_current_stock_serial_data = array('current_stock_status'=>'1');
					$this->base_model->update_record_by_id('product_current_stock_serial_number_'.$office_id,$update_current_stock_serial_data,array('product_serial_number'=>$serial_number));
				}
				 */
				
			}
		
}
		$payment_mode_amount = $this->input->post('payment_mode_amount');
		$payment_mode = $this->input->post('payment_mode');
		$card_check_number = $this->input->post('card_check_number');
		$card_check_name = $this->input->post('card_check_name');
		$card_issuing_bank = $this->input->post('card_issuing_bank');
		
		foreach($payment_mode as $key=>$pm)
		{
		$invoicePaymentModeData = array('invoice_id'=>$invoiceId,
									'payment_type' => $pm,
									'payment_amount' => $payment_mode_amount[$key],
									'card_cheque_number' => $card_check_number[$key],
									'bank_name'=>$card_check_name[$key],
									'card_issuing_bank'=>$card_issuing_bank[$key],
									'creator_id' => $this->session->userdata('user_id'),
									'createdOn' => date('Y-m-d H:i:s'),									
									);
		$this->base_model->insert_one_row('invoice_showroom_payment_mode_'.$office_id,$invoicePaymentModeData);
		
		}

		echo json_encode(array('msg'=>'success','invoice_id'=>base64_encode($invoiceId)));
		
		exit;
		/*sales_invoice_receipt(base64_encode($invoiceId));*/
		}
		//redirect(base_url('invoice/sales_invoice_receipt/'.$invoiceId));
	}
	
}

//function insert_new_colums_in_table(){
	
	//$users_master = $this->db->query("select office_id,office_operation_type from office_master where office_operation_type='showroom'")->result();
	
	//if(!empty($users_master)){
		
		//foreach($users_master as $row){
			//if($row->office_operation_type=='showroom'){
				//$office_id= $row->office_id;
				//$this->db->query('ALTER TABLE invoice_showroom_payment_mode_'.$office_id.' ADD `card_issuing_bank` VARCHAR(150) NULL DEFAULT NULL AFTER `card_cheque_number`');
				
			//}else{
				
		         //continue;		
				//}
			
			
		//}
		
		//}else{
			//echo "not_match";
			//exit;
			
			//}
	

	
	//}
	
	
	
	//function insert_new_colums_in_table_delete_by_user(){
	
	//$users_master = $this->db->query("select office_id,office_operation_type from office_master where office_operation_type='showroom'")->result();
	
	//if(!empty($users_master)){
		
		//foreach($users_master as $row){
			//if($row->office_operation_type=='showroom'){
				//$office_id= $row->office_id;
				//$this->db->query('ALTER TABLE invoice_showroom_payment_mode_'.$office_id.' ADD `delete_by_user` BIGINT(20) NULL DEFAULT NULL AFTER `is_deleted`');
				////ALTER TABLE `invoice_showroom_payment_mode_3` ADD `delete_by_user` BIGINT(20) NULL DEFAULT NULL AFTER `is_deleted`;
			//}else{
				
		         //continue;		
				//}
			
			
		//}
		
		//}else{
			//echo "not_match";
			//exit;
			
			//}
	

	
	//}
	
		//function insert_new_colums_in_table_delete_date(){
	
	//$users_master = $this->db->query("select office_id,office_operation_type from office_master where office_operation_type='showroom'")->result();
	
	//if(!empty($users_master)){
		
		//foreach($users_master as $row){
			//if($row->office_operation_type=='showroom'){
				//$office_id= $row->office_id;
				//$this->db->query('ALTER TABLE invoice_showroom_payment_mode_'.$office_id.' ADD `deleted_date` DATETIME NULL DEFAULT NULL AFTER `delete_by_user`');
				////ALTER TABLE `invoice_showroom_payment_mode_3` ADD `deleted_date` DATETIME NULL DEFAULT NULL AFTER `delete_by_user`;
			//}else{
				
		         //continue;		
				//}
			
			
		//}
		
		//}else{
			//echo "not_match";
			//exit;
			
			//}
	

	
	//}
	
		//function insert_new_colums_showroom_product_delete_date(){
	
	//$users_master = $this->db->query("select office_id,office_operation_type from office_master where office_operation_type='showroom'")->result();
	
	//if(!empty($users_master)){
		
		//foreach($users_master as $row){
			//if($row->office_operation_type=='showroom'){
				//$office_id= $row->office_id;
				////$this->db->query('ALTER TABLE invoice_showroom_'.$office_id.'  ADD  `delete_by_user`  BIGINT(20)  NULL DEFAULT NULL AFTER `is_deleted`');
				//$this->db->query('ALTER TABLE invoice_showroom_'.$office_id.'  ADD  `deleted_date`  DATETIME  NULL DEFAULT NULL AFTER `delete_by_user`');
			//}else{
				
		        //continue;		
				//}
			
			
		//}
		
		//}else{
			//echo "not_match";
			//exit;
			
			//}
	

	
	//}
	
	
	//function insert_new_colums_in_table(){
	
	//$users_master = $this->db->query("select office_id,office_operation_type from office_master where office_operation_type='showroom'")->result();
	
	//if(!empty($users_master)){
		
		//foreach($users_master as $row){
			//if($row->office_operation_type=='showroom'){
				//$office_id= $row->office_id;
				//$this->db->query('ALTER TABLE invoice_showroom_'.$office_id.' ADD `customer_transaction_id` VARCHAR(60) NULL DEFAULT NULL AFTER `showrom_invoice_narrative`');
				
			//}else{
				
		         //continue;		
				//}
			
			
		//}
		
		//}else{
			//echo "not_match";
			//exit;
			
			//}
	

	
	//}

	
}
