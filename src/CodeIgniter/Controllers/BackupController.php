<?php

namespace Basantsd\Backup\CodeIgniter\Controllers;

use Basantsd\Backup\Backup;

class BackupController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function daily()
    {
        $token = $this->input->get_request_header('X-Backup-Token');
        if ($token !== getenv('BACKUP_TOKEN')) {
            show_error('Unauthorized', 401);
        }

        $backup = new Backup();
        $backup->handle();
        echo "Backup has been sent to your email.";
    }
}
