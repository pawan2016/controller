<?php

error_reporting(0);

defined('BASEPATH') OR exit('No direct script access allowed');

//require_once('tcpdf/tcpdf.php');

class BackDateInvoice extends CI_Controller {
		private $mime_types= array();
    public function __construct() {
       parent::__construct();
	   sessionExist();
       $this->load->library('upload');
        $this->load->model('inventory_model');	
		if(!$this->session->userdata('is_logged_in'))
		{
			redirect('Login/index','refresh');
		}
		$this->mime_types = array('text/plain', 'image/png', 'image/jpeg', 'image/jpeg', 'image/jpeg', 'image/gif', 'application/pdf');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');		
	}
	public function back_date_sales_invoice_form(){
		$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		}else
		{

		$page_id = 28;
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
				$this->session->set_flashdata("error_message","You don't have permission back date sales invoice add.");
				redirect(base_url('user/dashboard'));
		}
		$header['title'] = "BackDate Sales Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$office_id = $this->session->userdata('office_id');
		$back_date_data=$this->base_model->get_all_record_by_condition('back_date_invoice',array('showroom_id'=>$office_id,'invoice_create'=>'1'));
		if(is_array($back_date_data) && !empty($back_date_data))
		{
			$date=strtotime(date('d-m-Y'));
			$to=strtotime($back_date_data['0']->to_date);
			$from=strtotime($back_date_data['0']->from_date);
		    //print_r($date <= $to);die;
		  if(( $date >= $from) && ($date<=$to))
		  {
			  //echo "hii";die;
				$data['state_master'] = $this->base_model->get_all_records('state_master');
				$data['id']=$back_date_data['0']->id;
				$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
				/* $product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>date('d/m/Y')));
				$todaysProducts = array();
				foreach($product_today_price_master as $todaysProduct){
					$todaysProducts[] = $todaysProduct->product_id;
				}
				$data['todaysProducts'] = $todaysProducts;
 */
				$this->load->view('BackDateInvoice/back_date_sales_invoice_form',$data);
				$this->load->view('includes/_footer');
		  }
		  else{
			
			    $update_data=array('invoice_create'=>'0');
				$where=array('id'=>$back_date_data['0']->id);
			    $return=$this->base_model->update_record_by_id('back_date_invoice',$update_data,$where);
				//print_r($return);die;
			    $this->session->set_flashdata('SuccessMessage','Your Validity Is Expired To Create Back Date Invoice '); 
				redirect('BackDateInvoice/back_date_sales_invoice_details');
		  }
		}
		else{
			 $this->session->set_flashdata('SuccessMessage','Your Have Not Access To Create Back Date Invoice'); 
				redirect('BackDateInvoice/back_date_sales_invoice_details');
		}

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
				 if($_POST['received_amount'] < $_POST['total_net_amount'] && ($_POST['invoice_type']!='backdateadvance' or $invoice_id!=''))
					{
						$errors['received_amount'] = 'Received amount should not less than Net Amount';
					}
				  if(!isset($_POST['round_off']) || $_POST['round_off']=="")
				 {
					if($_POST['received_amount'] < $_POST['total_net_amount'] && ($_POST['invoice_type']!='backdateadvance' or $invoice_id!=''))
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
				foreach($_POST['payment_mode'] as $key=>$value)
				{
					//$value=='cash' && 
					if($_POST['payment_mode_amount'][$key]>=200000){
				//if(empty($_POST['customer_pan_number']) && $_POST['total_net_amount']>=200000) 
					if(empty($_POST['customer_pan_number']))
					{
				     $errors['customer_pan_number'] = 'PAN Number required';
					}
				  }else{
					  //&& $value=='cash' 
					if(empty($_POST['id_proof']) && isset($_POST['id_proof'])  &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof'] = 'ID Proof required';
					}
					//&& $value=='cash' 
					if(empty($_POST['id_proof_number'])  &&  $_POST['payment_mode_amount'][$key]>=50000) {
					$errors['id_proof_number'] = 'ID Proof Number required';
					}
				  }
				}
				
				/* if(empty($_POST['customer_pan_number']) && $_POST['total_net_amount']>=200000) {
				$errors['customer_pan_number'] = 'PAN Number required';
				}
				
				if(empty($_POST['id_proof']) && !isset($_POST['id_proof']) && $_POST['total_net_amount']>=50000) {
				$errors['id_proof'] = 'ID Proof required';
				}
				if(empty($_POST['id_proof_number']) && $_POST['total_net_amount']>=50000) {
				$errors['id_proof_number'] = 'ID Proof Number required';
				} */
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
								   $errors['card_issuing_bank_'.$key] = 'Issuing bank name is required';				
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
						$errors['card_issuing_bank_'.$key] = 'Issuing bank name is required';				
					  }
					  
