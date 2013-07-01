<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Qr extends CI_Controller {
 
    function __construct()
    {
        parent::__construct();
 
        /* Standard Libraries of codeigniter are required */
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('ciqrcode');        
        $this->lang->load('inventory');
        
        $this->load->helper('language');       
 
    }
	
    public function index()
    {
		$this->generate();
    }
    
    public function generate($id)
    {
       if ($id != "") {
                 
       $view_url=base_url("/index.php/main/index/view/".$id);     
       $params['data'] = $view_url;
       $params['level'] = 'H';
       $params['size'] = 10;
       $params['savename'] = FCPATH."/assets/uploads/qrcodes/test.png";
       $this->ciqrcode->generate($params);
       
       echo '<img src="'.base_url().'/assets/uploads/qrcodes/test.png" />';       
       }
       else {
        show_error(lang('error_message_database_id_unespecified'));
       }
    }
        
}
 
/* End of file qr.php */
/* Location: ./application/controllers/qr.php */

