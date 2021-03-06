<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

    public $current_table = "inventory_object";
    public $login_page = "inventory_auth/login";
 
    function __construct()
    {
        parent::__construct();
        
        //LOAD GLOBAL INVENTORY CONFIG
        $this->config->load('inventory');
 
        /* Standard Libraries of codeigniter are required */
        $this->load->database();
        $this->load->helper('url');

        /* ------------------ */ 
        $this->load->library('grocery_CRUD');
		$this->load->library('image_CRUD');  
		$this->load->library('session');  
		$this->load->library('ion_auth');

		//LOAD INVENTORY MODEL
		$this->load->model('inventory_model');
		
		/* Set language */
		$current_language=$this->session->userdata("current_language");
		if ($current_language == "") {
			$current_language= $this->config->item('default_language');
		}
		$this->grocery_crud->set_language($current_language);
    	$this->lang->load('inventory', $current_language);	       

		
        //LANGUAGE HELPER:
        $this->load->helper('language');
    }
    
	public function signin(){
		$this->load->view('signin');
	}
	
    public function load_header($output = array(),$not_show_header = true){

             //GET GROCERY CRUD STATE & PASS INFO TO VIEW            
             $state = $this->grocery_crud->getState();
             $state_info = $this->grocery_crud->getStateInfo();
             $data['grocerycrudstate']=$state;
             
             $data['not_show_header2']=$not_show_header;

           switch ($state) {
			default:
			 $data['grocerycrudstate_text']=lang('grocerycrud_state_listing');
             break;
			case "unknown":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_unknown');
             break;
			case "list":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_listing');
             if ($not_show_header) {
			    unset($data['not_show_header2']);
			 } else {
				$data['not_show_header2']= true;
			 }
			 break;			 			 
            case "add":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_adding');
             break; 
            case "edit":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_editing');
             break;
            case "delete":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_deleting');
             break;
            case "insert":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_inserting');
             break;
            case "update":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_updating');
             break;
            case "ajax_list":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_listing_ajax');
             break;
            case "ajax_list_info":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_listing_ajax_info');
             break;
            case "insert_validation":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_inserting_validation');
             break;
            case "update_validation":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_uploading_validation');
             break;
            case "upload_file":			 
             $data['grocerycrudstate_text']=lan('grocerycrud_state_uploading_file');
             break;
			case "delete_file":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_deleting_file');
             break;
            case "ajax_relation":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_ajax_relation');
             break;
            case "ajax_relation_n_n":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_ajax_relation_n_n');
             break; 
            case "success":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_exit');
             if ($not_show_header) {
			    unset($data['not_show_header2']);
			 } else {
				$data['not_show_header2']= true;
			 }
             break; 
            case "export":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_exporting');
             break; 
            case "print":			 
             $data['grocerycrudstate_text']=lang('grocerycrud_state_printing');
             break;    
		   }
    
        $data['inventory_js_files'] = array(
            '//cdnjs.cloudflare.com/ajax/libs/lodash.js/1.2.1/lodash.min.js',
            base_url('assets/js/bootstrap.min.js'),
            base_url('assets/grocery_crud/js/jquery_plugins/jquery.chosen.min.js'),
            base_url('assets/js/jquery-ui.min.js'),
            base_url('assets/js/jquery-chosen-sortable.js')
            );
        $data['inventory_css_files'] = array(
            base_url('assets/css/bootstrap.min.css'),
            base_url('assets/css/bootstrap-responsive.min.css'),
            base_url('assets/css/font-awesome.css'),
            base_url('assets/css/custom.css'),
            base_url('assets/css/jquery.multiselect.css'),
            base_url('assets/grocery_crud/css/jquery_plugins/chosen/chosen.css'),
            'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css'
            );                 
            
        $data['fields_in_table'] = $this->db->list_fields($this->current_table);

		$current_fields_to_show= $this->session->userdata($this->current_table."_current_fields_to_show");
		
        $sorted_fields_in_table = array_unique(array_merge($current_fields_to_show, $data['fields_in_table']));
        
        $data['table_id'] = $this->getDatabaseIdByTable($this->current_table);
        
        $data['sorted_fields_in_table']= $sorted_fields_in_table;
        
        $data['organizational_units'] = $this->inventory_model->get_organizational_units();
        
        $data['institution_name'] = $this->config->item('institution_name');
        
        $data['current_table_name'] = $this->current_table;
        
        $data['current_organizational_unit'] = $this->session->userdata("current_organizational_unit");
        
        $data['current_role_id']   = $this->session->userdata('role');
        $data['current_role_name'] = $this->_get_rolename_byId($data['current_role_id']);
        
        $show_organizational_units=true;
        if ($data['current_role_name'] == $this->config->item('organizationalunit_group') )
			$show_organizational_units=false;
        $data['show_organizational_units'] =$show_organizational_units;
        
        $show_maintenace_menu=true;
        if ($data['current_role_name'] == $this->config->item('organizationalunit_group') )
			$show_maintenace_menu=false;
        $data['show_maintenace_menu'] =$show_maintenace_menu;
        
        $show_managment_menu=true;
        if ($data['current_role_name'] == $this->config->item('organizationalunit_group') )
			$show_managment_menu=false;
        $data['show_managment_menu'] =$show_managment_menu;
        
        //DEBUGGING PURPOSES
        if ($this->config->item('debug')) {
		 $data['debug']=true;	
		}
        
        $data['session_data']=$this->session->all_userdata(); 
                                                
        $this->load->view('include/header',array_merge((array) $output,$data));
                    
    }
    
    public function getDatabaseIdByTable($tablename) {
		switch ($tablename) {
			case "inventory_object":
				return "inventory_objectId";
				break;
			case "externalIDType":
				return "externalIDTypeID";
				break;
			case "organizational_unit":
				return "organizational_unitId";
				break;	
			case "location":
				return "locationId";
				break;
			case "material":
				return "materialId";
				break;
			case "brand":
				return "brandId";
				break;
			case "model":
				return "modelId";
				break;
			case "provider":
				return "providerId";
				break;
			case "money_source":
				return "moneySourceId";
				break;
			case "users":
				return "id";
				break;	
			case "groups":
				return "id";
				break;	
			default:
				return false;
				break;
		}
	}
	
	public function change_language($language) {
		$this->session->set_userdata('current_language', $language);
		redirect($_SERVER[‘HTTP_REFERER’]);
	}
	
	public function update_current_organizational_unit()
	{
		$current_selected_organizational_unit = $this->input->post('current_selected_organizational_unit');
		$this->session->set_userdata("current_organizational_unit",
									 $current_selected_organizational_unit);
		redirect("main/inventory_object", 'refresh');
	}
    
    //UPDATE SESSION VARIABLES WITH COLUMNS TO SHOW
    public function update_displayed_fields()
    {
		$table_name = $this->input->post('table_name');
		$selected_columns = $this->input->post('current_selected_table_fields');
		
		$skip=false;
		if (!$selected_columns) {
			$skip=true;
		}
		
		switch ($table_name) {
			case "inventory_object":
				if (!$skip) {
					$this->session->set_userdata("inventory_object_current_fields_to_show",
									 $selected_columns);
			}
				redirect("main/inventory_object", 'refresh');
				break;
			case "externalIDType":
				if (!$skip) {
					$this->session->set_userdata("externalIDType_current_fields_to_show",
									 $selected_columns);			 
				}
				redirect("main/externalIDType", 'refresh');
				break;
			case "organizational_unit":
				if (!$skip) {
					$this->session->set_userdata("organizational_unit_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/organizational_unit", 'refresh');
				break;	
			case "location":
				if (!$skip) {
					$this->session->set_userdata("location_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/location", 'refresh');
				break;
			case "material":
				if (!$skip) {
					$this->session->set_userdata("material_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/material", 'refresh');
				break;
			case "brand":
				if (!$skip) {
					$this->session->set_userdata("brand_current_fields_to_show",
									 $selected_columns);
				}
				$this->brand();
				break;
			case "model":
				if (!$skip) {
					$this->session->set_userdata("model_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/model", 'refresh');
				break;
			case "provider":
				if (!$skip) {
					$this->session->set_userdata("provider_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/provider", 'refresh');
				break;
			case "money_source":
				if (!$skip) {
					$this->session->set_userdata("money_source_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/money_source", 'refresh');
				break;
			case "users":
				if (!$skip) {
					$this->session->set_userdata("users_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/users", 'refresh');
				break;	
			case "groups":
				if (!$skip) {
					$this->session->set_userdata("groups_current_fields_to_show",
									 $selected_columns);
				}
				redirect("main/groups", 'refresh');
				break;	
			default:
				redirect("inventory_errors/tablenotfound", 'refresh');
				break;
		}
    }
 
    public function index()
    {
       if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
       $this->inventory_object();
    }
	
	public function images(){
	    if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		$image_crud = new image_CRUD();
		
		//CHECK IF USER IS READONLY --> unset upload & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$image_crud->unset_upload();
			$image_crud->unset_delete();
		}
	
		$image_crud->set_table('images');
	
		$image_crud->set_primary_key_field('imageId');
		$image_crud->set_url_field('url');
		$image_crud->set_title_field('title');
		$image_crud->set_ordering_field('priority');
		$image_crud->set_image_path('assets/uploads/files');
		$image_crud->set_relation_field('inventory_objectId');
	
		$output = $image_crud->render();
	
	    $this->load_header($output,false);	
		$this->load->view('images', $output);
	}
	
	public function inventory_object1()
    {
		
		//$this->grocery_crud->set_model('Users_join');
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		//COMMON CUSTOM ACTIONS
		$this->grocery_crud->add_action(lang('View'),
			base_url('assets/img/images.png'), '','',array($this,'_rewrite_table_url'));	
		
		//ESPECIFIC CUSTOM ACTIONS
        $this->grocery_crud->add_action(lang('Images'),base_url('assets/img/images.png'), '/main/images');
        $this->grocery_crud->add_action(lang('QRCode'),base_url('assets/img/qr_code.png'), '/qr/generate');
                
        
        //<i class="icon-large icon-qrcode"></i>
	
		//TODO!!!!!!
		if(isset($_GET['idioma'])){
			$idioma=$_GET['idioma'];
			$_SESSION['idioma'] = $idioma;
			$this->grocery_crud->set_language($_SESSION['idioma']);
		}
		
		$this->grocery_crud->set_table('inventory_object');
		
		$this->grocery_crud->where('organitzational_unitId',"1");
		//inventory_object_organizational_unit.organitzational_unitId
		
		//FILTER BY ORGANIZATIONAL UNIT
		//Realtion n a n table: inventory_object_organizational_unit
		
		
		//$crud->where('status','active');->where('status','active');
        
        //Exemples de com canviar l'idioma
        //$this->grocery_crud->set_language("catalan"); 
        
        //Establish subject:
        $this->grocery_crud->set_subject(lang('object_subject'));
                        
        //FIELD NAMES        

        //COMMON_COLUMNS               
        $this->set_common_columns_name();

        //ESPECIFIC COLUMNS                                            
        $this->grocery_crud->display_as('publicId',lang('publicId'));
        $this->grocery_crud->display_as('externalID',lang('externalId')); 
        $this->grocery_crud->display_as('externalIDType',lang('externalIDType')); 
        $this->grocery_crud->display_as('materialId',lang('materialId'));
        $this->grocery_crud->display_as('brandId',lang('brandId'));
        $this->grocery_crud->display_as('modelId',lang('modelId'));
        $this->grocery_crud->display_as('location',lang('location'));
        $this->grocery_crud->display_as('quantityInStock',lang('quantityInStock'));
        $this->grocery_crud->display_as('price',lang('price'));
        $this->grocery_crud->display_as('moneySourceId',lang('moneySourceId'));
        $this->grocery_crud->display_as('providerId',lang('providerId'));
        $this->grocery_crud->display_as('preservationState',lang('preservationState'));                
        $this->grocery_crud->display_as('file_url',lang('file_url'));
        $this->grocery_crud->display_as('OwnerOrganizationalUnit',lang('OwnerOrganizationalUnit'));
	
        //Establish order and limit columns to show:
        $this->grocery_crud->columns('preservationState','name','shortName','description');        
        
        //Limitar els camps a mostrar a add/edit
        //http://www.grocerycrud.com/documentation/options_functions/fields
        //$crud->fields('customerName','contactLastName','phone','city','country','creditLimit');
        
        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','location','markedForDeletion');
        //$this->grocery_crud->required_fields('externalCode','name','shortName','location','markedForDeletion');

        $this->grocery_crud->unset_add_fields('last_update','manualLast_update');
        
        //ExternID types
        $this->grocery_crud->set_relation('externalIDType','externalIDType','{name}',array('markedForDeletion' => 'n'));
        
        //BRAND RELATION
        $this->grocery_crud->set_relation('brandId','brand','{name}',array('markedForDeletion' => 'n'));
        
        //MODEL RELATION
        $this->grocery_crud->set_relation('modelId','model','{name}',array('markedForDeletion' => 'n'));
        
        //MATERIAL RELATION
        $this->grocery_crud->set_relation('materialId','material','{name}',array('markedForDeletion' => 'n'));
        
        //ORGANIZATIONAL UNIT
        $this->grocery_crud->set_relation_n_n('OwnerOrganizationalUnit', 'inventory_object_organizational_unit', 'organizational_unit', 'organitzational_unitId', 'inventory_objectId', 'name','priority');
        
        //LOCATION
        $this->grocery_crud->set_relation('location','location','{name}',array('markedForDeletion' => 'n'));
        
        //PROVIDERS
        $this->grocery_crud->set_relation('providerId','provider','{name}',array('markedForDeletion' => 'n'));
        
        //MONEYSOURCEID
        $this->grocery_crud->set_relation('moneySourceId','money_source ','{name}',array('markedForDeletion' => 'n'));
                
                
	   
        //Example de validació. Natural no zero
        $this->grocery_crud->set_rules('quantityInStock','Quantitat','is_natural_no_zero');
		
		$this->grocery_crud->callback_add_field('quantityInStock',array($this,'add_field_callback_quantityInStock'));
		
		//CREATION USER ID
    	//DEFAULT VALUE= LOGGED USER. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
        //$this->grocery_crud->callback_add_field('creationUserId',array($this,'add_field_callback_creationUserId'));
        $this->grocery_crud->callback_edit_field('creationUserId',array($this,'edit_field_callback_creationUserId'));
		
		
		//ENTRY DATE
		//DEFAULT VALUE=NOW. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
		$this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
		$this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
		
		//LAST UPDATE
		//DEFAULT VALUE=NOW. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
		$this->grocery_crud->callback_add_field('last_update',array($this,'add_callback_last_update'));
		$this->grocery_crud->callback_edit_field('last_update',array($this,'edit_callback_last_update'));
		
		//$this->grocery_crud->callback_add_field('markedForDeletion',array($this,'add_field_callback_markedForDeletionDate'));
		$this->grocery_crud->callback_column('price',array($this,'valueToEuro'));
		$this->grocery_crud->callback_field('Link Imatges',array($this,'field_callback_Link'));
		
		//UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
		
	
        $this->grocery_crud->set_field_upload('file_url','assets/uploads/files');
        
        //USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
        $output = $this->grocery_crud->render();
        
        $this->load_header($output);
        
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
        
        //GROCERYCRUD VIEW
        $this->load->view('inventory_object_view.php',$output);        
        $this->load->view('include/footer');          
    }
    
 /***************************************************************************************************************************************/
 /*                                         Classe inventari on forma tota la taula                                                     */               
 /***************************************************************************************************************************************/
  
    public function inventory_object() {
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
		
		//ESPECIFIC CUSTOM ACTIONS
        $this->grocery_crud->add_action(lang('Images'),base_url('assets/img/images.png'), '/main/images');
        $this->grocery_crud->add_action(lang('QRCode'),base_url('assets/img/qr_code.png'), '/qr/generate');
		
		$this->grocery_crud->set_table('inventory_object');
		
		//FILTER BY ORGANIZATIONAL UNIT
		//Relation n a n table: inventory_object_organizational_unit
		//$crud->where('status','active');->where('status','active');
        
        //Establish subject:
        $this->grocery_crud->set_subject(lang('object_subject'));
                        
        //COMMON_COLUMNS               
        $this->set_common_columns_name();

        //ESPECIFIC COLUMNS                                            
        $this->grocery_crud->display_as('publicId',lang('publicId'));
        $this->grocery_crud->display_as('externalID',lang('externalId')); 
        $this->grocery_crud->display_as('externalIDType',lang('externalIDType')); 
        $this->grocery_crud->display_as('materialId',lang('materialId'));
        $this->grocery_crud->display_as('brandId',lang('brandId'));
        $this->grocery_crud->display_as('modelId',lang('modelId'));
        $this->grocery_crud->display_as('location',lang('location'));
        $this->grocery_crud->display_as('quantityInStock',lang('quantityInStock'));
        $this->grocery_crud->display_as('price',lang('price'));
        $this->grocery_crud->display_as('moneySourceId',lang('moneySourceIdcolumn'));
        $this->grocery_crud->display_as('providerId',lang('providerId'));
        $this->grocery_crud->display_as('preservationState',lang('preservationState'));                
        $this->grocery_crud->display_as('file_url',lang('file_url'));
        $this->grocery_crud->display_as('OwnerOrganizationalUnit',lang('OwnerOrganizationalUnit'));
        $this->grocery_crud->display_as('mainOrganizationaUnitId',lang('mainOrganizationaUnitId'));
        
	
        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('inventory_object_current_fields_to_show'));       
        
        //$this->grocery_crud->columns("inventory_objectId","name","shortName");       
        
        //Limitar els camps a mostrar a add/edit
        //http://www.grocerycrud.com/documentation/options_functions/fields
        //$crud->fields('customerName','contactLastName','phone','city','country','creditLimit');
        
        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','location','markedForDeletion');
        //$this->grocery_crud->required_fields('externalCode','name','shortName','location','markedForDeletion');

        $this->grocery_crud->unset_add_fields('last_update','manualLast_update');
        
        //ExternID types
        $this->grocery_crud->set_relation('externalIDType','externalIDType','{name}',array('markedForDeletion' => 'n'));
        
        //BRAND RELATION
        $this->grocery_crud->set_relation('brandId','brand','{name}',array('markedForDeletion' => 'n'));
        
        //MODEL RELATION
        $this->grocery_crud->set_relation('modelId','model','{name}',array('markedForDeletion' => 'n'));
        
        //MATERIAL RELATION
        $this->grocery_crud->set_relation('materialId','material','{name}',array('markedForDeletion' => 'n'));
        
        //ORGANIZATIONAL UNIT
        $this->grocery_crud->set_relation_n_n('OwnerOrganizationalUnit', 'inventory_object_organizational_unit', 'organizational_unit', 'organitzational_unitId', 'inventory_objectId', 'name','priority');
        
        //MAIN ORGANIZATIONAL UNIT
        $this->grocery_crud->set_relation('mainOrganizationaUnitId','organizational_unit','{name}',array('markedForDeletion' => 'n'));
        
        //LOCATION
        $this->grocery_crud->set_relation('location','location','{name}',array('markedForDeletion' => 'n'));
        
        //PROVIDERS
        $this->grocery_crud->set_relation('providerId','provider','{name}',array('markedForDeletion' => 'n'));
        
        //MONEYSOURCEID
        $this->grocery_crud->set_relation('moneySourceId','money_source ','{name}',array('markedForDeletion' => 'n'));
                
                
	   
        //Example de validació. Natural no zero
        $this->grocery_crud->set_rules('quantityInStock','Quantitat','is_natural_no_zero');
		
		$this->grocery_crud->callback_add_field('quantityInStock',array($this,'add_field_callback_quantityInStock'));
		
		//CREATION USER ID
    	//DEFAULT VALUE= LOGGED USER. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
        //$this->grocery_crud->callback_add_field('creationUserId',array($this,'add_field_callback_creationUserId'));
        $this->grocery_crud->callback_edit_field('creationUserId',array($this,'edit_field_callback_creationUserId'));
		
		
		//ENTRY DATE
		//DEFAULT VALUE=NOW. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
		$this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
		$this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
		
		//LAST UPDATE
		//DEFAULT VALUE=NOW. ONLY WHEN ADDING
		//EDITING: SHOW CURRENT VALUE READONLY
		$this->grocery_crud->callback_add_field('last_update',array($this,'add_callback_last_update'));
		$this->grocery_crud->callback_edit_field('last_update',array($this,'edit_callback_last_update'));
		
		//$this->grocery_crud->callback_add_field('markedForDeletion',array($this,'add_field_callback_markedForDeletionDate'));
		$this->grocery_crud->callback_column('price',array($this,'valueToEuro'));
		$this->grocery_crud->callback_field('Link Imatges',array($this,'field_callback_Link'));
		
		//UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
		
	
        $this->grocery_crud->set_field_upload('file_url','assets/uploads/files');
        
        //USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
     
		$current_organizational_unit = $this->session->userdata("current_organizational_unit");
        if ($current_organizational_unit != "all")
			$this->grocery_crud->where('`inventory_object`.mainOrganizationaUnitId',$current_organizational_unit);    
		
		$current_role_id   = $this->session->userdata('role');	
		$current_role_name = $this->_get_rolename_byId($current_role_id);
        
        if ($current_role_name == $this->config->item('organizationalunit_group') ) {
			$this->grocery_crud->field_type('mainOrganizationaUnitId', 'hidden', $current_organizational_unit);
		}
        
        $output = $this->grocery_crud->render();
              
        $this->load_header($output);
               
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
                
        //GROCERYCRUD VIEW
        $this->load->view('inventory_object_view.php',$output);        
                
        $this->load->view('include/footer');   
    }
    
    function user_info() {
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		 $data['inventory_js_files'] = array(
            base_url('assets/grocery_crud/js/jquery-1.8.2.min.js'),
            '//cdnjs.cloudflare.com/ajax/libs/lodash.js/1.2.1/lodash.min.js', 
            base_url('assets/js/bootstrap.min.js'), 
            base_url('assets/js/custom.js'),
            base_url('assets/grocery_crud/themes/flexigrid/js/jquery.form.js'),
            base_url('assets/grocery_crud/themes/flexigrid/js/flexigrid-edit.js')
            );
        $data['inventory_css_files'] = array(
            base_url('assets/css/bootstrap.min.css'),
            base_url('assets/css/bootstrap-responsive.min.css'),
            base_url('assets/css/font-awesome.css'),
            base_url('assets/css/custom.css'),
            base_url('/assets/grocery_crud/themes/flexigrid/css/flexigrid.css')
            );           
            
        $data['not_show_header2']=true;
        
        $current_rol_id = $this->session->userdata('role');
		$current_role_name = $this->_get_rolename_byId($current_rol_id);
        $data['institution_name'] = $this->config->item('institution_name');
        $data['grocerycrudstate']=true;
        $data['grocerycrudstate_text']=lang('user_info_title');
        $user_groups_in_database= $this->ion_auth->get_users_groups()->result();
        $user_groups_in_database_names=array();
        foreach ($user_groups_in_database as $user_group_in_database) {
			$user_groups_in_database_names[]=$user_group_in_database->name;
		}
		
		$userid=$this->session->userdata('user_id');
		$user=$this->ion_auth->user($userid)->row();
		
		//print_r($user);
        
        $data['fields']=array (
			lang('user_id_title') => $userid,
			lang('username_title') => $this->session->userdata('username'),
			lang('name_title') => $user->first_name,
			lang('surname_title') => $user->last_name,
			lang('email_title') => $this->session->userdata('email'),
			lang('user_groups_in_database') => implode(", ",$user_groups_in_database_names),
			lang('rol_title') => $current_role_name,
			lang('realm_title') => $this->session->userdata('default_realm'),
			lang('main_user_organizational_unit') => $this->inventory_Model->get_main_organizational_unit_name_from_userid($userid),
			lang('inventory_object_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_inventory_object')),
			lang('externalIDType_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_externalIDType')),
			lang('organizational_unit_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_organizational_unit')),
			lang('location_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_location')),
			lang('material_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_material')),
			lang('brand_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_brand')),
			lang('model_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_model')),
			lang('provider_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_provider')),
			lang('money_source_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_money_source')),
			lang('users_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_users')),
			lang('groups_fields_title') => implode(", ",(array) $this->config->item('default_fields_table_groups'))
        );
		$this->load->view('include/header',$data);
		
		
		$this->load->view('user_info_view'); 
		$this->load->view('include/footer'); 
	}
    
    function _get_rolename_byId($id){
		
		$roles = (array) $this->config->item('roles');
		
		return $roles[(int) $id];
	}
    
    //
    function _get_default_values() {
		
		$defaultvalues['defaultcreationUserId']= $this->session->userdata('user_id');
		
		$defaultvalues['defaultfieldexternalIDType']= $this->config->item('default_externalID_type');
		$defaultvalues['defaultfieldlocation']= $this->config->item('default_location');
     	$defaultvalues['defaultfieldmoneysourceid']= $this->config->item('default_moneysourceid');
     	$defaultvalues['defaultfieldpreservationstate']= $this->config->item('default_preservationState');
     	$defaultvalues['defaultfieldprovider']= $this->config->item('default_provider');
     	$defaultvalues['defaultfieldmarkedfordeletion']= $this->config->item('default_markedfordeletionvalue');
     	$defaultvalues['defaultfieldMaterialId']= $this->config->item('default_materialid');
     	$defaultvalues['defaultfieldparentMaterialId']= $this->config->item('default_materialid');
     	$defaultvalues['defaultfieldBrandId']= $this->config->item('default_brandid');
     	$defaultvalues['defaultfieldModelId']= $this->config->item('default_modelid');

     	//TRANSLATIONS:
     	$defaultvalues['good_translated']= lang('Good');
     	$defaultvalues['bad_translated']= lang('Bad');
     	$defaultvalues['regular_translated']= lang('Regular');
     	$defaultvalues['yes_translated']= lang('Yes');
     	$defaultvalues['no_translated']= lang('No');
     	
     	//ORGANIZATIONAL UNIT
     	if ($this->session->userdata("current_organizational_unit")) {
			$defaultvalues['defaultmainOrganizationaUnitId']=$this->session->userdata("current_organizational_unit");
		}
		
	    $current_role_id   = $this->session->userdata('role');
        $current_role_name = $this->_get_rolename_byId($current_role_id);
		if ( $current_role_name == $this->config->item('organizationalunit_group')) {
			$defaultvalues['disable_mainOrganizationaUnitId']=true;
		}
     	
		return $defaultvalues;
		
	}
		 
	function valueToEuro($value, $row)
    {
        return $value.' &euro;';
    }
    
	
    /********************************************************************************************************************/
    /*                             Funcions callback per modificar text dels inputs                                     */
    /********************************************************************************************************************/
    function field_callback_Link($value = '', $primary_key = null)
    {
        return '<input type="text" maxlength="50" value="'.$value.'" name="phone" style="width:462px">';
    }
   
    function add_field_callback_quantityInStock(){
    return '<input id="field-quantityInStock" type="text" maxlength="6" value="1" name="quantityInStock">';
    }
    
    function edit_field_callback_creationUserId($value, $primary_key){
     return '<input type="text" maxlength="11" class="numeric" value="' . $value  . '" name="creationUserId" id="field-creationUserId" readonly> ';
    }
    
	function add_field_callback_entryDate(){  
	  $data= date('d/m/Y H:i:s', time());
	  return '<input type="text" class="datetime-input hasDatepicker" maxlength="19" value="'.$data.'" name="entryDate" id="field-entryDate" readonly>';    
    }
    
    function edit_field_callback_entryDate($value, $primary_key){  
	  return '<input type="text" class="datetime-input hasDatepicker" maxlength="19" value="'. date('d/m/Y H:i:s', strtotime($value)) .'" name="entryDate" id="field-entryDate" readonly>';    
    }
    
    function edit_field_callback_lastupdate($value, $primary_key){
	  return '<input type="text" class="datetime-input hasDatepicker" maxlength="19" value="'. date('d/m/Y H:i:s', strtotime($value)) .'" name="entryDate" id="field-last_update" readonly>';    	
	}
    
    function add_callback_last_update(){  
	 return '<input type="text" class="datetime-input hasDatepicker" maxlength="19" name="last_update" id="field-last_update" readonly>';
    }
    
    function edit_callback_last_update($value, $primary_key){  
	 return '<input type="text" class="datetime-input hasDatepicker" maxlength="19" value="'. date('d/m/Y H:i:s', strtotime($value)) .'"  name="last_update" id="field-last_update" readonly>';
    }
    
    //UPDATE AUTOMATIC FIELDS BEFORE INSERT
    function before_insert_object_callback($post_array, $primary_key) {
		//UPDATE LAST UPDATE FIELD
		$data= date('d/m/Y H:i:s', time());
		$post_array['last_update'] = $data;
		
		$post_array['creationUserId'] = $this->session->userdata('user_id');
		$post_array['lastupdateUserId'] = $this->session->userdata('user_id');
		
		
		return $post_array;
		//TODO
		//return from_date_to_unix($post_array);
    }
    
    //UPDATE AUTOMATIC FIELDS BEFORE UPDATE
    function before_update_object_callback($post_array, $primary_key) {
		//UPDATE LAST UPDATE FIELD
		$data= date('d/m/Y H:i:s', time());
		$post_array['last_update'] = $data;
		
		$post_array['lastupdateUserId'] = $this->session->userdata('session_id');
		return $post_array;
		//TODO:
		//return from_date_to_unix($post_array);
    }
    
    
	public function from_date_to_unix($array_post){
	    $date_delimiter='/';//assuming uk-date as defined date format
	    $posted_dates=array('fecha'=>$array_post['fecha']);//fecha is the name of the field posted
	    //if have more than one field add it 'yourfield'=>$array_post['yourfield] to the array
	    foreach($posted_dates as $key=>$posted_date){
		    $date_array=explode($date_delimiter,$posted_date);
		    $date=strtotime(implode('-', array($date_array[2], $date_array[1], $date_array[0])));//indexed may vary from us-date and uk-date
		    //you need to know implode must receive('-',array(YEAR,MONTH,DAY)
		    $array_post[$key]=$date;
	    }
	    return $array_post;
	}
	

	public function externalIDType() {

        if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
		
		$this->current_table="externalIDType";
        $this->grocery_crud->set_table($this->current_table);
        
        //Establish subject:
        $this->grocery_crud->set_subject(lang('externalID_subject'));
                  
        //COMMON_COLUMNS               
        $this->set_common_columns_name();
        
        //SPECIFIC COLUMNS
        //$this->grocery_crud->display_as('externalCode',lang('code'));
        
        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('externalIDType_current_fields_to_show')); 
                                                         
        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');
 
        //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
		
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
		
		$this->grocery_crud->unset_add_fields('last_update');
		
        $output = $this->grocery_crud->render();
           
        $this->load_header($output);        

		// VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
        $this->load->view('externalid_view.php',$output);
        
	    $this->load->view('include/footer');                        
    
    }
       
    public function organizational_unit()
    {
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
		
        $this->current_table="organizational_unit";
        $this->grocery_crud->set_table($this->current_table);
        
        //Establish subject:
        $this->grocery_crud->set_subject(lang('organizationalunit_subject'));
                  
        //COMMON_COLUMNS               
        $this->set_common_columns_name();
        
        //SPECIFIC COLUMNS
        $this->grocery_crud->display_as('externalCode',lang('code'));
        $this->grocery_crud->display_as('location',lang('location'));
        
        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('organizational_unit_current_fields_to_show'));
                                                         
        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');

        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_add_field('last_update',array($this,'add_field_callback_last_update'));
        
        //Relacions entre taules
        $this->grocery_crud->set_relation('location','location','{name}',array('markedForDeletion' => 'n'));
        
        //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
   		$this->grocery_crud->unset_add_fields('last_update');
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
        $output = $this->grocery_crud->render();
           
        $this->load_header($output);        

		// VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
        $this->load->view('organizational_unit_view.php',$output);
        
	    $this->load->view('include/footer');                        
	}
	
    
    
    
    public function location()
    {
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
		
		$this->current_table="location";
        $this->grocery_crud->set_table($this->current_table);
        
        //ESTABLISH SUBJECT
        $this->grocery_crud->set_subject(lang('location_subject'));                
        
        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');
        
        //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //COMMON_COLUMNS               
        $this->set_common_columns_name();
               
        //SPECIFIC COLUMNS
        $this->grocery_crud->display_as('parentLocation',lang('parentLocation'));
        
        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('location_current_fields_to_show'));
        
        //Relacions entre taules
        $this->grocery_crud->set_relation('parentLocation','location','{name}',array('markedForDeletion' => 'n'));
        
         //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
        $this->grocery_crud->unset_add_fields('last_update');
        
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
                   
        $output = $this->grocery_crud->render();
        
        $this->load_header($output);
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
        $this->load->view('grocerycrud_view.php',$output);
                
        $this->load->view('include/footer');                            
    } 
	