					  elseif($value=='credit card' || $value=='debit card')
					  {
						  if(strlen($_POST['card_check_number'][$key])>4)
						  {
							  $errors['card_check_number_'.$key] = 'Please enter last 4 digit of '.$value.' number';				
						  }
					  }
						  
				  }
				  if($value =='cheque'){
				  if($_POST['cheque_relese'][$key] =='select'){
						$errors['cheque_relese_'.$key] = 'Cheque Realization is required';		
						
					  }
				  }
			}
		$office_id = $this->session->userdata('office_id');
		$back_date_data=$this->base_model->get_all_record_by_condition('back_date_invoice',array('showroom_id'=>$office_id,'invoice_create'=>'1'));
		if(is_array($back_date_data) && !empty($back_date_data))
		{
		  $date=strtotime(date('d-m-Y'));
			$to=strtotime($back_date_data['0']->to_date);
			$from=strtotime($back_date_data['0']->from_date);
		    //print_r($date <= $to);die;
		  if(( $date >= $from) && ($date<=$to))
		  {
			    $date_of_invoice=$_POST['invoice_date'];
				if(isset($date_of_invoice))
				{
					$this->db->select('*');
					$this->db->from('back_date_invoice_range');
				   
					$this->db->where('back_date_invoice_id', $back_date_data['0']->id);  // Also mention table name here
					$query = $this->db->get(); 
					$seleted_date=$query->result_array();
					foreach($seleted_date as $value)
					{
						$date_array['date'][]=date('d-m-Y',strtotime($value['date_range']));
					}
					if(!in_array($date_of_invoice,$date_array['date']))
					{
						$errors['invoice_date'] = 'This Date Not Valid For Create Invoice';
					}
				}
				  
		  }
		  else{
			    
			    $update_data=array('invoice_create'=>'0');
				$where=array('id'=>$back_date_data['0']->id);
			    $return=$this->base_model->update_record_by_id('back_date_invoice',$update_data,$where);
				//print_r($return);die;
				$errors['Validity']='Your Validity Is Expired To Create Back Date Invoice';
			    $this->session->set_flashdata('SuccessMessage','Your Validity Is Expired To Create Back Date Invoice '); 
				//redirect('invoice/sales_invoice_details','refresh');
			  
		  }
		}
		else{
			$errors['Access']='Your Have Not Access To Create Back Date Invoice';
			$this->session->set_flashdata('SuccessMessage','Your Have Not Access To Create Back Date Invoice'); 
				//redirect('invoice/sales_invoice_details','refresh');
			
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
		
		//if(isset($_FILES["user_image"]["name"]) && $_FILES['user_image']['error'] == '0' && !empty($_FILES["user_image"]["name"]))
				//{
					//$config['upload_path'] = './uploads/backdatefile/';
					//$config['allowed_types'] = 'gif|jpg|png|JPG|GIF|PNG|PDF|pdf';
					

					//$this->load->library('upload', $config);

					//if ( ! $this->upload->do_upload('user_image'))
					//{
						//$error = array('error' => $this->upload->display_errors());
                        //redirect('user/user_master?error=0');
						////$this->load->view('upload_form', $error);
					//}
					//else
					//{
						//$data = array('upload_data' => $this->upload->data());
						//if(!empty($data))
						//{
							//$userImage=$data['upload_data']['file_name'];
						//}
						
					//}
				//}
		//$invoice_number = $this->input->post('invoice_number');
		        //$customer_fname = '';
				//if($_FILES['customer_image']['name'] != '')
				//{	
					//$this->load->library('upload');
					//$fname = $_FILES['customer_image']['name'];
					//$config['upload_path'] = './uploads/backdatefile';
					//$config['allowed_types'] = 'gif|jpg|png|JPG|GIF|PNG|PDF|pdf';
					//$config['overwrite'] = FALSE;
					//$this->upload->initialize($config);
					//if ( ! $this->upload->do_upload()) 
					//{ 
						////echo $this->upload->display_errors(); 
					//}	
					//else
					//{
						//$this->upload->data();
						
					//}
				//}
				
		$invoice_date = $this->input->post('invoice_date');
		$customer_code = $this->input->post('customer_code');
		$customer_narration = $this->input->post('customer_narration');
		$customer_transaction_id = $this->input->post('customer_transaction_id');
		$customer_reference_number = $this->input->post('customer_reference_number');
	//	$customer_fname = $this->input->post('customer_image');
		//$userImage = $this->input->post('user_image');
		
		$customer_name = $this->input->post('customer_name');
		$customer_address = $this->input->post('customer_address');
		$customer_phone_number = $this->input->post('customer_phone_number');
		$customer_email_id = $this->input->post('customer_email_id');
		$customer_pan_number = $this->input->post('customer_pan_number');
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
									
									//'modal_customer_image' => $userImage,
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
				}else{
				$customer_id='0';	
				}
		
				}else{
					
					$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData_mob->customer_id));
					$customer_id = $customerData_mob->customer_id;
				}
			}else{
				$this->db->update('customer_master',$customerInsertData,array('customer_id'=>$customerData->customer_id));
				$customer_id = (isset($customerData->customer_id)) ? $customerData->customer_id : '0';
		}
}
		$product_id = $this->input->post('product_id');
		//$serial_number = $this->input->post('serial_number');
		$quantity = $this->input->post('quantity');
		$invoice_type = $this->input->post('invoice_type');
		$weight = $this->input->post('weight');
		$rate_per_quantity = $this->input->post('rate_per_quantity');
		$discount_percent = $this->input->post('discount_percent');
		$tax = $this->input->post('tax');
		$entryTax = $this->input->post('entry_tax');
		$net_amount = $this->input->post('net_amount');
		$total_amount = $this->input->post('total_amount');
		$surcharge_on_vat = ($this->input->post('surcharge_on_vat')) ? $this->input->post('surcharge_on_vat') : '0';
		$total_net_amount = $this->input->post('total_net_amount');
		$round_off=($this->input->post('round_off')) ? $this->input->post('round_off') : '0';
		/* if(isset($round_off) && !empty($round_off) && $round_off!='')
		{
				if($round_off<0)
				{
				  $total_amount=$total_net_amount;
				}
				if($round_off >=0)
				{
				  $total_amount=$total_net_amount;
				  
				}
			
		} */

		$received_amount = $this->input->post('received_amount');
		$amount_refunded = ($this->input->post('amount_refunded')) ? $this->input->post('amount_refunded') : '0';
		$invoiceId=$invoice_id;
		if($invoice_id==''){	
		$invoiceInsertData = array(
							'invoice_date'=>$invoice_date,
							'invoice_type'=>$invoice_type,
							'customer_id' => $customer_id,
							'total_amount' => $total_amount,
							'surcharge_on_vat' => $surcharge_on_vat,
							'amount_received' => $received_amount,
							'adjustment'=>$round_off,
							'amount_refunded' =>$amount_refunded,
							'narration' => $customer_narration,
							'customer_transaction_id' => $customer_transaction_id,
							'manual_invoice_number' => $customer_reference_number,
							'creator_id' => $this->session->userdata('user_id'),

							'createdOn' => date('Y-m-d H:i:s'),									

							);
              
		$this->base_model->insert_one_row('invoice_showroom_'.$office_id,$invoiceInsertData);

		$invoiceId = $this->db->insert_id();

		if(isset($_FILES['invoice_upload_document']) && $_FILES['invoice_upload_document']['error'] == '0') {

					$mime = $_FILES['invoice_upload_document']['type'];

					$fileSize = $_FILES['invoice_upload_document']['size'];

					if(in_array($mime,$this->mime_types)){
							$filename = $_FILES['invoice_upload_document']['name'];
							$ext = pathinfo($filename, PATHINFO_EXTENSION);

							$file_name = $invoiceId.'.'.$ext;

							$directory = getcwd() . '/uploads/Invoice';

							move_uploaded_file($_FILES['invoice_upload_document']['tmp_name'], $directory . '/' . $file_name);

							$data_document = array('invoice_upload_document' => $file_name	);

							$this->db->where('invoice_id', $invoiceId);
							$this->db->update('invoice_showroom_'.$office_id, $data_document);

					}

					else{

					

					}

            }
			
			$invoice_number = 1;
		$myInvoiceDate = explode("-",$invoice_date);
		
		$financialFirstYear = ($myInvoiceDate['1']<'04') ? date('y',strtotime('-1 year')) : date('y');
		
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

			// $invoice_number = str_pad($invoiceId,6,"0", STR_PAD_LEFT);

			// //$financialYear = date('y').'-'.(date('y') + 1);

			// $financialFirstYear = (date('m')<'04') ? date('y',strtotime('-1 year')) : date('y');

			// $financialSecondYear = $financialFirstYear+1;

			// $financialYear = $financialFirstYear.'-'.$financialSecondYear;
            if($_POST['invoice_type']=='backdateadvance')
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
				/* $product_serial_number="";
				if(($received_amount>=$total_net_amount) && ($_POST['invoice_type']!='backdateadvance') ) 
                 {
					 $invoice_product_serial_number = $_POST['serial_number_'.$key];
					 $product_serial_number=implode(',',$invoice_product_serial_number);
					 //print_r($product_serial_number);die;
				 } */
				
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
	$this->base_model->update_record_by_id('invoice_showroom_'.$office_id,array('adjustment'=>$round_off,'amount_refunded' =>$amount_refunded),array('invoice_id'=>$invoiceId));
}
//condition for advance mode
	
