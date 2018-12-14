<?php

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// Jika ada pesan "REST_Controller not found"
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class User extends REST_Controller {

    // Konfigurasi letak folder untuk upload image
    private $folder_upload = 'uploads_user/';

    function user_get(){
        $get_user = $this->db->query("
            SELECT
               *
            FROM user")->result();
       $this->response(
           array(
               "status" => "success",
               "result" => $get_user
           )
       );
    }

    function user_post() {
        $action  = $this->post('action');
        $data_user = array(
                     'idUser'       => $this->post('idUser'),
                     'nama'         => $this->post('nama'),
                     'umur'         => $this->post('umur'),
                     'jenisKulit'   => $this->post('jenisKulit'),
                     'warnaKulit'   => $this->post('warnaKulit'),
                     'username'     => $this->post('username'),
                     'password'     => $this->post('password'),
                     'photo_url_user'    => $this->post('photo_url_user')
                 );

        switch ($action) {
            case "insert" :
                $this->insertUser($data_user);
                break;
            
            case 'update':
                $this->updateUser($data_user);
                break;
            
            case "delete":
                $this->deleteUser($data_user);
                break;
            
            default:
                $this->response(
                    array(
                        "status"  =>"failed",
                        "message" => "action harus diisi"
                    )
                );
                break;
        }
    }

    function insertUser($data_user){

     // Cek validasi
     if (empty($data_user['nama']) || empty($data_user['username']) || empty($data_user['password']) ){
         $this->response(
             array(
                 "status" => "failed",
                 "message" => "Nama / username / password harus diisi"
             )
         );
     } else {

         $data_user['photo_url_user'] = $this->uploadPhoto();

         $do_insert = $this->db->insert('user', $data_user);
        
         if ($do_insert){
             $this->response(
                 array(
                     "status" => "success",
                     "result" => array($data_user),
                     "message" => $do_insert
                 )
             );
            }
     }
    }

    function updateUser($data_user){

     // Cek validasi
     if (empty($data_user['nama']) || empty($data_user['username']) || empty($data_user['password']) ){
        $this->response(
             array(
                 "status" => "failed",
                 "message" => "Nama / username / password harus diisi"
             )
        );
     } else {
         // Cek apakah ada di database
         $get_user_baseID = $this->db->query("
             SELECT 1
             FROM user
             WHERE idUser =  {$data_user['idUser']}")->num_rows();

         if($get_user_baseID === 0){
             // Jika tidak ada
             $this->response(
                 array(
                     "status"  => "failed",
                     "message" => "ID User tidak ditemukan"
                 )
             );
         } else {
             // Jika ada
             $data_user['photo_url_user'] = $this->uploadPhoto();

             if ($data_user['photo_url_user']){
                 // Jika upload foto berhasil, eksekusi update
                 $update = $this->db->query("
                     UPDATE user SET
                         nama           = '{$data_user['nama']}',
                         umur           = '{$data_user['umur']}',
                         jenisKulit     = '{$data_user['jenisKulit']}',
                         warnaKulit     = '{$data_user['warnaKulit']}',
                         username       = '{$data_user['username']}',
                         password       = '{$data_user['password']}',
                         photo_url_user = '{$data_user['photo_url_user']}'
                     WHERE idUser = '{$data_user['idUser']}'");

             } else {
                 // Jika foto kosong atau upload foto tidak berhasil, eksekusi update
                    $update = $this->db->query("
                        UPDATE user
                        SET
                            nama        = '{$data_user['nama']}',
                            umur        = '{$data_user['umur']}',
                            jenisKulit  = '{$data_user['jenisKulit']}',
                            warnaKulit  = '{$data_user['warnaKulit']}',
                            username    = '{$data_user['username']}',
                            password    = '{$data_user['password']}'
                        WHERE idUser = {$data_user['idUser']}"
                    );
             }
            
             if ($update){
                 $this->response(
                     array(
                         "status"    => "success",
                         "result"    => array($data_user),
                         "message"   => $update
                     )
                 );
                }
         }   
     }
    }

    function deleteUser($data_user){

        if (empty($data_user['idUser'])){
         $this->response(
             array(
                 "status" => "failed",
                 "message" => "ID User harus diisi"
             )
         );
     } else {
         // Cek apakah ada di database
         $get_user_baseID =$this->db->query("
             SELECT 1
             FROM user
             WHERE idUser = {$data_user['idUser']}")->num_rows();

         if($get_user_baseID > 0){
             
             $get_photo_url_user =$this->db->query("
             SELECT photo_url_user
             FROM user
             WHERE idUser = {$data_user['idUser']}")->result();
         
                if(!empty($get_photo_url_user)){

                    // Dapatkan nama file
                    $photo_nama_file = basename($get_photo_url_user[0]->photo_url_user);
                    // Dapatkan letak file di folder upload
                    $photo_lokasi_file = realpath(FCPATH . $this->folder_upload . $photo_nama_file);
                    
                    // Jika file ada, hapus
                    if(file_exists($photo_lokasi_file)) {
                        // Hapus file
                     unlink($photo_lokasi_file);
                 }

                 $this->db->query("
                     DELETE FROM user
                     WHERE idUser = {$data_user['idUser']}");
                 $this->response(
                     array(
                         "status" => "success",
                         "message" => "Data ID = " .$data_user['idUser']. " berhasil dihapus"
                     )
                 );
             }
         
            } else {
                $this->response(
                    array(
                        "status" => "failed",
                        "message" => "ID User tidak ditemukan"
                    )
                );
            }
     }
    }

    function uploadPhoto() {

        // Apakah user upload gambar?
        if ( isset($_FILES['photo_url']) && $_FILES['photo_url']['size'] > 0 ){

            // Foto disimpan di android-api/uploads
            $config['upload_path'] = realpath(FCPATH . $this->folder_upload);
            $config['allowed_types'] = 'jpg|png';

         // Load library upload & helper
         $this->load->library('upload', $config);
         $this->load->helper('url');

         // Apakah file berhasil diupload?
         if ( $this->upload->do_upload('photo_url')) {

               // Berhasil, simpan nama file-nya
               // URL image yang disimpan adalah http://localhost/android-api/uploads/namafile
             $img_data = $this->upload->data();
             $post_image = $this->folder_upload .$img_data['file_name'];

         } else {

             // Upload gagal, beri nama image dengan errornya
             // Ini bodoh, tapi efektif
             $post_image = $this->upload->display_errors();
             
         }
     } else {
         // Tidak ada file yang di-upload, kosongkan nama image-nya
         $post_image = '';
     }

     return $post_image;
    }

    function login_post()
    {

        $username = $this->post('username');
        $password = $this->post('password');

        // Validasi
        $this->db->where('username', $username);
        $this->db->where('password', $password);

        $result = $this->db->get('user');

        if($result->num_rows() === 1){
            // Jika ada
                $this->response(
                    array(
                        "status"  => "success", 
                        "result" => $result->row(0)->idUser,
                        "message" => "User ditemukan"
                    )
                );
        } else {
            // Jika tidak ada
                $this->response(
                    array(
                        "status"  => "failed", 
                        "message" => "Username atau password salah"
                    )
                );
        }
    }

}