public function material()
{
	   if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}	
	   $this->current_table="material";
       $this->grocery_crud->set_table($this->current_table);
       
       //CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
        
       //ESTABLISH SUBJECT
       $this->grocery_crud->set_subject(lang('material_subject'));
       
       //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');
               
       //COMMON_COLUMNS                 
       $this->set_common_columns_name();

       //SPECIFIC COLUMNS
       $this->grocery_crud->display_as('parentMaterialId',lang('parentMaterialId'));
       
       //Establish fields/columns order and wich camps to show
       $this->grocery_crud->columns($this->session->userdata('material_current_fields_to_show'));
       
       //Parent Material
       $this->grocery_crud->set_relation('parentMaterialId','material','{name}',array('markedForDeletion' => 'n'));
                                        
       //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
   		$this->grocery_crud->unset_add_fields('last_update');
   		
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
       $output = $this->grocery_crud->render();


        $this->load_header($output);          
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values());            
        $this->load->view('material_view.php',$output); 
        $this->load->view('include/footer');                            
} 

public function brand()
{
	   if (!$this->ion_auth->logged_in())
	   {
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
	   }
	   //CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
	   }	
	   $this->current_table="brand";
       $this->grocery_crud->set_table($this->current_table);
       
       //ESTABLISH SUBJECT
       $this->grocery_crud->set_subject(lang('brand_subject'));
       
       //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');
               
       //COMMON_COLUMNS                 
       $this->set_common_columns_name();
       
       //Establish fields/columns order and wich camps to show
       $this->grocery_crud->columns($this->session->userdata('brand_current_fields_to_show'));
                                       
       //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
   		$this->grocery_crud->unset_add_fields('last_update');
   		
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
       $output = $this->grocery_crud->render();


        $this->load_header($output);          
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values());            
        $this->load->view('material_view.php',$output); 
        $this->load->view('include/footer');                            
}