if($received_amount>=$total_net_amount) 
{
	
	
        $payment_mode_array=$this->input->post("payment_mode");
	    $cheque_relese=$this->input->post("cheque_relese");
	    $payment_mode_hidden=$this->input->post("payment_mode_hidden");
	    $invoice_payment_id=$this->input->post("invoice_payment_id");
		
		$conbine_array=array_combine($invoice_payment_id,$cheque_relese);
		//print_r($cheque_relese);
		//print_r($invoice_payment_id);
		//print_r($conbine_array);die;
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
							
							
							$historyData = array('current_stock'=>$arr_his_data->net_stock,'product_id'=>$value_product,'transfer_stock'=>$quantity[$key],'net_stock'=>($arr_his_data->net_stock-$quantity[$key]),'type_value'=>'BackDateInvoice','transfer_to'=>$arr_invoice_data->customer_id,'transaction_number'=>$arr_invoice_data->invoice_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s')); 
							$this->base_model->insert_one_row($history_table,$historyData);

			

			    
				
                if(($received_amount>=$total_net_amount) && ($_POST['invoice_type']!='backdateadvance') ) 
                 {
					foreach($invoice_product_serial_number as $serial_number){

						$insert_invoice_serial_data = array('invoice_id'=>$invoiceId,'invoice_product_id'=>$invoice_product_id[$value_product],'serial_number'=>$serial_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn' => date('Y-m-d H:i:s'));



						$this->base_model->insert_one_row('invoice_showroom_product_serial_number_'.$office_id,$insert_invoice_serial_data);



						$update_current_stock_serial_data = array('current_stock_status'=>'1');

						$this->base_model->update_record_by_id('product_current_stock_serial_number_'.$office_id,$update_current_stock_serial_data,array('product_serial_number'=>$serial_number));

					}
				 }
				 if(($invoice_id!='') && ($received_amount>=$total_net_amount) && ($_POST['invoice_type']=='backdateadvance'))
                 {
					 foreach($invoice_product_serial_number as $serial_number){

						$insert_invoice_serial_data = array('invoice_id'=>$invoiceId,'invoice_product_id'=>$invoice_product_id[$value_product],'serial_number'=>$serial_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn' => date('Y-m-d H:i:s'));
						$this->base_model->insert_one_row('invoice_showroom_product_serial_number_'.$office_id,$insert_invoice_serial_data);
						$update_current_stock_serial_data = array('current_stock_status'=>'1');

						$this->base_model->update_record_by_id('product_current_stock_serial_number_'.$office_id,$update_current_stock_serial_data,array('product_serial_number'=>$serial_number));

					}
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
						//print_r($keydata);
						//print_r($valuedata);die;
					 $data=$this->base_model->update_record_by_id('invoice_showroom_payment_mode_'.$office_id,array('cheque_release'=>$valuedata),array('invoice_payment_id'=>$keydata));	
					 //echo $data;die;
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
		$card_check_name   = $this->input->post('card_check_name');
		$card_issuing_bank = ($this->input->post("card_issuing_bank"));
		$cheque_relese=($this->input->post("cheque_relese"));
		
		foreach($payment_mode as $key=>$pm)
		{

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
	public function back_date_sales_invoice_edit()
	{
	$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		}else
		{

		$page_id = 28;
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
				$this->session->set_flashdata("error_message","You don't have permission back date sales invoice add.");
				redirect(base_url('user/dashboard'));
		}
		$header['title'] = "Edit BackDate Sales Invoice Form";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		$office_id = $this->session->userdata('office_id');
		$invoice_id = ($this->input->get('invoice_id')) ? base64_decode($this->input->get('invoice_id')) : '';
		
		
		 $this->db->select('showroom.*,cust.modal_customer_name,cust.modal_customer_email_id,cust.modal_customer_pan_number,cust.modal_customer_address,cust.id_proof,cust.id_proof_number,cust.modal_customer_phone_number')->from('invoice_showroom_'.$office_id.' as showroom');

		$aaa= $this->db->join('customer_master as cust','showroom.customer_id=cust.customer_id','left')->where('invoice_id',$invoice_id);

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
			$this->load->view('BackDateInvoice/back_date_sales_invoice_edit',$data);
		}
		$this->load->view('includes/_footer');
	}
    

	

	/* public function back_date_invoice_form(){

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		$this->load->view('inventory/back_date_invoice_form');

		$this->load->view('includes/_footer');

    } */

	

	/* public function sales_return_form(){

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		$this->load->view('inventory/sales_return_form');

		$this->load->view('includes/_footer');

    } */

	

	public function add_customer_info(){

		// print_r($_POST);

		//$this->form_validation->set_rules('modal_customer_name','Customer name', 'required|alpha_dash_dot');

		//$this->form_validation->set_rules('modal_customer_short_name','Customer short name', 'required|alpha_numeric|is_unique[customer_master.modal_customer_short_name]');

		//$this->form_validation->set_rules('modal_customer_email_id','Customer email-id', 'required|is_unique[customer_master.modal_customer_email_id]|valid_email');

		

		/*$this->form_validation->set_rules('modal_customer_phone_number','Customer phone', 'required');

		$this->form_validation->set_rules('modal_customer_mobile_number','Customer mobile', 'required');*/

		//$this->form_validation->set_rules('modal_customer_pan_number','Customer pan number', 'trim|regex_match[/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/]|exact_length[50]|xss_clean');
		
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
			$back_date = trim($this->input->post('back_date'));
            $date_back=date('d/m/Y',strtotime($back_date));
			//echo $date_back;die;
			$price_data = $this->db->get_where('price_master',array('product_id'=>$product_id,'price_date'=>$date_back))->row();

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
			break;
			case 'sales_invoice_change_products':
			$data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
			break;
			
		}

		$product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>date('d/m/Y')));

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



	/* public function stock_transfer_inventory(){

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		$this->load->view('inventory/stock_transfer_inventory');

		$this->load->view('includes/_footer');

	} */

	

	/* public function stock_receipt_inventory(){

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		$this->load->view('inventory/stock_receipt_inventory');

		$this->load->view('includes/_footer');

	}
 */
	

	/* public function product_stock_receipt_inventory(){

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		$this->load->view('inventory/product_stock_receipt_inventory');

		$this->load->view('includes/_footer');

	} */




	/* public function invoice_inventory()

	{

		$office_id = $this->session->userdata('office_id');

		$this->load->view("includes/_header");

		$this->load->view("includes/_top_menu");

		

		$data['invoice_master'] = $this->db->get_where('invoice_showroom_'.$office_id)->result();

		

		$this->load->view('inventory/invoice_inventory',$data);

		$this->load->view('includes/_footer');

	} */

	

	 public function back_date_sales_invoice_receipt()

	{

		$invoiceId =  base64_decode($this->input->get('invoice_id'));

		$office_id = $this->session->userdata('office_id');



		$data['invoiceDetails'] = $this->db->get_where('invoice_showroom_'.$office_id,array('invoice_id'=>$invoiceId))->row();

		$custId = $data['invoiceDetails']->customer_id;

		$data['customerDetails'] = $this->db->get_where('customer_master',array('customer_id'=>$custId))->row();

		

		$this->db->select('showroom.*,product_master.product_name')->from('invoice_showroom_product_'.$office_id.' as showroom');

		$this->db->join('product_master','showroom.product_id=product_master.product_id')->where('invoice_id',$invoiceId);

		$data['productDetails'] = $this->db->get()->result();

		$data['office_location'] = $this->db->get_where('office_master',array('office_id'=>$office_id))->row();

		$data['paymenttype_details'] = $this->db->get_where('invoice_showroom_payment_mode_'.$office_id,array('invoice_id'=>$invoiceId))->result();
		

		//$this->load->view('inventory/sales_invoice_receipt',$data);

		$this->load->view('BackDateInvoice/back_date_view_invoice',$data);

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

			

			if($pageName == "sales_invoice_form"){

				$data['serialMaster'] = $this->db->select($fieldName.' as serial_number')->get_where($table_name,array('product_id'=>$product_id,'current_stock_status'=>'0'))->result();

				$data['query'] = $this->db->last_query();

				$data['select_id'] = "serial_number-".$net_stock_id;

				$data['select_name'] = "serial_number_".$net_stock_id;

			}

			$data['net_stock_id'] = $net_stock_id;

			$data['product_id'] = $product_id;

			$data['quantity'] = $quantity;

			echo $this->load->view('includes/_product_serial_number_list',$data,true);

		}

	}
	public function _get_all_record_Sales_to_by_join($tableNameINVOICE,$office_operation_type,$office_id){
		
		$table_payment='invoice_showroom_payment_mode_'.$office_id;
		$this->db->select('invoice.*,customer_master.modal_customer_name,customer_master.modal_customer_pan_number')->from($tableNameINVOICE.' as invoice');
		$this->db->join('customer_master as customer_master','invoice.customer_id=customer_master.customer_id','left');
		$check_back_date_data = array("backdatesale","backdatecredit","backdateproforma","backdatecorporate","backdateadvance");
		$this->db->where_in("invoice.invoice_type",$check_back_date_data);
		$this->db->order_by('invoice.invoice_id','DESC');
		$data = $this->db->get()->result();
		return $data;
	}
	public function back_date_sales_invoice_details(){
			$check_super_admin=$this->session->all_userdata();
		if($check_super_admin["role_id"]==0 && $check_super_admin["office_id"]==0)
		{
		$add_value = 1;
		$edit_value = 2;
		$view_value = 3;
		}else
		{

		$page_id = 28;
		$page_permission_array = $this->role_model->get_page_permission($page_id);
		$add_value = $page_permission_array->add_value;
		$edit_value = $page_permission_array->edit_value;
		$view_value = $page_permission_array->view_value;
		}
		if($view_value == "0")
		{
				$this->session->set_flashdata("error_message","You don't have permission to view.");
				redirect(base_url('user/dashboard'));
		}
		$data=array();
		$office_operation_type=$this->session->userdata('office_operation_type');
		$office_id=$this->session->userdata('office_id');
		//$customer_id=$this->session->userdata('customer_id');
	    $tableNameINVOICE='invoice_'.$office_operation_type.'_'.$office_id;	
		$header['title'] = "BackDate Sales Invoice Details";
		$this->load->view("includes/_header",$header);
		$this->load->view("includes/_top_menu");
		 $data['invoice_details']=$this->_get_all_record_Sales_to_by_join($tableNameINVOICE,$office_operation_type,$office_id);
		$this->load->view('BackDateInvoice/back_date_sales_invoice_details',$data);
		$this->load->view('includes/_footer');
	}
	public function getdate_time()
	{
		echo date('Y-m-d H:i:s');
	}

public function productsbydate()
{
	            
	            $office_id = $this->session->userdata('office_id');
				$date1=$this->input->post('incoice_date_value');
				$date=date('d/m/Y',strtotime($date1));
				
	            $data['pageName']=$this->input->post('pageName');
	            $data['product_master'] = $this->inventory_model->getAllProductsByOfficeId($office_id);
                // print_r($data['product_master']);die;
				$product_today_price_master = $this->base_model->get_all_record_by_condition('price_master',array('price_date'=>$date));

				$todaysProducts = array();
				
				foreach($product_today_price_master as $todaysProduct){

					$todaysProducts[] = $todaysProduct->product_id;

				}

				$data['todaysProducts'] = $todaysProducts;
				echo $this->load->view('includes/_AjaxAddNewDivCommon',$data,true);
	
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

			$page_id = 28;
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
				$office_operation_type=$this->session->userdata('office_operation_type');
				$office_id=$this->session->userdata('office_id');
				$table_invoice='invoice_'.$office_operation_type.'_'.$office_id;
				$table_productinvoice='invoice_'.$office_operation_type.'_product_'.$office_id;
				$table_productserialinvoice='invoice_'.$office_operation_type.'_product_serial_number_'.$office_id;
				$table_history='inventory_office_history_'.$office_id;
				$table_current='product_current_stock_'.$office_id;
				$table_current_serials='product_current_stock_serial_number_'.$office_id;
				$invoice_data=$this->db->get_where($table_invoice,array('invoice_id'=>$invoice_id))->row();
				
		    $delete_data = array('is_deleted'=>'1',
			'delete_by_user'=>$this->session->userdata('user_id'),
			'deleted_date'=>date('Y-m-d H:i:s')
			 );
				//$this->db->delete('invoice_'.$office_operation_type.'_payment_mode_'.$office_id,array('invoice_id'=>$invoice_data->invoice_id));
				$this->db->update('invoice_'.$office_operation_type.'_payment_mode_'.$office_id, $delete_data, array('invoice_id'=>$invoice_data->invoice_id));
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
						  $delete_data = array('is_deleted'=>'1',
			             'delete_by_user'=>$this->session->userdata('user_id'),
			             'deleted_date'=>date('Y-m-d H:i:s')
			              );
						//$this->db->delete($table_productserialinvoice,array('invoice_product_id'=>$value->invoice_product_id));
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
							
							
							
							$historyData = array('current_stock'=>$arr_his_netstock_latest->net_stock,'product_id'=>$value->product_id,'received_stock'=>$arr_his_data->transfer_stock,'net_stock'=>($arr_his_netstock_latest->net_stock+$arr_his_data->transfer_stock),'type_value'=>'Back Date Invoice Deleted','received_from'=>$arr_his_data->transfer_to,'transaction_number'=>$arr_his_data->transaction_number,'creator_id'=>$this->session->userdata('user_id'),'createdOn'=>date('Y-m-d H:i:s'),'extra_field_1'=>$reason); 
							
							
							$this->base_model->insert_one_row($table_history,$historyData);
						}
				}
				     $delete_data = array('is_deleted'=>'1',
			             'delete_by_user'=>$this->session->userdata('user_id'),
			             'deleted_date'=>date('Y-m-d H:i:s')
			              );
				//$this->db->delete($table_productinvoice,array('invoice_id'=>$invoice_id));
			$this->db->update($table_productinvoice,$delete_data,array('invoice_id'=>$invoice_id));
			//$this->db->delete($table_invoice,array('invoice_id'=>$invoice_id));
			$this->db->update($table_invoice,$delete_data,array('invoice_id'=>$invoice_id));
			
			echo '1';	
		    }
        }
  else{
	  $this->session->set_flashdata('SuccessMessage',DELETE_MSG_COMMON_PERMISSION_ERROR);
	  echo '2';
  }
	
}


