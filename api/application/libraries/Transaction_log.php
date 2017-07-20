<?php 

/**
* 
*/
class Transaction_log
{
	
	public function __construct(){		
		$this->CI =& get_instance();
		$this->CI->load->model(array('M_workflow_transaction','M_workflow_transaction_log','M_workflow','M_workflow_transition','M_transaction_properties','M_crud'));
		$this->CI->load->library('service_execute');
		$this->CI->load->database();		
		$this->table_parent = '';
		$this->data_parameter = array();
		date_default_timezone_set("Asia/Bangkok");
	}

	public function save($value,$is_waiting_time = false)
	{		
		$params_service = array(
			'Workflow_ID' => $value['Workflow_ID'],
			'Transition_ID' => $value['Auto_Transition_ID']
			);
		$params_workflow = array(
			'Workflow_ID' => $value['Workflow_ID'], 
			'Workflow_Version' => $value['Workflow_Version'], 
			'Active' => 1, 
			);		
		$workflow = $this->CI->M_workflow->get_where($params_workflow)->row_array();
		$value['Form_ID'] = $workflow['Form_ID'];
		$data_parameter = $this->get_data_parameter($value);
		$list_service = $this->get_service_workflow($params_service);		

		$service_initial = $this->get_service_execution($list_service,1);		
		if ($is_waiting_time) {
			$params_auto = array(
				'Idx' => $value['Auto_Idx'],
				'Start_Execute' => date('Y-m-d H:i:s'),
				);
			$this->CI->M_workflow_transaction->upd_auto_start($params_auto,'Idx');
		}
		if ($service_initial) {

			$service_validation = $this->get_service_execution($list_service,2);	

			if ($service_validation) {				
				
				$service_before_execute = $this->get_service_execution($list_service,4);

				$params_upd = array(			
					'Idx' => $value['Log_Idx'],
					'Parent_Transaction_ID' => $value['Parent_Transaction_ID'],
					'Workflow_To_State_Name' => $value['Workflow_To_State_Name'],
					'User_ID' => 'Auto',
					'Transaction_Timestamp' => date("Y-m-d H:i:s"),
					'Transition_ID' => $value['Auto_Transition_ID'],
					'Workflow_Is_Completed' => $value['End_State'],
					'Active' => 0,
					'Status' => 0,
					'Transaction_Header_ID' => $value['Transaction_Header_ID'],
				);		

				$res_upd = $this->upd_tostate($params_upd);
				if ($is_waiting_time) {
					$params_auto = array(
						'Idx' => $value['Auto_Idx'],
						'End_Execute' => date('Y-m-d H:i:s'),
						'excecuted' => 1,
						);
					$this->CI->M_workflow_transaction->upd_auto_start($params_auto,'Idx');
				}
				if ($value['End_State'] != 1) {				
					$params_new_state = array(
						'Transaction_ID' => '',
						'Parent_Transaction_ID' => $value['Parent_Transaction_ID'],
						'Workflow_ID' => $value['Workflow_ID'],
						'Workflow_Version' => $value['Workflow_Version'],
						'Transaction_Number' => $value['Transaction_Number'],
						'Workflow_State_Name' => $value['Workflow_To_State_Name'],			
						'User_ID' => ifunsetempty($_POST,'User_ID',0),
						'Transaction_Timestamp' => date("Y-m-d H:i:s"),
						'Workflow_Is_Completed' => 0,
						'Active' => 1,
						'Status' => ifunsetempty($_POST,'Status',0),
						'Transaction_Header_ID' => $value['Transaction_Header_ID'],
					);
					$service_after_execute = $this->get_service_execution($list_service,5);
					$new_state_id = $this->simpan_newstate($params_new_state);			
					$business_rule = $this->check_business_rule($value,$new_state_id);					
				}else{
					$service_after_execute = $this->get_service_execution($list_service,5);
				}						
			}else{
				$service_validation_failed = $this->get_service_execution($list_service,3);					
			}
			
		}

		$out = false;
		if ($res_upd) {
			$out = true;
		}

		return $out;
	}

	public function upd_tostate($params)
	{		
		$where = array(
			'Idx' => $params['Idx'],
			);
		unset($params['Idx']);
		$res = $this->CI->M_workflow_transaction_log->upd($params,$where);
		return $res;
	}