public function model()
{
	   if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}	
	   $this->current_table="model";
       $this->grocery_crud->set_table($this->current_table);
         
       //ESTABLISH SUBJECT
       $this->grocery_crud->set_subject(lang('model_subject'));
       
       //Camps obligatoris
        $this->grocery_crud->required_fields('brandId','name','shortName','markedForDeletion');
               
       //COMMON_COLUMNS                 
       $this->set_common_columns_name();

       //SPECIFIC COLUMNS
       $this->grocery_crud->display_as('brandId',lang('brand'));
                                         
       $this->grocery_crud->columns($this->session->userdata('model_current_fields_to_show'));
       
       //Parent brand
       $this->grocery_crud->set_relation('brandId','brand','{name}',array('markedForDeletion' => 'n'));
                                        
       //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
   		$this->grocery_crud->unset_add_fields('last_update');
   		
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
       $output = $this->grocery_crud->render();


        $this->load_header($output);          
        // VIEW WITH DINAMIC JAVASCRIPT. Purpose: set default values
        $this->load->view('defaultvalues_view.php',$this->_get_default_values());            
        $this->load->view('material_view.php',$output); 
        $this->load->view('include/footer');                            
}	
public function provider()
{
	   if (!$this->ion_auth->logged_in()) {
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
	   }	
	   //CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
       $this->current_table="provider";
       $this->grocery_crud->set_table($this->current_table);    
       
       //ESTABLISH SUBJECT
       $this->grocery_crud->set_subject(lang('provider_subject'));
       
       //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');
                              
       //COMMON_COLUMNS                 
       $this->set_common_columns_name();
       
       //Establish fields/columns order and wich camps to show
       $this->grocery_crud->columns($this->session->userdata('provider_current_fields_to_show'));
                                            
        //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
        
   		$this->grocery_crud->unset_add_fields('last_update');
   		
   		
   		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
        
                                                          
       $output = $this->grocery_crud->render(); 
                                                   
       $this->load_header($output);
       $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
       $this->load->view('provider_view.php',$output);
       $this->load->view('include/footer');
}

