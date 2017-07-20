<?php 
/**
* 
*/
class List_of_value_test
{
	
	public function __construct(){		
		$this->CI =& get_instance();
		$this->CI->load->model('M_list_of_value');
		$this->CI->load->database();
	}

	

	public function lov_test($params = array()){		


		$params['User_ID'] = json_decode($params['User_ID']);
		$params['LOV_Items'] = make_wherein_from_array(explode(',', $params['LOV_Items']));

		$query = $this->get_query_lov_permission($params);		
		extract($query);
		extract($params);
		$valid = TRUE;
		$msg = ', ';

		$where = array();

		if(isset($query['LOV']->LOV_Key) AND !empty($LOV_Items))
		{
			$where[] = "QUERY.".$query['LOV']->LOV_Key." = $LOV_Items";
		}
		else
		{
			$valid = FALSE;
		}

		if(!empty($Access_Level))
		{
			$opt = '=';
			if($Or_Higher == 1) $opt = '>=';
			$where[] = "QUERY.GNTLOV_Permission_Level $opt $Access_Level";
		}
		else
		{
			$valid = FALSE;
		}

		if(count($where) > 0)
		{
			$where = "WHERE (".implode(' AND ', $where).") OR 1 = ".$Optimistic;
		}
		else
		{
			$valid = FALSE;
		}

		$testing = 0;
		if($valid)
		{
			$query_last = "SELECT *\n
			FROM ( $Result_Query ) QUERY\n
			$where";
			$res = $this->CI->db->query($query_last);
			$testing = $res->num_rows();
		}

		$out = array(
			'success' => $valid,
			'msg' => ($msg==', ')?'':$msg,
			'result_testing' => $testing
		);

		return $out;
	}

	public function get_query_lov_permission($params)
	{
		$res = $this->CI->M_list_of_value->get($params);
		$data = array();
		$data_permission = array();

		if($res->num_rows())
		{
			$data = $res->first_row();
			$params_permission = array(
				'LOV_ID' => $data->LOV_ID
			);
			$res_permission = $this->CI->M_list_of_value->get_permission($params_permission);
			$data_permission = $res_permission->result();
		}

		$query = $this->generate_query_simple_testing($data, $data_permission, $params['User_ID']);
		$out = array(
			'LOV' => $data,
			'Permission' => $data_permission,
			'Result_Query' => $query
		);
		return $out;
	}

	public function generate_query_simple_testing($data_lov = array(), $data_permission = array(), $User_ID = array())
	{
		$array_User_ID = $User_ID;
		$array_User_ID[] = 'All User';
		
		$User_ID = make_wherein_from_array($User_ID);

		$main_query = '';
		$LOV_Key = '';

		if(!empty($data_lov->Generated_Query))
		{
			$main_query = $data_lov->Generated_Query;
		}

		if(!empty($data_lov->LOV_Key))
		{
			$LOV_Key = $data_lov->LOV_Key;
		}

		$join = array();
		$case_permission = array();

		foreach ($data_permission as $key => $value) {
			// mengisi nilai awal
			$key = 'SQ'.($key+1);
			$string_Permission_Subject_List = '';
			$string_Item_Criteria = '';
			$array_Permission_Subject_List = array();
			$join_vemployee = '';

			$value = (array) $value;
			extract($value);

			if($Permission_Subject_List != '' && $Permission_Subject_List != '[]')
				$array_Permission_Subject_List = json_decode($Permission_Subject_List);

			if(count($array_Permission_Subject_List)>0)
				$string_Permission_Subject_List = make_wherein_from_array($array_Permission_Subject_List);

			if($Item_Criteria!='')
				$string_Item_Criteria = make_wherein_from_array(explode(',', $Item_Criteria));

			$where = array();
			if(count($array_Permission_Subject_List)>0)
			{
				// nanti disini harusnya ada tambahan dari vemploye
				$subject_list = array();
				foreach ($array_Permission_Subject_List as $key1 => $value1) {
					foreach ($array_User_ID as $key2 => $value2) {
						$subject_list[] = "'$value1' = '$value2'";
					}
				}
				$whr = array();
				if(count($subject_list)>0)
				{
					$whr[] = '('.implode(' OR ', $subject_list).')';
				}

				if($Relationship_Pattern != '')
				{
					$Relationship_Pattern = json_decode($Relationship_Pattern, TRUE);
					if(isset($Relationship_Pattern['LOV_Parameter']) && isset($Relationship_Pattern['User_Parameter']))
					{
						extract($Relationship_Pattern);
						$join_vemployee = "LEFT JOIN vemployee VE ON VE.code IN ($User_ID)";
						$whr[] = "VE.$User_Parameter = MQ.$LOV_Parameter";
					}
				}
				$where[] = "(".implode(' AND ', $whr).")";
			}

			switch ($Item_Criteria_Type) {
				case 'all_items':
					
					break;
				case 'select':
					$where[] = "MQ.$LOV_Key IN ($string_Item_Criteria)";
					break;

				case 'pattern':
					if($value['Item_Criteria']!='')
					{
						$conf = json_decode($value['Item_Criteria'], TRUE);
						extract($conf);
						if($string_start != -1 AND $string_length != -1)
							$whr = "SUBSTRING(MQ.$field, $string_start, $string_length) LIKE '$string_value'";
						else
							$whr = "MQ.$field = $number_value";

						$where[] = $whr;
					}
					break;
			}

			if(count($where) > 0)
			{
				$where = "WHERE ".implode(' AND ', $where);
			}
			else
			{
				$where = '';
			}


			$case_permission[$key] = "WHEN $key.GNTLOV_Permission_Level IS NOT NULL THEN $key.GNTLOV_Permission_Level";
			$sql = "SELECT MQ.*, $Permission_Level AS GNTLOV_Permission_Level\n
				FROM ($main_query) MQ\n
				$join_vemployee\n
				$where";
			$join[$key] = $sql;

			// echo $sql;
			// exit;

			// $found = $this->try_find_full_access($sql);
			// if($found>0){
			// 	break;
			// }
		}

		$case = "CASE\n";
		foreach ($case_permission as $key => $value) {
			$case .= $value."\n";
		}
		if(count($case_permission)==0)
		{
			$case .= "WHEN 1=1 THEN 0\n";
		}
		$case .= "ELSE 0 END AS GNTLOV_Permission_Level\n";

		$sql = "SELECT TMQ.*, $case \n
		FROM ($main_query) TMQ\n";

		foreach ($join as $key => $value) {
			$sql .= "LEFT JOIN (\n$value\n) $key ON TMQ.$LOV_Key = $key.$LOV_Key\n";
		}

		return $sql;
	}

}