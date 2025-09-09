<?php
/*
* Mail Configuration
* driver: 'log' (writes emails to storage/mails), 'mail' (PHP mail()), 'smtp' (placeholder)
*/

// Mail driver: 'log' for local, 'mail' for basic PHP mail(), 'smtp' for future SMTP integration
$MAIL_DRIVER = 'log';

// Default sender
$MAIL_FROM_ADDRESS = 'noreply@inventra.local';
$MAIL_FROM_NAME = 'Inventra';

// SMTP settings (if/when driver = 'smtp')
$SMTP_HOST = 'smtp.example.com';
$SMTP_PORT = 587;
$SMTP_ENCRYPTION = 'tls'; // 'tls' or 'ssl'
$SMTP_USERNAME = 'your_username';
$SMTP_PASSWORD = 'your_password';

?>