public function money_source()
{
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
		}
		
        $this->current_table="money_source";
        $this->grocery_crud->set_table($this->current_table);  
        
        //ESTABLISH SUBJECT
        $this->grocery_crud->set_subject(lang('money_source_id_subject'));

        //Camps obligatoris
        $this->grocery_crud->required_fields('name','shortName','markedForDeletion');

        //COMMON_COLUMNS                 
        $this->set_common_columns_name();

        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('money_source_current_fields_to_show'));
        
        //CALLBACKS        
        $this->grocery_crud->callback_add_field('entryDate',array($this,'add_field_callback_entryDate'));
        $this->grocery_crud->callback_edit_field('entryDate',array($this,'edit_field_callback_entryDate'));
        
        //Camps last update no editable i automàtic        
        $this->grocery_crud->callback_edit_field('last_update',array($this,'edit_field_callback_lastupdate'));
        
        //UPDATE AUTOMATIC FIELDS
		$this->grocery_crud->callback_before_insert(array($this,'before_insert_object_callback'));
		$this->grocery_crud->callback_before_update(array($this,'before_update_object_callback'));
		
		//USER ID
        $this->grocery_crud->set_relation('creationUserId','users','{username}',array('active' => '1'));
        
        //LAST UPDATE USER ID
        $this->grocery_crud->set_relation('lastupdateUserId','users','{username}',array('active' => '1'));
		        
   		$this->grocery_crud->unset_add_fields('last_update');
                                                           
        $output = $this->grocery_crud->render();
                
        $this->load_header($output);
        $this->load->view('defaultvalues_view.php',$this->_get_default_values()); 
        $this->load->view('money_source_view.php',$output);
        $this->load->view('include/footer');
}
                                        
