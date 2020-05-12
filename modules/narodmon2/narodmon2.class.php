<?php
/**
* NarodMon.ru_2 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 09:04:00 [Apr 04, 2016])
*/
//
//
class narodmon2 extends module {
/**
* narodmon2
*
* Module class constructor
*
* @access private
*/
function narodmon2() {
  $this->name="narodmon2";
  $this->title="NarodMon.ru";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
  $this->API_KEY = "35uRe2lIkUUPY"; // Module Key
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 
 $out['UUID'] = $this->config['UUID'];
 $out['SRV_NAME']=$this->config['SRV_NAME'];
 $out['API_MAC']=$this->config['API_MAC'];
 $out['LOGIN']=$this->config['LOGIN'];
 $out['PASSWORD']=$this->config['PASSWORD'];
 $out['EVERY']=$this->config['EVERY'];
 
 if (!$out['UUID']) {
	 $out['UUID'] = md5(microtime() . rand(0, 9999));
	 $this->config['UUID'] = $out['UUID'];
	 $this->saveConfig();
 }
 
 if ($this->view_mode=='update_settings') {
	global $srv_name;
	$this->config['SRV_NAME']=$srv_name;	 
	global $api_mac;
	$this->config['API_MAC']=$api_mac;
	global $narodmon_login;
	$this->config['LOGIN']=$narodmon_login;
	global $narodmon_password;
	if($narodmon_password != $this->config['PASSWORD']){
	$this->config['PASSWORD']=md5($this->config['UUID'] . md5($narodmon_password));
	}
	global $every;
	$this->config['EVERY']=$every;
   
   $this->saveConfig();
   $this->Logon();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 
 if ($this->tab=='' || $this->tab=='outdata') {
   $this->outdata_search($out);
 }  
 if ($this->tab=='indata') {
   $this->indata_search($out); 
 }
 if ($this->view_mode=='test') {
		$this->sendData();
		$this->Logon();
		$this->readData();
		$this->redirect("?");
 }
 if ($this->view_mode=='outdata_edit') {
   $this->outdata_edit($out, $this->id);
 }
 if ($this->view_mode=='outdata_del') {
   $this->outdata_del($this->id);
   $this->redirect("?data_source=$this->data_source&view_mode=node_edit&id=$pid&tab=outdata");
 }	
 if ($this->view_mode=='indata_edit') {
   $this->indata_edit($out, $this->id);
 }
 if ($this->view_mode=='indata_del') {
   $this->indata_del($this->id);
   $this->redirect("?data_source=$this->data_source&view_mode=node_edit&id=$pid&tab=indata");
 }	
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* OutData search
*
* @access public
*/
 function outdata_search(&$out) {	 
  require(DIR_MODULES.$this->name.'/outdata.inc.php');
 }
/**
* InData search
*
* @access public
*/ 
 function indata_search(&$out) {	 
  require(DIR_MODULES.$this->name.'/indata.inc.php');
 }
/**
* OutData edit/add
*
* @access public
*/
 function outdata_edit(&$out, $id) {	
  require(DIR_MODULES.$this->name.'/outdata_edit.inc.php');
 } 
/**
* OutData delete record
*
* @access public
*/
 function outdata_del($id) {
  $rec=SQLSelectOne("SELECT * FROM nm_outdata WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM nm_outdata WHERE ID='".$rec['ID']."'");
 }
/**
* InData edit/add
*
* @access public
*/
 function indata_edit(&$out, $id) {	
  require(DIR_MODULES.$this->name.'/indata_edit.inc.php');
 } 
/**
* InData delete record
*
* @access public
*/
 function indata_del($id) {
  $rec=SQLSelectOne("SELECT * FROM nm_indata WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM nm_indata WHERE ID='".$rec['ID']."'");
 }
 
 function propertySetHandle($object, $property, $value) {
   $this->getConfig();
   $table='nm_outdata';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processCycle() {
	$this->getConfig();

	$every = $this->config['EVERY'];
	$last  = $this->config['LATEST_UPDATE'];
	
	$tdev = time()-$last;   
	if ($tdev > $every*60) {
		$this->config['LATEST_UPDATE']=time();
		$this->saveConfig();
   
		$this->sendData();
		$this->readData();
	} 
 }

 function sendData() {
	$this->getConfig();

	$table='nm_outdata';
	$properties=SQLSelect("SELECT * FROM $table WHERE active=1;");
	$total=count($properties);
	if ($total) {
		$send="#".$this->config['API_MAC'];
		if ($this->config['SRV_NAME'])
			$send.="#".$this->config['SRV_NAME'];
			$send.="\n";
			for($i=0;$i<$total;$i++){
				$val = round( getGlobal($properties[$i]['LINKED_OBJECT'].'.'.$properties[$i]['LINKED_PROPERTY']), 2);
				
				$send.="#".$properties[$i]['MAC']."#".$val."#".$properties[$i]['TITLE']."\n";
				
				$properties[$i]['UPDATED'] = date('Y-m-d H:i:s');
				SQLUpdate($table, $properties[$i]);
			}
		$send.="##";

		$fp = @fsockopen("tcp://narodmon.ru", 8283, $errno, $errstr);
		if($fp) {
			fwrite($fp, $send);

			$result='';
			while (!feof($fp)) {
				$result.=fread($fp, 128);
			}
		}
		@fclose($fp);		
		
		echo date("Y-m-d H:i:s")." Send ok\n";		
	}
 }
 
 function sendVals($vals){ 
		$this->getConfig();
 
	  $total=count($vals);
		if ($total) {		
			$send="#".$this->config['API_MAC'];
			if ($this->config['SRV_NAME'])
				$send.="#".$this->config['SRV_NAME'];
			$send.="\n";
			for($i=0;$i<$total;$i++)
				$send.="#".$vals[$i]['MAC']."#".$vals[$i]['VALUE']."#".$vals[$i]['TITLE']."\n";
			$send.="##";		
		 
			$fp = @fsockopen("tcp://narodmon.ru", 8283, $errno, $errstr);
			if($fp) {
			 fwrite($fp, $send);

			 $result='';
			 while (!feof($fp)) {
				 $result.=fread($fp, 128);
			 }
			}
			@fclose($fp);		

			echo date("Y-m-d H:i:s")." Send vals ok\n";		
		}	
 }
 
 function readData() {
	$this->getConfig();

	$table='nm_indata';	
	$properties=SQLSelect("SELECT * FROM $table;");
	$total=count($properties);
	if ($total) {
		$sens = array();
		for($i=0;$i<$total;$i++)
			$sens[] = $properties[$i]['DID'];

		$request =
			array( 
				'cmd' => "sensorsValues", 
				'sensors' => $sens,
				'uuid' => $this->config['UUID'],
				'api_key' => $this->API_KEY
			);
			
		if($ch = curl_init('http://narodmon.ru/api')) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'MajorDomo module');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
			$reply = curl_exec($ch); 
			
			if(!$reply or empty($reply)) 
			{
				echo date("Y-m-d H:i:s")."Request: Connect error : ".$reply."\n";
				return false;
			}
			
			$data = json_decode($reply, true);
			if(!$data or !is_array($data))
			{
				echo date("Y-m-d H:i:s")."Request: Wrong data\n";
				return false;
			}
			
			echo date("Y-m-d H:i:s")." Request: ok\n";
		
			foreach($data['sensors'] as $S) {
				// Find propertys
				$prop = false;
				for($i=0;$i<$total;$i++)
				{
					if ($properties[$i]['DID'] == $S['id'])
					{
						$prop = $properties[$i];
						break;
					}
				}
				
				// Skip ready has
				if ($prop['VALDATE'] == date('Y-m-d H:i:s', $S['time']))
				{
					echo date("Y-m-d H:i:s")." Skip prop for date: ".$prop['TITLE']."\n";
					continue;
				}
					
				if ($prop !== false)
				{
					// Set updated
					$prop['VALUE'] = $S['value'];
					$prop['VALDATE'] = date('Y-m-d H:i:s', $S['time']);
					$prop['UPDATED'] = date('Y-m-d H:i:s');
					
					//DebMes("ReadData: ".print_r($prop, true));
					
					SQLUpdate($table, $prop);
					
					// Set object value
					setGlobal($prop['LINKED_OBJECT'].'.'.$prop['LINKED_PROPERTY'], $S['value'], array($this->name=>'0'));
				}
			}
						
			curl_close($ch); 
		}
	}
 }
 
 function Logon()
{
	$this->getConfig(); 

	$request =
		array( 
			'cmd' => "userLogon", 
			'login' => $this->config['LOGIN'],
			'hash' => $this->config['PASSWORD'],
			'uuid' => $this->config['UUID'],
			'api_key' => $this->API_KEY,
			'lang' => "ru"
		);

	if($ch = curl_init('http://narodmon.ru/api')) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MajorDomo module');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
		$reply = curl_exec($ch);

		if(!$reply or empty($reply)) 
		{
			echo date("Y-m-d H:i:s")."Request: Connect error : ".$reply."\n";
			return false;
		}

		$data = json_decode($reply, true);
		if(!$data or !is_array($data))
		{
			echo date("Y-m-d H:i:s")."Request: Wrong data\n";
			return false;
		}

		echo date("Y-m-d H:i:s")." Request: ok\n";
			
		curl_close($ch); 

		print_r($data);

		return ($data);
	}	

	return false;
}

function readHistory($id, $period, $offset)
{
	$this->getConfig(); 

	$request =
		array( 
			'cmd' => "sensorLog", 
			'id' => $id,
			'period' => $period,
			'offset' => $offset,
			'uuid' => $this->config['UUID'],
			'api_key' => $this->API_KEY
		);

	if($ch = curl_init('http://narodmon.ru/api')) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MajorDomo module');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
		$reply = curl_exec($ch); 

		if(!$reply or empty($reply)) 
		{
			echo date("Y-m-d H:i:s")."Request: Connect error : ".$reply."\n";
			return false;
		}

		$data = json_decode($reply, true);
		if(!$data or !is_array($data))
		{
			echo date("Y-m-d H:i:s")."Request: Wrong data\n";
			return false;
		}

		echo date("Y-m-d H:i:s")." Request: ok\n";
			
		curl_close($ch); 

		print_r($data);

		return ($data);
	}	

	return false;
}
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS nm_outdata');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
nm_outdata - 
*/
  $data = <<<EOD
 nm_outdata: ID int(10) unsigned NOT NULL auto_increment
 nm_outdata: TITLE varchar(100) NOT NULL DEFAULT ''
 nm_outdata: MAC varchar(100) NOT NULL DEFAULT ''
 nm_outdata: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 nm_outdata: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 nm_outdata: UPDATED datetime
 nm_outdata: ACTIVE int(3) DEFAULT 1  
 
 nm_indata: ID int(10) unsigned NOT NULL auto_increment
 nm_indata: DID int(10) NOT NULL
 nm_indata: TITLE varchar(100) NOT NULL DEFAULT ''
 nm_indata: VALUE varchar(100)
 nm_indata: VALDATE datetime
 nm_indata: UPDATED datetime
 nm_indata: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 nm_indata: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA0LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
