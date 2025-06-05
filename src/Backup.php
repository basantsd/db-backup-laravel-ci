<?php

namespace Basantsd\Backup;

use Exception;

class Backup
{
    public function handle()
    {
        try {
            $backupFile = $this->createBackup();

            if (class_exists('\Illuminate\Support\Facades\Mail')) {
                $this->sendBackupViaEmailLaravel($backupFile);
            } elseif (class_exists('\CI_Controller')) {
                $this->sendBackupViaEmailCodeIgniter($backupFile);
            } else {
                throw new Exception('Unsupported framework');
            }
        } catch (Exception $e) {
            // Log the exception details
            error_log('Error during backup or email sending: ' . $e->getMessage());
        } finally {
            // Ensure the backup file is removed even if there are exceptions
            if (isset($backupFile) && file_exists($backupFile)) {
                unlink($backupFile); // Remove file after sending or on error
            }
        }
    }

    protected function createBackup()
    {
        try {
            $project_name = str_replace(' ', '-', strtolower(config('backup.project_name')));
            $baseName = $project_name . '_backup_' . date('Y-m-d_H-i-s');
            $rawFilePath = sys_get_temp_dir() . '/' . $baseName . '.sql';
            $filePath = $rawFilePath . '.gz';

            $database = config('database.connections.' . config('database.default'));

            switch ($database['driver']) {
                case 'mysql':
                    $command = sprintf(
                        'mysqldump -h%s -u%s %s %s | gzip > %s',
                        escapeshellarg($database['host']),
                        escapeshellarg($database['username']),
                        $database['password'] ? '-p' . escapeshellarg($database['password']) : '',
                        escapeshellarg($database['database']),
                        escapeshellarg($filePath)
                    );
                    break;

                case 'pgsql':
                    $command = sprintf(
                        'PGPASSWORD=%s pg_dump -h %s -U %s %s | gzip > %s',
                        escapeshellarg($database['password']),
                        escapeshellarg($database['host']),
                        escapeshellarg($database['username']),
                        escapeshellarg($database['database']),
                        escapeshellarg($filePath)
                    );
                    break;

                case 'sqlsrv':
                    $command = sprintf(
                        'sqlcmd -S %s -U %s -P %s -Q "BACKUP DATABASE %s TO DISK=\'%s\'" && gzip %s',
                        escapeshellarg($database['host']),
                        escapeshellarg($database['username']),
                        escapeshellarg($database['password']),
                        escapeshellarg($database['database']),
                        escapeshellarg($rawFilePath),
                        escapeshellarg($rawFilePath)
                    );
                    break;

                case 'sqlite':
                    $command = sprintf(
                        'sqlite3 %s .dump | gzip > %s',
                        escapeshellarg(database_path($database['database'])),
                        escapeshellarg($filePath)
                    );
                    break;

                default:
                    throw new Exception('Unsupported database driver: ' . $database['driver']);
            }

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new Exception('Backup command failed: ' . implode("\n", $output));
            }

            return $filePath;
        } catch (Exception $e) {
            // Log the exception details
            error_log('Error during backup creation: ' . $e->getMessage());
            throw $e; // Re-throw the exception to handle it in the calling function
        }
    }

    protected function sendBackupViaEmailLaravel($filePath)
    {
        try {
            if (empty($filePath) || !file_exists($filePath)) {
                throw new ValueError('Path cannot be empty or non-existent');
            }

            $to = config('backup.email');
            $project_name = config('backup.project_name');

            $subject = $project_name . ' Database Backup | ' . date('Y-m-d');
            $message = $project_name . ' Database backup attached. Date: ' . date('Y-m-d_H-i-s');

            $attach = true;
            $maxSize = config('backup.max_attachment_size', 25 * 1024 * 1024);
            if (filesize($filePath) > $maxSize) {
                $attach = false;
                $storageDir = function_exists('storage_path') ? storage_path('app/backups') : sys_get_temp_dir() . '/backups';
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0777, true);
                }
                $destination = rtrim($storageDir, '/') . '/' . basename($filePath);
                copy($filePath, $destination);
                $message .= "\nBackup stored at: {$destination}";
            }

            // Use Laravel's Mail facade to send the email
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($to, $subject, $filePath, $attach) {
                $mail->to($to)
                    ->subject($subject);
                if ($attach) {
                    $mail->attach($filePath);
                }
            });

        } catch (Exception $e) {
            // Log the exception details
            error_log('Error during backup email sending: ' . $e->getMessage());
            throw $e; // Re-throw the exception to handle it in the calling function
        }
    }

    protected function sendBackupViaEmailCodeIgniter($filePath)
    {
        try {
            if (empty($filePath) || !file_exists($filePath)) {
                throw new ValueError('Path cannot be empty or non-existent');
            }

            $to = config_item('backup_email');
            $project_name = config_item('backup_project_name');

            $subject = $project_name . ' Database Backup | ' . date('Y-m-d');
            $message = $project_name . ' Database backup attached. Date: ' . date('Y-m-d_H-i-s');

            // Load the CodeIgniter email library
            $CI =& get_instance();
            $CI->load->library('email');

            $attach = true;
            $maxSize = getenv('BACKUP_MAX_ATTACHMENT_SIZE') ?: 25 * 1024 * 1024;
            if (filesize($filePath) > $maxSize) {
                $attach = false;
                $storageDir = defined('APPPATH') ? APPPATH . 'backups' : sys_get_temp_dir() . '/backups';
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0777, true);
                }
                $destination = rtrim($storageDir, '/') . '/' . basename($filePath);
                copy($filePath, $destination);
                $message .= "\nBackup stored at: {$destination}";
            }

            $CI->email->from('no-reply@example.com', 'Backup System');
            $CI->email->to($to);
            $CI->email->subject($subject);
            $CI->email->message($message);
            if ($attach) {
                $CI->email->attach($filePath);
            }

            if (!$CI->email->send()) {
                throw new Exception('Mail error: ' . $CI->email->print_debugger());
            }

        } catch (Exception $e) {
            // Log the exception details
            error_log('Error during backup email sending: ' . $e->getMessage());
            throw $e; // Re-throw the exception to handle it in the calling function
        }
    }
}