public function users() {
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
	    }
		$user_groups = $this->ion_auth->get_users_groups($this->session->userdata('user_id'))->result();
		
		/*
		foreach ($user_groups as $group){
			echo "GROUP: " . $group->name . " ";
		}
		echo "<br/><br/>";
		echo "User groups: " . print_r($user_groups);
		
		if ($this->ion_auth->in_group("prova"))
		{
			echo "XiVATO";
		}
	    */
	    $this->current_table="users";
        $this->grocery_crud->set_table($this->current_table);  
        
        $this->grocery_crud->required_fields('username','password','email','active','groups');
        
        //Establish subject:
        $this->grocery_crud->set_subject(lang('users_subject'));
        
        //COMMON_COLUMNS               
        $this->set_common_columns_name();
        
        //ESPECIFIC COLUMNS                                            
        $this->grocery_crud->display_as('ip_address',lang('ip_address'));
        $this->grocery_crud->display_as('username',lang('username')); 
        $this->grocery_crud->display_as('password',lang('Password')); 
        $this->grocery_crud->display_as('email',lang('email'));
        $this->grocery_crud->display_as('activation_code',lang('activation_code'));
        $this->grocery_crud->display_as('forgotten_password_code',lang('forgotten_password_code'));
        $this->grocery_crud->display_as('forgotten_password_time',lang('forgotten_password_time'));
        $this->grocery_crud->display_as('remember_code',lang('remember_code'));
        $this->grocery_crud->display_as('created_on',lang('created_on'));                
        $this->grocery_crud->display_as('active',lang('active'));
        $this->grocery_crud->display_as('first_name',lang('first_name'));
        $this->grocery_crud->display_as('last_name',lang('last_name'));
        $this->grocery_crud->display_as('company',lang('company'));
        $this->grocery_crud->display_as('phone',lang('phone'));
        
        //Establish fields/columns order and wich camps to show
        $this->grocery_crud->columns($this->session->userdata('users_current_fields_to_show'));

        
        $this->grocery_crud->field_type('password', 'password');
        $this->grocery_crud->field_type('created_on', 'date_timestamp');
		$this->grocery_crud->field_type('last_login', 'date_timestamp');
        
        $this->grocery_crud->unset_add_fields('ip_address','salt','activation_code','forgotten_password_code','forgotten_password_time','remember_code','last_login','created_on');
        $this->grocery_crud->unset_edit_fields('ip_address','salt','activation_code','forgotten_password_code','forgotten_password_time','remember_code','last_login','created_on');

	    //GROUPS
        $this->grocery_crud->set_relation_n_n('groups', 'users_groups','groups', 'group_id', 'id', 'name');
        
        //USER MAIN ORGANIZATIONAL UNIT
        $this->grocery_crud->set_relation('mainOrganizationaUnitId','organizational_unit','{name}',array('markedForDeletion' => 'n'));

        $output = $this->grocery_crud->render();
        
        $this->load_header($output);
               
        $this->load->view('users_view.php',$output);
        $this->load->view('include/footer');
}