public function back_date_sales_invoice_edit_form()
	{
	
		$this->load->view("includes/_header");
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
			
			$this->load->view('BackDateInvoice/back_date_sales_invoice_edit_form',$data);
		}
		$this->load->view('includes/_footer');
	}
public function saveInvoiceDataEdit()
{

if($_POST){
	//echo "<pre>";
//print_r($_POST);die;
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
				// && $value=='cash'
				if(empty($_POST['id_proof']) && isset($_POST['id_proof']) &&  $_POST['payment_mode_amount'][$key]>=50000) {
				$errors['id_proof'] = 'ID Proof required';
				}
				//&& $value=='cash'
				if(empty($_POST['id_proof_number']) &&  $_POST['payment_mode_amount'][$key]>=50000) {
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
						/*if($_POST['received_amount'] >= $_POST['total_net_amount'])
						{
							$quantity=$_POST['quantity'][$key];
				//	$stock_product_serial_number=$postedArr['stock_product_serial_number'];
							$serial_number=$_POST['serial_number_'.$key];
							if($quantity!=count($serial_number))
							{
							//$errors['serial_number_'.$key.'_chosen'] = 'Please select '.$quantity.' serial number';
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
								 
								   $errors['card_issuing_bank_'.$key] = 'Issuing bank name is required';				
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
					    if(empty($_POST['card_issuing_bank'][$key]))
							  {
								 
								   $errors['card_issuing_bank_'.$key] = 'Issuing bank name is required';				
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
					$errors['cheque_relese_'.$key] = 'Cheque Realization is required';		
						
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

		
		
		$table_invoice='invoice_'.$office_operation_type.'_'.$office_id;
		$table_productinvoice='invoice_'.$office_operation_type.'_product_'.$office_id;
		$table_productserialinvoice='invoice_'.$office_operation_type.'_product_serial_number_'.$office_id;
		$table_history='inventory_office_history_'.$office_id;
		$table_current='product_current_stock_'.$office_id;
		$table_current_serials='product_current_stock_serial_number_'.$office_id;
		$invoice_data=$this->db->get_where($table_invoice,array('invoice_id'=>$invoice_id))->row();
		
		
	//	$this->db->delete($table_history,array('transaction_number'=>$invoice_data->invoice_number));
	

		
		$this->db->delete('invoice_'.$office_operation_type.'_payment_mode_'.$office_id,array('invoice_id'=>$invoice_data->invoice_id));
		 
	
	/*	$invoice_product_data=$this->db->get_where($table_productinvoice,array('invoice_id'=>$invoice_id))->result();
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
		
		$this->db->delete($table_productinvoice,array('invoice_id'=>$invoice_id));*/
		
		
		

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

	

		$invoiceInsertData = array('invoice_type'=>$invoice_type,
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

				//$this->base_model->insert_one_row('invoice_showroom_product_'.$office_id,$productInsertData);

				

				$invoice_product_id[$value_product] = $this->db->insert_id();

			}


//condition for advance mode
	
if($received_amount>=$total_net_amount) 
{
			
			foreach($product_id as $key=>$value_product)
			{
				
					

				$table_name_product_current='product_current_stock_'.$office_id;

				

				$invoice_product_serial_number = $_POST['serial_number_'.$key];

				

				/*$inventory_stock_product_current_stock=$this->base_model->get_record_by_id($table_name_product_current,array('product_id'=>$value_product));

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
							$this->base_model->insert_one_row($history_table,$historyData);

			

				

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
		$card_issuing_bank = $this->input->post('card_issuing_bank');
		$card_check_name = $this->input->post('card_check_name');
		
		foreach($payment_mode as $key=>$pm)
		{

		$invoicePaymentModeData = array('invoice_id'=>$invoiceId,
									'payment_type' => $pm,
									'payment_amount' => $payment_mode_amount[$key],
									'card_cheque_number' => $card_check_number[$key],
									'bank_name'=>$card_check_name[$key],
									'card_issuing_bank'=>$card_issuing_bank[$key],
									'creator_id' => $this->session->userdata('user_id'),
									'createdOn' => date('Y-m-d H:i:s')								

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



}

