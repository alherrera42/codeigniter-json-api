<?php

/*
 * Generic API controller for CodeIgniter
 * by Alberto Herrera <alherrera42@gmail.com>
 * Jan 2016
 */

class Api extends CI_Controller{
	
	function __construct(){
		parent::__construct();
		$this->load->dbutil();
		$tablas = $this->db->query('show tables')->result_array();
		$this->tablas = array();
		foreach($tablas as $t)
		{
			$tabla = $t['Tables_in_'.$this->db->database];
			$atribs = $this->db->query("describe {$tabla}")->result_array();
			$cols = array();
			foreach($atribs as $a)
				$cols[] = $a['Field'];
			$this->tablas[] = array(
				$tabla => $cols
			);
		}
	}
	
	function index()
	{
		show_error('Bienvenido a la API de '.$this->config->item('appname').". Asumo que eres un intruso, así que esto será notificado. ",200);
		$this->seguridad->bitacora('Acceso a la API sin llamada a método específico. Posible intento de infiltración. ');
	}
	
	function response($r = array())
	{
		if(empty($r))
		{
			$r = array(
				'result_msg' => 'Resource not specified'
			);
		}
		$response = array(
			'app_name' => $this->config->item('appname'),
			'query_date_str' => date('Y-m-d H:i:s'),
			'query_date_int' => time(),
			'resource' => 'GET',
			'query_sql' => $this->db->last_query(),
			'expanding' => $expandir,
			'result' => $r
		);
		
		header("Content-type: application/json; charset=utf-8");
		echo json_encode($response);
		
	}
	
	function get($tabla, $id=0)
	{
		if($_GET['tabla'])
			$tabla = $_GET['tabla'];
		if($_GET['id'] AND is_numeric($id))
			$id = $_GET['id'];
		if($_GET['filtros'])
			$filtros = json_decode($_GET['filtros'],true);
		$limite = ($id) ? 1 : 20;
		if($_GET['limite'] AND is_numeric($_GET['limite']))
			$limite = $_GET['limite'];
		
		if(!$tabla OR !in_array($tabla,array_keys($this->tablas)))
		{
			$this->index();
			exit();
		}
		
		$this->db
			->from($tabla);
		if($id)
			$this->db
				->where(array_values($this->tablas)[0],$id);
		$this->db->limit($limite);
		if(is_array($filtros))
			foreach(array_keys($filtros) as $f)
				$this->db
					->where($f,$filtros["{$f}"]);
		$result = $this->db->get()->result_array();
		
		$this->response($result);
		
	}
	
	function model($model,$function,$params)
	{
		if(!$model OR !$function)
			$this->response(array('result_msg'=>'Modelo o parametros no especificados'));
		if(!file_exists(APPPATH."models/{$model}.php"))
			$this->response(array('result_msg'=>'El modelo no existe'));
		
		$params = str_replace(":",",",$params);
		
		$this->load->model($model);
		
		$str = "\$r = \$this->{$model}->{$function}({$params});";
		eval($str);
		
		$this->response($r);
	}
	
}