public function groups(){
	   if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
	   }
	   $this->current_table="groups";
       $this->grocery_crud->set_table($this->current_table);  
       
       $this->grocery_crud->required_fields('name');

       //Establish subject:
        $this->grocery_crud->set_subject(lang('groups_subject'));
        
       //COMMON_COLUMNS               
       $this->set_common_columns_name();

       //ESPECIFIC COLUMNS                                            
       $this->grocery_crud->display_as('name',lang('name'));
       $this->grocery_crud->display_as('description',lang('description')); 
       
       //Establish fields/columns order and wich camps to show
       $this->grocery_crud->columns($this->session->userdata('groups_current_fields_to_show'));
       
       $this->grocery_crud->set_relation_n_n('users', 'users_groups','users', 'user_id', 'id', 'username');
            
       $output = $this->grocery_crud->render();
       
       $this->load_header($output);
       $this->load->view('groups_view.php',$output);
       $this->load->view('include/footer');                          
}      

protected function set_common_columns_name()
{
       //COMMON_COLUMNS                      
       $this->grocery_crud->display_as('name',lang('name'));       
       $this->grocery_crud->display_as('shortName',lang('shortName'));       
       $this->grocery_crud->display_as('description',lang('description'));       
       $this->grocery_crud->display_as('entryDate',lang('entryDate'));       
       $this->grocery_crud->display_as('manualEntryDate',lang('manualEntryDate'));       
       $this->grocery_crud->display_as('last_update',lang('last_update')); 
       $this->grocery_crud->display_as('manualLast update',lang('manual_last_update')); 
       $this->grocery_crud->display_as('creationUserId',lang('creationUserId'));
       $this->grocery_crud->display_as('lastupdateUserId',lang('lastupdateUserId'));  
       $this->grocery_crud->display_as('markedForDeletion',lang('markedForDeletion'));
       $this->grocery_crud->display_as('markedForDeletionDate',lang('markedForDeletionDate'));
}

public function devices() {
	   if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect($this->login_page, 'refresh');
		}
		//CHECK IF USER IS READONLY --> unset add, edit & delete actions
		$readonly_group = $this->config->item('readonly_group');
		if ($this->ion_auth->in_group($readonly_group)) {
			$this->grocery_crud->unset_add();
			$this->grocery_crud->unset_edit();
			$this->grocery_crud->unset_delete();
	   }
       $this->current_table="inventory_object";
       $this->grocery_crud->set_table($this->current_table);       
       
       $this->set_common_columns_name();

       $output = $this->grocery_crud->render();
       
       $this->load_header($output);
       $this->load->view('devices_view.php',$output);
       $this->load->view('include/footer');         
} 	
	
	
	
	
	
	  

}
 
/* End of file main.php */
/* Location: ./application/controllers/main.php */

