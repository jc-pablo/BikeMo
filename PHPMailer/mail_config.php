<?php
return [
    'host' => getenv('BIKEMO_SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io',
    'port' => (int) (getenv('BIKEMO_SMTP_PORT') ?: 2525),
    'username' => getenv('BIKEMO_SMTP_USERNAME') ?: 'fe515b24ea025f',
    'password' => getenv('BIKEMO_SMTP_PASSWORD') ?: '757bb0c751c003',
    'from_email' => getenv('BIKEMO_FROM_EMAIL') ?: 'bikemoph@gmail.com',
    'from_name' => 'BikeMo'
];
