<?php

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once 'xp.class.php';

class xpmodel extends xp {

	var $model;
	var $metadata;

	// parametros del URI

	var $module;
	var $req_object;

	// sql
	var $search;
	var $order;
	var $pager = array();

	// process
	var $process;
	var $current_process;

	// instances
	var $instances = array();
	var $obj_collection;

	// construct

	function  __construct( $model ) {/*{{{*/

		$this->load_model( $model );

		return $this;

	}/*}}}*/

	function set_model( $model = null ) {/*{{{*/

		/* el modulo del sistema tiene asociado un modelo */

		( isset( $model ) and $this->module = $model and M()->info( 'recibiendo el modulo por parametro '. $model ) )
		or ( $this->module and M()->info( 'iniciando proceso para el modulo '. $this->module ) ) 
		or ( $this->module = $this->feat->default_module and M()->info( 'valor por default del modulo '. $this->module )) 
		or ( $this->module = 'users' and M()->info( "Valor por omision para el modulo: ". $this->module ));

		$this->feat->module = $this->module;

	}/*}}}*/

	// init

	function close() {/*{{{*/

		foreach( $this->obj_collection as $class_name => $obj_collection )
			foreach( $obj_collection as $obj )
				if ( is_object( $obj->recordset ) )
					$obj->recordset->free();

		$this->debug_obj_collection();

		unset( $this->obj_collection );

	}/*}}}*/

	// head

	function load_metadata() {/*{{{*/

		$file = "modules/{$this->module}/{$this->module}.metadata.xml";

		( $this->metadata = simplexml_load_file( $file ) 
			and M()->info( "cargando el modelo para el modulo {$this->module} desde $file" ) ) 
		or M()->fatal( "no puedo encontrar la definicion de la metadata en $file:". $e->getMessage() ); 

		return $this;
	}/*}}}*/

	// model

	function load_model( $model = null ) {/*{{{*/

		( isset( $model ) and $this->set_model( $model ) ) 
		or ( isset( $this->module ) and  $this->set_model( $this->module ) );

		$file = "modules/{$this->module}/{$this->module}.model.xml";

		( $this->model = simplexml_load_file( $file )
			and M()->info( "cargando el modelo para el modulo {$this->module} desde $file" ) )
		or M()->fatal( "no puedo encontrar la descripcion del modelo en $file" );

		$this->load_metadata();

		foreach ( $this->model->xpath( "//obj" ) as $model )
			if ( !$this->add_class( $model ) ) 
				return false;

		// chequeos varios del modelo y los objetos

		foreach( $this->instances as $name => $obj ) 
			if ( !$obj->constructed ) 
				M()->error( "el objeto $name no fue inicializado correctamente. Incluya el llamado ::__construct() para este objeto" );

		M()->info( "OK" );

		return true;

	}/*}}}*/

	function instance( $object, $model = null ) {/*{{{*/

		$class_name = XP_CLASS_NAMESPACE . $object;
		$class_file_name = "modules/$object/$object.class.php";

		try {
			require_once $class_file_name;
			M()->info( "nueva clase $class_name para la instancia $object" );
		
		} catch (Exception $e) {

			M()->fatal( "no el archivo $class_file_name para la clase $class_name" );
		}


		return new $class_name( $model );
	}	/*}}}*/

	function get_instance( $obj_name = null ) {/*{{{*/

		if ( count( $this->instances ) < 1 ) {

			M()->error( 'No hay instancias definidas' );
			return null;

		} else if ( !$obj_name ) {

			reset( $this->instances );
			return current( $this->instances );

		} else if ( $obj_name and !array_key_exists( $obj_name, $this->instances ) ) {

			if (  $instance = $this->instance( $obj_name ) ) 

				return $instance;
			else
				M()->fatal( 'No encuentro la instancia '. $obj_name );

		} else return $this->instances[$obj_name];


	}/*}}}*/

	function add_class( $model ) {/*{{{*/

		if ( !$model ) M()->fatal( "No hay modelo para esta definicion del objeto" );

		$class_name = (string) $model['name'];

		$this->instances[$class_name] = $this->instance( $class_name, $model );

		return true;

	}/*}}}*/

	// procesos

	function process() {/*{{{*/

		global $xpdoc;

		$this->set_view( 'xml' );

		$obj = $this->get_instance( $this->module );
		$instances = $this->instances;
		$user = $xpdoc->user;
		$xml = $xpdoc->xml;
		$processes = $this->process;

		$this->current_process = new xpdataprocess( $obj, $instances, $user, $processes, $xml ) and $this->current_process->process();


		// DEBUG: view es de xpdoc o de model?
		if ( ! $this->view ) $this->set_view( 'error' );

		return isset($this->xdoc) ? $this->xdoc : $this->get_messages();

	}/*}}}*/

	// roles

	function get_metadata() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:metadata") );

		foreach( $this->instances as $name => $obj )
			simplexml_append( $x, $obj->metadata() );

		return $x;

	}/*}}}*/

	function get_model() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:model") );

		simplexml_append($x, $this->model);

		return $x;

	}/*}}}*/

	function get_settings() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:settings") );

		simplexml_append( $x, array2xml( 'search', $this->search ));
		simplexml_append( $x, array2xml( 'order', $this->order ));
		simplexml_append( $x, array2xml( 'req_object', $this->req_object ));

		$xfeat = $this->feat->get_xml();

		$obj_feat = false;

		foreach( $this->instances as $name => $obj ) { 

			$xobj = new SimpleXMLELement('<obj/>');
			$xobj['name'] = $obj->class_name;

			if ( $xof = $obj->feat->get_xml() ) {

				$obj_feat = true;

				simplexml_append( $xobj, $xof );
				simplexml_append( $xfeat, $xobj );
			}
		}

		simplexml_append( $x, $xfeat );

		foreach( $this->instances as $name => $obj ) { 

			$xobj = new SimpleXMLELement('<obj/>');
			$xobj['name'] = $obj->class_name;

			simplexml_append( $xobj, array2xml( 'acl', $obj->acl ) );

			simplexml_append( $xobj, $obj->processes );

			if ( file_exists( ( $extra_js_files = $obj->get_module_path(). '/js.xml' ) ) ) {

				$files = $xobj->addChild('files');
				$files['type'] = 'js';

				$js_files = simplexml_load_file( $extra_js_files );
				foreach( $js_files->xpath( "//file" ) as $js_xml_file ) 
					simplexml_append( $files, $js_xml_file );

				simplexml_append( $xobj, $files );
			}

			simplexml_append( $x, $xobj );
		}

		return $x;

	}/*}}}*/

	function get_dataset( $flags = false, $ns = false ) {/*{{{*/

		M()->info();

		$obj_name = $this->req_object ? $this->req_object : $this->module;

		if ( ! ( $obj = $this->get_instance( $obj_name ) ) ) return null;

		if ( $this->query ) $obj->add_query( $this->query );

		$dataset = $obj->serialize( $flags );

		if ( $ns ) { 

			$d = new DOMDocument;
			$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:dataset") );

			simplexml_append($x, $dataset);

			return $x;
		
		}

		return $dataset;

	}/*}}}*/

	// debug

	function debug_obj_collection() {/*{{{*/

		foreach( $this->obj_collection as $class_name => $obj_collection ) {

			M()->info( "class_name $class_name count: ". count( $obj_collection ) );
		}
	}/*}}}*/

}

// vim600: fdm=marker sw=3 ts=8 ai:
?>