	public function execute_services($service)
	{
		$result = true;
		$allow =  array('user_attribute', 'transaction_property_name_static', 'transaction_property_data', 'parent_transaction_property_data');
		$params = array();
		$grouping = array();
		$list_table = array();
		foreach ($service['Parameter'] as $key => $value) {

			$params[$value['Parameter_Name']] = $this->get_value_parameter($value['Field']);
			
			if ($value['Is_Grouping'] == 1 && !in_array($value['Table_Name'],$allow)) {
                $grouping[] = $value;
            }

            if (!in_array($value['Table_Name'],$allow)) {
            	if (!isset($list_table[$value['Table_Name']])) {
            		$list_table[$value['Table_Name']] = array();
            		$list_table[$value['Table_Name']][] = $value;
            	}else{
            		$list_table[$value['Table_Name']][] = $value;
            	}
            }

		}
		$params['token'] = 1;
		if (count($grouping) > 0) {
			echo "asas";
		}else{

			$url = $this->CI->config->item('base_url').'index.php/service_library/'.$service['Service_ID'];
			$result = $this->CI->service_execute->servicePost($url,$params);			
		}

		return $result;
		

	}

	public function simpan_newstate($params)
	{		
		$where = array(
			'Workflow_ID' => $params['Workflow_ID'],
			'Workflow_To_State_Name' => $params['Workflow_State_Name'],
			'Transaction_Header_ID' => $params['Transaction_Header_ID'],
			'Parent_Transaction_ID' => $params['Parent_Transaction_ID'],		
			);		
		$params['Transaction_ID'] = $this->CI->M_workflow_transaction_log->get_where($where)->Transaction_ID;
		$params['Workflow_Version'] = $this->CI->M_workflow->get_workflow_version($params['Workflow_ID']);
		$res = $this->CI->M_workflow_transaction_log->add($params);
		return $this->CI->db->insert_id();				
	}	

	public function get_service_execution($data = array(),$time_exec = 0)
	{		
		$out = true;
		$list = array();
		foreach ($data as $val) {

			if ($val['Execution_Time'] == $time_exec) {
				$list[] = $val;
			}
		}
		if (count($list) > 0) {			
			foreach ($list as $value) {
				$out = $this->execute_services($value);
				if (!$out) {					
					break;
				}
			}
		}

		return $out;
	}

	public function get_service_workflow($params,$encode = false)
	{		
		$res = $this->CI->M_workflow_transition->get_service_workflow($params);
		$data = array();		
		$data1 = array();				
		foreach ($res->result_array() as $key => $val) {									
			$data1[$val['Service_ID'].$val['Execution_State']] = array(
				"Service_Name" => $val['Service_Name'],
				"Service_ID" => $val['Service_ID'],
				"Execution_Time" => $val['Execution_State'],
				"Execution_Order" => $val['Execution_Order'],		
				"Status" => '-',
				);
			if ($val['Parameter_Name']  != '') {

				$field = '';
				if ($val['Parameter_Table'] != '') {
					$field =  $val['Parameter_Table'];
					if ($val['Parameter_Field'] != '') {
						$field = $field.".".$val['Parameter_Field'];						
					}					
				}

				$data[$val['Service_ID'].$val['Execution_State']]['Parameter'][] = array(
					'Field' => $field,
					'Parameter_Name' => $val['Parameter_Name'],
					'Table_Name' => $val['Parameter_Table'],
					'Is_Grouping' => $val['Is_Grouping']

				);			

			}else{
				$data[$val['Service_ID'].$val['Execution_State']]['Parameter'] = array();
			}
		}				

		$result = array();
		foreach($data1 as $key=>$val){ // Loop though one array
		    $val2 = $data[$key]; // Get the values from the other array
		    $result[$key] = $val + $val2; // combine 'em
		}

		if ($encode) {
			$out = array(
				'success' => true,
				'data' => array_values($result),			
			);
			echo json_encode($out);
		}else{
			return array_values($result);
		}
	}

