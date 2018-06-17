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
		$this->load->library('upload');
//        if (!isset($_SESSION['user_logged'])) {
//            $this->session->set_flashdata("error", "Harap login terlebih dahulu.");
//            redirect(base_url()."auth/login", "refresh");
//        }
	}

	public function index()
    {
    	redirect(base_url()."voterManagement/search","refresh");
    }

	public function search() {
//        if (isset($_SESSION['user_logged'])) {
		if (isset($_POST['search']) || $this->uri->segment(3)) {

//                $param_offset=0;
//                $params = array_slice($this->uri->rsegment_array(), $param_offset);
//                var_dump($params);

			$this->load->model("Voter_m");

//			search by name or passport no
			if ($_POST['searchBy'] == 'name') {
				$name = $_POST['searchVal'];
				$passport_no = "";
			} else {
				$name = "";
				$passport_no = $_POST['searchVal'];
			}

//          set array for PAGINATION LIBRARY, and show view data according to page.
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

			$this->load->view('layout/header');
			$this->load->view('voter/searchResult', $data);
			$this->load->view('layout/footer');
		} else {
			$this->load->view('layout/header');
			$this->load->view('voter/searchVoter');
			$this->load->view('layout/footer');
		}

	}

	public function register() {
//        if (isset($_SESSION['user_logged'])) {

		if (isset($_POST['register'])) {
			var_dump($_POST);
			$passport_no = $_POST['passport_no'];

			//check user in database
			$this->load->model("Voter_m");
			$user = $this->Voter_m->exist($passport_no);

			$recaptcha = $this->input->post('g-recaptcha-response');
			//if (!empty($recaptcha)) {
			if(true){
				$response = $this->recaptcha->verifyResponse($recaptcha);
				if(true){
					//if (isset($response['success']) and $response['success'] === true) {

					//if user already exists
					if (!is_null($user)) {
						//temporary message
						$this->session->set_flashdata("error", "Registrasi gagal. Pemilih telah terdaftar!");

						//redirect to profile page
						redirect(base_url()."voterManagement/register");
					} else {

						//untuk ketentuan upload
						$config['upload_path'] = './assets/idimages/'; //path folder
						$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
						$config['encrypt_name'] = TRUE; //nama yang terupload nantinya
						$this->upload->initialize($config);

						if(!empty($_FILES['photo']['name']))
						{
							if ($this->upload->do_upload('photo'))
							{
								$gbr = $this->upload->data();
								$gambar=$gbr['file_name']; //Mengambil file name dari gambar yang diupload
								//$judul=strip_tags($this->input->post('judul'));
								$data = array(
									'nik' => $_POST['nik'],
									'passport_no' => $_POST['passport_no'],
									'photo' => $gambar,
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
									'kpps_type' => $_POST['kpps_type']
								);
								if (isset($_POST['register'])) {
									if (!is_null($user)) {
										//temporary message
										$this->session->set_flashdata("error", "Registrasi gagal. Pemilih telah terdaftar!");

										//redirect to profile page
										redirect(base_url()."voterManagement/register","refresh");
									} else {
										$data['date_created'] = date("Y-m-d h:i:sa");
										$result = $this->Voter_m->insert($data);
									}

								} 
								if ($result) {
//									$this->session->set_flashdata("success", "Registrasi pemilih berhasil!");
//									redirect(base_url() . "voterManagement/register", "refresh");
									$this->load->view('layout/header');
									$this->load->view('v_thanks');
									$this->load->view('layout/footer');
								} else {
									$this->session->set_flashdata("error", "Registrasi gagal!");
//									redirect(base_url() . "voterManagement/register", "refresh");
								}
						
							}else{
								echo "Gambar Gagal Upload. Gambar harus bertipe gif|jpg|png|jpeg|bmp";
							}

						}else{
							echo "Gagal, gambar belum di pilih";
						}
					}
					
				}//close captcha dibawah
			}


		} else {
			$data = array(
				'widget' => $this->recaptcha->getWidget(),
				'script' => $this->recaptcha->getScriptTag(),
			);
			if($this->uri->segment(3)){
				$passport_no = $this->uri->segment(3);

				//check user in database
				$this->load->model("Voter_m");
				$user = $this->Voter_m->exist($passport_no);

				$data['user'] = $user;
				$data['status'] = "update";
			}
			$this->load->view('layout/header');
			$this->load->view('voter/registerVoter',$data);
			$this->load->view('layout/footer');
		}
//        } else {
//            $this->load->view('login');
//        }
	}

	public function update() {
		if (isset($_POST['update'])) {
			var_dump($_POST);
			$passport_no = $_POST['passport_no'];
			
			//open database
			$this->load->model("Voter_m");
			$data['date_modified'] = date("Y-m-d h:i:sa");
		

				//untuk ketentuan upload
						$config['upload_path'] = './assets/idimages/'; //path folder
						$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
						$config['encrypt_name'] = TRUE; //nama yang terupload nantinya
						$this->upload->initialize($config);

						if(!empty($_FILES['photo']['name']))
						{
							if ($this->upload->do_upload('photo'))
							{
								$gbr = $this->upload->data();
								$gambar=$gbr['file_name']; //Mengambil file name dari gambar yang diupload
								//$judul=strip_tags($this->input->post('judul'));
								$data['photo'] = $gambar;
							}
						}

							$data = array(
									'nik' => $_POST['nik'],
									'passport_no' => $_POST['passport_no'],
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
									'kpps_type' => $_POST['kpps_type']
								);
			//eksekusi update
			$result = $this->Voter_m->update($data);
			if ($result) {
				$this->session->set_flashdata("success", "Update data pemilih berhasil!");
				redirect(base_url() . "voterManagement", "refresh");
			} else {
				$this->session->set_flashdata("error", "Registrasi gagal!");
//				redirect(base_url() . "voterManagement/register", "refresh");
			}




		}
	}
}