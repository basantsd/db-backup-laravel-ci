<?php

namespace Basantsd\Backup;

use Exception;

class Backup
{
    public function handle()
    {
        try {
            $backupFile = $this->createBackup();
            $this->sendBackupViaEmail($backupFile);
        } catch (Exception $e) {
            // Log the exception details
            error_log('Error during backup or email sending: ' . $e->getMessage());
            // Re-throw the exception if necessary or handle it as per your application's requirement
        } finally {
            // Ensure the backup file is removed even if there are exceptions
            if (isset($backupFile) && file_exists($backupFile)) {
                unlink($backupFile); // Remove file after sending or on error
            }
        }
    }

    protected function createBackup()
    {
        $fileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = sys_get_temp_dir() . '/' . $fileName;

        $database = config('database.connections.' . config('database.default'));

        switch ($database['driver']) {
            case 'mysql':
                $command = sprintf(
                    'mysqldump -h%s -u%s -p%s %s > %s',
                    $database['host'],
                    $database['username'],
                    $database['password'],
                    $database['database'],
                    $filePath
                );
                break;

            case 'pgsql':
                $command = sprintf(
                    'PGPASSWORD=%s pg_dump -h %s -U %s %s > %s',
                    $database['password'],
                    $database['host'],
                    $database['username'],
                    $database['database'],
                    $filePath
                );
                break;

            case 'sqlsrv':
                $command = sprintf(
                    'sqlcmd -S %s -U %s -P %s -Q "BACKUP DATABASE %s TO DISK=\'%s\'"',
                    $database['host'],
                    $database['username'],
                    $database['password'],
                    $database['database'],
                    $filePath
                );
                break;

            case 'sqlite':
                $command = sprintf(
                    'sqlite3 %s .dump > %s',
                    database_path($database['database']),
                    $filePath
                );
                break;

            default:
                throw new Exception('Unsupported database driver: ' . $database['driver']);
        }

        exec($command);

        return $filePath;
    }

    protected function sendBackupViaEmail($filePath)
    {
        $to = config('backup.email');
        $subject = 'Daily Database Backup';
        $message = 'Database backup attached.';
        $headers = "From: no-reply@example.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";

        $content = chunk_split(base64_encode(file_get_contents($filePath)));

        $body = "--boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($message));
        $body .= "--boundary\r\n";
        $body .= "Content-Type: application/octet-stream; name=\"" . basename($filePath) . "\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--boundary--";

        mail($to, $subject, $body, $headers);
    }
}