	public function get_data_parameter($value)
	{
		$params = array(
				'Workflow_ID' => $value['Workflow_ID'],				
				'Form_ID' => $value['Form_ID'],
				'Transaction_ID' => $value['Transaction_ID'],
				'Parent_Transaction_ID' => $value['Parent_Transaction_ID'],
				'Transaction_Header_Idx' => $value['Transaction_Header_ID'],
			);

		$params_property = array(
			'Transaction_ID' => $params['Transaction_ID'],
			'Parent_Transaction_ID' => $params['Parent_Transaction_ID']
			);

		
		$q_property = $this->CI->M_transaction_properties->get_data($params_property);

		$property = array(
				'transaction_property_data'=>array(),
				'parent_transaction_property_data'=>array()
			);
		foreach ($q_property->result_array() as $key => $val) {
			if ($val['Transaction_ID'] == $params['Parent_Transaction_ID']) {
				$property['transaction_property_data'][$val['Property_Name']] = $val['Property_Value'];
			}else{
				$property['parent_transaction_property_data'][$val['Property_Name']] = $val['Property_Value'];
			}
		}
		$property['transaction_property_data']['Transaction_ID'] = $params['Transaction_ID'];
		$property['parent_transaction_property_data']['Transaction_ID'] = $params['Parent_Transaction_ID'];
		
		$property['user_attribute_adhoc'] = $this->get_user_adhoc($value['User_ID']);
		$property['user_attribute'] = $this->get_user($value['User_ID']);
		$params_lov = array(
			'Form_ID' => $value['Form_ID'],
			);
		$res_lov_fields = $this->CI->M_workflow_transaction_log->get_table_lov($params_lov);
		
		$q_tbl = $this->CI->db->query("select tbl.Parent_Table,tbl.Table_Name,tbl.Structure_Level, fld.Field_Name, fld.FK_Parent from c_transaction_form_tables tbl
			left JOIN c_transaction_form_field_configuration fld on tbl.Table_Name=fld.Table_Name and tbl.Form_ID=fld.Form_ID and LENGTH(FK_Parent)>0
			where tbl.Form_ID=? AND tbl.Active=1 
			order by tbl.Structure_Level asc
		",array($params['Form_ID']));

		$table_child_relation = $this->CI->M_workflow_transaction_log->get_list_tables_child($params_lov);	
		
		$table = array();
		$list_lov = array();
		$table_parent_id = array();
		foreach ($q_tbl->result_array() as $key => $value) {						

				$list_lov = array();
				foreach ($res_lov_fields as $lov) {				
					$temp_relation = array(
						'LOV_ID' => $lov['LOV_ID'],
	                    'Field_Name' => $lov['Field_Name'],
	                    'Display_Field' => $lov['Display_Field'],
	                    'Value_Field' => $lov['Value_Field'],
	                    'LOV_Source_Table' => $lov['LOV_Source_Table'],
						);			

					if ($lov['Table_Name'] == $value['Table_Name']) {						
						if (isset($list_lov[$lov['Table_Name'].$lov['LOV_ID']])) {
							$list_lov[$lov['Table_Name'].$lov['LOV_ID']] = array();
						}
						$list_lov[$lov['Table_Name'].$lov['LOV_ID']][] = $temp_relation;
					}
				}

				if($value['Structure_Level']==1){					
					$table_parent_id[$value['Table_Name']] = array();
					$this->table_parent = $value['Table_Name'];					
					$params_table = array(
						'Id' => $params['Transaction_Header_Idx'],
						'Table_Name' => $value['Table_Name'],
						'Table_LOV' => json_encode($list_lov),			
						);
					$data = $this->CI->M_workflow_transaction->get_data($params_table,false);
					$table[$value['Table_Name']][] = $data;					
				}elseif($value['Structure_Level']==2){					

					$table_parent_id[$value['Table_Name']]=$this->_get_data_table_transaksi($table[$value['Parent_Table']],$value['FK_Parent']);
					$params_table = array(						
						'Table_Name' => $value['Table_Name'],
						'Table_LOV' => json_encode($list_lov),			
						'Relation' => $value['Field_Name'],
						'Value' => $table_parent_id[$value['Table_Name']][0]
						);
					$data = $this->CI->M_workflow_transaction->get_data($params_table,true);					
					$table[$value['Table_Name']] = $data;					
					
				}else{
					$table_parent_id[$value['Table_Name']]=$this->_get_data_table_transaksi($table[$value['Parent_Table']],$value['FK_Parent']);
					$x = 0;
					foreach ($table_parent_id[$value['Table_Name']] as $key_id => $value_id) {
						if ($x == 0) {
							$relation_id = $value_id;
						}else{
							$relation_id .= ','.$value_id;
						}
						$x++;
					}

					$params_table = array(						
						'Table_Name' => $value['Table_Name'],
						'Table_LOV' => json_encode($list_lov),			
						'Relation' => $value['Field_Name'],
						'Value' => $relation_id
						);
					$data = $this->CI->M_workflow_transaction->get_data($params_table,true);					
					$table[$value['Table_Name']] = $data;					
								
				}			
		}
				
		$property['table'] = $table;
		$this->data_parameter = $property;
		return $property;
		/*echo json_encode($property);
		echo json_encode($q_task->result());
		echo json_encode($q_lov_field->result());*/
	}

	function get_value_parameter($param)
	{
		$source = explode('.', $param);
		$source_from = $source[0];
		$source_length = count($source);
		$source_value = $source[($source_length-1)];		
		$value = '';
		if ($source_length == 1 || $source_from == 'transaction_property_name_static') {
			$value = $source_value; 
		}else if ($source_length == 2 && $source_from != 'transaction_property_name_static') {
			if ($source_from == 'transaction_property_data' || $source_from == 'parent_transaction_property_data' || $source_from == 'user_attribute') {
                if (isset($this->data_parameter[$source_from][$source_value])) {
                    $value = $this->data_parameter[$source_from][$source_value];
                }
            }else{            	
            	 if ($source_from == $this->table_parent) {            	 	
            	 	if (isset($this->data_parameter['table'][$source_from])) {            	 		            	 		
                    	$value = $this->data_parameter['table'][$source_from][0][$source_value];
            	 	}
                } else {                	
                    if (isset($this->data_parameter['table'][$source_from])) {
                    	if (count($this->data_parameter['table'][$source_from]) > 0) {                    		
                    		$data = $this->data_parameter['table'][$source_from];
                    		$num = 0;
                    		$str = '';
                    		$is_numeric = false;
                    		foreach ($data as $key => $rec) {
                    			if ($key == 0) {                    					                    					
                    				if (is_numeric($rec[$source_value])) {
                    					$num = $rec[$source_value];
                    					$is_numeric = true;
                    				}else{
                    					$str = $rec[$source_value];
                    					$is_numeric = false;
                    				}
                    			}else{
                    				if (is_numeric($rec[$source_value])) {
                    					$num = $num+$rec[$source_value];
                    					$is_numeric = true;
                    				}else{
                    					$str = $str.','.$rec[$source_value];
                    					$is_numeric = false;
                    				}
                    			}
                    		}                    		
                    		if ($is_numeric) {
                    			$value = $num;
                    		}else{
                    			$value = $str;
                    		}
                    	}
                    }
                }
            }
		}

		return $value;
	}

	public function get_user($id){
		$params_user = array(
				'id' => $id,
				'origin_sts' => 1,
			);		
		$res = $this->CI->M_crud->get_where('vemployee',$params_user);	
		return $res->row_array();
	}

	public function get_user_adhoc($id){
		$params_user = array(
				'id' => $id
			);
		$data = array();
		$res = $this->CI->M_crud->get_where('vemployee',$params_user);
		foreach ($res->result_array() as $value) {
			foreach ($value as $key => $key_value) {					
				if (isset($data[$key])) {
					array_push($data[$key],$key_value);						
				}else{
					$data[$key] = array();
					array_push($data[$key],$key_value);
				}
			}
		}
		return $data;
	}


	protected function compare_condition($source,$value)
	{
		$result = true;
		$operator = $source['Condition_Operator'];
		$value_condition = $source['Condition_Value'];

		if ($value == 'nothing' || $value == 'waiting_time') {
                if ($value == 'waiting_time') {                    
                    return 'waiting_time';
                }else{
                    return 'is_nothing';
                }
            }

        if ($operator == '=') {
            if ($value == $value_condition) {
                $result = true;
            }
        } else if ($operator == 'EQUAL' || $operator == 'NOT_EQUAL') {
            
        } else if ($operator == 'LIKE') {
        	if (strpos($value,$value_condition)) {
        		$result = true;
        	}        
        } else if ($operator == 'NOT LIKE') {
            if (!strpos($value,$value_condition)) {
        		$result = true;
        	}
        } else if ($operator == 'LIKE_STARTWITH') {
            $length = strlen($condition_value);
            return (substr($value,0,$length) == $condition_value);
        } else if ($operator == 'LIKE_AFTERWITH') {
            $length = strlen($condition_value);
            return (substr($value,-$length) == $condition_value);
        } else if ($operator == 'BETWEEN') {
            $cond = explode(',',$condition_value);
            	$res = '';
            	foreach ($cond as $key => $rec) {
            		$res .= $res.$value.' '.$rec;
            		if ($key > 0) {
            			$res .= ' && ';
            		}
            	}               
            if (eval('return '.$res.';')) {
                $result = true;
            }

        } else {
            
            $res = eval('return '.$value.' '.$operator.' '.$condition_value.';');
            if ($res) {
                $result = true;
            }
        }

        return $result;
	}

	public function task_business_rule($state,$task,$business_rule,$log_id)
	{		
		foreach ($task as $value) {			

			$source = $value['Workflow_Task_Name'];
			$match = true;
			$is_nothing = false;
			$is_waiting_time = false;

			foreach ($business_rule as $key => $val) {
				$data = $val['Condition_Parameter'];				
				if (!strpos($source,'['.$val['ID_BUSINESS_RULE'].']')) {
					$result = $this->compare_condition($val,$data);
					if (gettype($result) != 'boolean') {
						if ($val['Condition_Parameter'] == 'nothing' || $val['Condition_Parameter'] == 'waiting_time') {
							if ($val['Condition_Parameter'] == 'waiting_time') {
								
								$date = date_create();
								$time_unix = date_timestamp_get($date);		
								$waiting = (int) $val['Condition_Value'];
								$waiting_time = date('Y-m-d H:i:s',$time_unix+($waiting*60));				
								$params = array(
									'Transaction_ID' => $state['Transaction_ID'],
									'Transition_ID' => $value['Idx'],
									'Waiting_Time' => $waiting_time,
									'User_ID' => 'Auto',
									'excecuted' => 0,
									);								
								$this->CI->M_workflow_transaction->save_auto_start($params);
								$is_waiting_time = true;														
							}
							$is_nothing = true;							
							break;
						}
					}else{
						$source = str_replace('['.$val['ID_BUSINESS_RULE'].']', $result, $source);
					}
				}								
			}

			if (!$is_waiting_time) {
				if (!$is_nothing) {
					$match = eval('return '.$source.';');
				}

				if ($match) {
					$state['Log_Idx'] = $log_id;
					$state['Auto_Transition_ID'] = $value['Idx'];
					$state['Workflow_State_Idx'] = $value['Workflow_State_Idx'];
					$state['Workflow_to_State_Idx'] = $value['Workflow_to_State_Idx'];
					$state['Workflow_State_Name'] = $value['Workflow_State_Name'];
					$state['Workflow_To_State_Name'] = $value['Workflow_To_State_Name'];					
					$this->save($state);
				}
			}


		}
	}

	public function check_business_rule($state,$log_id){
		$params = array(			
			'Workflow_ID' => $state['Workflow_ID'],
			'Workflow_State_Idx' => $state['Workflow_to_State_Idx'],
		);				

		$res = $this->CI->M_workflow_transition->check_business_rule($params);		
		$task_bussiness_rule = array();
		foreach ($res['items'] as $key => $value) {
			$value['index'] = $key;
			if ($value['Workflow_Task_Idx'] == 0) {
				$task_bussiness_rule[] = $value;
			}			
		}		
		if (count($task_bussiness_rule) > 0) {
			$this->task_business_rule($state,$task_bussiness_rule,$res['business_rule'],$log_id);
		}

		return $res;
		
	}


	function _get_data_table_transaksi($record,$kolom){
		$out=array();
		foreach ($record as $key => $value) {
			foreach ($value as $fld => $val) {
				if($fld==$kolom){
					$out[]=$val;
				}
			}
		}
		return $out;
	}
}