<?php
/**
 * Created by PhpStorm.
 * User: Cyber2-PPLN Taipei
 * Date: 5/23/2018
 * Time: 11:19 AM
 */

class VoterManagement extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->library('recaptcha');
//        if (!isset($_SESSION['user_logged'])) {
//            $this->session->set_flashdata("error", "Harap login terlebih dahulu.");
//            redirect(base_url()."auth/login", "refresh");
//        }
    }

    public function search() {
//        if (isset($_SESSION['user_logged'])) {
            if (isset($_POST['search']) || $this->uri->segment(3)) {

//                $param_offset=0;
//                $params = array_slice($this->uri->rsegment_array(), $param_offset);
//                var_dump($params);

                $this->load->model("Voter_m");

                // search by name or passport no
                if ($_POST['searchBy'] == 'name') {
                    $name = $_POST['searchVal'];
                    $passport_no = "";
                } else {
                    $name = "";
                    $passport_no = $_POST['searchVal'];
                }

//                // set array for PAGINATION LIBRARY, and show view data according to page.
                $config = array();
                $config["base_url"] = base_url() . "/voterManagement/search";
                $total_row = $this->Voter_m->record_count($name, $passport_no);
                $config["total_rows"] = $total_row;
                $config["per_page"] = 2000;
                $config['use_page_numbers'] = TRUE;
                $config['num_links'] = $total_row;
                $config['cur_tag_open'] = '&nbsp;<a class="current">';
                $config['cur_tag_close'] = '</a>';
                $config['next_link'] = 'Next';
                $config['prev_link'] = 'Previous';

                $this->pagination->initialize($config);
                if($this->uri->segment(3)){
                    $page = ($this->uri->segment(3)) ;
                    $offset = $page * $config["per_page"];
                }
                else{
                    $page = 0;
                    $offset = 0;
                }

                $data["voters"] = $this->Voter_m->getAllData($config["per_page"], $offset, $name, $passport_no);
                $str_links = $this->pagination->create_links();
                $data["links"] = explode('&nbsp;',$str_links );
                $data["searchBy"] = $_POST['searchBy'];
                $data["searchVal"] = $_POST['searchVal'];
                $data["totalRows"] = $total_row;



//                $this->load->model("Voter_m");
//                $data['voters'] = $this->Voter_m->getAllData($name,$passport_no);

                $this->load->view('searchResult', $data);
            } else {
                $this->load->view('searchVoter');
            }
//        } else {
//            $this->load->view('login');
//        }
    }

    public function register() {
//        if (isset($_SESSION['user_logged'])) {

            if (isset($_POST['register'])) {
                $passport_no = $_POST['passport_no'];

                //check user in database
                $this->load->model("Voter_m");
                $user = $this->Voter_m->exist($passport_no);

                //if user exists
                if (!is_null($user)) {
                    //temporary message
                    $this->session->set_flashdata("error", "Registrasi gagal. Pemilih telah terdaftar!");

                    //redirect to profile page
                    redirect(base_url()."voterManagement/register","refresh");
                } else {
                    $data = array(
                        'nik' => $_POST['nik'],
                        'passport_no' => $_POST['passport_no'],
                        'photo' => $_POST['photo'],
                        'fullname' => $_POST['fullname'],
                        'birthdate' => $_POST['birthdate'],
                        'birthplace' => $_POST['birthplace'],
                        'phone_number' => $_POST['phone_number'],
                        'line_id' => $_POST['line_id'],
                        'email' => $_POST['email'],
                        'gender' => $_POST['gender'],
                        'marital_status' => $_POST['marital_status'],
                        'city' => $_POST['city'],
                        'address' => $_POST['address'],
                        'disability_type' => $_POST['disability_type'],
                        'kpps_type' => $_POST['kpps_type'],
                        'date_created' => date("Y-m-d h:i:sa")
                    );
                    $result = $this->Voter_m->insert($data);
                    if ($result) {
                        $this->session->set_flashdata("success", "Registrasi pemilih berhasil!");
                        redirect(base_url() . "voterManagement/register", "refresh");
                    } else {
                        $this->session->set_flashdata("error", "Registrasi gagal!");
                        redirect(base_url() . "voterManagement/register", "refresh");
                    }

                }
            } else {
                 $data = array(
                    'widget' => $this->recaptcha->getWidget(),
                    'script' => $this->recaptcha->getScriptTag(),
                     );
                $this->load->view('registerVoter',$data);
            }
//        } else {
//            $this->load->view('login');
//        }
    }

    public function edit() {

    }
}