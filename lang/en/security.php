<?php

return [
    'title' => 'Security settings',
    'password' => [
        'heading' => 'Update password',
        'description' => 'Ensure your account is using a long, random password to stay secure',
        'current' => 'Current password',
        'new' => 'New password',
        'confirm' => 'Confirm password',
        'save' => 'Save password',
    ],
    '2fa' => [
        'title' => 'Two-factor authentication',
        'description' => 'Manage your two-factor authentication settings',
        'help_disabled' => 'When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.',
        'help_enabled' => 'You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.',
        'continue' => 'Continue setup',
        'enable' => 'Enable 2FA',
        'disable' => 'Disable 2FA',
    ],
];
